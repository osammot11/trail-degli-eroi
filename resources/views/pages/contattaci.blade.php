@extends('layouts.app')

@section('content')
    <main>
        <section>
            <h1>Contatti</h1>
            <p class="top-margin-large">Per informazioni su iscrizioni, gare e aspetti organizzativi puoi scrivere allo staff del Trail degli Eroi.</p>
            <a href="mailto:info@traildeglieroi.it" class="btn top-margin-xl mobile-fullwidth">Scrivici</a>
        </section>

        <section class="grid-2">
            <div class="card">
                <h2>Email</h2>
                <p class="top-margin-large"><a class="hyperlink" href="mailto:info@traildeglieroi.it">info@traildeglieroi.it</a></p>
            </div>
            <div class="card">
                <h2>Luogo evento</h2>
                <p class="top-margin-large">Valle Santa Felicita, Romano d'Ezzelino (VI), Monte Grappa.</p>
            </div>
        </section>
    </main>
@endsection
