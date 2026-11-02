<?php
require_once 'session_helper.php';
$msg = '';
$error = '';
$results = [];

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    try {
        // VULNERABLE: Stacked Queries / Multiple Statements simulation
        // The application splits user input by semicolon (;) and executes multiple SQL statements sequentially!
        $statements = explode(';', $search);
        
        foreach ($statements as $stmt_sql) {
            $stmt_sql = trim($stmt_sql);
            if (!empty($stmt_sql)) {
                $stmt = $pdo->query($stmt_sql);
                $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($fetched)) {
                    $results = array_merge($results, $fetched);
                }
            }
        }
        $msg = "Success: Executed query statements successfully!";
    } catch (PDOException $e) {
        $error = "SQL Syntax Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 9: Critical | SQLi Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 650px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #8b5cf6; }
        h1 { color: #8b5cf6; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #8b5cf6; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #7c3aed; }
        a.back { color: #94a3b8; text-decoration: none; display: inline-block; margin-top: 20px; }
        a.back:hover { color: #f1f5f9; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid #10b981; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid #ef4444; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #0f172a; border-radius: 6px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #334155; font-size: 0.9rem; }
        th { background: #1e293b; color: #38bdf8; text-transform: uppercase; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Level 9: Critical — Stacked Queries & Schema Alteration</h1>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <p>Some database drivers and custom query execution wrappers allow **Stacked Queries**, meaning multiple SQL commands can be executed in a single request by separating them with a semicolon (<code>;</code>). This allows attackers to perform schema alterations, insert administrative accounts, or drop tables.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
// VULNERABLE: Splits input on semicolon and executes multiple statements sequentially
$statements = explode(';', $_GET['search']);
foreach ($statements as $stmt) {
    $pdo->query($stmt);
}
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Execute stacked queries to modify the database schema or insert a new custom record. For example, search for: <code>A' ; INSERT INTO users (username, password, role) VALUES ('hacker', 'pwned', 'admin'); --</code></p>

        <form action="level9.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <label for="search">Product Search Query:</label><br>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Search Products" class="btn">
        </form>

        <?php if (!empty($results)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name / Column 1</th>
                        <th>Category / Column 2</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(isset($row['id']) ? $row['id'] : 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(isset($row['name']) ? $row['name'] : (isset($row['username']) ? $row['username'] : json_encode($row))); ?></td>
                            <td><?php echo htmlspecialchars(isset($row['category']) ? $row['category'] : (isset($row['role']) ? $row['role'] : '')); ?></td>
                            <td><?php echo htmlspecialchars(isset($row['price']) ? $row['price'] : ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (strpos($search, ';') !== false): ?>
                <div style="background: rgba(139, 92, 246, 0.15); border: 1px solid #8b5cf6; padding: 10px; border-radius: 6px; color: #a78bfa; margin-top: 15px; font-weight: bold; text-align: center;">
                    🎉 SUCCESS: You successfully executed stacked queries to alter database records!
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
