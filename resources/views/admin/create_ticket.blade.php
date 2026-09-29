<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea Biglietto Manuale - Trail degli Eroi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8 bg-white p-6 rounded-lg shadow">
            <h1 class="text-3xl font-bold text-gray-800">Crea Biglietto Manuale</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">&larr; Torna alla Dashboard</a>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <strong class="font-bold">Attenzione!</strong>
                <ul class="list-disc ml-5 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white p-8 rounded-lg shadow">
            <p class="text-gray-600 mb-6 border-b pb-4">Compila tutti i dati dell'iscritto. Per motivi assicurativi è obbligatorio inserire correttamente i dati anagrafici e il codice fiscale. In fondo potrai specificare quanto ha pagato (es. contanti).</p>

            <form action="{{ route('admin.store_ticket') }}" method="POST">
                @csrf
                
                <h3 class="font-bold text-lg mb-4 text-blue-600">Dati Anagrafici e Contatti</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <input type="text" name="first_name" placeholder="Nome" value="{{ old('first_name') }}" required class="border p-2 rounded">
                    <input type="text" name="last_name" placeholder="Cognome" value="{{ old('last_name') }}" required class="border p-2 rounded">
                    
                    <input type="date" name="dob" value="{{ old('dob') }}" required title="Data di nascita" class="border p-2 rounded">
                    <input type="text" name="birth_place" placeholder="Luogo di Nascita" value="{{ old('birth_place') }}" required class="border p-2 rounded">
                    
                    <input type="text" name="nationality" placeholder="Nazionalità (es. IT)" value="{{ old('nationality', 'IT') }}" maxlength="2" required class="border p-2 rounded">
                    <input type="text" name="codice_fiscale" placeholder="Codice Fiscale (Obbligatorio per IT)" value="{{ old('codice_fiscale') }}" class="border p-2 rounded uppercase">
                    
                    <input type="email" name="email" placeholder="Email (a cui inviare il PDF)" value="{{ old('email') }}" required class="border p-2 rounded">
                    <input type="text" name="phone" placeholder="Telefono" value="{{ old('phone') }}" required class="border p-2 rounded">
                </div>

                <h3 class="font-bold text-lg mb-4 text-blue-600">Residenza</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <input type="text" name="residence_address" placeholder="Indirizzo (Via/Piazza)" value="{{ old('residence_address') }}" required class="border p-2 rounded">
                    <input type="text" name="city" placeholder="Città" value="{{ old('city') }}" required class="border p-2 rounded">
                    <input type="text" name="zip_code" placeholder="CAP" value="{{ old('zip_code') }}" required class="border p-2 rounded">
                    <input type="text" name="province" placeholder="Provincia" value="{{ old('province') }}" required class="border p-2 rounded">
                    <input type="text" name="region" placeholder="Regione" value="{{ old('region') }}" required class="border p-2 rounded">
                    <input type="text" name="country" placeholder="Nazione" value="{{ old('country', 'Italia') }}" required class="border p-2 rounded">
                </div>

                <h3 class="font-bold text-lg mb-4 text-blue-600">Opzioni Gara</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <select name="route_choice" required class="border p-2 rounded">
                        <option value="">-- Scelta Tratta --</option>
                        <option value="Trail degli Eroi 46K" {{ old('route_choice') == 'Trail degli Eroi 46K' ? 'selected' : '' }}>Trail degli Eroi — 46 km / 2.500 D+</option>
                        <option value="Sky degli Eroi 27K" {{ old('route_choice') == 'Sky degli Eroi 27K' ? 'selected' : '' }}>Sky degli Eroi — 27 km / 1.700 D+</option>
                        <option value="Downhill degli Eroi 12K" {{ old('route_choice') == 'Downhill degli Eroi 12K' ? 'selected' : '' }}>Downhill degli Eroi — 12 km / 1.500 D-</option>
                        <option value="Santa Felicita 14K" {{ old('route_choice') == 'Santa Felicita 14K' ? 'selected' : '' }}>Santa Felicita — 14 km / 800 D+</option>
                    </select>

                    <select name="tshirt_size" required class="border p-2 rounded">
                        <option value="">-- Taglia Maglia --</option>
                        <option value="XS" {{ old('tshirt_size') == 'XS' ? 'selected' : '' }}>XS</option>
                        <option value="S" {{ old('tshirt_size') == 'S' ? 'selected' : '' }}>S</option>
                        <option value="M" {{ old('tshirt_size') == 'M' ? 'selected' : '' }}>M</option>
                        <option value="L" {{ old('tshirt_size') == 'L' ? 'selected' : '' }}>L</option>
                        <option value="XL" {{ old('tshirt_size') == 'XL' ? 'selected' : '' }}>XL</option>
                        <option value="XXL" {{ old('tshirt_size') == 'XXL' ? 'selected' : '' }}>XXL</option>
                    </select>
                </div>

                <div class="flex space-x-6 mb-8 p-4 bg-gray-50 border rounded">
                    <label class="flex items-center space-x-2">
                        <input type="hidden" name="shuttle_needed" value="0">
                        <input type="checkbox" name="shuttle_needed" value="1" {{ old('shuttle_needed') ? 'checked' : '' }}>
                        <span class="font-semibold">Necessità di Navetta</span>
                    </label>
                    
                    <label class="flex items-center space-x-2">
                        <input type="hidden" name="celiac" value="0">
                        <input type="checkbox" name="celiac" value="1" {{ old('celiac') ? 'checked' : '' }}>
                        <span class="font-semibold">Pasto per Celiaci</span>
                    </label>
                </div>

                <h3 class="font-bold text-lg mb-4 text-blue-600">Dettagli Pagamento</h3>
                <div class="mb-8 bg-blue-50 p-4 border rounded">
                    <label class="block font-bold mb-2">Importo Pagato (€)</label>
                    <input type="number" step="0.01" name="price_paid" value="{{ old('price_paid', '0.00') }}" required class="border p-2 rounded w-full md:w-1/3">
                    <p class="text-sm text-gray-600 mt-2">Inserisci "0" per un ospite VIP omaggio, oppure la cifra esatta pagata in contanti/bonifico. Verrà sommata all'incasso totale della dashboard.</p>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-lg text-lg transition shadow-lg">
                    Salva Iscritto e Invia Biglietto
                </button>
            </form>
        </div>
    </div>

</body>
</html>
