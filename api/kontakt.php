<?php
/**
 * API: Hantera kontaktformulär
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Endast POST tillåtet']);
    exit;
}

$namn = sanitize($_POST['namn'] ?? '');
$foretag = sanitize($_POST['foretag'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$telefon = sanitize($_POST['telefon'] ?? '');
$amne = sanitize($_POST['amne'] ?? '');
$meddelande = sanitize($_POST['meddelande'] ?? '');

// Validering
if (empty($namn) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($meddelande)) {
    echo json_encode(['success' => false, 'message' => 'Fyll i alla obligatoriska fält']);
    exit;
}

// E-post
$mailHtml = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='font-family: Arial, sans-serif;'>
    <h2>Nytt kontaktmeddelande</h2>
    <p><strong>Från:</strong> {$namn}</p>
    <p><strong>Företag:</strong> {$foretag}</p>
    <p><strong>E-post:</strong> {$email}</p>
    <p><strong>Telefon:</strong> {$telefon}</p>
    <p><strong>Ämne:</strong> {$amne}</p>
    <hr>
    <p><strong>Meddelande:</strong></p>
    <p>" . nl2br($meddelande) . "</p>
    <hr>
    <p style='color: #666; font-size: 12px;'>Skickat: " . date('Y-m-d H:i:s') . "</p>
</body>
</html>
";

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . COMPANY_NAME . ' <' . COMPANY_EMAIL . '>',
    'Reply-To: ' . $email
];

$sent = mail(
    COMPANY_EMAIL,
    "Kontaktformulär: {$amne} - från {$namn}",
    $mailHtml,
    implode("\r\n", $headers)
);

echo json_encode([
    'success' => true,
    'message' => 'Meddelandet har skickats!'
]);
