<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use App\Services\OrderPaymentFinalizer;
use App\Services\RaceCapacityService;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class CheckoutController extends Controller
{
    public function store(Request $request, RaceCapacityService $raceCapacity)
    {
        // 1. Validazione massiccia
        $validatedData = $request->validate([
            'payment_method' => 'required|in:stripe,paypal',
            'is_sports_association' => 'boolean',
            'association_name' => 'required_if:is_sports_association,1|nullable|string|max:255',
            'is_mutua_member' => 'boolean', 
            'tickets' => 'required|array|min:1',
            'tickets.*.first_name' => 'required|string|max:255',
            'tickets.*.last_name' => 'required|string|max:255',
            'tickets.*.codice_fiscale' => 'nullable|string|max:16', 
            'tickets.*.route_choice' => ['required', Rule::in($raceCapacity->routeValues())],
            'tickets.*.dob' => 'required|date',
            'tickets.*.birth_place' => 'required|string|max:255',
            'tickets.*.residence_address' => 'required|string|max:255',
            'tickets.*.city' => 'required|string|max:255',
            'tickets.*.zip_code' => 'required|string|max:20',
            'tickets.*.province' => 'required|string|max:255',
            'tickets.*.region' => 'required|string|max:255',
            'tickets.*.country' => 'required|string|max:255',
            'tickets.*.nationality' => 'required|string|max:2',
            'tickets.*.email' => 'required|email',
            'tickets.*.phone' => 'required|string|max:20',
            'tickets.*.tshirt_size' => 'required|in:XS,S,M,L,XL,XXL',
            'tickets.*.shuttle_needed' => 'required|boolean',
            'tickets.*.celiac' => 'required|boolean',
        ]);

        $routeChoices = array_column($validatedData['tickets'], 'route_choice');
        $distinctRoutes = array_values(array_unique($routeChoices));
        $requestWaitlist = ! $raceCapacity->canReserve($routeChoices);

        if ($requestWaitlist && count($distinctRoutes) > 1) {
            return back()
                ->withInput()
                ->withErrors(['tickets' => 'La richiesta contiene distanze disponibili e distanze in lista d’attesa. Invia due richieste separate.']);
        }

        // 2. Calcolo del prezzo
        // Leggiamo il prezzo dal database. Se non dovesse esistere, usiamo 28.00 come paracadute.
        $basePrice = \App\Models\Setting::where('key', 'ticket_price')->value('value') ?? 28.00;
        $discountAmount = 0;
        $appliedDiscountLabel = null;

        // Prezzo standard per i biglietti paganti
        $finalPricePerTicket = max(0, $basePrice - $discountAmount);

        // --- LOGICA PROMO GRUPPI (Ogni 11 biglietti, 1 è gratis) ---
        $totalTickets = count($validatedData['tickets']);
        $freeTicketsCount = floor($totalTickets / 11);
        $paidTicketsCount = $totalTickets - $freeTicketsCount;

        // Il totale dell'ordine si calcola ESCLUSIVAMENTE sui biglietti paganti
        $totalAmount = $paidTicketsCount * $finalPricePerTicket;

        if (! $requestWaitlist && $validatedData['payment_method'] === 'stripe' && (blank(config('services.stripe.secret')) || blank(config('services.stripe.key')))) {
            return back()
                ->withInput()
                ->withErrors(['payment_method' => 'Stripe non è configurato: controlla STRIPE_SECRET e STRIPE_KEY nel file .env oppure la configurazione Laravel in cache.']);
        }

        // Aggiungiamo l'etichetta se è scattata la promo
        if ($freeTicketsCount > 0) {
            $promoLabel = "PROMO_11_GRATIS";
            $appliedDiscountLabel = $appliedDiscountLabel ? $appliedDiscountLabel . ' + ' . $promoLabel : $promoLabel;
        }

        // 3. Creazione Ordine e Biglietti
        $isWaitlist = false;
        $order = DB::transaction(function () use ($request, $validatedData, $appliedDiscountLabel, $totalAmount, $finalPricePerTicket, $raceCapacity, $routeChoices, $distinctRoutes, $requestWaitlist, &$isWaitlist) {
            $isWaitlist = $requestWaitlist || ! $raceCapacity->canReserve($routeChoices, lock: true);

            if ($isWaitlist && count($distinctRoutes) > 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'tickets' => 'Nel frattempo una delle distanze selezionate è diventata sold out. Invia separatamente le richieste per la lista d’attesa.',
                ]);
            }

            $order = Order::create([
                'group_code' => ($isWaitlist ? 'WAIT_' : 'WFU_') . strtoupper(\Illuminate\Support\Str::random(10)),
                'total_amount' => $isWaitlist ? 0 : $totalAmount,
                'payment_method' => $isWaitlist ? 'waitlist' : $validatedData['payment_method'],
                'is_sports_association' => $request->has('is_sports_association'),
                'association_name' => $validatedData['association_name'] ?? null,
                'discount_code' => $appliedDiscountLabel,
                'status' => $isWaitlist ? 'waitlist' : 'pending',
            ]);

            foreach ($validatedData['tickets'] as $index => $data) {
                // L'undicesimo biglietto (indice 10, 21, 32...) ha prezzo 0€
                $isFreeTicket = (($index + 1) % 11 == 0);
                $ticketPrice = $isFreeTicket ? 0 : $finalPricePerTicket;

                $order->tickets()->create(array_merge($data, [
                    'unique_ticket_code' => null,
                    'price_paid' => $isWaitlist ? 0 : $ticketPrice,
                    'payment_tag' => $isWaitlist ? 'Lista d’attesa' : ucfirst($validatedData['payment_method']),
                    'status' => $isWaitlist ? 'waitlist' : 'pending',
                ]));
            }

            return $order;
        });

        if ($isWaitlist) {
            return redirect()->route('waitlist.confirmation', ['order_code' => $order->group_code]);
        }

        // 5. Redirect Pagamento
        if ($totalAmount == 0) {
            return redirect()->route('checkout.success', ['order_code' => $order->group_code]);
        }

        try {
            return ($validatedData['payment_method'] === 'stripe')
                ? $this->processStripePayment($order)
                : $this->processPaypalPayment($order);
        } catch (Throwable $e) {
            $order->update(['status' => 'payment_error']);
            Log::error('Errore avvio pagamento', [
                'order_id' => $order->id,
                'group_code' => $order->group_code,
                'payment_method' => $order->payment_method,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['payment_method' => 'Non siamo riusciti ad avviare il pagamento. Controlla la configurazione del gateway e riprova.']);
        }
    }

    private function processStripePayment(Order $order)
    {
        $this->createOrRetrieveStripePaymentIntent($order);

        return redirect()->route('checkout.payment', ['order_code' => $order->group_code]);
    }

    public function payment($order_code)
    {
        $order = Order::with('tickets')->where('group_code', $order_code)->firstOrFail();

        if ($order->payment_method !== 'stripe') {
            return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Metodo di pagamento non valido per questo ordine.');
        }

        if ($order->status === 'paid') {
            return redirect()->route('checkout.success', ['order_code' => $order->group_code]);
        }

        try {
            $paymentIntent = $this->createOrRetrieveStripePaymentIntent($order);

            if ($paymentIntent->status === 'succeeded') {
                app(OrderPaymentFinalizer::class)->finalize($order);

                return redirect()->route('checkout.success', ['order_code' => $order->group_code]);
            }
        } catch (Throwable $e) {
            $order->update(['status' => 'payment_error']);
            Log::error('Errore creazione Payment Intent Stripe', [
                'order_id' => $order->id,
                'group_code' => $order->group_code,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('iscrizione')
                ->withErrors(['payment_method' => 'Non siamo riusciti ad avviare il pagamento con carta. Riprova tra poco.']);
        }

        return view('checkout.payment', [
            'order' => $order,
            'clientSecret' => $paymentIntent->client_secret,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    public function cancel(string $order)
    {
        $orderModel = Order::where('group_code', $order)->first();

        if ($orderModel && $orderModel->status === 'pending') {
            $orderModel->update(['status' => 'cancelled']);
        }

        return redirect()->route('iscrizione')
            ->withErrors(['payment_method' => 'Pagamento annullato. I posti riservati sono stati nuovamente liberati.']);
    }

    public function waitlistConfirmation(string $order_code)
    {
        $order = Order::with('tickets')
            ->where('group_code', $order_code)
            ->where('status', 'waitlist')
            ->firstOrFail();

        $positions = $order->tickets->mapWithKeys(function (Ticket $ticket) {
            $position = Ticket::query()
                ->where('status', 'waitlist')
                ->where('route_choice', $ticket->route_choice)
                ->where(function ($query) use ($ticket) {
                    $query->where('created_at', '<', $ticket->created_at)
                        ->orWhere(function ($query) use ($ticket) {
                            $query->where('created_at', $ticket->created_at)
                                ->where('id', '<=', $ticket->id);
                        });
                })
                ->count();

            return [$ticket->id => $position];
        });

        return view('checkout.waitlist-confirmation', compact('order', 'positions'));
    }

    private function createOrRetrieveStripePaymentIntent(Order $order)
    {
        $stripeSecret = config('services.stripe.secret');

        if (blank($stripeSecret) || blank(config('services.stripe.key'))) {
            throw new RuntimeException('Stripe keys non configurate.');
        }

        \Stripe\Stripe::setApiKey($stripeSecret);

        if ($order->stripe_payment_intent_id) {
            try {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($order->stripe_payment_intent_id);

                if ($paymentIntent->status !== 'canceled') {
                    return $paymentIntent;
                }
            } catch (Throwable $e) {
                Log::warning('Payment Intent Stripe non recuperabile, ne creo uno nuovo', [
                    'order_id' => $order->id,
                    'group_code' => $order->group_code,
                    'stripe_payment_intent_id' => $order->stripe_payment_intent_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $order->loadMissing('tickets');
        $firstTicket = $order->tickets->first();

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => (int) round($order->total_amount * 100),
            'currency' => 'eur',
            'payment_method_types' => ['card'],
            'description' => 'Iscrizione Trail degli Eroi - Ordine ' . $order->group_code,
            'receipt_email' => $firstTicket?->email,
            'metadata' => [
                'order_id' => (string) $order->id,
                'group_code' => $order->group_code,
            ],
        ]);

        $order->update(['stripe_payment_intent_id' => $paymentIntent->id]);

        return $paymentIntent;
    }

    private function processPaypalPayment(Order $order)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('checkout.success', ['order_code' => $order->group_code]),
                "cancel_url" => route('checkout.cancel', ['order' => $order->group_code]),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "EUR",
                        "value" => $order->total_amount
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    $order->update(['paypal_order_id' => $response['id']]); 
                    return redirect()->away($links['href']);
                }
            }
        }

        return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Qualcosa è andato storto con PayPal.');
    }

    public function success(Request $request, $order_code)
    {
        $order = Order::with('tickets')->where('group_code', $order_code)->firstOrFail();

        // 1. Se l'ordine risulta già pagato, mostriamo solo la vista.
        if ($order->status === 'paid') {
            app(OrderPaymentFinalizer::class)->finalize($order);
            return view('checkout.success', compact('order'));
        }

        // --- 2. VERIFICA E CATTURA PAGAMENTO PAYPAL ---
        if ($order->payment_method === 'paypal' && $request->has('token')) {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            // QUESTA È LA RIGA MAGICA CHE PRELEVA EFFETTIVAMENTE I SOLDI
            $response = $provider->capturePaymentOrder($request->query('token'));
            
            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                app(OrderPaymentFinalizer::class)->finalize($order);
            } else {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Il pagamento PayPal non è stato autorizzato o è stato annullato.');
            }
        }
        
        // --- 3. VERIFICA PAGAMENTO STRIPE ---
        elseif ($order->payment_method === 'stripe' && $order->stripe_payment_intent_id) {
            $stripeSecret = config('services.stripe.secret');

            if (blank($stripeSecret)) {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Stripe non è configurato correttamente.');
            }

            \Stripe\Stripe::setApiKey($stripeSecret);
            $paymentIntent = \Stripe\PaymentIntent::retrieve($order->stripe_payment_intent_id);
            
            if ($paymentIntent->status === 'succeeded') {
                app(OrderPaymentFinalizer::class)->finalize($order);
            } else {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Il pagamento Stripe non è stato completato.');
            }
        }
        elseif ($order->payment_method === 'stripe' && $order->stripe_session_id) {
            $stripeSecret = config('services.stripe.secret');

            if (blank($stripeSecret)) {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Stripe non è configurato correttamente.');
            }

            \Stripe\Stripe::setApiKey($stripeSecret);
            $session = \Stripe\Checkout\Session::retrieve($order->stripe_session_id);

            if ($session->payment_status == 'paid') {
                app(OrderPaymentFinalizer::class)->finalize($order);
            } else {
                return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Il pagamento Stripe non è stato completato.');
            }
        }
        
        // --- 4. VERIFICA ORDINI A ZERO EURO (Promo Gruppi) ---
        elseif ($order->total_amount == 0) {
            app(OrderPaymentFinalizer::class)->finalize($order);
        }

        // --- 5. AZIONI POST-PAGAMENTO VERIFICATO ---
        // Se l'ordine ha passato i controlli ed è "paid", attiviamo i biglietti
        if ($order->status === 'paid') {
            app(OrderPaymentFinalizer::class)->finalize($order);
            return view('checkout.success', compact('order'));
        }

        // Fallback in caso di tentativi anomali
        return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Errore nella convalida del pagamento.');
    }

    public function downloadTicket($unique_code)
    {
        $ticket = Ticket::with('order')->where('unique_ticket_code', $unique_code)->firstOrFail();

        if ($ticket->order->status !== 'paid') {
            return back()->with('error', 'Il pagamento non è ancora stato confermato. Riprova tra poco.');
        }

        $pdf = Pdf::loadView('pdf.ticket', compact('ticket'));
        $fileName = 'Biglietto_Trail_degli_Eroi_' . str_replace(' ', '_', $ticket->last_name) . '.pdf';
        
        return $pdf->download($fileName);
    }
}
