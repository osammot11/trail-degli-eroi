<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Barryvdh\DomPDF\Facade\Pdf; // Aggiunto per generare i PDF
use App\Jobs\SendTicketEmail;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validazione massiccia (aggiungiamo discount_code)
        $validatedData = $request->validate([
            'payment_method' => 'required|in:stripe,paypal',
            'is_sports_association' => 'boolean',
            'association_name' => 'required_if:is_sports_association,1|nullable|string|max:255',
            'discount_code' => 'nullable|string|max:255', // <-- NUOVO CAMPO
            'tickets' => 'required|array|min:1',
            // ... [qui lascia tutta la validazione dei tickets.* che avevamo prima] ...
            'tickets.*.first_name' => 'required|string|max:255',
            'tickets.*.last_name' => 'required|string|max:255',
            'tickets.*.route_choice' => 'required|in:Trail degli Eroi 46K,Sky degli Eroi 27K,Downhill degli Eroi 12K,Santa Felicita 14K',
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

        // 2. Calcolo del prezzo e dello Sconto
        $basePrice = 26.00;
        $discountAmount = 0;

        // Se l'utente ha inserito un codice, verifichiamo che esista
        if (!empty($validatedData['discount_code'])) {
            $discountModel = \App\Models\DiscountCode::where('code', strtoupper($validatedData['discount_code']))->first();
            
            if ($discountModel) {
                $discountAmount = $discountModel->amount;
            } else {
                // Se non esiste, rimandiamo indietro con un errore!
                return back()->withErrors(['discount_code' => 'Il codice sconto inserito non è valido.'])->withInput();
            }
        }

        // Calcoliamo il prezzo finale per biglietto (evitando che vada sotto zero)
        $finalPricePerTicket = max(0, $basePrice - $discountAmount);
        $totalAmount = count($validatedData['tickets']) * $finalPricePerTicket;

        // 3. Creazione Ordine
        $order = Order::create([
            'group_code' => 'WFU_' . strtoupper(\Illuminate\Support\Str::random(10)), 
            'total_amount' => $totalAmount,
            'payment_method' => $validatedData['payment_method'],
            'is_sports_association' => $request->has('is_sports_association'),
            'association_name' => $validatedData['association_name'] ?? null,
            'discount_code' => $validatedData['discount_code'] ?? null, // Salviamo il codice usato
            'status' => 'pending',
        ]);

        // 4. Creazione Biglietti
        foreach ($validatedData['tickets'] as $data) {
            $order->tickets()->create(array_merge($data, [
                // Generiamo il codice PDF solo DOPO che ha pagato (lo farà il webhook)
                'unique_ticket_code' => null, 
                'price_paid' => $finalPricePerTicket, 
                'payment_tag' => ucfirst($validatedData['payment_method']),
                // FONDAMENTALE: Nascono in attesa!
                'status' => 'pending', 
            ]));
        }

        // 5. Redirect Pagamento
        if ($totalAmount == 0) {
            // Se con lo sconto il totale è 0€, saltiamo Stripe/PayPal e andiamo diretti al successo!
            return redirect()->route('checkout.success', ['order_code' => $order->group_code]);
        }

        return ($validatedData['payment_method'] === 'stripe') 
            ? $this->processStripePayment($order) 
            : $this->processPaypalPayment($order);
    }

    private function processStripePayment(Order $order)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = [];
        foreach ($order->tickets as $ticket) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Iscrizione Trail degli Eroi - ' . $ticket->first_name . ' ' . $ticket->last_name,
                        'description' => 'Taglia maglia: ' . $ticket->tshirt_size,
                    ],
                    // ECCO LA MAGIA: Moltiplichiamo il prezzo pagato per 100 (Stripe vuole i centesimi)
                    // Usiamo intval() per assicurarci che sia un numero intero pulito
                    'unit_amount' => intval($ticket->price_paid * 100), 
                ],
                'quantity' => 1,
            ];
        }

        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('checkout.success', ['order_code' => $order->group_code]),
            'cancel_url' => route('checkout.cancel', ['order' => $order->group_code]),
            'client_reference_id' => $order->group_code, 
        ]);

        $order->update(['stripe_session_id' => $checkout_session->id]); 

        return redirect()->away($checkout_session->url);
    }

    private function processPaypalPayment(Order $order)
    {
        // 1. Inizializza il client di PayPal
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        // 2. Crea l'ordine su PayPal passando il totale
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                // Usiamo le stesse rotte di ritorno che abbiamo creato per Stripe!
                "return_url" => route('checkout.success', ['order_code' => $order->group_code]), // Aggiornato con order_code
                "cancel_url" => route('checkout.cancel', ['order' => $order->group_code]),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "EUR",
                        "value" => $order->total_amount // Il totale calcolato prima
                    ]
                ]
            ]
        ]);

        // 3. Cerca il link di approvazione nella risposta di PayPal e fai il redirect
        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    // Salva l'ID della transazione PayPal se vuoi tenerne traccia
                    $order->update(['paypal_order_id' => $response['id']]); 
                    
                    return redirect()->away($links['href']);
                }
            }
        }

        // Se qualcosa va storto, rimanda alla pagina di annullamento
        return redirect()->route('checkout.cancel', ['order' => $order->group_code])->with('error', 'Qualcosa è andato storto con PayPal.');
    }

    /**
     * Mostra la Thank You Page dinamica
     */
    public function success(Request $request, $order_code)
    {
        $order = Order::with('tickets')->where('group_code', $order_code)->firstOrFail();

        if ($order->status !== 'paid') {
            $order->update(['status' => 'paid']);
            
            // INVIA LE EMAIL IN BACKGROUND!
            foreach ($order->tickets as $ticket) {
                SendTicketEmail::dispatch($ticket);
            }
        }

        return view('checkout.success', compact('order'));
    }

    /**
     * Genera e scarica il PDF del singolo biglietto on-demand
     */
    public function downloadTicket($unique_code)
    {
        // Recuperiamo il biglietto specifico dal codice univoco
        $ticket = Ticket::with('order')->where('unique_ticket_code', $unique_code)->firstOrFail();

        // Sicurezza: scarica il PDF solo se l'ordine è pagato
        if ($ticket->order->status !== 'paid') {
            return back()->with('error', 'Il pagamento non è ancora stato confermato. Riprova tra poco.');
        }

        // Carica la vista del singolo biglietto e passa i dati
        $pdf = Pdf::loadView('pdf.ticket', compact('ticket'));
        
        // Nome file pulito: Biglietto_Nome_Cognome.pdf
        $fileName = 'Biglietto_Trail_degli_Eroi_' . str_replace(' ', '_', $ticket->last_name) . '.pdf';
        
        return $pdf->download($fileName);
    }
}
