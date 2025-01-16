<!DOCTYPE html>
<html>
    <head>
        <title>La tua prenotazione è stata confermata</title>
    </head>
    <body>

        <center>
            <h2>Email di conferma prenotazione</h2>
        </center>

        <p>Ciao,</p>
        <p>La tua prenotazione è stata confermata. Di seguito un riepilogo:</p>

        <br><br>

        <p><strong>Nome:</strong> {{ $nome }}</p>
        <p><strong>Cognome:</strong> {{ $cognome }}</p>
        <p><strong>Data:</strong> {{ $data_prenotazione }}</p>
        <p><strong>Ora inizio:</strong> {{ $ora_inizio }}</p>
        <p><strong>Ora fine:</strong> {{ $ora_fine }}</p>
        <p><strong>Tipo prenotazione:</strong> {{ $tipo_prenotazione }}</p>
        <p><strong>Email:</strong> {{ $email }}</p>
        <p><strong>Telefono:</strong> {{ $telefono }}</p>
        <p><strong>Indirizzo:</strong> {{ $indirizzo }}</p>

        <br><br>

        <strong>Grazie per aver prenotato con noi, a presto.</strong>
        <h4>Il team di Coworking</h4>

    </body>
</html>
