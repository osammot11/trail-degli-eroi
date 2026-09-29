<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTicketEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function handle()
    {
        $thankYouLink = route("checkout.success", [
            "order_code" => $this->ticket->order->group_code,
        ]);

        $htmlContent = "
            <h2>Ciao {$this->ticket->first_name}, iscrizione confermata!</h2>
            <p>La tua partecipazione al Trail degli Eroi è ufficiale.</p>
            <p>Puoi accedere alla tua area riservata per scaricare il tuo biglietto nominale in PDF in qualsiasi momento:</p>
            <p><a href='{$thankYouLink}' style='padding: 10px 20px; background-color: #c64735; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>Scarica il tuo Biglietto</a></p>
            <p>A presto!</p>

            <p>Se non riesci ad aprire il biglietto, copia e incolla nel browser questo link:</p>

            <p>{$thankYouLink}</p>
        ";

        // Chiamata API diretta e leggerissima a Brevo
        $response = Http::withHeaders([
            "api-key" => env("BREVO_API_KEY"),
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ])->post("https://api.brevo.com/v3/smtp/email", [
            "sender" => [
                "name" => env("BREVO_SENDER_NAME", "Tommaso Giovannoni"),
                "email" => env(
                    "BREVO_SENDER_EMAIL",
                    "info@tommasogiovannoni.com",
                ),
            ],
            "to" => [
                [
                    "email" => $this->ticket->email,
                    "name" =>
                        $this->ticket->first_name .
                        " " .
                        $this->ticket->last_name,
                ],
            ],
            "subject" => "Iscrizione confermata - Trail degli Eroi",
            "htmlContent" => $htmlContent,
        ]);

        // Controllo errori
        if ($response->failed()) {
            Log::error("Errore invio Brevo: " . $response->body());
            throw new \Exception("Chiamata API Brevo fallita.");
        }
    }
}
