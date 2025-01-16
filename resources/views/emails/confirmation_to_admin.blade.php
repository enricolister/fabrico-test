<!DOCTYPE html>
<html>
    <head>
        <title>E' stata inserita una nuova prenotazione</title>
    </head>
    <body>

        <center>
            <h2>Nuova prenotazione per il Coworking</h2>
        </center>

        <p>Ciao,</p>
        <p>Il cliente {{ $nome }} {{ $cognome}} ha inserito una nuova prenotazione</p>

        <br><br>
        <p>DATI DELLA PRENOTAZIONE:</p>

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

        <h4>Il team di Coworking</h4>

    </body>
</html>
