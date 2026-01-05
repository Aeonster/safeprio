<?php
/**
 * Databaskonfiguration för Varsel webbutik
 * Ändra dessa värden till dina egna inställningar
 */

// Starta session endast om ingen session är aktiv
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Språkhantering
$allowed_languages = ['sv', 'en'];
$default_language = 'sv';

// Byt språk via URL-parameter
if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_languages)) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Sätt aktuellt språk
$current_lang = $_SESSION['lang'] ?? $default_language;
define('CURRENT_LANG', $current_lang);

// Valutahantering
$allowed_currencies = ['SEK', 'EUR'];
$default_currency = 'SEK';

// Byt valuta via URL-parameter
if (isset($_GET['currency']) && in_array($_GET['currency'], $allowed_currencies)) {
    $_SESSION['currency'] = $_GET['currency'];
}

// Sätt aktuell valuta
$current_currency = $_SESSION['currency'] ?? $default_currency;
define('CURRENT_CURRENCY', $current_currency);

// Ladda språkfil
$lang_file = __DIR__ . '/../lang/' . CURRENT_LANG . '.php';
$GLOBALS['lang'] = file_exists($lang_file) ? require $lang_file : require __DIR__ . '/../lang/sv.php';

// Hjälpfunktion för översättning
function __($key, $default = '') {
    return $GLOBALS['lang'][$key] ?? ($default ?: $key);
}

// Databasinställningar
define('DB_HOST', 'mysql'); // Ändra från 'localhost'
define('DB_NAME', 'safeprio_db'); // Ändra från 'varsel_db'
define('DB_USER', 'root');
define('DB_PASS', 'root_password'); // Ändra från '', Laragon använder tomt lösenord som standard

// Företagsinställningar
define('COMPANY_NAME', 'kortsystem AB');
define('COMPANY_EMAIL', 'patricio.santiago@kortsystem.se'); // Ändra till er ordermail
define('COMPANY_PHONE', '0371-22 24 44');
define('COMPANY_ADDRESS', 'Verkstadsgatan 6, 332 35 Gislaved');

// E-postinställningar
define('SMTP_HOST', 'smtp.example.com'); // Ändra till din SMTP-server
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// Sidans URL
define('SITE_URL', 'http://localhost:8000');

// Valuta (DEPRECATED - använd CURRENT_CURRENCY istället)
define('CURRENCY', CURRENT_CURRENCY === 'EUR' ? '€' : 'kr');
define('VAT_RATE', 0.25); // 25% moms

// Databasanslutning med PDO
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Säkerställ UTF-8 encoding
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        return $pdo;
    } catch (PDOException $e) {
        // I produktion: logga felet istället för att visa det
        die("Databasanslutning misslyckades: " . $e->getMessage());
    }
}

// Hjälpfunktion för att formatera pris
function formatPrice($price, $currency = null) {
    $currency = $currency ?? CURRENT_CURRENCY;
    
    if ($currency === 'EUR') {
        return '€' . number_format($price, 2, ',', ' ');
    } else {
        return number_format($price, 2, ',', ' ') . ' kr';
    }
}

// Placeholder-bild
define('PLACEHOLDER_IMAGE', 'placeholder.svg');

// Hjälpfunktion för att sanitera input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>
