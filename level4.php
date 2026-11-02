<?php
require_once 'session_helper.php';
$error = '';
$results = [];

$id = isset($_GET['id']) ? $_GET['id'] : '1';

try {
    // VULNERABLE: Direct concatenation with verbose error reporting enabled
    $query = "SELECT id, name, description FROM products WHERE id = " . $id;
    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // VULNERABLE: Leaking verbose database error directly to screen
    $error = "Database Execution Exception: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 4: Medium | SQLi Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 650px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #f59e0b; }
        h1 { color: #f59e0b; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #f59e0b; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #d97706; }
        a.back { color: #94a3b8; text-decoration: none; display: inline-block; margin-top: 20px; }
        a.back:hover { color: #f1f5f9; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid #ef4444; font-family: monospace; word-break: break-all; }
        .product-card { background: #0f172a; border-radius: 8px; padding: 15px; border: 1px dashed #334155; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Level 4: Medium — Error-Based SQL Injection</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <p>When database exception messages are fully displayed to users, attackers can force syntax or type-casting errors that include query results inside the error text itself (**Error-Based SQLi**).</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
try {
    $query = "SELECT * FROM products WHERE id = " . $_GET['id'];
    $result = $pdo->query($query);
} catch (PDOException $e) {
    // VULNERABLE: Full exception string exposed
    echo "Database Error: " . $e->getMessage();
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Force an explicit error that leaks data. In SQLite, casting a string subquery to an integer will trigger a type conversion error containing the subquery's string value:<br>
        <em>Payload example:</em> <code>1 AND CAST((SELECT password FROM users WHERE username='admin') AS INT)</code></p>

        <form action="level4.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <label for="id">Product ID Parameter:</label><br>
            <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($id); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Query Database" class="btn">
        </form>

        <?php if (!empty($results)): ?>
            <div style="margin-top: 20px;">
                <p style="margin: 0 0 5px 0; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase;">Query Results:</p>
                <?php foreach ($results as $p): ?>
                    <div class="product-card">
                        <div><strong>Name:</strong> <span style="color: #38bdf8;"><?php echo htmlspecialchars($p['name'] ?? ''); ?></span></div>
                        <div><strong>Description:</strong> <?php echo htmlspecialchars($p['description'] ?? ''); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (strpos($error, 'admin_super_secret') !== false): ?>
            <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid #f59e0b; padding: 10px; border-radius: 6px; color: #fbbf24; margin-top: 15px; font-weight: bold; text-align: center;">
                🎉 SUCCESS: You leaked database secrets through verbose error exceptions!
            </div>
        <?php endif; ?>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
