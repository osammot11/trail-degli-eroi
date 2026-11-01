<?php

namespace App\Jobs;

use App\Models\TicketChange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendRaceChangeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public TicketChange $change)
    {
    }

    public function handle(): void
    {
        $change = $this->change->loadMissing('ticket');
        $ticket = $change->ticket;
        $difference = (float) $change->amount_difference;

        $financialNote = match (true) {
            $difference > 0 => 'Differenza da versare: € '.number_format($difference, 2, ',', '.').'. Il comitato ti contatterà con le modalità di pagamento.',
            $difference < 0 => 'Importo a tuo favore: € '.number_format(abs($difference), 2, ',', '.').'. Il comitato ti contatterà per la gestione.',
            default => 'Il cambio non comporta differenze di prezzo.',
        };

        $htmlContent = sprintf(
            '<h2>Ciao %s, il cambio distanza è confermato.</h2><p>La tua iscrizione è stata spostata da <strong>%s</strong> a <strong>%s</strong>.</p><p>%s</p><p>Il codice del tuo biglietto rimane invariato: <strong>%s</strong>.</p><p>A presto!</p>',
            e($ticket->first_name),
            e($change->from_value),
            e($change->to_value),
            e($financialNote),
            e($ticket->unique_ticket_code ?? 'in assegnazione'),
        );

        $response = Http::withHeaders([
            'api-key' => env('BREVO_API_KEY'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => env('BREVO_SENDER_NAME', 'Trail degli Eroi'),
                'email' => env('BREVO_SENDER_EMAIL', 'info@traildeglieroi.it'),
            ],
            'to' => [[
                'email' => $ticket->email,
                'name' => $ticket->first_name.' '.$ticket->last_name,
            ]],
            'subject' => 'Cambio distanza confermato - Trail degli Eroi',
            'htmlContent' => $htmlContent,
        ]);

        if ($response->failed()) {
            Log::error('Errore invio notifica cambio distanza Brevo: '.$response->body(), [
                'ticket_change_id' => $change->id,
            ]);

            throw new \RuntimeException('Chiamata API Brevo fallita.');
        }
    }
}
