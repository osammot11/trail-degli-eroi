<?php

namespace Tests\Feature;

use App\Jobs\SendRaceChangeEmail;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TicketRouteChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_route_with_capacity_price_history_and_notification(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());
        Setting::where('key', 'race_price_trail_46k')->update(['value' => '28']);
        Setting::where('key', 'race_price_sky_27k')->update(['value' => '35']);
        $ticket = $this->createTicket('Trail degli Eroi 46K', 28);

        $response = $this->post(route('admin.update_ticket', $ticket->id), $this->updatePayload(
            $ticket,
            'Sky degli Eroi 27K',
        ));

        $response->assertRedirect(route('admin.edit_ticket', $ticket->id));
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'route_choice' => 'Sky degli Eroi 27K',
        ]);
        $this->assertDatabaseHas('ticket_changes', [
            'ticket_id' => $ticket->id,
            'type' => 'route_change',
            'from_value' => 'Trail degli Eroi 46K',
            'to_value' => 'Sky degli Eroi 27K',
            'amount_difference' => 7,
        ]);
        Queue::assertPushed(SendRaceChangeEmail::class, fn ($job) => $job->change->ticket_id === $ticket->id);

        $this->get(route('admin.edit_ticket', $ticket->id))
            ->assertOk()
            ->assertSee('Storico operazioni')
            ->assertSee('Differenza: € 7,00');
    }

    public function test_route_change_is_rejected_when_destination_is_sold_out(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());
        Setting::where('key', 'race_capacity_sky_27k')->update(['value' => '1']);
        $ticket = $this->createTicket('Trail degli Eroi 46K', 28);
        $this->createTicket('Sky degli Eroi 27K', 28);

        $response = $this->from(route('admin.edit_ticket', $ticket->id))
            ->post(route('admin.update_ticket', $ticket->id), $this->updatePayload(
                $ticket,
                'Sky degli Eroi 27K',
            ));

        $response->assertRedirect(route('admin.edit_ticket', $ticket->id));
        $response->assertSessionHasErrors('tickets');
        $this->assertSame('Trail degli Eroi 46K', $ticket->fresh()->route_choice);
        $this->assertDatabaseCount('ticket_changes', 0);
        Queue::assertNothingPushed();
    }

    private function createTicket(string $route, float $price): Ticket
    {
        $order = Order::create([
            'group_code' => 'TEST_'.uniqid(),
            'total_amount' => $price,
            'payment_method' => 'manual',
            'status' => 'paid',
        ]);

        return $order->tickets()->create([
            'unique_ticket_code' => 'TK_'.strtoupper(uniqid()),
            'price_paid' => $price,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'route_choice' => $route,
            'dob' => '1990-01-01',
            'birth_place' => 'Vicenza',
            'residence_address' => 'Via Roma 1',
            'city' => 'Vicenza',
            'zip_code' => '36100',
            'province' => 'Vicenza',
            'region' => 'Veneto',
            'country' => 'Italia',
            'nationality' => 'IT',
            'codice_fiscale' => 'RSSMRA90A01L840A',
            'email' => uniqid().'@example.com',
            'phone' => '3330000000',
            'tshirt_size' => 'M',
            'shuttle_needed' => false,
            'celiac' => false,
            'status' => 'active',
            'payment_tag' => 'Test',
        ]);
    }

    private function updatePayload(Ticket $ticket, string $route): array
    {
        return [
            'first_name' => $ticket->first_name,
            'last_name' => $ticket->last_name,
            'email' => $ticket->email,
            'phone' => $ticket->phone,
            'codice_fiscale' => $ticket->codice_fiscale,
            'tshirt_size' => $ticket->tshirt_size,
            'residence_address' => $ticket->residence_address,
            'city' => $ticket->city,
            'zip_code' => $ticket->zip_code,
            'tag_1' => $ticket->tag_1,
            'tag_2' => $ticket->tag_2,
            'route_choice' => $route,
        ];
    }
}
