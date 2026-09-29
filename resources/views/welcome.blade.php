<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iscrizione Trail degli Eroi 2026</title>
    <link rel="stylesheet" href="{{ asset('css/style.css?v=1.8') }}">
</head>
<body class="center-items checkout-body">

    <div class="checkout-wrapper stack-large">
        <div class="center-items"><a href="/"><img src="/images/trail-eroi/logo-mark.svg" alt="Trail degli Eroi" style="width:84px;height:auto"></a></div>
        <h1 class="center-text">Iscrizione Trail degli Eroi</h1>
        <p class="center-text">Scegli la tua sfida sul Monte Grappa.</p>

        <form action="{{ route('checkout.store') }}" method="POST" id="marathon-form">
            @if($errors->any())
                <div class="bg-primary-hover">
                    <strong>Attenzione!</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @csrf

            <div class="box-associazione stack-mid">
                <label>
                    <input type="checkbox" name="is_sports_association" value="1" onchange="document.getElementById('assoc_name_div').style.display = this.checked ? 'block' : 'none'">
                    <span>Siamo un'associazione sportiva</span>
                </label>
                <div class="label-box" id="assoc_name_div" style="display:none">
                    <label>Nome Associazione</label>
                    <input type="text" name="association_name">
                </div>
            </div>

            <div id="tickets-container">
                <div class="ticket-block top-margin-large">
                    <h3>Partecipante #1</h3>

                    <div class="grid-2 gap-20 top-margin-mid">
                        <div class="label-box">
                            <label>Nome</label>
                            <input type="text" name="tickets[0][first_name]" placeholder="Nome" required>
                        </div>

                        <div class="label-box">
                            <label>Cognome</label>
                            <input type="text" name="tickets[0][last_name]" placeholder="Cognome" required>
                        </div>

                        <div class="label-box">
                            <label>Scelta del percorso</label>
                            <select name="tickets[0][route_choice]" required>
                                <option value="">Scegli la gara</option>
                                <option value="Trail degli Eroi 46K" {{ request('percorso') == 'Trail degli Eroi 46K' ? 'selected' : '' }}>Trail degli Eroi — 46 km / 2.500 D+</option>
                                <option value="Sky degli Eroi 27K" {{ request('percorso') == 'Sky degli Eroi 27K' ? 'selected' : '' }}>Sky degli Eroi — 27 km / 1.700 D+</option>
                                <option value="Downhill degli Eroi 12K" {{ request('percorso') == 'Downhill degli Eroi 12K' ? 'selected' : '' }}>Downhill degli Eroi — 12 km / 1.500 D-</option>
                                <option value="Santa Felicita 14K" {{ request('percorso') == 'Santa Felicita 14K' ? 'selected' : '' }}>Santa Felicita — 14 km / 800 D+</option>
                            </select>
                        </div>

                        <div class="label-box">
                            <label>Data di nascita</label>
                            <input type="date" name="tickets[0][dob]" required title="Data di nascita">
                        </div>

                        <div class="label-box">
                            <label>Città di nascita</label>
                            <input type="text" name="tickets[0][birth_place]" placeholder="Luogo di nascita" required>
                        </div>

                        <div class="label-box">
                            <label>Nazionalità (sigla)</label>
                            <input type="text" name="tickets[0][nationality]" placeholder="Nazionalità (es. IT)" maxlength="2" oninput="this.value = this.value.toUpperCase()" required>
                        </div>

                        <div class="label-box">
                            <label>Codice fiscale (se cittadino italiano)</label>
                            <input type="text" name="tickets[0][codice_fiscale]" placeholder="Codice Fiscale (Obbligatorio se IT)" oninput="this.value = this.value.toUpperCase()">
                        </div>

                        <div class="label-box">
                            <label>Indirizzo mail</label>
                            <input type="email" name="tickets[0][email]" placeholder="Email" required>
                        </div>

                        <div class="label-box">
                            <label>Telefono</label>
                            <input type="text" name="tickets[0][phone]" placeholder="Telefono" required>
                        </div>

                        <div class="label-box">
                            <label>Taglia maglia</label>
                            <select name="tickets[0][tshirt_size]" required>
                                <option value="">Taglia Maglia</option>
                                <option value="XS">XS</option>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid-3 gap-20 top-margin-large">
                        <div class="label-box">
                            <label>Indirizzo</label>
                            <input type="text" name="tickets[0][residence_address]" placeholder="Indirizzo" required>
                        </div>

                        <div class="label-box">
                            <label>Città</label>
                            <input type="text" name="tickets[0][city]" placeholder="Città" required>
                        </div>

                        <div class="label-box">
                            <label>CAP</label>
                            <input type="text" name="tickets[0][zip_code]" placeholder="CAP" required>
                        </div>

                        <div class="label-box">
                            <label>Paese</label>
                            <select class="country-select" onchange="toggleCountry(this)">
                                <option value="Italia" selected>Italia</option>
                                <option value="Estero">Estero (Specifica...)</option>
                            </select>

                            <input type="hidden" name="tickets[0][country]" class="country-hidden" value="Italia">

                            <input type="text" placeholder="Specifica nazione" class="country-text hidden" oninput="updateCountryHidden(this)">
                        </div>

                        <div class="label-box">
                            <label>Regione</label>
                            <select name="tickets[0][region]" class="region-select" onchange="updateProvinces(this)" required></select>

                            <input type="text" name="tickets[0][region]" placeholder="Regione/Stato" class="region-text hidden" disabled required>
                        </div>

                        <div class="label-box">
                            <label>Provincia</label>
                            <select name="tickets[0][province]" class="province-select" required></select>

                            <input type="text" name="tickets[0][province]" placeholder="Provincia/Contea" class="province-text hidden" disabled required>
                        </div>
                    </div>

                    <div class="top-margin-large">
                        <label>
                            <input type="hidden" name="tickets[0][shuttle_needed]" value="0">
                            <input type="checkbox" name="tickets[0][shuttle_needed]" value="1"> Navetta?
                        </label>

                        <label>
                            <input type="hidden" name="tickets[0][celiac]" value="0">
                            <input type="checkbox" name="tickets[0][celiac]" value="1"> Celiaco?
                        </label>
                    </div>
                </div>
            </div>

            <button class="top-margin-large grey-btn" type="button" onclick="addParticipant()">+ Aggiungi un altro partecipante</button>

            <input type="hidden" name="is_mutua_member" value="0">

            <div class="stack-mid">
              <div class="label-box top-margin-xl">
                  <label>Metodo di Pagamento</label>
                  <select name="payment_method">
                      <option value="stripe">Carta</option>
                      <option value="paypal">PayPal</option>
                  </select>
              </div>
            
              <label>
                  <input type="checkbox" value="1" checked required>Procedendo al pagamento dichiaro di accettare la <a href="/privacy-policy">privacy policy</a> e il <a href="/regolamento">regolamento</a> dell'evento
              </label>

              <label>
                  <input type="checkbox" value="1" checked required>Acconsento al trattamento dei miei dati particolari relativi alla salute (es. celiachia), forniti volontariamente, al fine di gestire correttamente le esigenze alimentari durante l’evento, ai sensi dell’art. 9 del Regolamento UE 2016/679 (GDPR).
              </label>

              <button class="btn fullwidth" id="payment-button" type="submit">Procedi al Pagamento - {{ $currentPrice }}€</button>
            </div>
        </form>
    </div>

    <script>
        // --- 1. DATI GEOGRAFICI ---
        const italyLocations = {
            "Abruzzo": ["Chieti", "L'Aquila", "Pescara", "Teramo"],
            "Basilicata": ["Matera", "Potenza"],
            "Calabria": ["Catanzaro", "Cosenza", "Crotone", "Reggio Calabria", "Vibo Valentia"],
            "Campania": ["Avellino", "Benevento", "Caserta", "Napoli", "Salerno"],
            "Emilia-Romagna": ["Bologna", "Ferrara", "Forlì-Cesena", "Modena", "Parma", "Piacenza", "Ravenna", "Reggio Emilia", "Rimini"],
            "Friuli-Venezia Giulia": ["Gorizia", "Pordenone", "Trieste", "Udine"],
            "Lazio": ["Frosinone", "Latina", "Rieti", "Roma", "Viterbo"],
            "Liguria": ["Genova", "Imperia", "La Spezia", "Savona"],
            "Lombardia": ["Bergamo", "Brescia", "Como", "Cremona", "Lecco", "Lodi", "Mantova", "Milano", "Monza e della Brianza", "Pavia", "Sondrio", "Varese"],
            "Marche": ["Ancona", "Ascoli Piceno", "Fermo", "Macerata", "Pesaro e Urbino"],
            "Molise": ["Campobasso", "Isernia"],
            "Piemonte": ["Alessandria", "Asti", "Biella", "Cuneo", "Novara", "Torino", "Verbano-Cusio-Ossola", "Vercelli"],
            "Puglia": ["Bari", "Barletta-Andria-Trani", "Brindisi", "Foggia", "Lecce", "Taranto"],
            "Sardegna": ["Cagliari", "Nuoro", "Oristano", "Sassari", "Sud Sardegna"],
            "Sicilia": ["Agrigento", "Caltanissetta", "Catania", "Enna", "Messina", "Palermo", "Ragusa", "Siracusa", "Trapani"],
            "Toscana": ["Arezzo", "Firenze", "Grosseto", "Livorno", "Lucca", "Massa-Carrara", "Pisa", "Pistoia", "Prato", "Siena"],
            "Trentino-Alto Adige": ["Bolzano", "Trento"],
            "Umbria": ["Perugia", "Terni"],
            "Valle d'Aosta": ["Aosta"],
            "Veneto": ["Belluno", "Padova", "Rovigo", "Treviso", "Venezia", "Verona", "Vicenza"]
        };

        function populateRegions(block) {
            const regionSelect = block.querySelector('.region-select');
            regionSelect.innerHTML = '<option value="">Seleziona Regione</option>';
            Object.keys(italyLocations).sort().forEach(region => {
                const isSelected = region === 'Veneto' ? 'selected' : '';
                regionSelect.innerHTML += `<option value="${region}" ${isSelected}>${region}</option>`;
            });
            updateProvinces(regionSelect, true);
        }

        function updateProvinces(regionSelect, setVicenzaDefault = false) {
            const block = regionSelect.closest('.ticket-block');
            const provinceSelect = block.querySelector('.province-select');
            const region = regionSelect.value;

            provinceSelect.innerHTML = '<option value="">Seleziona Provincia</option>';
            if (italyLocations[region]) {
                italyLocations[region].sort().forEach(prov => {
                    const isSelected = (setVicenzaDefault && prov === 'Vicenza') ? 'selected' : '';
                    provinceSelect.innerHTML += `<option value="${prov}" ${isSelected}>${prov}</option>`;
                });
            }
        }

        function toggleCountry(countrySelect) {
            const block = countrySelect.closest('.ticket-block');
            const isItaly = countrySelect.value === 'Italia';
            const countryHidden = block.querySelector('.country-hidden');
            const countryText = block.querySelector('.country-text');
            const regionSelect = block.querySelector('.region-select');
            const regionText = block.querySelector('.region-text');
            const provinceSelect = block.querySelector('.province-select');
            const provinceText = block.querySelector('.province-text');

            if (isItaly) {
                countryHidden.value = 'Italia';
                countryText.classList.add('hidden');
                countryText.required = false;
                regionText.classList.add('hidden');
                regionText.disabled = true;
                regionSelect.classList.remove('hidden');
                regionSelect.disabled = false;
                provinceText.classList.add('hidden');
                provinceText.disabled = true;
                provinceSelect.classList.remove('hidden');
                provinceSelect.disabled = false;
            } else {
                countryHidden.value = countryText.value;
                countryText.classList.remove('hidden');
                countryText.required = true;
                regionSelect.classList.add('hidden');
                regionSelect.disabled = true;
                regionText.classList.remove('hidden');
                regionText.disabled = false;
                provinceSelect.classList.add('hidden');
                provinceSelect.disabled = true;
                provinceText.classList.remove('hidden');
                provinceText.disabled = false;
            }
        }

        function updateCountryHidden(textInput) {
            const block = textInput.closest('.ticket-block');
            block.querySelector('.country-hidden').value = textInput.value;
        }

        function updateTotal() {
            const basePrice = {{ $currentPrice }};
            const finalPricePerTicket = basePrice;

            const ticketBlocks = document.querySelectorAll('.ticket-block');
            const count = ticketBlocks.length;

            const freeTickets = Math.floor(count / 11);
            const paidTickets = count - freeTickets;
            const total = paidTickets * finalPricePerTicket;

            const btn = document.getElementById('payment-button');
            btn.innerText = `Procedi al Pagamento - ${total}€`;
        }

        let participantCount = 1;

        function addParticipant() {
            const container = document.getElementById('tickets-container');
            const clone = container.querySelector('.ticket-block').cloneNode(true);

            participantCount++;

            clone.querySelectorAll('input, select').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/\[\d+\]/, '[' + participantCount + ']');
                }

                if (input.type !== 'hidden' && input.type !== 'checkbox') {
                    input.value = "";
                }

                if (input.type === 'checkbox') {
                    input.checked = false;
                }
            });

            clone.querySelector('.country-select').value = "Italia";
            clone.querySelector('.country-hidden').value = "Italia";
            toggleCountry(clone.querySelector('.country-select'));
            populateRegions(clone);

            container.appendChild(clone);

            reindexParticipants();
            updateTotal();
        }

        function removeParticipant(button) {
            button.closest('.ticket-block').remove();

            reindexParticipants();
            updateTotal();
        }

        function reindexParticipants() {
            const blocks = document.querySelectorAll('.ticket-block');

            blocks.forEach((block, index) => {
                const h3 = block.querySelector('h3');

                if (index === 0) {
                    h3.innerHTML = "Partecipante #1";
                } else {
                    h3.innerHTML = `Partecipante #${index + 1} <button class="remove-btn" type="button" onclick="removeParticipant(this)">Rimuovi</button>`;
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const firstBlock = document.querySelector('.ticket-block');
            populateRegions(firstBlock);
            updateTotal();
        });
    </script>
</body>
</html>
