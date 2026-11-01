<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Str;
use App\Jobs\SendTicketEmail;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\RaceCapacityService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // --- 1. Dashboard con Filtri di Ricerca ---
    public function dashboard(Request $request, RaceCapacityService $raceCapacity)
    {
        $query = \App\Models\Ticket::with('order')->where('status', 'active');

        // Filtro di ricerca testuale (Nome, Cognome, Email, Codice)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('unique_ticket_code', 'like', "%{$search}%");
            });
        }

        // Filtri Temporali
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activeTickets = $query->orderBy('created_at', 'desc')->get();
        $currentPrice = \App\Models\Setting::where('key', 'ticket_price')->value('value') ?? 26.00;
        $totalRevenue = $activeTickets->sum('price_paid');
        $raceAvailability = $raceCapacity->availability();

        return view('admin.dashboard', compact('activeTickets', 'currentPrice', 'totalRevenue', 'raceAvailability'));
    }

    // --- 2. Esportazione CSV (Il Super CSV Completo, Filtrato e TUTTO MAIUSCOLO) ---
    public function exportCsv(Request $request)
    {
        // Partiamo dalla query base
        $query = \App\Models\Ticket::with('order')->where('status', 'active');

        // 1. APPLICHIAMO GLI STESSI FILTRI DELLA DASHBOARD
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('unique_ticket_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Eseguiamo la query filtrata
        $tickets = $query->orderBy('created_at', 'desc')->get();

        return response()->stream(function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); 
            
            // Intestazione colonne (già scritte in maiuscolo)
            fputcsv($handle, [
                'CODICE BIGLIETTO', 'GRUPPO ORDINE', 'NOME', 'COGNOME', 'EMAIL', 'TELEFONO', 
                'DATA NASCITA', 'LUOGO NASCITA', 'NAZIONALITA', 'CODICE FISCALE', 
                'INDIRIZZO', 'CITTA', 'CAP', 'PROVINCIA', 'REGIONE', 'PAESE', 
                'PERCORSO', 'TAGLIA', 'NAVETTA', 'CELIACO', 
                'ASSOCIAZIONE SPORTIVA', 'NOME ASSOCIAZIONE', 'CONVENZIONE',
                'METODO PAGAMENTO', 'IMPORTO PAGATO', 'TAG 1', 'TAG 2', 
                'DATA ISCRIZIONE', 'ORA ISCRIZIONE'
            ], ';');

            foreach ($tickets as $ticket) {
                $isAssoc = ($ticket->order && $ticket->order->is_sports_association) ? 'SI' : 'NO';
                $assocName = $ticket->order->association_name ?? '-';
                $discountLabel = $ticket->order->discount_code ?? '-';

                // Creiamo la riga dati
                $rowData = [
                    $ticket->unique_ticket_code,
                    $ticket->order->group_code ?? '-',
                    $ticket->first_name,
                    $ticket->last_name,
                    $ticket->email,
                    $ticket->phone,
                    $ticket->dob,
                    $ticket->birth_place,
                    $ticket->nationality,
                    $ticket->codice_fiscale,
                    $ticket->residence_address,
                    $ticket->city,
                    $ticket->zip_code,
                    $ticket->province,
                    $ticket->region,
                    $ticket->country,
                    $ticket->route_choice,
                    $ticket->tshirt_size,
                    $ticket->shuttle_needed ? 'SI' : 'NO',
                    $ticket->celiac ? 'SI' : 'NO',
                    $isAssoc,
                    $assocName,
                    $discountLabel,
                    $ticket->payment_tag,
                    number_format($ticket->price_paid, 2, ',', ''),
                    $ticket->tag_1,
                    $ticket->tag_2,
                    $ticket->created_at->format('d/m/Y'), 
                    $ticket->created_at->format('H:i')    
                ];

                // ECCO LA MAGIA: Applichiamo il maiuscolo a tutti gli elementi della riga in un colpo solo
                $uppercaseRow = array_map(function($value) {
                    return mb_strtoupper((string)$value, 'UTF-8');
                }, $rowData);

                // Scriviamo la riga maiuscola nel CSV
                fputcsv($handle, $uppercaseRow, ';');
            }
            fclose($handle);
        }, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=iscritti_filtrati_" . date('Y-m-d_H-i') . ".csv",
        ]);
    }
    
    // --- 3. Modifica Utente ---
    public function editTicket($id)
    {
        $ticket = \App\Models\Ticket::findOrFail($id);
        return view('admin.edit_ticket', compact('ticket'));
    }

    public function updateTicketData(Request $request, $id)
    {
        $ticket = \App\Models\Ticket::findOrFail($id);
        
        // Aggiorniamo i dati modificabili (puoi aggiungere regole di validazione se vuoi)
        $ticket->update($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Dati di ' . $ticket->first_name . ' aggiornati con successo!');
    }

    // --- 4. Download PDF Admin ---
    public function downloadPdf($id)
    {
        $ticket = \App\Models\Ticket::findOrFail($id);
        // Usiamo la stessa vista del biglietto che usa l'utente
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket', compact('ticket'));
        return $pdf->download('Biglietto_' . $ticket->unique_ticket_code . '.pdf');
    }

    public function updatePrice(Request $request)
    {
        $request->validate([
            'ticket_price' => 'required|numeric|min:0'
        ]);

        // Aggiorna o crea l'impostazione del prezzo nel database
        Setting::updateOrCreate(
            ['key' => 'ticket_price'],
            ['value' => $request->ticket_price]
        );

        return back()->with('success', 'Prezzo base aggiornato con successo!');
    }

    public function updateRaceCapacities(Request $request)
    {
        $rules = [];

        foreach (RaceCapacityService::RACES as $race) {
            $rules[$race['capacity_key']] = ['required', 'integer', 'min:0', 'max:100000'];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) $value]
                );
            }
        });

        return back()->with('success', 'Capienze aggiornate. Le gare al limite vengono chiuse automaticamente.');
    }

    // Mostra la pagina dei biglietti in attesa e il form di upload
    public function pending()
    {
        $pendingTickets = Ticket::with('order')->where('status', 'in-approvazione')->get();
        return view('admin.pending', compact('pendingTickets'));
    }

    // Processa il file CSV (Versione Completa - Tutti i campi)
    public function uploadCsv(Request $request, RaceCapacityService $raceCapacity)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();

        // 1. Apriamo il file in lettura in modo sicuro
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return back()->with('error', 'Impossibile leggere il file CSV.');
        }

        // 2. Leggiamo la primissima riga per capire se usa la virgola (,) o il punto e virgola (;)
        $firstLine = fgets($handle);
        $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
        
        // Riportiamo il "cursore" di lettura all'inizio del file
        rewind($handle); 

        // 3. Saltiamo la prima riga (l'intestazione)
        fgetcsv($handle, 1000, $delimiter);

        // 4. Creiamo l'ordine fittizio per raggrupparli
        $order = \App\Models\Order::create([
            'group_code' => 'CSV_' . strtoupper(\Illuminate\Support\Str::random(8)),
            'total_amount' => 0, 
            'payment_method' => 'import_csv',
            'status' => 'paid',
        ]);

        $count = 0;
        $skippedForCapacity = 0;
        $skippedInvalidRoute = 0;
        $currentPrice = \App\Models\Setting::where('key', 'ticket_price')->value('value') ?? 26.00;

        // 5. Leggiamo riga per riga (Ora con TUTTE le 18 colonne)
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            
            // Se la riga è completamente vuota, saltala
            if (empty(array_filter($row))) continue;

            // Se mancano delle colonne in fondo, le riempiamo con vuoto per non far schiantare Laravel
            $row = array_pad($row, 18, '');

            // Funzione di utilità per capire se l'utente ha scritto "Si", "True", "1" o "Yes" nelle checkbox
            $isTrue = function($val) {
                return in_array(strtolower(trim($val)), ['1', 'si', 'sì', 'true', 'yes', 'y']);
            };

            // Convertiamo le date dal formato italiano (GG/MM/AAAA) al formato DB (AAAA-MM-GG) se necessario
            $dob = trim($row[4]);
            if (strpos($dob, '/') !== false) {
                $dateParts = explode('/', $dob);
                if(count($dateParts) == 3) {
                    $dob = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                }
            }

            $routeChoice = trim($row[3]) ?: 'Trail degli Eroi 46K';

            if (! in_array($routeChoice, $raceCapacity->routeValues(), true)) {
                $skippedInvalidRoute++;
                continue;
            }

            $ticketData = [
                // Dati Anagrafici Base
                'first_name' => trim($row[0]) ?: 'Sconosciuto',
                'last_name' => trim($row[1]) ?: 'Sconosciuto',
                'email' => trim($row[2]) ?: 'no-email@test.com',
                
                // Scelte Maratona
                'route_choice' => $routeChoice,
                'tshirt_size' => trim($row[9]) ?: 'L',
                
                // Dati di Nascita e Documenti
                'dob' => $dob ?: '1990-01-01',
                'birth_place' => trim($row[5]) ?: 'N/D',
                'nationality' => strtoupper(trim($row[6])) ?: 'IT',
                'codice_fiscale' => strtoupper(trim($row[7])) ?: null,
                
                // Contatti e Residenza
                'phone' => trim($row[8]) ?: '0000000000',
                'residence_address' => trim($row[10]) ?: 'N/D',
                'city' => trim($row[11]) ?: 'N/D',
                'zip_code' => trim($row[12]) ?: '00000',
                'province' => trim($row[13]) ?: 'N/D',
                'region' => trim($row[14]) ?: 'N/D',
                'country' => trim($row[15]) ?: 'Italia',
                
                // Opzioni Extra
                'shuttle_needed' => $isTrue($row[16]),
                'celiac' => $isTrue($row[17]),
                
                // Dati di Sistema
                'price_paid' => $currentPrice,
                'payment_tag' => 'Importato CSV',
                'status' => 'in-approvazione', 
            ];

            try {
                DB::transaction(function () use ($raceCapacity, $routeChoice, $order, $ticketData) {
                    $raceCapacity->assertCanReserve([$routeChoice]);
                    $order->tickets()->create($ticketData);
                });
                $count++;
            } catch (\Illuminate\Validation\ValidationException) {
                $skippedForCapacity++;
            }
        }

        fclose($handle);

        $message = "$count biglietti caricati e messi in attesa di approvazione.";

        if ($skippedForCapacity > 0) {
            $message .= " $skippedForCapacity righe saltate perché la distanza era sold out.";
        }

        if ($skippedInvalidRoute > 0) {
            $message .= " $skippedInvalidRoute righe saltate perché la distanza non era riconosciuta.";
        }

        return back()->with('success', $message);
    }

    // Approva il singolo biglietto e invia l'email
    public function approveTicket($id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->status !== 'active') {
            $ticket->update([
                'status' => 'active',
                'unique_ticket_code' => 'TK_' . strtoupper(Str::random(8)) // Genera il codice solo ora!
            ]);

            // SPARA L'EMAIL IN BACKGROUND TRAMITE BREVO!
            SendTicketEmail::dispatch($ticket);
        }

        return back()->with('success', "Biglietto di {$ticket->first_name} approvato! Email in fase di invio.");
    }

    // Elimina un biglietto in attesa se c'è un errore
    public function deleteTicket($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return back()->with('success', 'Biglietto eliminato correttamente.');
    }

    // Mostra il form per creare un biglietto manuale
    public function createTicket()
    {
        return view('admin.create_ticket');
    }

    // Salva il biglietto manuale e spedisce l'email
    public function storeTicket(Request $request, RaceCapacityService $raceCapacity)
    {
        // 1. Validazione
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'route_choice' => ['required', Rule::in($raceCapacity->routeValues())],
            'dob' => 'required|date',
            'birth_place' => 'required|string|max:255',
            'residence_address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:20',
            'province' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'nationality' => 'required|string|max:2',
            'codice_fiscale' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('nationality') === 'IT') {
                        if (!preg_match('/^[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z]$/i', $value)) {
                            $fail('Il Codice Fiscale inserito non è valido.');
                        }
                    }
                },
            ],
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'tshirt_size' => 'required|in:XS,S,M,L,XL,XXL',
            'shuttle_needed' => 'required|boolean',
            'celiac' => 'required|boolean',
            'price_paid' => 'required|numeric|min:0', // <-- NUOVO CAMPO IMPORTI
        ]);

        $ticket = DB::transaction(function () use ($validated, $raceCapacity) {
            $raceCapacity->assertCanReserve([$validated['route_choice']]);

            $order = Order::create([
                'group_code' => 'MAN_' . strtoupper(Str::random(8)),
                'total_amount' => $validated['price_paid'],
                'payment_method' => 'manual',
                'status' => 'paid',
            ]);

            return $order->tickets()->create(array_merge($validated, [
                'payment_tag' => 'Contanti / Manuale',
                'status' => 'active',
                'unique_ticket_code' => 'TK_' . strtoupper(Str::random(8)),
            ]));
        });

        // 4. Invio email in background
        SendTicketEmail::dispatch($ticket);

        return redirect()->route('admin.dashboard')->with('success', "Biglietto creato con successo! Email inviata a {$ticket->email}.");
    }

}
