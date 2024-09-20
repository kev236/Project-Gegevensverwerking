<?php
session_start(); // Start de sessie

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: adminlogin.php"); // Stuur niet-ingelogde gebruikers naar de inlogpagina
    exit();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gegevens Bijwerken</title>
    <link rel="stylesheet" href="../css/form.css"> <!-- Verwijst naar hetzelfde CSS-bestand voor consistentie -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link" href="adminview.php">Gegevens Invullen</a></li>
                    <li class="nav-item"><a class="nav-link" href="adminview.php">Uw gegevens</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center">Gegevens Bijwerken</h1>
        <a href="adminview.php" class="btn btn-secondary mb-3">Terug Naar Admin Pagina</a>

        <?php
        // Verbinding maken met de database
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "gegevens";
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        try {
            // Maak verbinding met de database
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                throw new Exception("Verbinding mislukt: " . $conn->connect_error);
            }

            // Haal bestaande gegevens op
            $gegevens = [];
            if ($id > 0) {
                $sql = "SELECT Naam, Tussenvoegsel, Achternaam, Geslacht, Straatnaam, Huisnummer, Postcode, Woonplaats, Telefoonnummer, Email FROM gegevens WHERE id=?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Fout bij het voorbereiden van de SQL-query: " . $conn->error);
                }
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $gegevens = $result->fetch_assoc();
            }

            // Verwerk het formulier wanneer het wordt ingediend
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $naam = $_POST['Naam'];
                $tussenvoegsel = $_POST['Tussenvoegsel'];
                $achternaam = $_POST['Achternaam'];
                $geslacht = $_POST['Geslacht'];
                $straatnaam = $_POST['Straatnaam'];
                $huisnummer = $_POST['Huisnummer'];
                $postcode = $_POST['Postcode'];
                $woonplaats = $_POST['Woonplaats'];
                $telefoonnummer = $_POST['Telefoonnummer'];
                $email = $_POST['Email'];
                $wachtwoord = $_POST['Wachtwoord']; // Nieuw wachtwoord veld

                if (!empty($wachtwoord)) {
                    $hashedPassword = password_hash($wachtwoord, PASSWORD_DEFAULT); // Hash nieuw wachtwoord
                    $sql = "UPDATE gegevens SET Naam=?, Tussenvoegsel=?, Achternaam=?, Geslacht=?, Straatnaam=?, Huisnummer=?, Postcode=?, Woonplaats=?, Telefoonnummer=?, Email=?, Wachtwoord=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Fout bij het voorbereiden van de SQL-query: " . $conn->error);
                    }
                    $stmt->bind_param("sssssssssssi", $naam, $tussenvoegsel, $achternaam, $geslacht, $straatnaam, $huisnummer, $postcode, $woonplaats, $telefoonnummer, $email, $hashedPassword, $id);
                } else {
                    // Update zonder wachtwoord wijziging
                    $sql = "UPDATE gegevens SET Naam=?, Tussenvoegsel=?, Achternaam=?, Geslacht=?, Straatnaam=?, Huisnummer=?, Postcode=?, Woonplaats=?, Telefoonnummer=?, Email=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Fout bij het voorbereiden van de SQL-query: " . $conn->error);
                    }
                    $stmt->bind_param("ssssssssssi", $naam, $tussenvoegsel, $achternaam, $geslacht, $straatnaam, $huisnummer, $postcode, $woonplaats, $telefoonnummer, $email, $id);
                }

                if ($stmt->execute()) {
                    echo '<div class="alert alert-success">Gegevens succesvol bijgewerkt.</div>';
                } else {
                    throw new Exception("Fout bij het bijwerken van gegevens: " . $stmt->error);
                }
            }
        } catch (Exception $e) {
            echo '<div class="alert alert-danger" role="alert">' . $e->getMessage() . '</div>';
        } finally {
            if (isset($conn) && $conn->ping()) {
                $conn->close(); // Zorg ervoor dat de verbinding wordt gesloten
            }
        }
        ?>

        <!-- Formulier voor gegevens bijwerken -->
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="Naam" class="form-label">Naam</label>
                    <input type="text" id="Naam" name="Naam" class="form-control" value="<?php echo htmlspecialchars($gegevens['Naam'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="Tussenvoegsel" class="form-label">Tussenvoegsel</label>
                    <input type="text" id="Tussenvoegsel" name="Tussenvoegsel" class="form-control" value="<?php echo htmlspecialchars($gegevens['Tussenvoegsel'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="Achternaam" class="form-label">Achternaam</label>
                    <input type="text" id="Achternaam" name="Achternaam" class="form-control" value="<?php echo htmlspecialchars($gegevens['Achternaam'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="Geslacht" class="form-label">Geslacht</label>
                <select id="Geslacht" name="Geslacht" class="form-control" required>
                    <option value="Man" <?php echo (isset($gegevens['Geslacht']) && $gegevens['Geslacht'] === 'Man') ? 'selected' : ''; ?>>Man</option>
                    <option value="Vrouw" <?php echo (isset($gegevens['Geslacht']) && $gegevens['Geslacht'] === 'Vrouw') ? 'selected' : ''; ?>>Vrouw</option>
                    <option value="Overig" <?php echo (isset($gegevens['Geslacht']) && $gegevens['Geslacht'] === 'Overig') ? 'selected' : ''; ?>>Overig</option>
                </select>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="Straatnaam" class="form-label">Straatnaam</label>
                    <input type="text" id="Straatnaam" name="Straatnaam" class="form-control" value="<?php echo htmlspecialchars($gegevens['Straatnaam'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="Huisnummer" class="form-label">Huisnummer</label>
                    <input type="text" id="Huisnummer" name="Huisnummer" class="form-control" value="<?php echo htmlspecialchars($gegevens['Huisnummer'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="Postcode" class="form-label">Postcode</label>
                <input type="text" id="Postcode" name="Postcode" class="form-control" value="<?php echo htmlspecialchars($gegevens['Postcode'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="Woonplaats" class="form-label">Woonplaats</label>
                <input type="text" id="Woonplaats" name="Woonplaats" class="form-control" value="<?php echo htmlspecialchars($gegevens['Woonplaats'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="Telefoonnummer" class="form-label">Telefoonnummer</label>
                <input type="text" id="Telefoonnummer" name="Telefoonnummer" class="form-control" value="<?php echo htmlspecialchars($gegevens['Telefoonnummer'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="Email" class="form-label">Email</label>
                <input type="email" id="Email" name="Email" class="form-control" value="<?php echo htmlspecialchars($gegevens['Email'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="Wachtwoord" class="form-label">Wachtwoord</label>
                <input type="password" id="Wachtwoord" name="Wachtwoord" class="form-control">
                <button type="button" class="btn btn-outline-secondary" id="togglePassword">Zichtbaar</button>
                <small class="text-muted">Laat dit leeg als u het wachtwoord niet wilt wijzigen.</small>
            </div>
            <button type="submit" class="btn btn-primary w-100">Bijwerken</button>
        </form>
    </div>

    <!-- JavaScript voor wachtwoord zichtbaar/onzichtbaar maken -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            var passwordField = document.getElementById('Wachtwoord');
            var button = this;
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                button.textContent = 'Verberg';
            } else {
                passwordField.type = 'password';
                button.textContent = 'Zichtbaar';
            }
        });
    </script>
</body>
</html>
