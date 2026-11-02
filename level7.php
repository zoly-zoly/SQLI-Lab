<?php
require_once 'session_helper.php';
$msg = '';
$error = '';
$search_results = [];

// Handle profile update (First order: stored safely)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $new_username = $_POST['new_username'];
    try {
        // First order insert/update using prepared statement (Safe!)
        $stmt = $pdo->prepare("UPDATE users SET username = :uname WHERE id = 1");
        $stmt->execute(['uname' => $new_username]);
        $msg = "Success: Profile username updated to " . htmlspecialchars($new_username) . " (Stored safely)!";
    } catch (PDOException $e) {
        $error = "Error updating profile: " . $e->getMessage();
    }
}

// Handle search (Second order: unsafely concatenated in secondary query)
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search_query)) {
    try {
        // VULNERABLE SECOND-ORDER QUERY: Takes stored user input and concatenates it unsafely!
        // If a user registered/updated their username to an injected payload (e.g. admin' --),
        // searching for their profile will trigger this secondary query injection.
        $sql = "SELECT * FROM users WHERE username = '$search_query'";
        $stmt = $pdo->query($sql);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "SQL Syntax Error in Secondary Query: " . $e->getMessage();
    }
}

$current_alice = $pdo->query("SELECT * FROM users WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Level 7: High | SQLi Lab</title>
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
        <h1>Level 7: High — Second-Order SQL Injection</h1>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <p>Second-order SQL injection occurs when user input is safely sanitized and stored in the database during an initial request (first-order), but is later retrieved and concatenated unsafely into a secondary query (second-order).</p>
        
        <h3>Source Code Snippet:</h3>
        <pre><code>&lt;?php
// Step 1 (Safe Insert):
$stmt = $pdo->prepare("UPDATE users SET username = :uname WHERE id = 1");
$stmt->execute(['uname' => $new_username]);

// Step 2 (Unsafe Secondary Query):
$sql = "SELECT * FROM users WHERE username = '$search_query'";
$results = $pdo->query($sql)->fetchAll();
?&gt;</code></pre>

        <h3>Your Goal:</h3>
        <p>1. Update your username to a payload like <code>admin' --</code> using the form below.<br>
        2. Search for your username to trigger the secondary query and bypass authentication or dump database records!</p>

        <!-- Form 1: Profile Update (First-order safe storage) -->
        <form action="level7.php" method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #334155;">
            <input type="hidden" name="action" value="update_profile">
            <label for="new_username">Update Profile Username:</label><br>
            <input type="text" id="new_username" name="new_username" value="<?php echo htmlspecialchars($current_alice['username']); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Save Profile Name" class="btn" style="background-color: #475569;">
        </form>

        <!-- Form 2: Profile Search (Second-order unsafe query execution) -->
        <form action="level7.php" method="GET" style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed #334155;">
            <label for="search">Search User Profile (Triggers Secondary Query):</label><br>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: white;" required><br>
            <input type="submit" value="Search Profile" class="btn">
        </form>

        <?php if (!empty($search_results)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($search_results as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['id']); ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><strong style="color: #38bdf8;"><?php echo htmlspecialchars($u['role']); ?></strong></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (strpos($current_alice['username'], '--') !== false): ?>
                <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; padding: 10px; border-radius: 6px; color: #34d399; margin-top: 15px; font-weight: bold; text-align: center;">
                    🎉 SUCCESS: Second-order SQL injection successfully triggered!
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <br>
        <a href="index.php" class="back">⬅️ Back to Dashboard</a>
    </div>
</body>
</html>
