<?php
session_start(); // Start de sessie

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: adminlogin.php"); // Stuur niet-ingelogde gebruikers naar de inlogpagina
    exit();
}

// Controleer of de back-up al is gemaakt
$backupMade = isset($_SESSION['backup_made']) && $_SESSION['backup_made'] === true;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uw gegevens</title>
    <link rel="stylesheet" href="../css/view.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.html">Ashura</a>
            <label class="burger" for="burger">
                <input type="checkbox" id="burger" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span></span> 
                <span></span> 
                <span></span> 
            </label>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="form.php">Gegevens Invullen</a></li>
                    <li class="nav-item"><a class="nav-link" href="adminview.php">Uw gegevens</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1 class="text-center">Uw Gegevens</h1>
        <div class="row">
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "gegevens";

            try {
                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    throw new Exception("Verbinding mislukt: " . $conn->connect_error);
                }

                $sql = "SELECT id, Naam, Tussenvoegsel, Achternaam, Geslacht, Straatnaam, Huisnummer, Postcode, Woonplaats, Telefoonnummer, Email FROM gegevens";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="col-md-6 col-lg-4 mb-4">';
                        echo '<div class="card">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . htmlspecialchars($row['Naam']) . ' ' . htmlspecialchars($row['Tussenvoegsel']) . ' ' . htmlspecialchars($row['Achternaam']) . '</h5>';
                        echo '<p class="card-text"><strong>Geslacht:</strong> ' . htmlspecialchars($row['Geslacht']) . '</p>';
                        echo '<p class="card-text"><strong>Straatnaam:</strong> ' . htmlspecialchars($row['Straatnaam']) . ' ' . htmlspecialchars($row['Huisnummer']) . '</p>';
                        echo '<p class="card-text"><strong>Postcode:</strong> ' . htmlspecialchars($row['Postcode']) . '</p>';
                        echo '<p class="card-text"><strong>Woonplaats:</strong> ' . htmlspecialchars($row['Woonplaats']) . '</p>';
                        echo '<p class="card-text"><strong>Telefoonnummer:</strong> ' . htmlspecialchars($row['Telefoonnummer']) . '</p>';
                        echo '<p class="card-text"><strong>Email:</strong> ' . htmlspecialchars($row['Email']) . '</p>';
                        echo '<a href="adminupdate.php?id=' . htmlspecialchars($row['id']) . '" class="btn btn-warning me-2">Bewerken</a>';
                        echo '<form method="POST" action="delete.php" class="d-inline delete-form">';
                        echo '<input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">';
                        echo '<button type="submit" class="btn btn-danger">Verwijderen</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-info" role="alert">Geen gegevens gevonden.</div>';
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger" role="alert">Fout: ' . $e->getMessage() . '</div>';
            } finally {
                if (isset($conn) && !$conn->connect_error) $conn->close();
            }
            ?>
        </div>
        <div class="text-center mt-4">
            <?php if (!$backupMade): ?>
            <form method="POST" action="backup.php">
                <button type="submit" class="btn btn-success">Maak Back-up</button>
            </form>
            <?php else: ?>
            <div class="alert alert-success" role="alert">Back-up is al gemaakt.</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!confirm('Weet u zeker dat u dit record wilt verwijderen?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
