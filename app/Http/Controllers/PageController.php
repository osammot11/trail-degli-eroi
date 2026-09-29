<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Throwable;

class PageController extends Controller
{
    private const PAGES = [
        '' => [
            'view' => 'pages.home',
            'title' => 'Trail degli Eroi 2026 | Monte Grappa',
            'description' => 'Trail degli Eroi 2026: cinque sfide tra crinali, trincee e memoria sul Monte Grappa.',
            'canonical' => 'https://www.traildeglieroi.it/',
            'bodyClass' => 'home-page',
        ],
        'contattaci' => [
            'view' => 'pages.contattaci',
            'title' => 'Contatti | Trail degli Eroi 2026',
            'description' => 'Contatta lo staff Trail degli Eroi per informazioni su iscrizioni, gare e organizzazione.',
            'canonical' => 'https://www.traildeglieroi.it/contatti/',
        ],
        'contatti' => [
            'view' => 'pages.contattaci',
            'title' => 'Contatti | Trail degli Eroi 2026',
            'description' => 'Contatta lo staff Trail degli Eroi per informazioni su iscrizioni, gare e organizzazione.',
            'canonical' => 'https://www.traildeglieroi.it/contatti/',
        ],
        'cookie-policy' => [
            'view' => 'pages.cookie-policy',
            'title' => 'Cookie Policy | Trail degli Eroi',
            'description' => 'Informativa sull\'uso dei cookie tecnici e di preferenza del sito Trail degli Eroi.',
            'canonical' => 'https://www.traildeglieroi.it/cookie-policy/',
        ],
        'partnership' => [
            'view' => 'pages.partnership',
            'title' => 'Partner | Trail degli Eroi 2026',
            'description' => 'Partner, supporter e istituzioni del Trail degli Eroi 2026.',
            'canonical' => 'https://www.traildeglieroi.it/partnership/',
        ],
        'privacy-policy' => [
            'view' => 'pages.privacy-policy',
            'title' => 'Privacy Policy | Trail degli Eroi',
            'description' => 'Informativa privacy per iscrizioni e contatti del sito Trail degli Eroi.',
            'canonical' => 'https://www.traildeglieroi.it/privacy-policy/',
        ],
        'regolamento' => [
            'view' => 'pages.regolamento',
            'title' => 'Regolamento | Trail degli Eroi 2026',
            'description' => 'Regolamento sintetico del Trail degli Eroi: partecipazione, responsabilità e gare.',
            'canonical' => 'https://www.traildeglieroi.it/regolamento/',
        ],
        'termini-condizioni' => [
            'view' => 'pages.regolamento',
            'title' => 'Regolamento | Trail degli Eroi 2026',
            'description' => 'Regolamento sintetico del Trail degli Eroi: partecipazione, responsabilità e gare.',
            'canonical' => 'https://www.traildeglieroi.it/regolamento/',
        ],
    ];

    private const HOME_ALIASES = [
        'come-arrivare',
        'gallery',
        'orario',
        'percorsi',
        'ricettivita',
        'styleguide',
    ];

    public function show(?string $slug = ''): Response
    {
        $slug = trim((string) $slug, '/');
        $slug = Str::replaceEnd('.php', '', $slug);

        if (in_array($slug, self::HOME_ALIASES, true)) {
            $slug = '';
        }

        abort_unless(array_key_exists($slug, self::PAGES), 404);

        $page = self::PAGES[$slug];
        $data = [
            'title' => $page['title'],
            'description' => $page['description'],
            'canonical' => $page['canonical'],
            'bodyClass' => $page['bodyClass'] ?? '',
        ];

        if ($page['view'] === 'pages.home') {
            try {
                $data['totalIscritti'] = Ticket::whereNotNull('unique_ticket_code')->count();
            } catch (Throwable $e) {
                $data['totalIscritti'] = 0;
            }
        }

        return response()->view($page['view'], $data);
    }
}
