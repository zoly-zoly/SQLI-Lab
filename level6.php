<?php
require_once 'session_helper.php';
$message = 'Request completed successfully.';
$search = isset($_GET['q']) ? $_GET['q'] : 'test';
$execution_time = 0;

if (!empty($search)) {
    $start_time = microtime(true);
    try {
        // VULNERABLE: Time-based blind SQLi using heavy SQLite subqueries or randomblob processing if true
        $query = "SELECT * FROM products WHERE name LIKE '%" . $search . "%'";
        $stmt = $pdo->query($query);
        $stmt->fetchAll();
    } catch (PDOException $e) {
        // Suppress errors
    }
    $end_time = microtime(true);
    $execution_time = round(($end_time - $start_time) * 1000, 2); // in milliseconds
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 6: High | SQLi Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 650px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #ef4444; }
        h1 { color: #ef4444; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #ef4444; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #dc2626; }
        a.back { color: #94a3b8; text-decoration: none; display: inline-block; margin-top: 20px; }
        a.back:hover { color: #f1f5f9; }
        .result-box { background: #0f172a; border-radius: 8px; padding: 15px; border: 1px dashed #334155; margin-top: 15px; font-size: 1.1rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Level 6: High — Blind Time-Based SQLi</h1>

        <p>When an application behaves identically regardless of query results and hides errors, **Time-Based Blind SQL Injection** is used. By injecting database-heavy processing delays (such as SQLite's <code>randomblob()</code> multiplication) inside conditional statements, you can measure response time to infer data.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$search = $_GET['q'];
// VULNERABLE: Application returns static output regardless of query results
$query = "SELECT * FROM products WHERE name LIKE '%" . $search . "%'";
$pdo->query($query);
echo "Request completed successfully.";
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Trigger a time delay when a condition is met. In SQLite, heavy subqueries like <code>OR (SELECT CASE WHEN (1=1) THEN randomblob(50000000) ELSE 0 END)</code> will pause execution.<br>
        <em>Payload example:</em> <code>test' OR (SELECT CASE WHEN (SUBSTR(password,1,1)='a') THEN randomblob(30000000) ELSE 0 END FROM users WHERE username='admin') --</code></p>

        <form action="level6.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <label for="q">Search Parameter:</label><br>
            <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Search with Timing" class="btn">
        </form>

        <?php if (!empty($search)): ?>
            <div class="result-box">
                <div>Status: <?php echo $message ?? 'Request completed.'; ?></div>
                <div style="margin-top: 10px; font-size: 0.9rem; color: #38bdf8;">Server Response Time: <strong><?php echo $execution_time; ?> ms</strong></div>
                
                <?php if ($execution_time > 100 || strpos($search, 'randomblob') !== false): ?>
                    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; padding: 10px; border-radius: 6px; color: #f87171; margin-top: 15px; font-weight: bold; text-align: center;">
                        🎉 SUCCESS: Server time delay successfully measured via time-based SQLi!
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
