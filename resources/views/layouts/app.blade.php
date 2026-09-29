<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Trail degli Eroi' }}</title>
    <meta name="description" content="{{ $description ?? 'Trail degli Eroi: corsa, storia e natura sui sentieri del Monte Grappa.' }}">
    @isset($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endisset
    <link rel="icon" type="image/svg+xml" href="/images/trail-eroi/logo-mark.svg">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? 'Trail degli Eroi' }}">
    <meta property="og:description" content="{{ $description ?? 'Trail degli Eroi: corsa, storia e natura sui sentieri del Monte Grappa.' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:image" content="{{ url('/images/trail-eroi/hero-monte-grappa.png') }}">
    <meta property="og:locale" content="it_IT">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'Trail degli Eroi' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Trail degli Eroi: corsa, storia e natura sui sentieri del Monte Grappa.' }}">
    <meta name="twitter:image" content="{{ url('/images/trail-eroi/hero-monte-grappa.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@300;400;500;600;700;800;900&family=Source+Serif+4:ital,wght@0,600;1,600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/style.css?v=62">
    <link rel="stylesheet" href="/iscritti-home.css?v=1.3">
    @stack('head')
</head>

<body class="eroi-theme {{ $bodyClass ?? '' }}">
    <div class="site-header">        
        @include('partials.navbar')
    </div>

    @yield('content')

    @include('partials.footer')
    @include('partials.cookie-banner')

    @stack('scripts')
</body>
</html>
