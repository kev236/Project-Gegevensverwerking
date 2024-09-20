<?php
session_start(); // Start de sessie

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: adminlogin.php"); // Stuur niet-ingelogde gebruikers naar de inlogpagina
    exit();
}

// Verbind met de database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gegevens";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Haal het ID op van het record dat verwijderd moet worden
    $id = isset($_POST['id']) ? $_POST['id'] : '';

    if ($id) {
        // Beveiliging tegen SQL injectie
        $id = $conn->real_escape_string($id);

        // SQL-query om het record te verwijderen
        $sql = "DELETE FROM gegevens WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            echo "Record succesvol verwijderd!";
        } else {
            echo "Fout bij het verwijderen van record: " . $conn->error;
        }
    } else {
        echo "Geen ID opgegeven voor verwijdering.";
    }

    // Sluit de verbinding
    $conn->close();

    // Redirect terug naar de gegevenspagina
    header("Location: adminview.php");
    exit();
}
?>
