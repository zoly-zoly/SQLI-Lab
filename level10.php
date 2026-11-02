<?php
require_once 'session_helper.php';
$msg = '';
$error = '';
$results = [];

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    try {
        // VULNERABLE: Final Boss Enterprise Search Portal (Multiple SQLi vectors)
        $query = "SELECT owner, flag FROM secrets WHERE owner LIKE '%$search%'";
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
    <title>Level 10: Critical | SQLi Lab</title>
    <style>
        body { font-family: sans-serif; background-color: #0f172a; color: #f1f5f9; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        .box { background: #1e293b; padding: 30px; border-radius: 12px; max-width: 650px; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.3); border-top: 5px solid #8b5cf6; }
        h1 { color: #8b5cf6; margin-top: 0; }
        code { background: #0f172a; padding: 2px 6px; border-radius: 4px; color: #f43f5e; font-family: monospace; }
        pre { background: #0f172a; padding: 15px; border-radius: 6px; overflow-x: auto; border: 1px solid #334155; }
        .btn { display: inline-block; background-color: #8b5cf6; color: white; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 15px; border: none; cursor: pointer; }
        .btn:hover { background-color: #7c3aed; }
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
        <h1>Level 10: Critical — SQLi to Full Database Compromise</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <p>This is the **Final Boss** scenario. An enterprise secret vault search portal allows querying high-privilege administrative flags and system configurations. Because the search parameter is concatenated directly into the SQL query without parameterization, an attacker can extract the master administrator flag for complete database compromise.</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
$search = $_POST['search'];
// VULNERABLE: Direct string concatenation in enterprise secret search
$query = "SELECT owner, flag FROM secrets WHERE owner LIKE '%$search%'";
$results = $pdo->query($query)->fetchAll();
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>Extract the master administrator flag from the `secrets` table. Use a UNION payload or boolean injection to dump all records: <code>' UNION SELECT owner, flag FROM secrets --</code></p>

        <form action="level10.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <label for="search">Secret Vault Search Query:</label><br>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Search Secret Vault" class="btn">
        </form>

        <?php if (!empty($results)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Owner</th>
                        <th>Flag / Secret Record</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['owner']); ?></td>
                            <td style="color: #38bdf8; font-family: monospace;"><?php echo htmlspecialchars($row['flag']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (strpos($search, 'UNION') !== false || strpos($search, 'secrets') !== false): ?>
                <div style="background: rgba(139, 92, 246, 0.15); border: 1px solid #8b5cf6; padding: 12px; border-radius: 6px; color: #a78bfa; margin-top: 20px; font-weight: bold; text-align: center;">
                    🎉 FINAL BOSS DEFEATED: You successfully extracted the master admin flag and achieved full database compromise!
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
