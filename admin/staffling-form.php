<?php
/**
 * Admin - Staffling formulär
 */
session_start();

require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = getDBConnection();
$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$tier = null;
$message = '';
$messageType = '';

// Hämta staffling om redigering
if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM pricing_tiers WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $tier = $stmt->fetch();
    
    if (!$tier) {
        header('Location: stafflingar.php');
        exit;
    }
}

// Hantera formulärsubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = sanitize($_POST['name']);
        $name_en = sanitize($_POST['name_en']);
        $pricing_unit = sanitize($_POST['pricing_unit']);
        $description = sanitize($_POST['description']);
        $description_en = sanitize($_POST['description_en']);
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Bygg tier_config från formulärdata
        $tiers = [];
        for ($i = 1; $i <= 5; $i++) {
            if (isset($_POST["tier{$i}_min"]) && isset($_POST["tier{$i}_max"])) {
                $min = (int)$_POST["tier{$i}_min"];
                $max = (int)$_POST["tier{$i}_max"];
                if ($min > 0 && $max > 0) {
                    $tiers[] = ['min' => $min, 'max' => $max];
                }
            }
        }
        
        $tier_config = json_encode($tiers);
        
        if ($isEdit) {
            // Uppdatera
            $stmt = $db->prepare("
                UPDATE pricing_tiers SET
                    name = ?,
                    name_en = ?,
                    pricing_unit = ?,
                    tier_config = ?,
                    description = ?,
                    description_en = ?,
                    active = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $name_en, $pricing_unit, $tier_config, $description, $description_en, $active, $_GET['id']]);
            header('Location: stafflingar.php?msg=updated');
            exit;
        } else {
            // Skapa ny
            $stmt = $db->prepare("
                INSERT INTO pricing_tiers 
                (name, name_en, pricing_unit, tier_config, description, description_en, active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $name_en, $pricing_unit, $tier_config, $description, $description_en, $active]);
            header('Location: stafflingar.php?msg=created');
            exit;
        }
    } catch (PDOException $e) {
        $message = 'Databasfel: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Parse config för formulär
$config = $tier ? json_decode($tier['tier_config'], true) : [];
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Redigera' : 'Ny'; ?> Staffling - Admin</title>
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
                <h1><?php echo $isEdit ? 'Redigera' : 'Ny'; ?> Staffling</h1>
                <a href="stafflingar.php" class="btn">← Tillbaka</a>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" class="admin-form">
                    <!-- Grundinfo -->
                    <div class="form-section">
                        <h2>Grundinformation</h2>
                        
                        <div class="form-group">
                            <label for="name">Namn (Svenska) *</label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo $tier['name'] ?? ''; ?>" 
                                   placeholder="T.ex. 'Per styck (1-4)'" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="name_en">Namn (Engelska) *</label>
                            <input type="text" id="name_en" name="name_en" 
                                   value="<?php echo $tier['name_en'] ?? ''; ?>" 
                                   placeholder="E.g. 'Per piece (1-4)'" required>
                        </div>

                        <div class="form-group">
                            <label for="pricing_unit">Prisenhet *</label>
                            <input type="text" id="pricing_unit" name="pricing_unit" 
                                   value="<?php echo $tier['pricing_unit'] ?? 'st'; ?>" 
                                   placeholder="st, ark, m², etc." required>
                            <small>T.ex. "st" (styck), "ark", "m²", "meter"</small>
                        </div>

                        <div class="form-group">
                            <label for="description">Beskrivning (Svenska)</label>
                            <textarea id="description" name="description" rows="3"><?php echo $tier['description'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="description_en">Beskrivning (Engelska)</label>
                            <textarea id="description_en" name="description_en" rows="3"><?php echo $tier['description_en'] ?? ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="active" value="1" 
                                       <?php echo (!$tier || $tier['active']) ? 'checked' : ''; ?>>
                                Aktiv
                            </label>
                        </div>
                    </div>

                    <!-- Stafflingsnivåer -->
                    <div class="form-section">
                        <h2>Stafflingsnivåer (5 nivåer)</h2>
                        <p>Ange min och max antal för varje prisnivå. Använd 9999 för "oändligt".</p>
                        
                        <?php for ($i = 1; $i <= 5; $i++): 
                            $tierData = $config[$i-1] ?? ['min' => '', 'max' => ''];
                        ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tier<?php echo $i; ?>_min">Nivå <?php echo $i; ?> - Minimum</label>
                                <input type="number" id="tier<?php echo $i; ?>_min" name="tier<?php echo $i; ?>_min" 
                                       value="<?php echo $tierData['min']; ?>" min="0">
                            </div>
                            <div class="form-group">
                                <label for="tier<?php echo $i; ?>_max">Nivå <?php echo $i; ?> - Maximum</label>
                                <input type="number" id="tier<?php echo $i; ?>_max" name="tier<?php echo $i; ?>_max" 
                                       value="<?php echo $tierData['max']; ?>" min="0">
                            </div>
                        </div>
                        <?php endfor; ?>

                        <div class="alert alert-info">
                            <strong>Exempel:</strong><br>
                            Per styck: 1-1, 2-2, 3-3, 4-4, 5-9999 = "1 st | 2 st | 3 st | 4 st | 5+ st"<br>
                            Per ark: 1-4, 5-9, 10-24, 25-49, 50-9999 = "1-4 ark | 5-9 ark | 10-24 ark | 25-49 ark | 50+ ark"
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-large">
                        <?php echo $isEdit ? 'Uppdatera' : 'Skapa'; ?> Staffling
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
