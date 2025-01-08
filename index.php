<?php
// Connessione al database SQLite
try {
    $db_path = 'db2.sqlite';  // Percorso al tuo database SQLite
    $conn = new PDO('sqlite:' . $db_path);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Creazione della tabella se non esiste già
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS partite (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            squadra1 TEXT NOT NULL,
            voto1 REAL NOT NULL,
            squadra2 TEXT NOT NULL,
            voto2 REAL NOT NULL,
            data DATE DEFAULT (date('now'))
        );
    ";
    $conn->exec($createTableQuery);

    // Inserimento dei dati nel database
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $squadra1 = isset($_POST['squadra1']) ? $_POST['squadra1'] : '';
        $voto1 = isset($_POST['voto1']) ? (float)$_POST['voto1'] : 0;
        $squadra2 = isset($_POST['squadra2']) ? $_POST['squadra2'] : '';
        $voto2 = isset($_POST['voto2']) ? (float)$_POST['voto2'] : 0;

        // Preparazione della query per l'inserimento
        $stmt = $conn->prepare("INSERT INTO partite (squadra1, voto1, squadra2, voto2) VALUES (:squadra1, :voto1, :squadra2, :voto2)");
        $stmt->bindParam(':squadra1', $squadra1, PDO::PARAM_STR);
        $stmt->bindParam(':voto1', $voto1, PDO::PARAM_STR);
        $stmt->bindParam(':squadra2', $squadra2, PDO::PARAM_STR);
        $stmt->bindParam(':voto2', $voto2, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "<h3>Voti salvati con successo!</h3>";
        } else {
            echo "<h3>Errore durante il salvataggio:</h3>";
            print_r($stmt->errorInfo());
        }
    }
} catch (PDOException $e) {
    echo "Errore nella connessione al database: " . $e->getMessage();
}
?>

<h2>Inserisci i voti della partita</h2>
<form method="POST" action="">
    <label for="squadra1">Squadra 1:</label>
    <input type="text" id="squadra1" name="squadra1" required><br><br>

    <label for="voto1">Voto Squadra 1:</label>
    <input type="number" id="voto1" name="voto1" step="0.1" required><br><br>

    <label for="squadra2">Squadra 2:</label>
    <input type="text" id="squadra2" name="squadra2" required><br><br>

    <label for="voto2">Voto Squadra 2:</label>
    <input type="number" id="voto2" name="voto2" step="0.1" required><br><br>

    <input type="submit" value="Salva">
</form>

<h2>Partite salvate</h2>
<?php
try {
    // Connessione al database per visualizzare i dati salvati
    $conn = new PDO('sqlite:db2.sqlite');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM partite ORDER BY data DESC");
    $stmt->execute();
    $partite = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($partite) > 0):
?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Squadra 1</th>
            <th>Voto Squadra 1</th>
            <th>Squadra 2</th>
            <th>Voto Squadra 2</th>
            <th>Data</th>
        </tr>
        <?php foreach ($partite as $partita): ?>
            <tr>
                <td><?= $partita['id'] ?></td>
                <td><?= $partita['squadra1'] ?></td>
                <td><?= $partita['voto1'] ?></td>
                <td><?= $partita['squadra2'] ?></td>
                <td><?= $partita['voto2'] ?></td>
                <td><?= $partita['data'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Non ci sono partite salvate.</p>
<?php endif; ?>
?>
