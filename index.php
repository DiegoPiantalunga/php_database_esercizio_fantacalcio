<?php

// Connessione al database SQLite
try {
    $conn = new PDO('sqlite:db2.sqlite');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Creazione della tabella se non esiste già
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS utenti (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            cognome TEXT NOT NULL,
            eta INTEGER NOT NULL
        );
    ";
    $conn->exec($createTableQuery);

    // Variabili di appoggio per l'inserimento dei dati
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
        $cognome = isset($_POST['cognome']) ? $_POST['cognome'] : '';
        $eta = isset($_POST['eta']) ? (int)$_POST['eta'] : 0;

        // Inserimento dei dati nel database
        $stmt = $conn->prepare("INSERT INTO utenti (nome, cognome, eta) VALUES (:nome, :cognome, :eta)");
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':cognome', $cognome, PDO::PARAM_STR);
        $stmt->bindParam(':eta', $eta, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "<h3>Utente salvato nel database!</h3>";
        } else {
            echo "<h3>Errore durante il salvataggio:</h3>";
            print_r($stmt->errorInfo());
        }
    }
} catch (PDOException $e) {
    echo "Errore nella connessione al database: " . $e->getMessage();
}
?>

<h2>Inserimento dati</h2>
<form method="POST" action="">
    <label for="nome">Inserisci il nome:</label>
    <input type="text" id="nome" name="nome" required><br><br>

    <label for="cognome">Inserisci il cognome:</label>
    <input type="text" id="cognome" name="cognome" required><br><br>

    <label for="eta">Inserisci l'età:</label>
    <input type="number" id="eta" name="eta" required><br><br>

    <input type="submit" value="Salva">
</form>
