<?php
/**
 * Admin - Symboler (Symbols)
 */
session_start();

require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$symboler = [];
$message = '';
$messageType = '';

// Hantera radering
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM symbols WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = 'Symbol borttagen';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Kunde inte ta bort: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Hämta symboler
try {
    $db = getDBConnection();
    $stmt = $db->query("SELECT * FROM symbols ORDER BY symbol_code");
    $symboler = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'Databasfel: ' . $e->getMessage();
    $messageType = 'error';
}

if (isset($_GET['msg']) && $_GET['msg'] === 'created') {
    $message = 'Symbol skapad!';
    $messageType = 'success';
}

if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $message = 'Symbol uppdaterad!';
    $messageType = 'success';
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Symboler - Admin</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>Admin</h2>
            <nav class="admin-nav">
                <a href="index.php">📦 Produkter</a>
                <a href="produktgrupper.php">🏷️ Produktgrupper</a>
                <a href="symboler.php" class="active">🔣 Symboler</a>
                <a href="stafflingar.php">💰 Prisstafflingar</a>
                <a href="import.php">📥 Mass Import</a>
                <a href="ordrar.php">📋 Ordrar</a>
                <hr>
                <a href="<?php echo SITE_URL; ?>" target="_blank">🌐 Visa butiken</a>
                <a href="logout.php">🚪 Logga ut</a>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1>Symboler</h1>
                <a href="symbol-form.php" class="btn btn-primary">+ Ny Symbol</a>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="help-text">
                    <p><strong>Symboler</strong> är de faktiska skyltmotiven som kombineras med produktgrupper.</p>
                    <p>Format: <code>M002</code>, <code>LV001</code>, etc.</p>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Symbolkod</th>
                            <th>Bild</th>
                            <th>Namn (SV)</th>
                            <th>Namn (EN)</th>
                            <th>Aktiv</th>
                            <th>Åtgärder</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($symboler) > 0): ?>
                            <?php foreach ($symboler as $symbol): ?>
                            <tr>
                                <td><?php echo $symbol['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($symbol['symbol_code']); ?></strong></td>
                                <td>
                                    <?php 
                                    $images = !empty($symbol['images']) ? explode(',', $symbol['images']) : [];
                                    $firstImage = !empty($images) ? trim($images[0]) : '';
                                    if ($firstImage): 
                                    ?>
                                    <img src="<?php echo SITE_URL; ?>/uploads/symbols/<?php echo htmlspecialchars($firstImage); ?>" 
                                         alt="<?php echo htmlspecialchars($symbol['symbol_code']); ?>" 
                                         style="width: 50px; height: 50px; object-fit: contain;">
                                    <?php else: ?>
                                    <span>Ingen bild</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($symbol['name_sv'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($symbol['name_en'] ?? ''); ?></td>
                                <td><?php echo $symbol['active'] ? '✅' : '❌'; ?></td>
                                <td class="actions">
                                    <a href="symbol-form.php?id=<?php echo $symbol['id']; ?>" class="btn btn-sm">Redigera</a>
                                    <a href="?delete=<?php echo $symbol['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Är du säker på att du vill ta bort denna symbol?')">Ta bort</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem;">
                                    Inga symboler än. <a href="symbol-form.php">Skapa en ny</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
