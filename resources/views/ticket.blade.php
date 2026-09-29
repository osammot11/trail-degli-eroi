<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Biglietto Trail degli Eroi</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; }
        .ticket-container { border: 2px dashed #000; padding: 20px; margin: 20px auto; max-width: 600px; text-align: center; }
        .header { background-color: #f3f4f6; padding: 15px; font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .details { margin: 20px 0; text-align: left; line-height: 1.6; font-size: 16px; }
        .footer { font-size: 12px; color: #666; margin-top: 30px; }
        .ticket-code { font-size: 20px; font-weight: bold; padding: 15px; border: 2px solid #333; display: inline-block; margin-top: 15px; background-color: #f9f9f9; }
    </style>
</head>
<body>

    <div class="ticket-container">
        <div class="header">
            Trail degli Eroi
        </div>
        
        <div class="details">
            <p><strong>Partecipante:</strong> {{ $ticket->first_name }} {{ $ticket->last_name }}</p>
            <p><strong>Taglia Maglia:</strong> {{ $ticket->tshirt_size }}</p>
            <p><strong>Gruppo/Ordine:</strong> {{ $ticket->order->group_code }}</p>
        </div>

        <div class="ticket-code">
            CODICE BIGLIETTO <br>
            <span style="color: #d32f2f;">{{ $ticket->unique_ticket_code }}</span>
        </div>

        <div class="footer">
            Mostra questo biglietto (stampato o su smartphone) al banco accettazione.
        </div>
    </div>

</body>
</html>
