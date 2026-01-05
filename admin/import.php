<?php
/**
 * Admin - Mass Import från CSV
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
$preview = null;
$importStats = null;

// Konvertera CSV från ANSI/Windows-1252 till UTF-8
function convertToUTF8($content) {
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
    if ($encoding && $encoding !== 'UTF-8') {
        return mb_convert_encoding($content, 'UTF-8', $encoding);
    }
    return $content;
}

// Parse CSV-innehåll
function parseCSV($content, $delimiter = ';') {
    $content = convertToUTF8($content);
    $lines = explode("\n", $content);
    $data = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Hantera citattecken korrekt
        $row = str_getcsv($line, $delimiter);
        $data[] = $row;
    }
    
    return $data;
}

// Hantera CSV-upload och preview/import
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $importType = $_POST['import_type'] ?? '';
        $isDryRun = isset($_POST['dry_run']);
        
        if (empty($importType) || !in_array($importType, ['product_groups', 'symbols', 'products'])) {
            throw new Exception('Ogiltig importtyp');
        }
        
        // Hämta CSV-innehåll (antingen från fil eller från hidden field efter dry run)
        if (isset($_POST['csv_content'])) {
            // Faktisk import efter dry run
            $content = base64_decode($_POST['csv_content']);
        } elseif (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            // Första uppladdningen (dry run)
            $content = file_get_contents($_FILES['csv_file']['tmp_name']);
        } else {
            throw new Exception('Ingen fil uppladdad');
        }
        
        $rows = parseCSV($content);
        
        if (count($rows) < 2) {
            throw new Exception('CSV-filen måste innehålla minst en rubrikrad och en datarad');
        }
        
        $headers = array_shift($rows); // Ta bort rubrikraden
        
        if ($isDryRun) {
            // DRY RUN - Visa preview
            $preview = [
                'type' => $importType,
                'headers' => $headers,
                'rows' => array_slice($rows, 0, 20), // Visa max 20 rader i preview
                'total' => count($rows),
                'file_data' => base64_encode($content) // Spara data för faktisk import
            ];
            $message = "Preview av " . count($rows) . " rader. Granska och klicka 'Genomför Import' för att fortsätta.";
            $messageType = 'info';
        } else {
            // FAKTISK IMPORT
            $stats = [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => []
            ];
            
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // +2 eftersom vi har rubrik + 0-baserad index
                
                try {
                    if (count($row) !== count($headers)) {
                        throw new Exception("Fel antal kolumner på rad $rowNum");
                    }
                    
                    $data = array_combine($headers, $row);
                    
                    // Import baserat på typ
                    switch ($importType) {
                        case 'product_groups':
                            importProductGroup($db, $data, $stats);
                            break;
                        case 'symbols':
                            importSymbol($db, $data, $stats);
                            break;
                        case 'products':
                            importProduct($db, $data, $stats);
                            break;
                    }
                } catch (Exception $e) {
                    $stats['errors'][] = "Rad $rowNum: " . $e->getMessage();
                    $stats['skipped']++;
                }
            }
            
            $importStats = $stats;
            $message = "Import klar!";
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'Fel: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Import-funktioner
function importProductGroup($db, $data, &$stats) {
    $id = !empty($data['id']) ? (int)$data['id'] : null;
    
    // Kolla om ID finns
    if ($id) {
        $stmt = $db->prepare("SELECT id FROM product_groups WHERE id = ?");
        $stmt->execute([$id]);
        $exists = $stmt->fetch();
    } else {
        $exists = false;
    }
    
    $fields = [
        'prefix' => $data['prefix'] ?? '',
        'size' => $data['size'] ?? '',
        'group_code' => $data['group_code'] ?? '',
        'symbol_category' => $data['symbol_category'] ?? '',
        'designation_sv' => $data['designation_sv'] ?? '',
        'designation_en' => $data['designation_en'] ?? '',
        'material_sv' => $data['material_sv'] ?? '',
        'material_en' => $data['material_en'] ?? '',
        'description_sv' => $data['description_sv'] ?? '',
        'description_en' => $data['description_en'] ?? '',
        'price_sek_1' => (float)($data['price_sek_1'] ?? 0),
        'price_sek_2' => (float)($data['price_sek_2'] ?? 0),
        'price_sek_3' => (float)($data['price_sek_3'] ?? 0),
        'price_sek_4' => (float)($data['price_sek_4'] ?? 0),
        'price_sek_5' => (float)($data['price_sek_5'] ?? 0),
        'price_eur_1' => (float)($data['price_eur_1'] ?? 0),
        'price_eur_2' => (float)($data['price_eur_2'] ?? 0),
        'price_eur_3' => (float)($data['price_eur_3'] ?? 0),
        'price_eur_4' => (float)($data['price_eur_4'] ?? 0),
        'price_eur_5' => (float)($data['price_eur_5'] ?? 0),
        'sheets_per_unit' => !empty($data['sheets_per_unit']) ? (int)$data['sheets_per_unit'] : 1,
        'pricing_tier_id' => !empty($data['pricing_tier_id']) ? (int)$data['pricing_tier_id'] : 1,
        'images' => $data['images'] ?? '',
        'active' => isset($data['active']) ? (int)$data['active'] : 1
    ];
    
    if ($exists) {
        // UPDATE
        $sql = "UPDATE product_groups SET 
                prefix = ?, size = ?, group_code = ?, symbol_category = ?,
                designation_sv = ?, designation_en = ?,
                material_sv = ?, material_en = ?,
                description_sv = ?, description_en = ?,
                price_sek_1 = ?, price_sek_2 = ?, price_sek_3 = ?, price_sek_4 = ?, price_sek_5 = ?,
                price_eur_1 = ?, price_eur_2 = ?, price_eur_3 = ?, price_eur_4 = ?, price_eur_5 = ?,
                sheets_per_unit = ?, pricing_tier_id = ?, images = ?, active = ?
                WHERE id = ?";
        $params = array_values($fields);
        $params[] = $id;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $stats['updated']++;
    } else {
        // INSERT
        if ($id) {
            $sql = "INSERT INTO product_groups (id, prefix, size, group_code, symbol_category,
                    designation_sv, designation_en,
                    material_sv, material_en, description_sv, description_en,
                    price_sek_1, price_sek_2, price_sek_3, price_sek_4, price_sek_5,
                    price_eur_1, price_eur_2, price_eur_3, price_eur_4, price_eur_5,
                    sheets_per_unit, pricing_tier_id, images, active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$id];
            $params = array_merge($params, array_values($fields));
        } else {
            $sql = "INSERT INTO product_groups (prefix, size, group_code, symbol_category,
                    designation_sv, designation_en,
                    material_sv, material_en, description_sv, description_en,
                    price_sek_1, price_sek_2, price_sek_3, price_sek_4, price_sek_5,
                    price_eur_1, price_eur_2, price_eur_3, price_eur_4, price_eur_5,
                    sheets_per_unit, pricing_tier_id, images, active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = array_values($fields);
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $stats['created']++;
    }
}

function importSymbol($db, $data, &$stats) {
    $id = !empty($data['id']) ? (int)$data['id'] : null;
    
    if ($id) {
        $stmt = $db->prepare("SELECT id FROM symbols WHERE id = ?");
        $stmt->execute([$id]);
        $exists = $stmt->fetch();
    } else {
        $exists = false;
    }
    
    $fields = [
        'symbol_code' => $data['symbol_code'] ?? '',
        'category' => $data['category'] ?? '',
        'name_sv' => $data['name_sv'] ?? '',
        'name_en' => $data['name_en'] ?? '',
        'images' => $data['images'] ?? '',
        'active' => isset($data['active']) ? (int)$data['active'] : 1
    ];
    
    if ($exists) {
        $sql = "UPDATE symbols SET 
                symbol_code = ?, category = ?, name_sv = ?, name_en = ?, images = ?, active = ?
                WHERE id = ?";
        $params = array_values($fields);
        $params[] = $id;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $stats['updated']++;
    } else {
        if ($id) {
            $sql = "INSERT INTO symbols (id, symbol_code, category, name_sv, name_en, images, active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = [$id];
            $params = array_merge($params, array_values($fields));
        } else {
            $sql = "INSERT INTO symbols (symbol_code, category, name_sv, name_en, images, active)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $params = array_values($fields);
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $stats['created']++;
    }
}

function importProduct($db, $data, &$stats) {
    $id = !empty($data['id']) ? (int)$data['id'] : null;
    
    if ($id) {
        $stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $exists = $stmt->fetch();
    } else {
        $exists = false;
    }
    
    // Validera att product_group_id och symbol_id finns
    $product_group_id = !empty($data['product_group_id']) ? (int)$data['product_group_id'] : null;
    $symbol_id = !empty($data['symbol_id']) ? (int)$data['symbol_id'] : null;
    
    if (!$product_group_id || !$symbol_id) {
        throw new Exception("product_group_id och symbol_id är obligatoriska");
    }
    
    $fields = [
        'product_group_id' => $product_group_id,
        'symbol_id' => $symbol_id,
        'article_number' => $data['article_number'] ?? '',
        'product_name' => $data['product_name'] ?? '',
        'active' => isset($data['active']) ? (int)$data['active'] : 1,
        'featured' => isset($data['featured']) ? (int)$data['featured'] : 0
    ];
    
    if ($exists) {
        $sql = "UPDATE products SET 
                product_group_id = ?, symbol_id = ?, article_number = ?, product_name = ?,
                active = ?, featured = ?
                WHERE id = ?";
        $params = array_values($fields);
        $params[] = $id;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $stats['updated']++;
    } else {
        if ($id) {
            $sql = "INSERT INTO products (id, product_group_id, symbol_id, article_number, product_name,
                    active, featured)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = [$id];
            $params = array_merge($params, array_values($fields));
        } else {
            $sql = "INSERT INTO products (product_group_id, symbol_id, article_number, product_name,
                    active, featured)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $params = array_values($fields);
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $stats['created']++;
    }
}

// Kolumnformat för olika typer
$formats = [
    'product_groups' => [
        'name' => 'Produktgrupper',
        'columns' => ['id', 'prefix', 'size', 'group_code', 'symbol_category', 'designation_sv', 'designation_en', 'description_sv', 'description_en', 'material_sv', 'material_en', 'price_sek_1', 'price_sek_2', 'price_sek_3', 'price_sek_4', 'price_sek_5', 'price_eur_1', 'price_eur_2', 'price_eur_3', 'price_eur_4', 'price_eur_5', 'sheets_per_unit', 'pricing_tier_id', 'active', 'images'],
        'example' => '1;VMS;210-300;M;M;Benämning sv;Designation en;Beskrivning sv;Description en;Aluminium;Aluminium;150.00;140.00;130.00;120.00;110.00;15.00;14.00;13.00;12.00;11.00;1;1;1;image.jpg'
    ],
    'symbols' => [
        'name' => 'Symboler',
        'columns' => ['id', 'symbol_code', 'category', 'name_sv', 'name_en', 'active', 'images'],
        'example' => '1;M002;M;Andningsskydd;Respiratory protection;1;symbol.jpg'
    ],
    'products' => [
        'name' => 'Produkter',
        'columns' => ['id', 'product_group_id', 'symbol_id', 'article_number', 'product_name', 'active', 'featured'],
        'example' => '1;1;1;VMS_210-300_M-M002;VMS 210-300 M - M002;1;0'
    ]
];
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mass Import - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .preview-table {
            overflow-x: auto;
            margin: 20px 0;
        }
        .preview-table table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .preview-table th,
        .preview-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .preview-table th {
            background: #004fa2;
            color: white;
            position: sticky;
            top: 0;
        }
        .format-box {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
        }
        .format-box h4 {
            margin-top: 0;
        }
        .format-box code {
            background: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 11px;
        }
        .column-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 10px 0;
        }
        .column-list code {
            background: #004fa2;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .stats-box {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .stat-card.success { border-color: #28a745; }
        .stat-card.warning { border-color: #ffc107; }
        .stat-card.error { border-color: #dc3545; }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .error-list {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
            max-height: 300px;
            overflow-y: auto;
        }
        .error-list li {
            margin: 5px 0;
            color: #856404;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h2>Admin Panel</h2>
            </div>
            <nav class="admin-nav">
                <a href="index.php">📦 Produkter</a>
                <a href="produktgrupper.php">🏷️ Produktgrupper</a>
                <a href="symboler.php">🔣 Symboler</a>
                <a href="stafflingar.php">💰 Prisstafflingar</a>
                <a href="import.php" class="active">📥 Mass Import</a>
                <a href="ordrar.php">📋 Ordrar</a>
                <hr>
                <a href="<?php echo SITE_URL; ?>" target="_blank">🌐 Visa butiken</a>
                <a href="logout.php">🚪 Logga ut</a>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-header">
                <h1>📥 Mass Import från CSV</h1>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <?php if ($importStats): ?>
            <!-- Visa importstatistik -->
            <div class="card">
                <h2>✅ Import genomförd!</h2>
                
                <div class="stats-box">
                    <div class="stat-card success">
                        <div class="stat-number"><?php echo $importStats['created']; ?></div>
                        <div class="stat-label">Nya poster skapade</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-number"><?php echo $importStats['updated']; ?></div>
                        <div class="stat-label">Poster uppdaterade</div>
                    </div>
                    <div class="stat-card error">
                        <div class="stat-number"><?php echo $importStats['skipped']; ?></div>
                        <div class="stat-label">Poster överhoppade (fel)</div>
                    </div>
                </div>

                <?php if (!empty($importStats['errors'])): ?>
                <div class="error-list">
                    <h4>⚠️ Fel som uppstod:</h4>
                    <ul>
                        <?php foreach ($importStats['errors'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <a href="import.php" class="btn">Gör ny import</a>
            </div>
            <?php elseif ($preview): ?>
            <!-- Visa preview -->
            <div class="card">
                <h2>🔍 Preview av import</h2>
                <p><strong>Typ:</strong> <?php echo $formats[$preview['type']]['name']; ?></p>
                <p><strong>Totalt antal rader:</strong> <?php echo $preview['total']; ?></p>
                <p><em>Visar max 20 första raderna...</em></p>

                <div class="preview-table">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <?php foreach ($preview['headers'] as $header): ?>
                                <th><?php echo htmlspecialchars($header); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($preview['rows'] as $index => $row): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <?php foreach ($row as $cell): ?>
                                <td><?php echo htmlspecialchars($cell); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <form method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="import_type" value="<?php echo $preview['type']; ?>">
                    <input type="hidden" name="csv_content" value="<?php echo $preview['file_data']; ?>">
                    <button type="submit" class="btn btn-success" style="background: #28a745;">✅ Genomför Import</button>
                    <a href="import.php" class="btn">❌ Avbryt</a>
                </form>
            </div>
            <?php else: ?>
            <!-- Visa uppladdningsformulär -->
            <div class="card">
                <h2>Välj importtyp och ladda upp CSV</h2>
                
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <div class="form-group">
                        <label for="import_type">Vad vill du importera? *</label>
                        <select name="import_type" id="import_type" required onchange="showFormat(this.value)">
                            <option value="">-- Välj typ --</option>
                            <option value="product_groups">Produktgrupper</option>
                            <option value="symbols">Symboler</option>
                            <option value="products">Produkter</option>
                        </select>
                    </div>

                    <div id="formatInfo"></div>

                    <div class="form-group">
                        <label for="csv_file">CSV-fil (semikolon-separerad) *</label>
                        <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                        <small>Filen konverteras automatiskt från ANSI/Windows-1252 till UTF-8</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="dry_run" class="btn btn-primary">🔍 Förhandsgranska (Dry Run)</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h3>ℹ️ Instruktioner</h3>
                <ul>
                    <li><strong>CSV-format:</strong> Använd semikolon (;) som separator</li>
                    <li><strong>Encoding:</strong> Filen konverteras automatiskt från ANSI/Windows-1252 till UTF-8</li>
                    <li><strong>Första raden:</strong> Måste innehålla kolumnnamn exakt som angivet nedan</li>
                    <li><strong>ID-hantering:</strong>
                        <ul>
                            <li>Om ID finns i databasen → uppdateras</li>
                            <li>Om ID saknas i databasen → ny post skapas</li>
                            <li>Om ID är tomt i CSV → ny post skapas med auto-increment ID</li>
                            <li>Poster i databasen som inte finns i CSV → ignoreras (raderas EJ)</li>
                        </ul>
                    </li>
                    <li><strong>Bilder:</strong> Ange endast filnamn (t.ex. "bild.jpg"), se till att filer finns i rätt mapp först</li>
                    <li><strong>Active:</strong> 1 = aktiv, 0 = inaktiv</li>
                </ul>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        const formats = <?php echo json_encode($formats); ?>;

        function showFormat(type) {
            const info = document.getElementById('formatInfo');
            if (!type) {
                info.innerHTML = '';
                return;
            }

            const format = formats[type];
            let html = `
                <div class="format-box">
                    <h4>📋 Kolumnformat för ${format.name}</h4>
                    <p><strong>Kolumner (i denna ordning):</strong></p>
                    <div class="column-list">
                        ${format.columns.map(col => `<code>${col}</code>`).join('')}
                    </div>
                    <p><strong>Exempel på rad:</strong></p>
                    <code style="display: block; padding: 10px; white-space: pre-wrap; word-break: break-all;">${format.example}</code>
                </div>
            `;
            info.innerHTML = html;
        }
    </script>
</body>
</html>
