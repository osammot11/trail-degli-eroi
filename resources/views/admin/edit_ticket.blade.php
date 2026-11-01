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

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

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
                    <div><label class="text-xs text-gray-500">Nome</label><input type="text" name="first_name" value="{{ old('first_name', $ticket->first_name) }}" class="w-full border p-2 rounded" required></div>
                    <div><label class="text-xs text-gray-500">Cognome</label><input type="text" name="last_name" value="{{ old('last_name', $ticket->last_name) }}" class="w-full border p-2 rounded" required></div>
                    <div><label class="text-xs text-gray-500">Email</label><input type="email" name="email" value="{{ old('email', $ticket->email) }}" class="w-full border p-2 rounded" required></div>
                    <div><label class="text-xs text-gray-500">Telefono</label><input type="text" name="phone" value="{{ old('phone', $ticket->phone) }}" class="w-full border p-2 rounded" required></div>
                    <div><label class="text-xs text-gray-500">Codice Fiscale</label><input type="text" name="codice_fiscale" value="{{ old('codice_fiscale', $ticket->codice_fiscale) }}" class="w-full border p-2 rounded uppercase"></div>
                    <div>
                        <label class="text-xs text-gray-500">Taglia Maglia</label>
                        <select name="tshirt_size" class="w-full border p-2 rounded" required>
                            @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)
                                <option value="{{ $size }}" @selected(old('tshirt_size', $ticket->tshirt_size) === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-8 p-5 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="font-bold text-lg text-blue-900">Cambio distanza</h3>
                    <p class="text-sm text-blue-700 mt-1 mb-4">La disponibilità viene verificata al salvataggio. Il cambio sarà registrato e comunicato all’atleta.</p>
                    <label class="text-xs text-gray-600">Nuova distanza</label>
                    <select name="route_choice" id="route-choice" class="w-full border p-3 rounded mt-1" required>
                        @foreach($raceAvailability as $race)
                            <option
                                value="{{ $race['route'] }}"
                                data-price="{{ $race['price'] }}"
                                @selected(old('route_choice', $ticket->route_choice) === $race['route'])
                            >
                                {{ $race['label'] }} — {{ $race['distance'] }} — € {{ number_format($race['price'], 2, ',', '.') }}{{ $race['route'] !== $ticket->route_choice && $race['is_sold_out'] ? ' — SOLD OUT' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div id="price-difference" class="mt-3 text-sm font-bold text-blue-900"></div>
                </div>

                <h3 class="font-bold text-lg mb-4 text-blue-600">Residenza</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <input type="text" name="residence_address" value="{{ old('residence_address', $ticket->residence_address) }}" class="border p-2 rounded" required>
                    <input type="text" name="city" value="{{ old('city', $ticket->city) }}" class="border p-2 rounded" required>
                    <input type="text" name="zip_code" value="{{ old('zip_code', $ticket->zip_code) }}" class="border p-2 rounded" required>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg text-lg transition">
                    Salva Modifiche
                </button>
            </form>
        </div>

        <div class="bg-white p-8 rounded-lg shadow mt-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Storico operazioni</h2>
            @forelse($ticket->changes as $change)
                <div class="border-l-4 border-blue-500 pl-4 py-2 mb-4">
                    <div class="font-semibold">{{ $change->from_value }} → {{ $change->to_value }}</div>
                    <div class="text-sm text-gray-600">
                        {{ $change->created_at->translatedFormat('d/m/Y H:i') }}
                        · {{ $change->changedBy?->name ?? 'Amministratore' }}
                        · Differenza: € {{ number_format($change->amount_difference, 2, ',', '.') }}
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">Nessun cambio distanza registrato.</p>
            @endforelse
        </div>
    </div>

    <script>
        const paidAmount = {{ (float) $ticket->price_paid }};
        const originalRoute = @json($ticket->route_choice);
        const routeSelect = document.getElementById('route-choice');
        const differenceBox = document.getElementById('price-difference');

        function updateDifferencePreview() {
            if (routeSelect.value === originalRoute) {
                differenceBox.textContent = 'Distanza invariata.';
                return;
            }

            const newPrice = Number(routeSelect.selectedOptions[0].dataset.price || 0);
            const difference = newPrice - paidAmount;

            if (difference > 0) {
                differenceBox.textContent = `Differenza da incassare: € ${difference.toFixed(2).replace('.', ',')}`;
            } else if (difference < 0) {
                differenceBox.textContent = `Importo a favore dell’atleta: € ${Math.abs(difference).toFixed(2).replace('.', ',')}`;
            } else {
                differenceBox.textContent = 'Nessuna differenza di prezzo.';
            }
        }

        routeSelect.addEventListener('change', updateDifferencePreview);
        updateDifferencePreview();
    </script>

</body>
</html>
