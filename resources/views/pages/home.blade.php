@extends('layouts.app')

@push('head')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SportsEvent",
        "name": "Trail degli Eroi 2026",
        "startDate": "2026-09-20T07:00:00+02:00",
        "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
        "eventStatus": "https://schema.org/EventScheduled",
        "url": "https://www.traildeglieroi.it/",
        "image": "{{ url('/images/trail-eroi/hero-monte-grappa.png') }}",
        "description": "Trail running sul Monte Grappa, tra crinali, trincee e memoria: cinque esperienze per vivere la montagna da protagonisti.",
        "location": {"@@type":"Place","name":"Valle Santa Felicita","address":{"@@type":"PostalAddress","addressLocality":"Romano d'Ezzelino","addressRegion":"Veneto","addressCountry":"IT"}},
        "organizer": {"@@type":"Organization","name":"I Lupi Team – Adventure Run","url":"https://www.traildeglieroi.it/"}
    }
    </script>
@endpush

@section('content')
    <div class="home-hero eroi-hero">
        <section class="hero-height light-color eroi-hero__content">
            <p class="hero-kicker">Monte Grappa · 20 settembre 2026</p>
            <h1 class="hero-title top-margin-mid">DOVE LA CORSA<br>INCONTRA <span>LA STORIA.</span></h1>
            <p class="hero-copy top-margin-large">Crinali, trincee e panorami aperti sulle Prealpi. Cinque modi di attraversare il Grappa, un solo modo di sentirlo.</p>
            <div class="hero-actions top-margin-xl">
                <a href="/iscrizione" class="btn">Iscriviti ora</a>
                <a href="#gare" class="btn btn-outline">Scegli la tua sfida</a>
            </div>
        </section>
        <div class="race-strip" aria-label="Dati dell'evento">
            <div><strong>20.09.2026</strong><span>Quindicesima edizione</span></div>
            <div><strong>5 SFIDE</strong><span>Per ogni tipo di eroe</span></div>
            <div><strong>1.775 M</strong><span>Cima Monte Grappa</span></div>
            <div><strong>ROMANO D'EZZELINO</strong><span>Valle Santa Felicita</span></div>
        </div>
    </div>

    <main>
        <section class="grid-2 intro-section eroi-intro">
            <div>
                <p class="section-kicker">Sport · natura · memoria</p>
                <h2>Non solo un traguardo.<br>Un viaggio nella memoria.</h2>
                <p class="top-margin-large">Da quindici edizioni il Trail degli Eroi attraversa i luoghi che hanno segnato la storia del Monte Grappa: sentieri, mulattiere, trincee e cime sospese tra pianura e Dolomiti.</p>
                <p class="top-margin-large">Si corre nel silenzio dei boschi, si sale fino al Sacrario e si torna a valle portando con sé qualcosa che va oltre il tempo e il cronometro.</p>
            </div>
            <aside class="eroi-poster-card" aria-label="Trail degli Eroi 2026, Monte Grappa">
                <div class="poster-visual">
                    <img src="/images/trail-eroi/logo-mark.svg" alt="Simbolo Trail degli Eroi" loading="lazy" decoding="async">
                    <p>XV EDIZIONE · 2026</p>
                    <strong>OLTRE<br>LA<br>SFIDA.</strong>
                    <span>MONTE GRAPPA</span>
                </div>
                <div class="poster-caption"><span class="numero">{{ $totalIscritti }}</span><span>Eroi già al via</span></div>
            </aside>
        </section>

        <section id="gare" class="race-section eroi-races">
            <div class="section-heading">
                <div><p class="section-kicker">Le protagoniste del 2026</p><h2>Scegli la tua montagna.</h2></div>
                <p>Dalla lunga traversata dei crinali alla discesa più tecnica, fino alla camminata aperta a tutti.</p>
            </div>
            <div class="race-grid top-margin-xl">
                <article class="eroi-race-card eroi-race-card--featured">
                    <div class="race-meta"><span>01</span><span>2 punti ITRA</span></div>
                    <div><p class="race-card__number">46K</p><h3>Trail degli Eroi</h3><p>Il percorso integrale sui crinali di Cima Grappa: 2.500 metri D+, tecnica, resistenza e memoria.</p><a href="/iscrizione?percorso=Trail%20degli%20Eroi%2046K" class="text-link">Scopri la gara <span>↗</span></a></div>
                </article>
                <article class="eroi-race-card">
                    <div class="race-meta"><span>02</span><span>1.700 D+</span></div>
                    <div><p class="race-card__number">27K</p><h3>Sky degli Eroi</h3><p>Una salita decisa verso Cima Grappa e 27 chilometri di natura, storia e cielo aperto.</p><a href="/iscrizione?percorso=Sky%20degli%20Eroi%2027K" class="text-link">Scopri la gara <span>↗</span></a></div>
                </article>
                <article class="eroi-race-card eroi-race-card--red">
                    <div class="race-meta"><span>03</span><span>Zero salite</span></div>
                    <div><p class="race-card__number">12K</p><h3>Downhill degli Eroi</h3><p>1.500 metri di discesa, passaggi tecnici e pura velocità dalla cima alla Valle Santa Felicita.</p><a href="/iscrizione?percorso=Downhill%20degli%20Eroi%2012K" class="text-link">Scopri la gara <span>↗</span></a></div>
                </article>
                <article class="eroi-race-card eroi-race-card--light">
                    <div class="race-meta"><span>04</span><span>Aperta a tutti</span></div>
                    <div><p class="race-card__number">14K</p><h3>Santa Felicita</h3><p>La novità non competitiva per camminare, respirare e vivere il paesaggio senza fretta.</p><a href="/iscrizione?percorso=Santa%20Felicita%2014K" class="text-link">Scopri la gara <span>↗</span></a></div>
                </article>
            </div>
        </section>

        <section id="storia" class="eroi-manifesto">
            <div class="manifesto-mark">22.910</div>
            <div>
                <p class="section-kicker">Correre per ricordare</p>
                <h2>“Se non fosse degli Eroi, sarebbe solo un Trail.”</h2>
                <p class="top-margin-large">Il percorso raggiunge il Sacrario di Cima Grappa, dove riposano 22.910 soldati. Un passaggio che invita a rallentare, guardarsi intorno e trasformare la fatica in consapevolezza.</p>
                <a href="https://www.traildeglieroi.it/la-storia-del-grappa/" class="btn btn-2 top-margin-xl" target="_blank" rel="noopener">Scopri la storia del Grappa</a>
            </div>
        </section>

        <section id="programma" class="weekend-section">
            <div class="section-heading">
                <div><p class="section-kicker">Un intero weekend sul Grappa</p><h2>Prima, durante, dopo.</h2></div>
                <p>La gara è il cuore di un’esperienza fatta di territorio, comunità e racconto.</p>
            </div>
            <div class="grid-3 top-margin-xl">
                <article class="card program-card"><span class="card-index">01 · SABATO</span><h3>Vivi il territorio</h3><p class="top-margin-large">Esperienze in alpeggio, cultura locale e una serata di parole, immagini e musica.</p></article>
                <article class="card program-card"><span class="card-index">02 · DOMENICA</span><h3>Corri la storia</h3><p class="top-margin-large">Partenze dalla Valle Santa Felicita, assistenza lungo i percorsi e festa all’arrivo.</p></article>
                <article class="card program-card"><span class="card-index">03 · SEMPRE</span><h3>Fai squadra</h3><p class="top-margin-large">Atleti, accompagnatori, volontari e famiglie: il Trail vive grazie alla sua comunità.</p></article>
            </div>
        </section>

        <section class="community-band">
            <div><p class="section-kicker">La forza del branco</p><h2>Più siamo,<br>più è bello.</h2></div>
            <p>Affronta il Trail insieme al tuo team. Per i gruppi di almeno 10 partecipanti è previsto un pettorale gratuito ogni 10 iscritti.</p>
            <a href="mailto:info@traildeglieroi.it" class="btn btn-2">Parliamone</a>
        </section>

        <section class="quick-links">
            <a href="/regolamento"><span>Regolamento</span><strong>→</strong></a>
            <a href="#programma"><span>Programma</span><strong>→</strong></a>
            <a href="/contattaci"><span>Contatti</span><strong>→</strong></a>
        </section>

        <section class="faq-section">
            <p class="section-kicker">Prima di partire</p><h2>Domande frequenti</h2>
            <div class="faq-accordion top-margin-xl">
                <div class="faq-item"><button class="faq-question" aria-expanded="false"><span>Quando e dove si svolge il Trail degli Eroi?</span><span class="faq-icon">+</span></button><div class="faq-answer"><p>La quindicesima edizione è in programma domenica 20 settembre 2026, con partenze dalla Valle Santa Felicita a Romano d’Ezzelino.</p></div></div>
                <div class="faq-item"><button class="faq-question" aria-expanded="false"><span>Quale distanza posso scegliere?</span><span class="faq-icon">+</span></button><div class="faq-answer"><p>Puoi scegliere il Trail da 46 km, la Sky da 27 km, la Downhill da 12 km o la Santa Felicita da 14 km. Il Mini Trail è dedicato ai più piccoli.</p></div></div>
                <div class="faq-item"><button class="faq-question" aria-expanded="false"><span>La Santa Felicita è competitiva?</span><span class="faq-icon">+</span></button><div class="faq-answer"><p>No. È una camminata ludico-motoria aperta a tutti e non richiede certificazione medica agonistica.</p></div></div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="/accordion.js"></script>
@endpush
