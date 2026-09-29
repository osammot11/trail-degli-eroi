<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Iscritto - Trail degli Eroi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8 bg-white p-6 rounded-lg shadow">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Modifica Iscritto</h1>
                <p class="text-gray-500">Biglietto: <span class="font-mono text-blue-600">{{ $ticket->unique_ticket_code }}</span></p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">&larr; Torna alla Dashboard</a>
        </div>

        <div class="bg-white p-8 rounded-lg shadow">
            <form action="{{ route('admin.update_ticket', $ticket->id) }}" method="POST">
                @csrf

                <div class="mb-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h3 class="font-bold text-lg mb-2 text-yellow-800">🏷️ Tag Amministrativi (Uso interno)</h3>
                    <p class="text-sm text-yellow-700 mb-4">Usa questi campi per segnare annotazioni, gruppi speciali o compiti (es. "Ritiro pacco anticipato", "Sponsor VIP").</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="tag_1" value="{{ $ticket->tag_1 }}" placeholder="Tag 1" class="border p-2 rounded">
                        <input type="text" name="tag_2" value="{{ $ticket->tag_2 }}" placeholder="Tag 2" class="border p-2 rounded">
                    </div>
                </div>

                <h3 class="font-bold text-lg mb-4 text-blue-600">Dati Anagrafici e Gara</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div><label class="text-xs text-gray-500">Nome</label><input type="text" name="first_name" value="{{ $ticket->first_name }}" class="w-full border p-2 rounded"></div>
                    <div><label class="text-xs text-gray-500">Cognome</label><input type="text" name="last_name" value="{{ $ticket->last_name }}" class="w-full border p-2 rounded"></div>
                    <div><label class="text-xs text-gray-500">Email</label><input type="email" name="email" value="{{ $ticket->email }}" class="w-full border p-2 rounded"></div>
                    <div><label class="text-xs text-gray-500">Telefono</label><input type="text" name="phone" value="{{ $ticket->phone }}" class="w-full border p-2 rounded"></div>
                    <div><label class="text-xs text-gray-500">Codice Fiscale</label><input type="text" name="codice_fiscale" value="{{ $ticket->codice_fiscale }}" class="w-full border p-2 rounded uppercase"></div>
                    <div><label class="text-xs text-gray-500">Taglia Maglia</label><input type="text" name="tshirt_size" value="{{ $ticket->tshirt_size }}" class="w-full border p-2 rounded"></div>
                </div>

                <h3 class="font-bold text-lg mb-4 text-blue-600">Residenza</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <input type="text" name="residence_address" value="{{ $ticket->residence_address }}" class="border p-2 rounded">
                    <input type="text" name="city" value="{{ $ticket->city }}" class="border p-2 rounded">
                    <input type="text" name="zip_code" value="{{ $ticket->zip_code }}" class="border p-2 rounded">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-lg transition">
                    Salva Modifiche
                </button>
            </form>
        </div>
    </div>

</body>
</html>
