<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Trail degli Eroi</title>
    <link rel="stylesheet" href="{{ asset('css/style.css?v=1.7') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-8xl mx-auto">
        <div class="flex justify-between items-center mb-8 bg-white p-6 rounded-lg shadow">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Pannello di Controllo</h1>
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="text-red-500 hover:underline font-bold text-sm">Esci (Logout)</button>
                </form>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Incasso Totale</p>
                <p class="text-2xl font-bold text-green-600">€ {{ number_format($totalRevenue, 2, ',', '.') }}</p>
            </div>
        </div>



        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">{{ session('success') }}</div>
        @endif

        <div>
            <div class="grid-3">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="font-bold text-lg mb-4">Prezzo Biglietto Default</h3>
                    <form action="{{ route('admin.update_price') }}" method="POST">
                        @csrf
                        <div class="flex items-center space-x-2">
                            <span class="text-gray-500">€</span>
                            <input type="number" step="0.01" name="ticket_price" value="{{ $currentPrice }}" class="border p-2 rounded w-full">
                        </div>
                        <button type="submit" class="mt-4 w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700">Aggiorna Prezzo</button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="font-bold text-lg mb-4">Creazione biglietti manuale</h3>
                    <a href="{{ route('admin.pending') }}" class="block text-center w-full bg-blue-100 text-blue-800 font-bold py-2 rounded mb-2 hover:bg-blue-200 transition">Gestione CSV & In Attesa</a>
                    <a href="{{ route('admin.create_ticket_form') }}" class="block text-center w-full bg-green-100 text-green-800 font-bold py-2 rounded hover:bg-green-200 transition">Crea Biglietto Singolo</a>
                </div>

                <div class="bg-white p-6 rounded-lg shadow flex flex-wrap items-end justify-between gap-4">
                    <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Cerca (Nome, Email, Codice)</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="border p-2 rounded text-sm w-48" placeholder="Es. Mario o TK_...">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Da Data</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border p-2 rounded text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">A Data</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border p-2 rounded text-sm">
                        </div>
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-900">Filtra</button>
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 text-sm hover:underline">Resetta</a>
                    </form>
                    <a href="{{ route('admin.export_csv', request()->query()) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm flex items-center shadow">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Esporta CSV
                    </a>
                </div>
            </div>

            <div class="col-span-3 top-margin-large">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Iscritti Trovati ({{ $activeTickets->count() }})</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-sm border-b">
                                    <th class="p-3">Codice / Ordine</th>
                                    <th class="p-3">Utente</th>
                                    <th class="p-3">Data</th>
                                    <th class="p-3">Codice fiscale</th>
                                    <th class="p-3">Pagamento</th>
                                    <th class="p-3 text-center">Taglia/Percorso</th>
                                    <th class="p-3 text-right">Importo</th>
                                    <th class="p-3 text-center">Azioni</th>
                                </tr>

                            </thead>
                            <tbody class="text-sm">
                                @foreach($activeTickets as $ticket)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="p-3 font-mono">
                                            <div class="font-bold text-blue-600">{{ $ticket->unique_ticket_code }}</div>
                                            <div class="text-xs text-gray-500">{{ $ticket->order->group_code ?? '-' }}</div>
                                        </td>

                                        <td class="p-3">
                                            <div class="font-semibold">{{ $ticket->first_name }} {{ $ticket->last_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $ticket->email }}</div>
                                            @if($ticket->order && $ticket->order->is_sports_association)
                                                <div class="mt-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 inline-block px-2 py-0.5 rounded border border-indigo-100">
                                                    🏃‍♂️ Assoc: {{ $ticket->order->association_name ?? 'Non specificata' }}
                                                </div>
                                            @endif

                                            @if($ticket->tag_1 || $ticket->tag_2)
                                                <div class="mt-1 flex gap-1">
                                                    @if($ticket->tag_1)<span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 text-[10px] rounded">{{ $ticket->tag_1 }}</span>@endif
                                                    @if($ticket->tag_2)<span class="px-2 py-0.5 bg-purple-100 text-purple-800 text-[10px] rounded">{{ $ticket->tag_2 }}</span>@endif
                                                </div>
                                            @endif
                                        </td>

                                        <td class="p-3">
                                          <div>{{ $ticket->created_at->translatedFormat('d F Y') }}</div>
                                        </td>

                                        <td class="p-3">
                                            <div>{{ $ticket->codice_fiscale }}</div>
                                        </td>

                                        

                                        <td class="p-3">
                                            <div>{{ $ticket->payment_tag }}</div>
                                        </td>
                                        
                                        

                                        <td class="p-3 text-center">
                                            <div>{{ $ticket->tshirt_size }}</div>
                                            <div class="text-xs text-gray-500">{{ $ticket->route_choice }}</div>
                                        </td>

                                        <td class="p-3 text-right font-bold text-green-600">€ {{ number_format($ticket->price_paid, 2, ',', '.') }}</td>

                                        <td class="p-3 center-items flex-row space-x-2">
                                            <a href="{{ route('admin.edit_ticket', $ticket->id) }}" title="Modifica e Tag"><svg class="lucide-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-pen-icon lucide-user-pen"><path d="M11.5 15H7a4 4 0 0 0-4 4v2"/><path d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/><circle cx="10" cy="7" r="4"/></svg></a>

                                            <a class="lucide-icon" href="{{ route('admin.download_pdf', $ticket->id) }}" title="Scarica PDF"><svg class="lucide-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-down-icon lucide-file-down"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
