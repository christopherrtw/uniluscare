<?php
require_once __DIR__.'/../../config/db.php';
requireRole('radiologist');
$PAGE_TITLE='Dashboard'; $ACTIVE_NAV='dashboard';
$pending = $conn->query("SELECT COUNT(*) c FROM imaging_reports WHERE status='pending'")->fetch_assoc()['c'];
$done = $conn->query("SELECT COUNT(*) c FROM imaging_reports WHERE status='completed'")->fetch_assoc()['c'];
include __DIR__.'/../../includes/layout.php';
?>
<div class="stats-grid mb-3">
    <div class="stat-card accent"><div class="label">Pending Reports</div><div class="value"><?=$pending?></div></div>
    <div class="stat-card success"><div class="label">Completed</div><div class="value"><?=$done?></div></div>
</div>
<div class="card">
    <div class="card-title">🚀 Actions</div>
    <a href="<?=BASE_URL?>/pages/radiologist/imaging.php" class="btn btn-primary">🩻 View Imaging Queue</a>
    <a href="<?=BASE_URL?>/pages/radiologist/upload.php" class="btn btn-primary">⬆️ Upload New Report</a>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
