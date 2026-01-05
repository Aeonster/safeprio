<?php
/**
 * Admin - Ordrar
 */
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$ordrar = [];
$message = '';

try {
    $db = getDBConnection();
    
    // Uppdatera status
    if (isset($_POST['update_status'])) {
        $stmt = $db->prepare("UPDATE ordrar SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        $message = 'Status uppdaterad!';
    }
    
    $stmt = $db->query("SELECT * FROM ordrar ORDER BY skapad DESC");
    $ordrar = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'Kunde inte hämta ordrar. Har du kört database.sql?';
}

$statusLabels = [
    'ny' => ['label' => 'Ny', 'class' => 'badge-warning'],
    'behandlas' => ['label' => 'Behandlas', 'class' => 'badge-info'],
    'skickad' => ['label' => 'Skickad', 'class' => 'badge-success'],
    'klar' => ['label' => 'Klar', 'class' => 'badge-gray']
];
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordrar | Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/admin.css">
    <style>
        .badge-info { background: #dbeafe; color: #1e40af; }
        .order-details { display: none; background: #f8fafc; }
        .order-details.open { display: table-row; }
        .order-details td { padding: 20px; }
        .order-products { margin: 15px 0; }
        .order-products table { width: 100%; margin-top: 10px; }
        .order-products th, .order-products td { padding: 8px; border: 1px solid #e2e8f0; }
        .toggle-btn { cursor: pointer; }
    </style>
</head>
<body class="admin-page">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-logo"><span>⚠️</span> Varsel Admin</div>
            <nav class="admin-nav">
                <a href="index.php">📦 Produkter</a>
                <a href="kategorier.php">📁 Kategorier</a>
                <a href="ordrar.php" class="active">📋 Ordrar</a>
                <hr>
                <a href="<?php echo SITE_URL; ?>" target="_blank">🌐 Visa butiken</a>
                <a href="logout.php">🚪 Logga ut</a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <h1>Ordrar</h1>
            </header>

            <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="admin-card">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Ordernr</th>
                            <th>Kund</th>
                            <th>E-post</th>
                            <th>Totalt</th>
                            <th>Status</th>
                            <th>Datum</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ordrar)): ?>
                        <tr><td colspan="7" class="text-center">Inga ordrar ännu.</td></tr>
                        <?php else: ?>
                        <?php foreach ($ordrar as $order): ?>
                        <tr class="toggle-btn" onclick="toggleOrder(<?php echo $order['id']; ?>)">
                            <td><strong><?php echo $order['ordernummer']; ?></strong></td>
                            <td><?php echo sanitize($order['foretag']); ?></td>
                            <td><?php echo sanitize($order['email']); ?></td>
                            <td><?php echo formatPrice($order['totalt']); ?></td>
                            <td>
                                <span class="badge <?php echo $statusLabels[$order['status']]['class']; ?>">
                                    <?php echo $statusLabels[$order['status']]['label']; ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($order['skapad'])); ?></td>
                            <td>▼</td>
                        </tr>
                        <tr class="order-details" id="order-<?php echo $order['id']; ?>">
                            <td colspan="7">
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                                    <div>
                                        <strong>Kunduppgifter:</strong><br>
                                        <?php echo sanitize($order['fornamn'] . ' ' . $order['efternamn']); ?><br>
                                        <?php echo sanitize($order['telefon']); ?><br>
                                        Org.nr: <?php echo sanitize($order['orgnr']); ?>
                                    </div>
                                    <div>
                                        <strong>Leveransadress:</strong><br>
                                        <?php echo sanitize($order['adress']); ?><br>
                                        <?php echo sanitize($order['postnr'] . ' ' . $order['ort']); ?>
                                    </div>
                                    <div>
                                        <strong>Ändra status:</strong>
                                        <form method="POST" style="margin-top: 10px;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" style="padding: 8px;">
                                                <?php foreach ($statusLabels as $key => $val): ?>
                                                <option value="<?php echo $key; ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>>
                                                    <?php echo $val['label']; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </div>
                                </div>
                                <?php if ($order['meddelande']): ?>
                                <p style="margin-top: 15px;"><strong>Meddelande:</strong> <?php echo sanitize($order['meddelande']); ?></p>
                                <?php endif; ?>
                                <div class="order-products">
                                    <strong>Produkter:</strong>
                                    <table>
                                        <tr><th>Produkt</th><th>Storlek</th><th>Antal</th><th>Pris</th></tr>
                                        <?php 
                                        $produkter = json_decode($order['produkter'], true) ?: [];
                                        foreach ($produkter as $p): 
                                        ?>
                                        <tr>
                                            <td><?php echo sanitize($p['namn']); ?></td>
                                            <td><?php echo sanitize($p['storlek'] ?? '-'); ?></td>
                                            <td><?php echo $p['antal']; ?></td>
                                            <td><?php echo formatPrice($p['pris'] * $p['antal']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>
        function toggleOrder(id) {
            document.getElementById('order-' + id).classList.toggle('open');
        }
    </script>
</body>
</html>
