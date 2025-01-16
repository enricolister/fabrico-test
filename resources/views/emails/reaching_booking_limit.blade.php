<!DOCTYPE html>
<html>
    <head>
        <title>Il limite di prenotazioni per il giorno {{ $data_prenotazione }} è vicino</title>
    </head>
    <body>

        <center>
            <h2>Il limite di prenotazioni per il Coworking quasi raggiunto</h2>
        </center>

        <p>Ciao,</p>
        <p>Per il giorno {{ $data_prenotazione }} sono state ricevute {{ $numero_prenotazioni}} prenotazioni.</p>

        <br><br>
        <p>Il limite massimo di prenotazioni in una giornata è <strong>{{ $numero_max_prenotazioni }}</strong>.</p>

        <p>Una volta superato il numero massimo di prenotazioni, non sarà più possibile accettarne di nuove per la stessa giornata!</p>

        <br><br>

        <h4>Il team di Coworking</h4>

    </body>
</html>
