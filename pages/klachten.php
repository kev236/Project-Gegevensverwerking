<?php
// Laad vereiste bibliotheken
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Variabelen voor het tonen van meldingen
$message = '';
$message_class = '';

// Functie voor ontsmetten van invoer
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Laad .env variabelen
$env = parse_ini_file('../.env');

// Controleer of het formulier is ingediend
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ontsmet en sla de ingevoerde gegevens op
    $naam = sanitize_input($_POST['naam'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $onderwerp = sanitize_input($_POST['onderwerp'] ?? '');
    $klacht = sanitize_input($_POST['klacht'] ?? '');

    // Controleer of alle vereiste velden zijn ingevuld
    if (!empty($naam) && !empty($email) && !empty($onderwerp) && !empty($klacht)) {
        // Verzend e-mail met PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $env['SMTP-Username'];
            $mail->Password = $env['SMTP-Password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Afzender en ontvanger instellen
            $mail->setFrom('example@gmail.com', 'Klachtverwerking');
            $mail->addAddress($email, $naam);

            // E-mailonderwerp en bericht
            $mail->Subject = $onderwerp;
            $mail->Body = "Geachte $naam,\n\nUw klacht is in behandeling.\n\nDetails van de klacht:\n$klacht";

            // Verstuur de e-mail
            $mail->send();

            // Stel succesmelding in
            $message = "Klacht succesvol verzonden!";
            $message_class = "success";
        } catch (Exception $e) {
            // Stel foutmelding in bij een e-mailfout
            $message = "Er is een fout opgetreden bij het versturen van uw klacht. Probeer het opnieuw.";
            $message_class = "error";
        }

        // Log de klacht met Monolog
        try {
            $log = new Logger('klachten');
            $log->pushHandler(new StreamHandler(__DIR__ . '/../klachten/info.log', Logger::INFO));
            $log->info('Nieuwe klacht ontvangen', ['naam' => $naam, 'email' => $email, 'klacht' => $klacht]);
        } catch (Exception $e) {
            // Stel foutmelding in bij loggen
            $message = "Er is een fout opgetreden bij het verwerken van uw klacht.";
            $message_class = "error";
        }
    } else {
        // Stel foutmelding in als velden niet zijn ingevuld
        $message = "Niet alle velden zijn ingevuld.";
        $message_class = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klachtenformulier</title>
    <link rel="stylesheet" href="../css/klachten.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
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
                    <li class="nav-item"><a class="nav-link" href="view.php">Uw gegevens</a></li>
                    <li class="nav-item"><a class="nav-link" href="klachten.php">Klachten</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Klachtenformulier -->
    <div class="container mt-5 pt-5">
        <h1 class="text-center">Dien uw klacht in</h1>

        <!-- Melding voor succes of fout -->
        <div id="melding-container">
            <?php if (!empty($message)): ?>
                <div class="<?= $message_class ?>"><?= $message ?></div>
            <?php endif; ?>
        </div>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="naam" class="form-label">Naam:</label>
                <input type="text" id="naam" name="naam" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="klacht" class="form-label">Uw klacht:</label>
                <textarea id="klacht" name="klacht" rows="5" class="form-control" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Verstuur klacht</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
