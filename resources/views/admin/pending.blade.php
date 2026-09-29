<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione CSV & Attesa - Trail degli Eroi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 bg-white p-6 rounded-lg shadow">
            <h1 class="text-3xl font-bold text-gray-800">Caricamento Massivo & Approvazioni</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">&larr; Torna alla Dashboard</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h2 class="text-xl font-bold mb-4">Carica Biglietti da CSV</h2>
            <p class="text-sm text-gray-600 mb-4">Il file deve avere le colonne in questo ordine esatto: <strong>Nome, Cognome, Email, Percorso, Taglia</strong>. (Senza intestazione o saltando la prima riga).</p>
            
            <form action="{{ route('admin.upload_csv') }}" method="POST" enctype="multipart/form-data" class="flex items-center space-x-4">
                @csrf
                <input type="file" name="csv_file" accept=".csv" required class="border p-2 rounded">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">Importa File</button>
            </form>
        </div>

        <h2 class="text-xl font-bold mb-4">Biglietti in Attesa ({{ $pendingTickets->count() }})</h2>
        
        @if($pendingTickets->isEmpty())
            <div class="bg-yellow-50 text-yellow-700 p-6 rounded text-center border border-yellow-200">
                Non ci sono biglietti in attesa di approvazione.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($pendingTickets as $ticket)
                    <div class="bg-white border-t-4 border-yellow-400 rounded-lg shadow p-5 relative">
                        
                        <div class="absolute top-2 right-2 bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">
                            PENDING
                        </div>

                        <h3 class="font-bold text-lg mt-2">{{ $ticket->first_name }} {{ $ticket->last_name }}</h3>
                        <p class="text-sm text-gray-600 truncate mb-2">{{ $ticket->email }}</p>
                        
                        <div class="bg-gray-50 p-2 rounded text-xs mb-4">
                            <p><strong>Percorso:</strong> {{ $ticket->route_choice }}</p>
                            <p><strong>Taglia:</strong> {{ $ticket->tshirt_size }}</p>
                            <p><strong>Gruppo:</strong> {{ $ticket->order->group_code ?? 'N/D' }}</p>
                        </div>

                        <div class="flex space-x-2">
                            <form action="{{ route('admin.approve_ticket', $ticket->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 rounded text-sm transition">
                                    ✓ Approva & Invia Email
                                </button>
                            </form>

                            <form action="{{ route('admin.delete_ticket', $ticket->id) }}" method="POST" onsubmit="return confirm('Sicuro di voler eliminare questo biglietto?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded text-sm transition">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</body>
</html>
