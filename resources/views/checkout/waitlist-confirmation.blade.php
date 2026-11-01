<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista d’attesa - Trail degli Eroi</title>
    <link rel="stylesheet" href="{{ asset('css/style.css?v=1.9') }}">
</head>
<body>
    <div class="checkout-page">
        <div class="checkout-wrapper checkout-narrow center-text">
            <a href="{{ route('home') }}" class="brand-link" aria-label="Trail degli Eroi">
                <img class="checkout-logo" src="{{ asset('images/trail-eroi/logo-mark.svg') }}" alt="Trail degli Eroi">
            </a>

            <p class="section-kicker">Richiesta ricevuta</p>
            <h1>Sei in lista d’attesa</h1>
            <p>Non è stato effettuato alcun pagamento. La priorità segue rigorosamente data e ora di inserimento.</p>

            <div class="waitlist-confirmation-list">
                @foreach($order->tickets as $ticket)
                    <article class="waitlist-confirmation-card">
                        <span>Posizione attuale</span>
                        <strong>#{{ $positions[$ticket->id] }}</strong>
                        <h2>{{ $ticket->first_name }} {{ $ticket->last_name }}</h2>
                        <p>{{ $ticket->route_choice }}</p>
                    </article>
                @endforeach
            </div>

            <p class="muted-text">Codice richiesta: <strong>{{ $order->group_code }}</strong></p>
            <p class="muted-text">La posizione potrà avanzare in caso di rinunce. Le comunicazioni saranno inviate ai recapiti indicati.</p>

            <a class="btn fullwidth" href="{{ route('home') }}">Torna al sito</a>
        </div>
    </div>
</body>
</html>
