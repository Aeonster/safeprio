<?php
/**
 * Admin - Produktgrupper (Product Groups)
 */
session_start();

require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$produktgrupper = [];
$message = '';
$messageType = '';

// Hantera radering
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM product_groups WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = 'Produktgrupp borttagen';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Kunde inte ta bort: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Hämta produktgrupper
try {
    $db = getDBConnection();
    $stmt = $db->query("SELECT * FROM product_groups ORDER BY prefix, size, group_code");
    $produktgrupper = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'Databasfel: ' . $e->getMessage();
    $messageType = 'error';
}

if (isset($_GET['msg']) && $_GET['msg'] === 'created') {
    $message = 'Produktgrupp skapad!';
    $messageType = 'success';
}

if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $message = 'Produktgrupp uppdaterad!';
    $messageType = 'success';
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produktgrupper - Admin</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>Admin</h2>
            <nav class="admin-nav">
                <a href="index.php">📦 Produkter</a>
                <a href="produktgrupper.php" class="active">🏷️ Produktgrupper</a>
                <a href="symboler.php">🔣 Symboler</a>
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
                <h1>Produktgrupper</h1>
                <a href="produktgrupp-form.php" class="btn btn-primary">+ Ny Produktgrupp</a>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="help-text">
                    <p><strong>Produktgrupper</strong> definierar bas-produkter med prissättning.</p>
                    <p>Format: <code>PREFIX_SIZE_GROUP</code> (t.ex. VMS_210-300_M)</p>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Prefix</th>
                            <th>Storlek</th>
                            <th>Grupp</th>
                            <th>Material</th>
                            <th>Pris 1 (SEK)</th>
                            <th>Pris 1 (EUR)</th>
                            <th>Ark/enhet</th>
                            <th>Aktiv</th>
                            <th>Åtgärder</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($produktgrupper) > 0): ?>
                            <?php foreach ($produktgrupper as $pg): ?>
                            <tr>
                                <td><?php echo $pg['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($pg['prefix']); ?></strong></td>
                                <td><?php echo htmlspecialchars($pg['size']); ?></td>
                                <td><?php echo htmlspecialchars($pg['group_code'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($pg['material_sv']); ?></td>
                                <td><?php echo number_format($pg['price_sek_1'], 2); ?> kr</td>
                                <td><?php echo number_format($pg['price_eur_1'], 2); ?> €</td>
                                <td><?php echo $pg['sheets_per_unit'] ?? '-'; ?></td>
                                <td><?php echo $pg['active'] ? '✅' : '❌'; ?></td>
                                <td class="actions">
                                    <a href="produktgrupp-form.php?id=<?php echo $pg['id']; ?>" class="btn btn-sm">Redigera</a>
                                    <a href="?delete=<?php echo $pg['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Är du säker på att du vill ta bort denna produktgrupp?')">Ta bort</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 2rem;">
                                    Inga produktgrupper än. <a href="produktgrupp-form.php">Skapa en ny</a>
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
