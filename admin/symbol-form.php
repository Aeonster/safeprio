<?php
/**
 * Admin - Symbol formulär
 */
session_start();

require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = getDBConnection();
$isEdit = isset($_GET['id']) && is_numeric($_GET['id']);
$symbol = null;
$message = '';
$messageType = '';

// Hämta symbol om redigering
if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM symbols WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $symbol = $stmt->fetch();
    
    if (!$symbol) {
        header('Location: symboler.php');
        exit;
    }
}

// Hantera formulärsubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $symbol_code = strtoupper(sanitize($_POST['symbol_code']));
        $symbol_name_sv = sanitize($_POST['symbol_name_sv']);
        $symbol_name_en = sanitize($_POST['symbol_name_en']);
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Hantera bilduppladdning
        $image_path = $symbol['image_path'] ?? '';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $newFilename = $symbol_code . '_' . time() . '.' . $ext;
                $uploadPath = '../uploads/produkter/' . $newFilename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    // Ta bort gammal bild om det finns en
                    if (!empty($symbol['image_path']) && file_exists('../uploads/produkter/' . $symbol['image_path'])) {
                        unlink('../uploads/produkter/' . $symbol['image_path']);
                    }
                    $image_path = $newFilename;
                }
            }
        }
        
        if ($isEdit) {
            // Uppdatera
            $stmt = $db->prepare("
                UPDATE symbols SET
                    symbol_code = ?,
                    symbol_name_sv = ?,
                    symbol_name_en = ?,
                    image_path = ?,
                    active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $symbol_code,
                $symbol_name_sv,
                $symbol_name_en,
                $image_path,
                $active,
                $_GET['id']
            ]);
            header('Location: symboler.php?msg=updated');
            exit;
        } else {
            // Skapa ny
            $stmt = $db->prepare("
                INSERT INTO symbols 
                (symbol_code, symbol_name_sv, symbol_name_en, image_path, active)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $symbol_code,
                $symbol_name_sv,
                $symbol_name_en,
                $image_path,
                $active
            ]);
            header('Location: symboler.php?msg=created');
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
    <title><?php echo $isEdit ? 'Redigera' : 'Ny'; ?> Symbol - Admin</title>
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
                <h1><?php echo $isEdit ? 'Redigera' : 'Ny'; ?> Symbol</h1>
                <a href="symboler.php" class="btn">← Tillbaka</a>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <!-- Grundinfo -->
                    <div class="form-section">
                        <h2>Symbolinformation</h2>
                        
                        <div class="form-group">
                            <label for="symbol_code">Symbolkod *</label>
                            <input type="text" id="symbol_code" name="symbol_code" 
                                   value="<?php echo $symbol['symbol_code'] ?? ''; ?>" 
                                   placeholder="M002, LV001, BG001" 
                                   pattern="[A-Z0-9]+" 
                                   required>
                            <small>Enbart stora bokstäver och siffror (t.ex. M002, LV001)</small>
                        </div>

                        <div class="form-group">
                            <label for="symbol_name_sv">Namn (Svenska) *</label>
                            <input type="text" id="symbol_name_sv" name="symbol_name_sv" 
                                   value="<?php echo $symbol['symbol_name_sv'] ?? ''; ?>" 
                                   placeholder="Lås" required>
                        </div>

                        <div class="form-group">
                            <label for="symbol_name_en">Namn (Engelska) *</label>
                            <input type="text" id="symbol_name_en" name="symbol_name_en" 
                                   value="<?php echo $symbol['symbol_name_en'] ?? ''; ?>" 
                                   placeholder="Lock" required>
                        </div>
                    </div>

                    <!-- Bild -->
                    <div class="form-section">
                        <h2>Symbolbild</h2>
                        
                        <?php if (!empty($symbol['image_path'])): ?>
                        <div class="current-image">
                            <p><strong>Nuvarande bild:</strong></p>
                            <img src="<?php echo SITE_URL; ?>/uploads/produkter/<?php echo htmlspecialchars($symbol['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($symbol['symbol_code']); ?>" 
                                 style="max-width: 200px; max-height: 200px; object-fit: contain; border: 1px solid #ddd; padding: 10px;">
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="image">Ladda upp ny bild</label>
                            <input type="file" id="image" name="image" accept="image/*">
                            <small>JPG, PNG, GIF eller SVG. Rekommenderad storlek: 500x500px</small>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-section">
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="active" <?php echo ($symbol['active'] ?? 1) ? 'checked' : ''; ?>>
                                Aktiv
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $isEdit ? 'Uppdatera' : 'Skapa'; ?> Symbol
                        </button>
                        <a href="symboler.php" class="btn">Avbryt</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Förhandsgranska bild
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.current-image');
                    if (preview) {
                        preview.querySelector('img').src = e.target.result;
                    } else {
                        const div = document.createElement('div');
                        div.className = 'current-image';
                        div.innerHTML = `
                            <p><strong>Förhandsvisning:</strong></p>
                            <img src="${e.target.result}" style="max-width: 200px; max-height: 200px; object-fit: contain; border: 1px solid #ddd; padding: 10px;">
                        `;
                        document.querySelector('.form-group input[type="file"]').parentElement.appendChild(div);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
