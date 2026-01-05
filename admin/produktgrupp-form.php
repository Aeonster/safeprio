<?php
/**
 * Admin - Produktgrupp formulär
 */
session_start();

require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = getDBConnection();
$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$produktgrupp = null;
$message = '';
$messageType = '';

// Hämta alla stafflingar för dropdown
$stmt = $db->query("SELECT * FROM pricing_tiers WHERE active = 1 ORDER BY name");
$stafflingar = $stmt->fetchAll();

// Hämta produktgrupp om redigering
if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM product_groups WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $produktgrupp = $stmt->fetch();
    
    if (!$produktgrupp) {
        header('Location: produktgrupper.php');
        exit;
    }
}

// Hantera formulärsubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $prefix = sanitize($_POST['prefix']);
        $size = sanitize($_POST['size']);
        $group_code = !empty($_POST['group_code']) ? sanitize($_POST['group_code']) : null;
        $symbol_category = !empty($_POST['symbol_category']) ? sanitize($_POST['symbol_category']) : null;
        $designation_sv = sanitize($_POST['designation_sv']);
        $designation_en = sanitize($_POST['designation_en']);
        $description_sv = sanitize($_POST['description_sv']);
        $description_en = sanitize($_POST['description_en']);
        $material_sv = sanitize($_POST['material_sv']);
        $material_en = sanitize($_POST['material_en']);
        
        $price_sek_1 = (float)$_POST['price_sek_1'];
        $price_sek_2 = (float)$_POST['price_sek_2'];
        $price_sek_3 = (float)$_POST['price_sek_3'];
        $price_sek_4 = (float)$_POST['price_sek_4'];
        $price_sek_5 = (float)$_POST['price_sek_5'];
        
        $price_eur_1 = (float)$_POST['price_eur_1'];
        $price_eur_2 = (float)$_POST['price_eur_2'];
        $price_eur_3 = (float)$_POST['price_eur_3'];
        $price_eur_4 = (float)$_POST['price_eur_4'];
        $price_eur_5 = (float)$_POST['price_eur_5'];
        
        $sheets_per_unit = !empty($_POST['sheets_per_unit']) ? (int)$_POST['sheets_per_unit'] : null;
        $pricing_tier_id = !empty($_POST['pricing_tier_id']) ? (int)$_POST['pricing_tier_id'] : null;
        $active = isset($_POST['active']) ? 1 : 0;
        
        if ($isEdit) {
            // Uppdatera
            $stmt = $db->prepare("
                UPDATE product_groups SET
                    prefix = ?, size = ?, group_code = ?, symbol_category = ?,
                    designation_sv = ?, designation_en = ?,
                    description_sv = ?, description_en = ?,
                    material_sv = ?, material_en = ?,
                    price_sek_1 = ?, price_sek_2 = ?, price_sek_3 = ?, price_sek_4 = ?, price_sek_5 = ?,
                    price_eur_1 = ?, price_eur_2 = ?, price_eur_3 = ?, price_eur_4 = ?, price_eur_5 = ?,
                    sheets_per_unit = ?, pricing_tier_id = ?, active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $prefix, $size, $group_code, $symbol_category,
                $designation_sv, $designation_en,
                $description_sv, $description_en,
                $material_sv, $material_en,
                $price_sek_1, $price_sek_2, $price_sek_3, $price_sek_4, $price_sek_5,
                $price_eur_1, $price_eur_2, $price_eur_3, $price_eur_4, $price_eur_5,
                $sheets_per_unit, $pricing_tier_id, $active,
                $_GET['id']
            ]);
            header('Location: produktgrupper.php?msg=updated');
            exit;
        } else {
            // Skapa ny
            $stmt = $db->prepare("
                INSERT INTO product_groups 
                (prefix, size, group_code, symbol_category, designation_sv, designation_en, description_sv, description_en, material_sv, material_en,
                 price_sek_1, price_sek_2, price_sek_3, price_sek_4, price_sek_5,
                 price_eur_1, price_eur_2, price_eur_3, price_eur_4, price_eur_5,
                 sheets_per_unit, pricing_tier_id, active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $prefix, $size, $group_code, $symbol_category,
                $designation_sv, $designation_en,
                $description_sv, $description_en,
                $material_sv, $material_en,
                $price_sek_1, $price_sek_2, $price_sek_3, $price_sek_4, $price_sek_5,
                $price_eur_1, $price_eur_2, $price_eur_3, $price_eur_4, $price_eur_5,
                $sheets_per_unit, $pricing_tier_id, $active
            ]);
            header('Location: produktgrupper.php?msg=created');
            exit;
        }
    } catch (PDOException $e) {
        $message = 'Databasfel: ' . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Redigera' : 'Ny'; ?> Produktgrupp - Admin</title>
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
                <h1><?php echo $isEdit ? 'Redigera' : 'Ny'; ?> Produktgrupp</h1>
                <a href="produktgrupper.php" class="btn">← Tillbaka</a>
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
                            <label for="prefix">Prefix *</label>
                            <input type="text" id="prefix" name="prefix" 
                                   value="<?php echo $produktgrupp['prefix'] ?? ''; ?>" 
                                   placeholder="VMS, VM, VMA, VMF, PM, RC, RM" required>
                            <small>VMS = Vinyl Märkning Standard, VM = Vinyl Märkning, etc.</small>
                        </div>

                        <div class="form-group">
                            <label for="size">Storlek *</label>
                            <input type="text" id="size" name="size" 
                                   value="<?php echo $produktgrupp['size'] ?? ''; ?>" 
                                   placeholder="210-300, 150-210, 350-40" required>
                            <small>Format: BxH i mm (t.ex. 210-300)</small>
                        </div>

                        <div class="form-group">
                            <label for="group_code">Gruppkod</label>
                            <input type="text" id="group_code" name="group_code" 
                                   value="<?php echo $produktgrupp['group_code'] ?? ''; ?>" 
                                   placeholder="M, W, P, E, F, D (för VM) eller LV, BG, etc (för RM)">
                            <small>M=Mandatory, W=Warning, P=Prohibition, etc. Lämna tom för PM/RC.</small>
                        </div>

                        <div class="form-group">
                            <label for="symbol_category">Symbol Kategori (matchning)</label>
                            <input type="text" id="symbol_category" name="symbol_category" 
                                   value="<?php echo $produktgrupp['symbol_category'] ?? ''; ?>" 
                                   placeholder="M, W, P, E, F, D, B, BG, BV, FG, LV, V, VA, PM, RC">
                            <small>Intern matchning mot symboler. Sätt automatiskt baserat på group_code eller prefix.</small>
                        </div>
                    </div>

                    <!-- Beskrivningar -->
                    <div class="form-section">
                        <h2>Benämning & Beskrivningar</h2>
                        
                        <div class="form-group">
                            <label for="designation_sv">Benämning (Svenska)</label>
                            <input type="text" id="designation_sv" name="designation_sv" 
                                   value="<?php echo $produktgrupp['designation_sv'] ?? ''; ?>"
                                   placeholder="Kort sammanfattning">
                            <small>Kort titel eller sammanfattning av produktgruppen</small>
                        </div>

                        <div class="form-group">
                            <label for="designation_en">Benämning (Engelska)</label>
                            <input type="text" id="designation_en" name="designation_en" 
                                   value="<?php echo $produktgrupp['designation_en'] ?? ''; ?>"
                                   placeholder="Short summary">
                            <small>Short title or summary of the product group</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="description_sv">Beskrivning (Svenska)</label>
                            <textarea id="description_sv" name="description_sv" rows="3"><?php echo $produktgrupp['description_sv'] ?? ''; ?></textarea>
                            <small>Utförlig beskrivning med detaljer</small>
                        </div>

                        <div class="form-group">
                            <label for="description_en">Beskrivning (Engelska)</label>
                            <textarea id="description_en" name="description_en" rows="3"><?php echo $produktgrupp['description_en'] ?? ''; ?></textarea>
                            <small>Detailed description</small>
                        </div>
                    </div>

                    <!-- Material -->
                    <div class="form-section">
                        <h2>Material</h2>
                        
                        <div class="form-group">
                            <label for="material_sv">Material (Svenska) *</label>
                            <input type="text" id="material_sv" name="material_sv" 
                                   value="<?php echo $produktgrupp['material_sv'] ?? ''; ?>" 
                                   placeholder="Vinyl, Hårdplast, Aluminium" required>
                        </div>

                        <div class="form-group">
                            <label for="material_en">Material (Engelska) *</label>
                            <input type="text" id="material_en" name="material_en" 
                                   value="<?php echo $produktgrupp['material_en'] ?? ''; ?>" 
                                   placeholder="Vinyl, Hard Plastic, Aluminium" required>
                        </div>
                    </div>

                    <!-- Priser SEK -->
                    <div class="form-section">
                        <h2>Priser (SEK)</h2>
                        <p class="help-text">Staffling: 1 st → 2 st → 3 st → 4 st → 5+ st (eller ark för RM)</p>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="price_sek_1">Pris 1 (SEK) *</label>
                                <input type="number" step="0.01" id="price_sek_1" name="price_sek_1" 
                                       value="<?php echo $produktgrupp['price_sek_1'] ?? '0'; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price_sek_2">Pris 2 (SEK) *</label>
                                <input type="number" step="0.01" id="price_sek_2" name="price_sek_2" 
                                       value="<?php echo $produktgrupp['price_sek_2'] ?? '0'; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price_sek_3">Pris 3 (SEK) *</label>
                                <input type="number" step="0.01" id="price_sek_3" name="price_sek_3" 
                                       value="<?php echo $produktgrupp['price_sek_3'] ?? '0'; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="price_sek_4">Pris 4 (SEK) *</label>
                                <input type="number" step="0.01" id="price_sek_4" name="price_sek_4" 
                                       value="<?php echo $produktgrupp['price_sek_4'] ?? '0'; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price_sek_5">Pris 5+ (SEK) *</label>
                                <input type="number" step="0.01" id="price_sek_5" name="price_sek_5" 
                                       value="<?php echo $produktgrupp['price_sek_5'] ?? '0'; ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Priser EUR -->
                    <div class="form-section">
                        <h2>Priser (EUR)</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="price_eur_1">Pris 1 (EUR) *</label>
                                <input type="number" step="0.01" id="price_eur_1" name="price_eur_1" 
                                       value="<?php echo $produktgrupp['price_eur_1'] ?? '0'; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price_eur_2">Pris 2 (EUR) *</label>
                                <input type="number" step="0.01" id="price_eur_2" name="price_eur_2" 
                                       value="<?php echo $produktgrupp['price_eur_2'] ?? '0'; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price_eur_3">Pris 3 (EUR) *</label>
                                <input type="number" step="0.01" id="price_eur_3" name="price_eur_3" 
                                       value="<?php echo $produktgrupp['price_eur_3'] ?? '0'; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="price_eur_4">Pris 4 (EUR) *</label>
                                <input type="number" step="0.01" id="price_eur_4" name="price_eur_4" 
                                       value="<?php echo $produktgrupp['price_eur_4'] ?? '0'; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price_eur_5">Pris 5+ (EUR) *</label>
                                <input type="number" step="0.01" id="price_eur_5" name="price_eur_5" 
                                       value="<?php echo $produktgrupp['price_eur_5'] ?? '0'; ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- RM Special -->
                    <div class="form-section">
                        <h2>Extra inställningar</h2>
                        
                        <div class="form-group">
                            <label for="pricing_tier_id">Prisstaffling *</label>
                            <select id="pricing_tier_id" name="pricing_tier_id" required>
                                <option value="">-- Välj staffling --</option>
                                <?php foreach ($stafflingar as $tier): ?>
                                <option value="<?php echo $tier['id']; ?>"
                                        <?php echo ($produktgrupp && $produktgrupp['pricing_tier_id'] == $tier['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tier['name']); ?> (<?php echo htmlspecialchars($tier['pricing_unit']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small>Välj vilken prisstaffling som ska användas. <a href="stafflingar.php" target="_blank">Hantera stafflingar →</a></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="sheets_per_unit">Märken per ark</label>
                            <input type="number" id="sheets_per_unit" name="sheets_per_unit" 
                                   value="<?php echo $produktgrupp['sheets_per_unit'] ?? ''; ?>" 
                                   placeholder="20, 10, 7, eller 5">
                            <small>Endast för RM-produkter. Lämna tomt för andra typer.</small>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-section">
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="active" <?php echo ($produktgrupp['active'] ?? 1) ? 'checked' : ''; ?>>
                                Aktiv
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $isEdit ? 'Uppdatera' : 'Skapa'; ?> Produktgrupp
                        </button>
                        <a href="produktgrupper.php" class="btn">Avbryt</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
