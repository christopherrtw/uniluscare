<?php
require_once __DIR__.'/../../config/db.php';
requireRole('lab');
$PAGE_TITLE='Dashboard'; $ACTIVE_NAV='dashboard';
$pending = $conn->query("SELECT COUNT(*) c FROM lab_tests WHERE status='requested'")->fetch_assoc()['c'];
$inProg = $conn->query("SELECT COUNT(*) c FROM lab_tests WHERE status IN ('sample_collected','in_progress')")->fetch_assoc()['c'];
$done = $conn->query("SELECT COUNT(*) c FROM lab_tests WHERE status='completed'")->fetch_assoc()['c'];
include __DIR__.'/../../includes/layout.php';
?>
<div class="stats-grid mb-3">
    <div class="stat-card danger"><div class="label">Pending Requests</div><div class="value"><?=$pending?></div></div>
    <div class="stat-card accent"><div class="label">In Progress</div><div class="value"><?=$inProg?></div></div>
    <div class="stat-card success"><div class="label">Completed</div><div class="value"><?=$done?></div></div>
</div>
<div class="card">
    <div class="card-title">🚀 Quick Actions</div>
    <a href="<?=BASE_URL?>/pages/lab/tests.php" class="btn btn-primary">🧪 View Test Requests</a>
    <a href="<?=BASE_URL?>/pages/lab/results.php" class="btn btn-primary">📝 Submit Results</a>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
