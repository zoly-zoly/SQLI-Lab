<?php
require_once 'session_helper.php';
$msg = '';
$error = '';
$results = [];

$category = isset($_GET['category']) ? $_GET['category'] : 'Electronics';

if (!empty($category)) {
    try {
        // VULNERABLE: UNION-based SQL injection via unquoted/quoted string concatenation
        $query = "SELECT name, category, price FROM products WHERE category = '$category'";
        $stmt = $pdo->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "SQL Syntax Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 3: Medium | SQLi Lab</title>
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
        .alert-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid #ef4444; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #0f172a; border-radius: 6px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #334155; font-size: 0.9rem; }
        th { background: #1e293b; color: #38bdf8; text-transform: uppercase; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Level 3: Medium — UNION-Based Exfiltration</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <p>When an application displays query results back to the user, you can use the <code>UNION</code> SQL operator to append the results of your own custom query onto the original query. To succeed, your injected query must match the exact number of columns and compatible data types as the original statement.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$category = $_GET['category'];
// VULNERABLE: Original query returns 3 columns (name, category, price)
$query = "SELECT name, category, price FROM products WHERE category = '$category'";
$results = $pdo->query($query)->fetchAll();
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Exfiltrate private user passwords from the <code>users</code> table. The original query returns **3 columns**. Use a UNION payload to retrieve usernames and passwords: <code>' UNION SELECT username, password, 0 FROM users --</code></p>

        <form action="level3.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <label for="category">Product Category Filter:</label><br>
            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($category); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Filter Products" class="btn">
        </form>

        <?php if (!empty($results)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product / Name</th>
                        <th>Category / Value 2</th>
                        <th>Price / Value 3</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars($row['price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (strpos($category, 'UNION') !== false || strpos($category, 'users') !== false): ?>
                <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid #f59e0b; padding: 10px; border-radius: 6px; color: #fbbf24; margin-top: 15px; font-weight: bold; text-align: center;">
                    🎉 SUCCESS: You successfully executed a UNION-based SQL injection to exfiltrate database records!
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
