<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Services\RaceCapacityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RaceCapacityTest extends TestCase
{
    use RefreshDatabase;

    public function test_zero_capacity_means_unlimited_registrations(): void
    {
        $availability = app(RaceCapacityService::class)->availability();

        $this->assertTrue($availability['Trail degli Eroi 46K']['is_unlimited']);
        $this->assertFalse($availability['Trail degli Eroi 46K']['is_sold_out']);
        $this->assertNull($availability['Trail degli Eroi 46K']['remaining']);
    }

    public function test_active_and_pending_tickets_fill_the_race_capacity(): void
    {
        Setting::where('key', 'race_capacity_trail_46k')->update(['value' => '2']);

        $this->createTicket('active', 'paid');
        $this->createTicket('pending', 'pending');
        $this->createTicket('pending', 'cancelled');

        $race = app(RaceCapacityService::class)
            ->availability()['Trail degli Eroi 46K'];

        $this->assertSame(2, $race['occupied']);
        $this->assertSame(0, $race['remaining']);
        $this->assertTrue($race['is_sold_out']);
    }

    public function test_capacity_is_rechecked_before_reserving_the_last_places(): void
    {
        Setting::where('key', 'race_capacity_trail_46k')->update(['value' => '1']);
        $this->createTicket('active', 'paid');

        $this->expectException(ValidationException::class);

        DB::transaction(function () {
            app(RaceCapacityService::class)->assertCanReserve(['Trail degli Eroi 46K']);
        });
    }

    public function test_public_availability_endpoint_exposes_remaining_places(): void
    {
        Setting::where('key', 'race_capacity_sky_27k')->update(['value' => '25']);
        Setting::where('key', 'race_price_sky_27k')->update(['value' => '35.50']);

        $this->getJson('/api/race-availability')
            ->assertOk()
            ->assertJsonPath('races.Sky degli Eroi 27K.capacity', 25)
            ->assertJsonPath('races.Sky degli Eroi 27K.price', 35.5)
            ->assertJsonPath('races.Sky degli Eroi 27K.remaining', 25)
            ->assertJsonPath('races.Sky degli Eroi 27K.is_sold_out', false);
    }

    public function test_sold_out_registration_automatically_enters_waitlist_without_payment(): void
    {
        Setting::where('key', 'race_capacity_trail_46k')->update(['value' => '1']);
        $this->createTicket('active', 'paid');

        $response = $this->post('/checkout', $this->checkoutPayload('mario@example.com'));

        $order = Order::where('status', 'waitlist')->firstOrFail();
        $response->assertRedirect(route('waitlist.confirmation', $order->group_code));
        $this->assertSame('waitlist', $order->payment_method);
        $this->assertEquals(0, $order->total_amount);
        $this->assertDatabaseHas('tickets', [
            'order_id' => $order->id,
            'email' => 'mario@example.com',
            'status' => 'waitlist',
            'price_paid' => 0,
        ]);
    }

    public function test_waitlist_positions_follow_chronological_order(): void
    {
        Setting::where('key', 'race_capacity_trail_46k')->update(['value' => '1']);
        $this->createTicket('active', 'paid');

        $this->post('/checkout', $this->checkoutPayload('first@example.com'));
        $this->post('/checkout', $this->checkoutPayload('second@example.com'));

        $secondOrder = Order::where('status', 'waitlist')->latest('id')->firstOrFail();

        $this->get(route('waitlist.confirmation', $secondOrder->group_code))
            ->assertOk()
            ->assertSee('Posizione attuale')
            ->assertSee('#2');
    }

    public function test_sold_out_race_is_presented_as_waitlist_in_registration_form(): void
    {
        Setting::where('key', 'race_capacity_trail_46k')->update(['value' => '1']);
        $this->createTicket('active', 'paid');

        $this->get('/iscrizione')
            ->assertOk()
            ->assertSee('Lista d’attesa aperta')
            ->assertSee('ENTRA IN LISTA');
    }

    private function createTicket(string $ticketStatus, string $orderStatus): void
    {
        $order = Order::create([
            'group_code' => 'TEST_'.uniqid(),
            'total_amount' => 28,
            'payment_method' => 'test',
            'status' => $orderStatus,
        ]);

        $order->tickets()->create([
            'price_paid' => 28,
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'route_choice' => 'Trail degli Eroi 46K',
            'dob' => '1990-01-01',
            'birth_place' => 'Vicenza',
            'residence_address' => 'Via Roma 1',
            'city' => 'Vicenza',
            'zip_code' => '36100',
            'province' => 'Vicenza',
            'region' => 'Veneto',
            'country' => 'Italia',
            'nationality' => 'IT',
            'email' => uniqid().'@example.com',
            'phone' => '0000000000',
            'tshirt_size' => 'M',
            'shuttle_needed' => false,
            'celiac' => false,
            'status' => $ticketStatus,
            'payment_tag' => 'Test',
        ]);
    }

    private function checkoutPayload(string $email): array
    {
        return [
            'payment_method' => 'stripe',
            'is_sports_association' => 0,
            'is_mutua_member' => 0,
            'tickets' => [[
                'first_name' => 'Mario',
                'last_name' => 'Rossi',
                'route_choice' => 'Trail degli Eroi 46K',
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
                'email' => $email,
                'phone' => '3330000000',
                'tshirt_size' => 'M',
                'shuttle_needed' => 0,
                'celiac' => 0,
            ]],
        ];
    }
}
