<?php
/**
 * Admin - Dashboard / Produktlista
 */
session_start();

require_once '../includes/config.php';

// Kolla inloggning
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Hämta produkter från nya strukturen
$produkter = [];
$message = '';
$messageType = '';

// Visa statistik från produktgenerering
if (isset($_GET['generated']) && isset($_SESSION['generate_stats'])) {
    $stats = $_SESSION['generate_stats'];
    unset($_SESSION['generate_stats']);
    
    $message = "✅ Produktgenerering klar! ";
    $message .= "Raderade gamla: {$stats['deleted']}, ";
    $message .= "Skapade nya: {$stats['created']}";
    
    if (!empty($stats['errors'])) {
        $message .= " | Fel: " . count($stats['errors']);
    }
    $messageType = 'success';
} elseif (isset($_GET['error']) && isset($_SESSION['generate_error'])) {
    $message = 'Fel vid produktgenerering: ' . $_SESSION['generate_error'];
    $messageType = 'error';
    unset($_SESSION['generate_error']);
}

try {
    $db = getDBConnection();
    
    // Hämta produkter från view_products_full
    $stmt = $db->query("
        SELECT * FROM view_products_full 
        ORDER BY created_at DESC
    ");
    $produkter = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $message = 'Kunde inte ansluta till databasen: ' . $e->getMessage();
    $messageType = 'error';
}

// Hantera borttagning
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: index.php?msg=deleted');
        exit;
    } catch (PDOException $e) {
        $message = 'Kunde inte ta bort produkten: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Visa meddelanden
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'created':
            $message = 'Produkten har skapats!';
            $messageType = 'success';
            break;
        case 'updated':
            $message = 'Produkten har uppdaterats!';
            $messageType = 'success';
            break;
        case 'deleted':
            $message = 'Produkten har tagits bort!';
            $messageType = 'success';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Produkter | <?php echo COMPANY_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/admin.css">
</head>
<body class="admin-page">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <span>⚠️</span> Varsel Admin
            </div>
            <nav class="admin-nav">
                <a href="index.php" class="active">📦 Produkter</a>
                <a href="produktgrupper.php">🏷️ Produktgrupper</a>
                <a href="symboler.php">🔣 Symboler</a>
                <a href="stafflingar.php">💰 Prisstafflingar</a>
                <a href="import.php">📥 Mass Import</a>
                <a href="ordrar.php">📋 Ordrar</a>
                <hr>
                <a href="<?php echo SITE_URL; ?>" target="_blank">🌐 Visa butiken</a>
                <a href="logout.php">🚪 Logga ut</a>
            </nav>
        </aside>

        <!-- Main content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Produkter</h1>
                <div>
                    <a href="generate_products.php" class="btn btn-success" 
                       onclick="return confirm('Detta skapar alla möjliga kombinationer av produktgrupper × symboler. Fortsätt?')">
                       ⚡ Generera produkter
                    </a>
                    <a href="produktgrupper.php" class="btn btn-primary">🏷️ Hantera Produktgrupper</a>
                    <a href="symboler.php" class="btn btn-primary">🔣 Hantera Symboler</a>
                </div>
            </header>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="admin-card">
                <div class="admin-toolbar">
                    <input type="text" id="searchInput" placeholder="Sök artikelnummer eller produktnamn..." class="admin-search">
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Artikelnummer</th>
                            <th>Produktnamn</th>
                            <th>Symbol</th>
                            <th>Pris (SEK)</th>
                            <th>Status</th>
                            <th width="120">Åtgärder</th>
                        </tr>
                    </thead>
                    <tbody id="productsTable">
                        <?php if (empty($produkter)): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                Inga produkter hittades. 
                                <a href="produktgrupper.php">Skapa produktgrupper</a> och 
                                <a href="symboler.php">symboler</a> först.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($produkter as $p): ?>
                        <tr>
                            <td><code><?php echo sanitize($p['article_number']); ?></code></td>
                            <td><strong><?php echo sanitize($p['product_name']); ?></strong></td>
                            <td><?php echo sanitize($p['symbol_name_sv']); ?></td>
                            <td><?php echo formatPrice($p['price_sek_1']); ?></td>
                            <td>
                                <?php if ($p['active']): ?>
                                <span class="badge badge-success">Aktiv</span>
                                <?php else: ?>
                                <span class="badge badge-gray">Inaktiv</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php echo SITE_URL; ?>/produkt.php?artno=<?php echo $p['article_number']; ?>" 
                                       class="btn-icon" title="Visa" target="_blank">👁️</a>
                                    <a href="index.php?delete=<?php echo $p['id']; ?>" 
                                       class="btn-icon btn-delete" title="Ta bort" 
                                       onclick="return confirm('Vill du verkligen ta bort denna produkt?')">🗑️</a>
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
        // Sök
        document.getElementById('searchInput').addEventListener('input', filterProducts);

        function filterProducts() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#productsTable tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const matchesSearch = text.includes(search);
                row.style.display = matchesSearch ? '' : 'none';
            });
        }
    </script>
</body>
</html>
