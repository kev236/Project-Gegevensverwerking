<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    
    <link rel="stylesheet" href="../css/adminlogin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <?php
session_start(); // Start de sessie

$message = "";
$message_class = "";

function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['username'], $_POST['password'])) {
            $username = sanitize_input($_POST['username']);
            $password = $_POST['password'];

            // Simulate a potential failure point
            if ($username === "Admin123" && $password === "Admin321") {
                $_SESSION['loggedin'] = true; // Zet sessievariabele
                $_SESSION['username'] = $username; // Opslaan van gebruikersnaam in sessie
                $message = "Login succesvol!";
                $message_class = "success";
                header("Location: adminview.php");
                exit();
            } else {
                throw new Exception("Onjuist gebruikersnaam of wachtwoord.");
            }
        } else {
            throw new Exception("Vul alle velden in.");
        }
    }
} catch (Exception $e) {
    $message = $e->getMessage();
    $message_class = "error";
} finally {
    // Code that always runs, e.g., closing a database connection
}
?>

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
                    <li class="nav-item"><a class="nav-link" href="view.php">Uw gegevens</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1 class="text-center">Admin Login</h1>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <form action="adminlogin.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Gebruikersnaam</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Wachtwoord</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">Zichtbaar</button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Inloggen</button>
        </form>
    </div>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            var passwordField = document.getElementById('password');
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
