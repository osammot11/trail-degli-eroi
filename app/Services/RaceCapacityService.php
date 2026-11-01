<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RaceCapacityService
{
    public const RACES = [
        'Trail degli Eroi 46K' => [
            'label' => 'Trail degli Eroi',
            'distance' => '46 km / 2.500 D+',
            'capacity_key' => 'race_capacity_trail_46k',
            'price_key' => 'race_price_trail_46k',
        ],
        'Sky degli Eroi 27K' => [
            'label' => 'Sky degli Eroi',
            'distance' => '27 km / 1.700 D+',
            'capacity_key' => 'race_capacity_sky_27k',
            'price_key' => 'race_price_sky_27k',
        ],
        'Downhill degli Eroi 12K' => [
            'label' => 'Downhill degli Eroi',
            'distance' => '12 km / 1.500 D-',
            'capacity_key' => 'race_capacity_downhill_12k',
            'price_key' => 'race_price_downhill_12k',
        ],
        'Santa Felicita 14K' => [
            'label' => 'Santa Felicita',
            'distance' => '14 km / 800 D+',
            'capacity_key' => 'race_capacity_santa_felicita_14k',
            'price_key' => 'race_price_santa_felicita_14k',
        ],
    ];

    public function routeValues(): array
    {
        return array_keys(self::RACES);
    }

    public function availability(): array
    {
        $settings = Setting::query()
            ->whereIn('key', array_merge($this->capacityKeys(), $this->priceKeys(), ['ticket_price']))
            ->pluck('value', 'key');

        $reserved = $this->reservedCounts();

        return collect(self::RACES)->mapWithKeys(function (array $race, string $route) use ($settings, $reserved) {
            $capacity = max(0, (int) ($settings[$race['capacity_key']] ?? 0));
            $price = max(0, (float) ($settings[$race['price_key']] ?? $settings['ticket_price'] ?? 28));
            $occupied = (int) ($reserved[$route] ?? 0);
            $isUnlimited = $capacity === 0;
            $remaining = $isUnlimited ? null : max(0, $capacity - $occupied);

            return [$route => array_merge($race, [
                'route' => $route,
                'capacity' => $capacity,
                'price' => $price,
                'occupied' => $occupied,
                'remaining' => $remaining,
                'is_unlimited' => $isUnlimited,
                'is_sold_out' => ! $isUnlimited && $remaining === 0,
                'percentage' => $isUnlimited || $capacity === 0
                    ? 0
                    : min(100, (int) round(($occupied / $capacity) * 100)),
            ])];
        })->all();
    }

    /**
     * Must be called inside a database transaction. The settings rows are
     * locked so two simultaneous checkouts cannot take the same final place.
     */
    public function assertCanReserve(array $routeChoices): void
    {
        $requested = array_count_values($routeChoices);
        $availability = $this->availabilityForReservation($routeChoices);

        foreach ($requested as $route => $quantity) {
            $race = $availability[$route] ?? null;

            if (! $race || $race['is_unlimited']) {
                continue;
            }

            if ($quantity > $race['remaining']) {
                $message = $race['remaining'] === 0
                    ? "{$race['label']} è sold out."
                    : "Per {$race['label']} restano {$race['remaining']} posti, ma ne hai richiesti {$quantity}.";

                throw ValidationException::withMessages([
                    'tickets' => $message.' Aggiorna i partecipanti e riprova.',
                ]);
            }
        }
    }

    public function canReserve(array $routeChoices, bool $lock = false): bool
    {
        $availability = $lock
            ? $this->availabilityForReservation($routeChoices)
            : $this->availability();

        foreach (array_count_values($routeChoices) as $route => $quantity) {
            $race = $availability[$route] ?? null;

            if (! $race || (! $race['is_unlimited'] && $quantity > $race['remaining'])) {
                return false;
            }
        }

        return true;
    }

    public function capacityKeys(): array
    {
        return array_column(self::RACES, 'capacity_key');
    }

    public function priceKeys(): array
    {
        return array_column(self::RACES, 'price_key');
    }

    public function priceForRoute(string $route): float
    {
        $race = self::RACES[$route] ?? null;
        $defaultPrice = (float) (Setting::where('key', 'ticket_price')->value('value') ?? 28);

        if (! $race) {
            return $defaultPrice;
        }

        return max(0, (float) (Setting::where('key', $race['price_key'])->value('value') ?? $defaultPrice));
    }

    private function reservedCounts(): array
    {
        return Ticket::query()
            ->select('route_choice', DB::raw('COUNT(*) as aggregate'))
            ->whereIn('status', ['pending', 'active', 'in-approvazione'])
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', ['payment_error', 'cancelled', 'canceled']);
            })
            ->groupBy('route_choice')
            ->pluck('aggregate', 'route_choice')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private function availabilityForReservation(array $routeChoices): array
    {
        Setting::query()
            ->whereIn('key', collect($routeChoices)
                ->map(fn (string $route) => self::RACES[$route]['capacity_key'] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all())
            ->lockForUpdate()
            ->get();

        return $this->availability();
    }
}
