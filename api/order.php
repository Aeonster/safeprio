<?php
/**
 * API: Hantera beställningar
 * Skickar e-post till företaget och kunden
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';

// Endast POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Endast POST tillåtet']);
    exit;
}

// Hämta formulärdata
$foretag = sanitize($_POST['foretag'] ?? '');
$orgnr = sanitize($_POST['orgnr'] ?? '');
$fornamn = sanitize($_POST['fornamn'] ?? '');
$efternamn = sanitize($_POST['efternamn'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$telefon = sanitize($_POST['telefon'] ?? '');
$adress = sanitize($_POST['adress'] ?? '');
$postnr = sanitize($_POST['postnr'] ?? '');
$ort = sanitize($_POST['ort'] ?? '');
$meddelande = sanitize($_POST['meddelande'] ?? '');
$produkter = $_POST['produkter'] ?? '[]';
$totalt = floatval($_POST['totalt'] ?? 0);

// Validering
$errors = [];
if (empty($foretag)) $errors[] = 'Företagsnamn saknas';
if (empty($fornamn)) $errors[] = 'Förnamn saknas';
if (empty($efternamn)) $errors[] = 'Efternamn saknas';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ogiltig e-postadress';
if (empty($telefon)) $errors[] = 'Telefon saknas';
if (empty($adress)) $errors[] = 'Adress saknas';
if (empty($postnr)) $errors[] = 'Postnummer saknas';
if (empty($ort)) $errors[] = 'Ort saknas';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Generera ordernummer
$ordernummer = 'VAR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

// Parse produkter
$produkterArray = json_decode($produkter, true) ?: [];

// Bygg produktlista för e-post
$produktLista = "";
$produktListaHtml = "";
foreach ($produkterArray as $p) {
    $radSumma = $p['pris'] * $p['antal'];
    $produktLista .= "- {$p['namn']}";
    if (!empty($p['storlek'])) $produktLista .= " ({$p['storlek']})";
    $produktLista .= " x {$p['antal']} st = " . number_format($radSumma, 2, ',', ' ') . " kr\n";
    
    $produktListaHtml .= "<tr>
        <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$p['namn']}" . (!empty($p['storlek']) ? " ({$p['storlek']})" : "") . "</td>
        <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$p['antal']}</td>
        <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>" . number_format($radSumma, 2, ',', ' ') . " kr</td>
    </tr>";
}

$moms = $totalt * VAT_RATE;
$totalInklMoms = $totalt + $moms;

// E-post innehåll (text)
$mailText = "
NY BESTÄLLNING - {$ordernummer}
================================

KUNDUPPGIFTER
-------------
Företag: {$foretag}
Org.nr: {$orgnr}
Kontaktperson: {$fornamn} {$efternamn}
E-post: {$email}
Telefon: {$telefon}

LEVERANSADRESS
--------------
{$adress}
{$postnr} {$ort}

BESTÄLLDA PRODUKTER
-------------------
{$produktLista}
-------------------
Delsumma: " . number_format($totalt, 2, ',', ' ') . " kr
Moms (25%): " . number_format($moms, 2, ',', ' ') . " kr
TOTALT: " . number_format($totalInklMoms, 2, ',', ' ') . " kr

MEDDELANDE/REFERENS
-------------------
{$meddelande}

---
Beställning mottagen: " . date('Y-m-d H:i:s') . "
";

// E-post innehåll (HTML)
$mailHtml = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #f59e0b; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; background: #f8f8f8; }
        .section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .section h3 { margin-top: 0; color: #1e293b; border-bottom: 2px solid #f59e0b; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        .total-row { font-weight: bold; font-size: 18px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>⚠️ Ny beställning</h1>
            <p>Ordernummer: {$ordernummer}</p>
        </div>
        <div class='content'>
            <div class='section'>
                <h3>Kunduppgifter</h3>
                <p><strong>Företag:</strong> {$foretag}<br>
                <strong>Org.nr:</strong> {$orgnr}<br>
                <strong>Kontaktperson:</strong> {$fornamn} {$efternamn}<br>
                <strong>E-post:</strong> {$email}<br>
                <strong>Telefon:</strong> {$telefon}</p>
            </div>
            <div class='section'>
                <h3>Leveransadress</h3>
                <p>{$adress}<br>{$postnr} {$ort}</p>
            </div>
            <div class='section'>
                <h3>Beställda produkter</h3>
                <table>
                    <thead>
                        <tr style='background: #f1f5f9;'>
                            <th style='padding: 10px; text-align: left;'>Produkt</th>
                            <th style='padding: 10px; text-align: center;'>Antal</th>
                            <th style='padding: 10px; text-align: right;'>Summa</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$produktListaHtml}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='2' style='padding: 10px; text-align: right;'>Delsumma:</td>
                            <td style='padding: 10px; text-align: right;'>" . number_format($totalt, 2, ',', ' ') . " kr</td>
                        </tr>
                        <tr>
                            <td colspan='2' style='padding: 10px; text-align: right;'>Moms (25%):</td>
                            <td style='padding: 10px; text-align: right;'>" . number_format($moms, 2, ',', ' ') . " kr</td>
                        </tr>
                        <tr class='total-row'>
                            <td colspan='2' style='padding: 10px; text-align: right; border-top: 2px solid #333;'>TOTALT:</td>
                            <td style='padding: 10px; text-align: right; border-top: 2px solid #333;'>" . number_format($totalInklMoms, 2, ',', ' ') . " kr</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            " . (!empty($meddelande) ? "<div class='section'><h3>Meddelande/Referens</h3><p>{$meddelande}</p></div>" : "") . "
        </div>
        <div class='footer'>
            <p>Beställning mottagen: " . date('Y-m-d H:i:s') . "</p>
            <p>" . COMPANY_NAME . "</p>
        </div>
    </div>
</body>
</html>
";

// Skicka e-post
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . COMPANY_NAME . ' <' . COMPANY_EMAIL . '>',
    'Reply-To: ' . $email
];

// Till företaget
$toCompany = mail(
    COMPANY_EMAIL,
    "Ny beställning {$ordernummer} från {$foretag}",
    $mailHtml,
    implode("\r\n", $headers)
);

// Till kunden (bekräftelse)
$kundenHtml = str_replace(
    '<h1>⚠️ Ny beställning</h1>',
    '<h1>⚠️ Tack för din beställning!</h1><p>Vi har tagit emot din beställning och återkommer med bekräftelse och betalningsinformation.</p>',
    $mailHtml
);

$headersKund = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . COMPANY_NAME . ' <' . COMPANY_EMAIL . '>'
];

$toCustomer = mail(
    $email,
    "Orderbekräftelse {$ordernummer} - " . COMPANY_NAME,
    $kundenHtml,
    implode("\r\n", $headersKund)
);

// Spara till databas (om tillgänglig)
try {
    $db = getDBConnection();
    $stmt = $db->prepare("
        INSERT INTO ordrar (ordernummer, foretag, orgnr, fornamn, efternamn, email, telefon, adress, postnr, ort, meddelande, produkter, totalt, skapad)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $ordernummer, $foretag, $orgnr, $fornamn, $efternamn, 
        $email, $telefon, $adress, $postnr, $ort, 
        $meddelande, $produkter, $totalInklMoms
    ]);
} catch (PDOException $e) {
    // Databasen kanske inte finns än - fortsätt ändå
    error_log("Order sparades inte i databas: " . $e->getMessage());
}

// Svara
echo json_encode([
    'success' => true,
    'ordernummer' => $ordernummer,
    'message' => 'Beställningen har skickats!'
]);
