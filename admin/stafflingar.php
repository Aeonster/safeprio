<?php
/**
 * Admin - Prisstafflingar
 */
session_start();

require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = getDBConnection();
$message = '';
$messageType = '';

// Hantera radering
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $db->prepare("DELETE FROM pricing_tiers WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = 'Staffling raderad!';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Kunde inte radera staffling (används kanske av produktgrupper?): ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Hämta alla stafflingar
$stmt = $db->query("SELECT * FROM pricing_tiers ORDER BY id DESC");
$stafflingar = $stmt->fetchAll();

// Räkna användning
$usage = [];
foreach ($stafflingar as $tier) {
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM product_groups WHERE pricing_tier_id = ?");
    $stmt->execute([$tier['id']]);
    $usage[$tier['id']] = $stmt->fetch()['cnt'];
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prisstafflingar - Admin</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>Admin</h2>
            <nav class="admin-nav">
                <a href="index.php">📦 Produkter</a>
                <a href="produktgrupper.php">🏷️ Produktgrupper</a>
                <a href="symboler.php">🔣 Symboler</a>
                <a href="stafflingar.php" class="active">💰 Prisstafflingar</a>
                <a href="import.php">📥 Mass Import</a>
                <a href="ordrar.php">📋 Ordrar</a>
                <hr>
                <a href="<?php echo SITE_URL; ?>" target="_blank">🌐 Visa butiken</a>
                <a href="logout.php">🚪 Logga ut</a>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1>Prisstafflingar</h1>
                <a href="staffling-form.php" class="btn btn-primary">+ Ny staffling</a>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <p>Hantera prisstafflingar som kan återanvändas av olika produktgrupper.</p>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Namn (SV / EN)</th>
                            <th>Enhet</th>
                            <th>Stafflingsnivåer</th>
                            <th>Används av</th>
                            <th>Status</th>
                            <th>Åtgärder</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($stafflingar) > 0): ?>
                            <?php foreach ($stafflingar as $tier): 
                                $config = json_decode($tier['tier_config'], true);
                                $levels = [];
                                foreach ($config as $range) {
                                    if ($range['min'] == $range['max']) {
                                        $levels[] = $range['min'];
                                    } elseif ($range['max'] >= 9999) {
                                        $levels[] = $range['min'] . '+';
                                    } else {
                                        $levels[] = $range['min'] . '-' . $range['max'];
                                    }
                                }
                            ?>
                            <tr>
                                <td><?php echo $tier['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($tier['name']); ?></strong>
                                    <?php if (!empty($tier['name_en'])): ?>
                                        <br><small style="color: #666;"><?php echo htmlspecialchars($tier['name_en']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($tier['pricing_unit']); ?></td>
                                <td>
                                    <small><?php echo implode(' | ', $levels) . ' ' . htmlspecialchars($tier['pricing_unit']); ?></small>
                                </td>
                                <td><?php echo $usage[$tier['id']]; ?> produktgrupper</td>
                                <td>
                                    <span class="badge badge-<?php echo $tier['active'] ? 'success' : 'gray'; ?>">
                                        <?php echo $tier['active'] ? 'Aktiv' : 'Inaktiv'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="staffling-form.php?id=<?php echo $tier['id']; ?>" class="btn btn-sm">Redigera</a>
                                    <?php if ($usage[$tier['id']] == 0): ?>
                                    <a href="?delete=<?php echo $tier['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Är du säker?')">Radera</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Inga stafflingar hittades. <a href="staffling-form.php">Skapa en ny</a></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
