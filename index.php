<?php
try {
    // Connessione al database SQLite
    $conn = new PDO('sqlite:db.sqlite');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Creazione della tabella partite e classifica, se non esistono
    $conn->exec("
        CREATE TABLE IF NOT EXISTS partite (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            squadra1 TEXT NOT NULL,
            voto1 INTEGER NOT NULL,
            squadra2 TEXT NOT NULL,
            voto2 INTEGER NOT NULL,
            data DATE DEFAULT (date('now'))
        );

        CREATE TABLE IF NOT EXISTS classifica (
            squadra TEXT PRIMARY KEY,
            punti INTEGER NOT NULL DEFAULT 0
        );
    ");

    // Inizializzo variabili
    $successo = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Recupero i dati dal modulo
        $squadra1 = $_POST['squadra1'];
        $voto1 = (int)$_POST['voto1'];
        $squadra2 = $_POST['squadra2'];
        $voto2 = (int)$_POST['voto2'];

        if ($squadra1 === $squadra2) {
            echo "<h3>Errore: le due squadre devono essere diverse!</h3>";
        } else {
            // Inserisco la partita nella tabella partite
            $stmt = $conn->prepare("
                INSERT INTO partite (squadra1, voto1, squadra2, voto2)
                VALUES (:squadra1, :voto1, :squadra2, :voto2)
            ");
            $stmt->bindParam(':squadra1', $squadra1);
            $stmt->bindParam(':voto1', $voto1);
            $stmt->bindParam(':squadra2', $squadra2);
            $stmt->bindParam(':voto2', $voto2);
            $stmt->execute();

            // Calcolo i punti
            $punti1 = 0;
            $punti2 = 0;

            if ($voto1 > $voto2) {
                $punti1 = 3;
            } elseif ($voto1 < $voto2) {
                $punti2 = 3;
            } else {
                $punti1 = 1;
                $punti2 = 1;
            }

            // Aggiorno la classifica
            $updateClassifica = $conn->prepare("
                INSERT INTO classifica (squadra, punti) VALUES (:squadra, :punti)
                ON CONFLICT(squadra) DO UPDATE SET punti = punti + :punti
            ");
            $updateClassifica->bindParam(':squadra', $squadra1);
            $updateClassifica->bindParam(':punti', $punti1);
            $updateClassifica->execute();

            $updateClassifica->bindParam(':squadra', $squadra2);
            $updateClassifica->bindParam(':punti', $punti2);
            $updateClassifica->execute();

            $successo = true;
        }
    }

    // Recupero la classifica ordinata
    $classificaQuery = $conn->query("
        SELECT squadra, punti FROM classifica ORDER BY punti DESC, squadra ASC
    ");
    $classifica = $classificaQuery->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Errore nella connessione al database: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Partite</title>
</head>
<body>

<?php
if ($successo) {
    echo "<h3>Risultati salvati con successo!</h3>";
}
?>

<h2>Inserisci Risultato Partita</h2>
<form method="POST" action="">
    <label for="squadra1">Squadra 1:</label>
    <select id="squadra1" name="squadra1" required>
        <option value="Juventus">Juventus</option>
        <option value="Milan">Milan</option>
        <option value="Inter">Inter</option>
        <option value="Roma">Roma</option>
        <option value="Napoli">Napoli</option>
        <option value="Atalanta">Atalanta</option>
        <option value="Fiorentina">Fiorentina</option>
        <option value="Lazio">Lazio</option>
    </select>
    <label for="voto1">Punteggio Squadra 1:</label>
    <input type="number" id="voto1" name="voto1" required><br><br>

    <label for="squadra2">Squadra 2:</label>
    <select id="squadra2" name="squadra2" required>
        <option value="Juventus">Juventus</option>
        <option value="Milan">Milan</option>
        <option value="Inter">Inter</option>
        <option value="Roma">Roma</option>
        <option value="Napoli">Napoli</option>
        <option value="Atalanta">Atalanta</option>
        <option value="Fiorentina">Fiorentina</option>
        <option value="Lazio">Lazio</option>
    </select>
    <label for="voto2">Punteggio Squadra 2:</label>
    <input type="number" id="voto2" name="voto2" required><br><br>

    <input type="submit" value="Salva Risultato">
</form>

<h2>Classifica</h2>
<table border="1">
    <tr>
        <th>Posizione</th>
        <th>Squadra</th>
        <th>Punti</th>
    </tr>
    <?php
    if (!empty($classifica)) {
        $posizione = 1;
        foreach ($classifica as $riga) {
            echo "<tr>";
            echo "<td>$posizione</td>";
            echo "<td>{$riga['squadra']}</td>";
            echo "<td>{$riga['punti']}</td>";
            echo "</tr>";
            $posizione++;
        }
    } else {
        echo "<tr><td colspan='3'>Nessun risultato disponibile</td></tr>";
    }
    ?>
</table>

</body>
</html>
