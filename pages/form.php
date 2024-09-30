<!DOCTYPE html>
<html lang="nl">
<head>
    <!-- Metadata voor de website -->
    <meta charset="UTF-8"> <!-- Tekenencodering voor speciale karakters -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Schaal voor mobiele apparaten -->
    <title>Gegevens Invullen</title> <!-- Titel van de pagina -->
    
    <!-- Externe CSS-bestand -->
    <link rel="stylesheet" href="../css/form.css"> <!-- CSS voor de opmaak van het formulier -->
    
    <!-- Bootstrap CSS voor responsief design en stijlen -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<?php
// PHP variabelen voor het opslaan van berichten
$message = ""; // Berichtvariabele voor succes- of foutmeldingen
$message_class = ""; // CSS-class voor de weergave van berichten (bijv. error of success)

// Functie voor het ontsmetten van invoer
function sanitize_input($data) {
    return htmlspecialchars(trim($data)); // Verwijdert witruimten en converteert speciale karakters
}

// Functies voor basisvalidatie
function validate_not_empty($value) {
    return !empty($value); // Controleert of een veld niet leeg is
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; // Controleert of het e-mailadres geldig is
}

function validate_phone($phone) {
    // Verwijder alle niet-cijfertekens voor validatie
    $phone = preg_replace('/\D/', '', $phone); // Verwijdert niet-cijfertekens
    return is_numeric($phone) && strlen($phone) >= 10 && strlen($phone) <= 15; // Controleert of het een geldig telefoonnummer is
}

function validate_postcode($postcode) {
    return preg_match('/^\d{4}\s?[A-Z]{2}$/', $postcode); // Controleert of de postcode het juiste formaat heeft (bijv. 1234 AB)
}

// Controleer of het formulier is ingediend
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['Naam'], $_POST['Achternaam'], $_POST['Geslacht'], 
        $_POST['Straatnaam'], $_POST['Huisnummer'], $_POST['Postcode'], 
        $_POST['Woonplaats'], $_POST['Telefoonnummer'], $_POST['Landcode'], 
        $_POST['Email'], $_POST['wachtwoord'])) {

        // Database verbinding informatie
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "gegevens";

        try {
            // Maak verbinding met de database
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                throw new Exception("Verbinding mislukt: " . $conn->connect_error); // Foutmelding bij mislukte verbinding
            }

            // Ontsmetten van invoer
            $Naam = sanitize_input($_POST['Naam']);
            $Tussenvoegsel = isset($_POST['Tussenvoegsel']) ? sanitize_input($_POST['Tussenvoegsel']) : ''; // Optioneel tussenvoegsel
            $Achternaam = sanitize_input($_POST['Achternaam']);
            $Geslacht = sanitize_input($_POST['Geslacht']);
            $Straatnaam = sanitize_input($_POST['Straatnaam']);
            $Huisnummer = sanitize_input($_POST['Huisnummer']);
            $Postcode = sanitize_input($_POST['Postcode']);
            $Woonplaats = sanitize_input($_POST['Woonplaats']);
            $Landcode = sanitize_input($_POST['Landcode']);
            $Telefoonnummer = sanitize_input($_POST['Telefoonnummer']);
            $Email = sanitize_input($_POST['Email']);
            $wachtwoord = $_POST['wachtwoord']; // Wachtwoord wordt niet ontsmet omdat het later gehasht wordt

            // Combineer landcode en telefoonnummer
            $volledige_telefoonnummer = $Landcode . $Telefoonnummer;

            // Basisvalidatie van velden
            if (!validate_not_empty($Naam) || !validate_not_empty($Achternaam) || 
                !validate_not_empty($Geslacht) || !validate_not_empty($Straatnaam) || 
                !validate_not_empty($Huisnummer) || !validate_not_empty($Postcode) || 
                !validate_not_empty($Woonplaats) || !validate_not_empty($Telefoonnummer) || 
                !validate_not_empty($Landcode) || !validate_not_empty($Email) || 
                !validate_not_empty($wachtwoord)) {
                throw new Exception("Niet alle velden zijn ingevuld."); // Foutmelding bij ontbrekende velden
            }

            // Valideer e-mailadres
            if (!validate_email($Email)) {
                throw new Exception("Ongeldig Emailadres."); // Foutmelding bij ongeldig e-mailadres
            }

            // Valideer telefoonnummer
            if (!validate_phone($volledige_telefoonnummer)) {
                throw new Exception("Ongeldig Telefoonnummer. Gebruik alleen cijfers, met een lengte tussen 10 en 15 cijfers."); // Foutmelding bij ongeldig telefoonnummer
            }

            // Valideer postcode
            if (!validate_postcode($Postcode)) {
                throw new Exception("Ongeldige Postcode. Gebruik het formaat 1234 AB."); // Foutmelding bij ongeldige postcode
            }

            // Hash het wachtwoord voor veilige opslag
            $hashed_password = password_hash($wachtwoord, PASSWORD_DEFAULT);

            // SQL-query om de gegevens in de database op te slaan
            $stmt = $conn->prepare("INSERT INTO gegevens (Naam, Tussenvoegsel, Achternaam, Geslacht, Straatnaam, Huisnummer, Postcode, Woonplaats, Telefoonnummer, Email, wachtwoord) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare statement mislukt: " . $conn->error); // Foutmelding bij mislukte query
            }

            // Bind de waarden aan de statement en voer de query uit
            $stmt->bind_param("sssssisssss", $Naam, $Tussenvoegsel, $Achternaam, $Geslacht, $Straatnaam, $Huisnummer, $Postcode, $Woonplaats, $volledige_telefoonnummer, $Email, $hashed_password);

            if ($stmt->execute()) {
                $message = "Registratie succesvol!"; // Succesbericht bij geslaagde registratie
                $message_class = "success"; // CSS-class voor succesbericht
            } else {
                throw new Exception("Fout: " . $stmt->error); // Foutmelding bij uitvoeringsfout
            }
        } catch (Exception $e) {
            $message = $e->getMessage(); // Toon de foutmelding
            $message_class = "error"; // CSS-class voor foutbericht
        } finally {
            // Sluit de statement en databaseverbinding
            if (isset($stmt)) $stmt->close();
            if (isset($conn) && !$conn->connect_error) $conn->close();
        }
    } else {
        $message = "Niet alle velden zijn ingevuld."; // Foutmelding bij ontbrekende velden
        $message_class = "error"; // CSS-class voor foutbericht
    }
}
?>
</head>
<body>
    <!-- Navigatiebalk -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <!-- Merk/logo link -->
            <a class="navbar-brand" href="../index.html">Ashura</a> 
            
            <!-- navbar slider voor mobiele apparaten -->
            <label class="burger" for="burger">
                <input type="checkbox" id="burger" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </label>

            <!-- Navigatie-items -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Links naar andere pagina's -->
                    <li class="nav-item"><a class="nav-link" href="../index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="form.php">Gegevens Invullen</a></li>
                    <li class="nav-item"><a class="nav-link" href="view.php">Uw gegevens</a></li>
                    <li class="nav-item"><a class="nav-link" href="klachten.php">Klachten</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hoofdcontainer voor het formulier -->
    <div class="container mt-5">
        <h1 class="text-center">Gegevens Invullen</h1>

        <!-- Bericht sectie voor succes of foutmeldingen -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?> <!-- Toon het bericht -->
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> <!-- Sluitknop voor het bericht -->
            </div>
        <?php endif; ?>

        <!-- Formulier voor gegevensinvoer -->
        <form action="form.php" method="POST">
            <div class="row mb-3">
                <!-- Veld voor Naam -->
                <div class="col-md-4">
                    <label for="Naam" class="form-label">Naam</label>
                    <input type="text" class="form-control" id="Naam" name="Naam" required>
                </div>
                <!-- Veld voor Tussenvoegsel (optioneel) -->
                <div class="col-md-4">
                    <label for="Tussenvoegsel" class="form-label">Tussenvoegsel</label>
                    <input type="text" class="form-control" id="Tussenvoegsel" name="Tussenvoegsel">
                </div>
                <!-- Veld voor Achternaam -->
                <div class="col-md-4">
                    <label for="Achternaam" class="form-label">Achternaam</label>
                    <input type="text" class="form-control" id="Achternaam" name="Achternaam" required>
                </div>
            </div>
            <!-- Select voor Geslacht -->
            <div class="mb-3">
                <label for="Geslacht" class="form-label">Geslacht</label>
                <select class="form-control" id="Geslacht" name="Geslacht" required>
                    <option value="Man">Man</option>
                    <option value="Vrouw">Vrouw</option>
                    <option value="Anders">In de war</option>
                </select>
            </div>
            <!-- Veld voor Straatnaam en Huisnummer -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="Straatnaam" class="form-label">Straatnaam</label>
                    <input type="text" class="form-control" id="Straatnaam" name="Straatnaam" required>
                </div>
                <div class="col-md-6">
                    <label for="Huisnummer" class="form-label">Huisnummer</label>
                    <input type="number" class="form-control" id="Huisnummer" name="Huisnummer" required>
                </div>
            </div>
            <!-- Veld voor Postcode -->
            <div class="mb-3">
                <label for="Postcode" class="form-label">Postcode</label>
                <input type="text" class="form-control" id="Postcode" name="Postcode" required>
            </div>
            <!-- Veld voor Woonplaats -->
            <div class="mb-3">
                <label for="Woonplaats" class="form-label">Woonplaats</label>
                <input type="text" class="form-control" id="Woonplaats" name="Woonplaats" required>
            </div>
            <!-- Veld voor telefoonnummer met landcode -->
            <div class="mb-3">
                <label for="Telefoonnummer" class="form-label">Telefoonnummer</label>
                <div class="input-group">
                    <!-- Landcode select -->
                    <select class="form-select" id="Landcode" name="Landcode" required>
                        <option value="+31" selected>+31 Nederland</option>
                        <option value="+32">+32 België</option>
                        <option value="+33">+33 Frankrijk</option>
                    </select>
                    <!-- Telefoonnummer veld -->
                    <input type="tel" class="form-control" id="Telefoonnummer" name="Telefoonnummer" placeholder="Telefoonnummer" required>
                </div>
            </div>
            <!-- Veld voor Emailadres -->
            <div class="mb-3">
                <label for="Email" class="form-label">Emailadres</label>
                <input type="email" class="form-control" id="Email" name="Email" required>
            </div>
            <!-- Veld voor wachtwoord -->
            <div class="mb-3">
                <label for="wachtwoord" class="form-label">Wachtwoord</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="wachtwoord" name="wachtwoord" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">Zichtbaar</button> <!-- Knop om het wachtwoord zichtbaar/onzichtbaar te maken -->
                </div>
            </div>
            <!-- Submit knop -->
            <button type="submit" class="btn btn-primary w-100">Sla Gegevens Op</button>
        </form>
    </div>

    <!-- FAQ Button in the bottom-right corner -->
    <button class="faq-button" onclick="window.location.href='adminlogin.php'">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
    <path
      d="M80 160c0-35.3 28.7-64 64-64h32c35.3 0 64 28.7 64 64v3.6c0 21.8-11.1 42.1-29.4 53.8l-42.2 27.1c-25.2 16.2-40.4 44.1-40.4 74V320c0 17.7 14.3 32 32 32s32-14.3 32-32v-1.4c0-8.2 4.2-15.8 11-20.2l42.2-27.1c36.6-23.6 58.8-64.1 58.8-107.7V160c0-70.7-57.3-128-128-128H144C73.3 32 16 89.3 16 160c0 17.7 14.3 32 32 32s32-14.3 32-32zm80 320a40 40 0 1 0 0-80 40 40 0 1 0 0 80z"
    ></path>
  </svg>
  <span class="tooltip">ADMIN</span>
</button>

    <!-- JavaScript voor Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

    <!-- JavaScript voor wachtwoord zichtbaar/onzichtbaar maken -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            var passwordField = document.getElementById('wachtwoord');
            var button = this;
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                button.textContent = 'Verberg'; // Verandert de knoptekst naar 'Verberg'
            } else {
                passwordField.type = 'password';
                button.textContent = 'Zichtbaar'; // Verandert de knoptekst naar 'Zichtbaar'
            }
        });
    </script>
</body>
</html>
