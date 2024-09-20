<?php

session_start(); // Start de sessie

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: adminlogin.php"); // Stuur niet-ingelogde gebruikers naar de inlogpagina
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gegevens";

// Bestandsnaam voor de back-up
$backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';

try {
    // Maak verbinding met de database
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Verbinding mislukt: " . $conn->connect_error);
    }

    // Haal de tabelnamen op
    $sql = "SHOW TABLES";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Fout bij het ophalen van tabelnamen: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $backupContent = '';
        while ($row = $result->fetch_row()) {
            $table = $row[0];

            // Haal de CREATE TABLE statement op
            $sqlTable = "SHOW CREATE TABLE $table";
            $resultTable = $conn->query($sqlTable);

            if (!$resultTable) {
                throw new Exception("Fout bij het ophalen van CREATE TABLE statement voor $table: " . $conn->error);
            }

            $rowTable = $resultTable->fetch_row();
            $backupContent .= $rowTable[1] . ";\n\n";

            // Haal de data op
            $sqlData = "SELECT * FROM $table";
            $resultData = $conn->query($sqlData);

            if (!$resultData) {
                throw new Exception("Fout bij het ophalen van gegevens voor $table: " . $conn->error);
            }

            while ($rowData = $resultData->fetch_assoc()) {
                $backupContent .= "INSERT INTO $table (" . implode(', ', array_keys($rowData)) . ") VALUES ('" . implode("', '", array_map([$conn, 'real_escape_string'], array_values($rowData))) . "');\n";
            }
            $backupContent .= "\n\n";
        }

        // Schrijf de back-up naar een bestand
        if (file_put_contents($backupFile, $backupContent) === false) {
            throw new Exception("Fout bij het schrijven van het back-up bestand.");
        }

        // Downloadbestand aanbieden
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($backupFile) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($backupFile));
        readfile($backupFile);

        // Verwijder het bestand na download
        if (!unlink($backupFile)) {
            throw new Exception("Fout bij het verwijderen van het back-up bestand.");
        }

        exit;
    } else {
        echo '<div class="alert alert-info" role="alert">Geen tabellen gevonden in de database.</div>';
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger" role="alert">' . $e->getMessage() . '</div>';
} finally {
    if (isset($conn) && $conn->ping()) {
        $conn->close(); // Zorg ervoor dat de verbinding wordt gesloten
    }
}
?>
