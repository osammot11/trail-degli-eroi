<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Biglietto {{ $ticket->unique_ticket_code }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #191d2f; }
        .ticket-container { padding: 20px; margin: 20px auto; max-width: 600px; text-align: center; }
        .header { font-size: 32px; font-weight: bold; text-transform: uppercase; }
        .details { margin: 20px 0; text-align: left; line-height: 1.6; font-size: 18px; }
        .ticket-code { box-sizing: border-box; width: 100%; font-size: 24px; padding: 40px 0px; border: 1px solid #25386a; display: inline-block; margin-top: 15px; background-color: #d8ad4a }
        .small-p {font-size: 13px; color: #626569; line-height: 1.6;}
        .center-text {text-align: center;}
   </style>
</head>
<body>
    <div class="ticket-container">
        <div class="header">
            WALKING FRANCIGENA ULTRA MARATHON 2026
        </div>
        <p class="center-text">10/11 OTTOBRE 2026 - DA SIENA AD ACQUAPENDENTE</p>
        <div class="details">
            <p><strong>Partecipante:</strong> {{ $ticket->first_name }} {{ $ticket->last_name }}</p>
            <p><strong>Percorso:</strong> {{ $ticket->route_choice }}</p>
            <p><strong>Taglia Maglia:</strong> {{ $ticket->tshirt_size }}</p>
            <p><strong>Celiachia:</strong> {{ $ticket->celiac }}</p>
            <p><strong>Navetta:</strong> {{ $ticket->shuttle_needed }}</p>
            <p class="small-p">Legenda per i campi "Celiachia" e "Navetta":<br>0 = NO<br>1 = SÌ</p>
            <p><strong>Importo pagato:</strong> {{ $ticket->price_paid }}€</p>
            <p class="small-p">Il biglietto funge da ricevuta di pagamento elettronico dell’importo sopra indicato. Nel caso si necessiti di fattura fiscale nominativa, preghiamo di contattare via mail l’indirizzo info@traildeglieroi.it inserendo nell’oggetto il codice biglietto e il tipo di richiesta.</p>
        </div>
        <div class="ticket-code">
            <p>CODICE BIGLIETTO</p>
            <p style="font-weight: bold;">{{ $ticket->unique_ticket_code }}</p>
        </div>
        <p class="small-p center-text">Ti ricordiamo che il biglietto è nominativo, ogni partecipante dovrà esserne munito al momento della partenza. Il formato cartaceo è opzionale, la versione digitale in PDF è accettata dallo staff addetto ai controlli.</p>
    </div>
</body>
</html>
