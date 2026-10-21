<?php
session_start();
if (!isset($_SESSION["admin_id"])) { header("Location: admin_login.php"); exit(); }
require_once '../includes/db_connect.php';

// 1. Pagination Setup
$limit = 10; // Constant page size (K)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

try {
    // 2. Optimized Query: O(K) complexity
    // Only fetch the slice of data needed for the current view.
    $stmt = $pdo->prepare("
        SELECT r.*, 
               u.first_name as reporter_fn, u.last_name as reporter_ln,
               reported_u.first_name as reported_fn, reported_u.last_name as reported_ln,
               l.title as listing_title
        FROM flagged_reports r
        JOIN users u ON r.reporter_id = u.user_id
        LEFT JOIN users reported_u ON r.reported_user_id = reported_u.user_id
        LEFT JOIN listings l ON r.listing_id = l.listing_id
        WHERE r.resolution_status = 'open'
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Get total count for pagination links
    $countStmt = $pdo->query("SELECT COUNT(*) FROM flagged_reports WHERE resolution_status = 'open'");
    $totalReports = $countStmt->fetchColumn();
    $totalPages = ceil($totalReports / $limit);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusDrop | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root { --admin-accent: #d32f2f; --admin-bg: #fcfcfc; }
        body { background-color: var(--admin-bg); font-family: sans-serif; }
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .admin-header { 
            display: flex; justify-content: space-between; align-items: center; 
            border-bottom: 3px solid var(--admin-accent); margin-bottom: 30px; padding-bottom: 10px;
        }
        .stats-grid { 
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; margin-bottom: 40px; 
        }
        .stat-card { 
            background: white; padding: 20px; border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid var(--admin-accent);
        }
        .stat-card h3 { font-size: 0.9rem; color: #666; text-transform: uppercase; }
        .stat-card p { font-size: 2rem; font-weight: bold; margin: 10px 0; color: #333; }
        .admin-section { 
            background: white; padding: 20px; border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 30px; 
        }
        .section-title { font-size: 1.5rem; margin-bottom: 20px; color: #333; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .admin-table th, .admin-table td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        .admin-table th { background-color: #f8f9fa; color: #555; }
        .status-pill { padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; }
        .status-open { background: #ffebee; color: #c62828; }
        .btn-action { padding: 6px 12px; border-radius: 4px; cursor: pointer; border: none; font-size: 0.85rem; }
        .btn-danger { background: var(--admin-accent); color: white; }
        .btn-success { background: #2e7d32; color: white; }
        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 20px; }
        .pagination a { padding: 8px 16px; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 4px; }
        .pagination a.active { background: var(--admin-accent); color: white; border-color: var(--admin-accent); }
    </style>
</head>
<body>

    <div class="admin-container">
        <header class="admin-header">
            <h1>🛡️ CampusDrop Administration</h1>
            <a href="index.php" class="btn-action" style="background: #eee; color: #333; text-decoration: none;">← Exit to Marketplace</a>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Open Reports</h3>
                <p style="color: var(--admin-accent);"><?php echo $totalReports; ?></p>
            </div>
            <div class="stat-card">
                <h3>Current Page</h3>
                <p><?php echo $page; ?> / <?php echo $totalPages; ?></p>
            </div>
        </div>

        <section class="admin-section">
            <div class="section-title">🚩 Moderation Queue</div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Reason</th>
                        <th>Target</th>
                        <th>Reporter</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr><td colspan="5" style="text-align:center;">No open reports found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td>#<?php echo $report['report_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($report['reason']); ?></strong></td>
                                <td>
                                    <?php if ($report['listing_id']): ?>
                                        Listing: <?php echo htmlspecialchars($report['listing_title']); ?>
                                    <?php elseif ($report['reported_user_id']): ?>
                                        User: <?php echo htmlspecialchars($report['reported_fn'] . ' ' . $report['reported_ln']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($report['reporter_fn'] . ' ' . $report['reporter_ln']); ?></td>
                                <td>
                                    <button class="btn-action btn-danger">Remove</button>
                                    <button class="btn-action btn-success">Dismiss</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </section>
    </div>

</body>
</html>
