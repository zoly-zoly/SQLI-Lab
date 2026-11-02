<?php
require_once 'session_helper.php';
$error = '';
$products = [];

$category = isset($_GET['category']) ? $_GET['category'] : 'Security';

try {
    // VULNERABLE: String-based query wrapping input in single quotes
    $query = "SELECT id, name, description, price, category FROM products WHERE category = '" . $category . "'";
    $stmt = $pdo->query($query);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 2: Low | SQLi Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 650px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #10b981; }
        h1 { color: #10b981; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #10b981; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #059669; }
        a.back { color: #94a3b8; text-decoration: none; display: inline-block; margin-top: 20px; }
        a.back:hover { color: #f1f5f9; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid #ef4444; }
        .product-card { background: #0f172a; border-radius: 8px; padding: 15px; border: 1px dashed #334155; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Level 2: Low — String-Based Quote Escaping</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <p>This product category filter wraps the user's string input inside single quotes (<code>'</code>). To inject SQL, you must first break out of the string literal quote context and use SQL comment syntax (<code>--</code>) to disable the remainder of the query.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$category = $_GET['category'];
// VULNERABLE: String input enclosed in single quotes
$query = "SELECT * FROM products WHERE category = '" . $category . "'";
$result = $pdo->query($query);
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Break out of the quote context. For example, submit: <code>Security' OR '1'='1</code> or <code>' UNION SELECT id, username, password, 0, role FROM users --</code>.</p>

        <form action="level2.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <label for="category">Category Parameter:</label><br>
            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($category); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Filter Products" class="btn">
        </form>

        <?php if (!empty($products)): ?>
            <div style="margin-top: 20px;">
                <p style="margin: 0 0 5px 0; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase;">Query Results (<?php echo count($products); ?> rows returned):</p>
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <div><strong>ID:</strong> <?php echo htmlspecialchars($p['id'] ?? ''); ?></div>
                        <div><strong>Name:</strong> <span style="color: #38bdf8;"><?php echo htmlspecialchars($p['name'] ?? $p['username'] ?? 'Unknown'); ?></span></div>
                        <div><strong>Details/Password:</strong> <span style="color: #facc15; font-family: monospace;"><?php echo htmlspecialchars($p['description'] ?? $p['password'] ?? ''); ?></span></div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (count($products) > 1 || isset($products[0]['password'])): ?>
                    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; padding: 10px; border-radius: 6px; color: #34d399; margin-top: 15px; font-weight: bold; text-align: center;">
                        🎉 SUCCESS: You successfully escaped string quotes via SQL injection!
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
