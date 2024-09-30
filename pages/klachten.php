<?php
require '../vendor/autoload.php'; // Zorg ervoor dat Composer autoload correct is

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$env = parse_ini_file('../.env');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam = $_POST['naam'] ?? '';
    $email = $_POST['email'] ?? '';
    $klacht = $_POST['klacht'] ?? '';

    if (!empty($naam) && !empty($email) && !empty($klacht)) {
        // Verzend e-mail met PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Mail server configuratie
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = $env['SMTP-Username']; // Vervang met je eigen gegevens
            $mail->Password = $env['SMTP-Password']; // Vervang met je eigen gegevens
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Ontvanger en afzender
            $mail->setFrom('example@gmail.com', 'Klachtverwerking');
            $mail->addAddress($email, $naam); 
            $mail->addCC('example@gmail.com');

            // Onderwerp en bericht
            $mail->Subject = 'Uw klacht is in behandeling';
            $mail->Body = "Geachte $naam,\n\nUw klacht is in behandeling.\n\nDetails van de klacht:\n$klacht";

            // Verzend e-mail
            $mail->send();
            echo '<p class="success">E-mail succesvol verzonden.</p>';
        } catch (Exception $e) {
            echo "<p class='error'>E-mail kon niet worden verzonden. Mailer Error: {$mail->ErrorInfo}</p>";
        }

        // Logging naar info.log met Monolog
        try {
            // Maak een nieuwe log aan
            $log = new Logger('klachten');
            $log->pushHandler(new StreamHandler(__DIR__ . '../klachten/info.log', Logger::INFO));

            // Log de informatie van het formulier
            $log->info('Nieuwe klacht ontvangen', [
                'naam' => $naam,
                'email' => $email,
                'klacht' => $klacht
            ]);
        } catch (Exception $e) {
            echo "<p class='error'>Fout bij het loggen van klacht: {$e->getMessage()}</p>";
        }
    } else {
        echo '<p class="error">Vul alstublieft alle velden in.</p>';
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

    <!-- Navbar toevoegen -->
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
