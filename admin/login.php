<?php
/**
 * Admin - Inloggning
 */
session_start();

require_once '../includes/config.php';

// Om redan inloggad, gå till admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

// Hantera inloggning
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Enkla inloggningsuppgifter - BYT DESSA!
    // I produktion: använd databas och password_hash()
    $admin_user = 'admin';
    $admin_pass = 'varsel2024'; // ÄndRA DETTA!
    
    if ($username === $admin_user && $password === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Felaktigt användarnamn eller lösenord';
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Logga in | <?php echo COMPANY_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/admin.css">
</head>
<body class="admin-login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <span class="logo-icon">⚠️</span>
                <h1>Varsel Admin</h1>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Användarnamn</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Lösenord</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Logga in</button>
            </form>
            
            <p class="login-footer">
                <a href="<?php echo SITE_URL; ?>">← Tillbaka till butiken</a>
            </p>
        </div>
    </div>
</body>
</html>
