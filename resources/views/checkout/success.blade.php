<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma iscrizione - Trail degli Eroi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 md:p-12">

    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-green-600 p-6 text-white text-center">
            <h1 class="text-3xl font-bold">Iscrizione Completata!</h1>
            <p class="mt-2 opacity-90">Grazie per la tua partecipazione al Trail degli Eroi.</p>
        </div>

        <div class="p-8">
            <div class="mb-8 text-center border-b pb-6">
                <p class="text-gray-600 uppercase text-sm font-bold tracking-widest">Codice Gruppo</p>
                <h2 class="text-2xl font-mono font-bold text-gray-800">{{ $order->group_code }}</h2>
            </div>

            <h3 class="text-lg font-bold mb-4 text-gray-700">I tuoi Biglietti Nominativi:</h3>
            <div class="space-y-4">
                @foreach($order->tickets as $ticket)
                    <div class="flex items-center justify-between p-4 border rounded-lg bg-gray-50 hover:bg-gray-100 transition">
                        <div>
                            <p class="font-bold text-gray-800">{{ $ticket->first_name }} {{ $ticket->last_name }}</p>
                            <p class="text-sm text-gray-500">{{ $ticket->route_choice }}</p>
                            <p class="text-xs font-mono text-blue-600 mt-1">{{ $ticket->unique_ticket_code }}</p>
                        </div>
                        
                        <a href="{{ route('ticket.download', $ticket->unique_ticket_code) }}" 
                           class="flex items-center space-x-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold text-sm transition">
                           <span>PDF</span>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-10 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 text-sm">
                <p><strong>Nota:</strong> Abbiamo inviato un'email a ciascun partecipante con il link per tornare su questa pagina e scaricare il proprio biglietto in qualsiasi momento.</p>
            </div>
            
            <div class="mt-8 text-center">
                <a href="/" class="text-blue-600 hover:underline font-medium">&larr; Torna alla Home</a>
            </div>
        </div>
    </div>

</body>
</html>
