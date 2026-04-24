<?php
require_once __DIR__.'/../../config/db.php';
requireRole('triage');
$PAGE_TITLE='Dashboard'; $ACTIVE_NAV='dashboard';
$red = $conn->query("SELECT COUNT(*) c FROM triage_cases WHERE priority='red' AND status='active'")->fetch_assoc()['c'];
$yellow = $conn->query("SELECT COUNT(*) c FROM triage_cases WHERE priority='yellow' AND status='active'")->fetch_assoc()['c'];
$green = $conn->query("SELECT COUNT(*) c FROM triage_cases WHERE priority='green' AND status='active'")->fetch_assoc()['c'];
include __DIR__.'/../../includes/layout.php';
?>
<div class="stats-grid mb-3">
    <div class="stat-card danger"><div class="label">🔴 Red (Critical)</div><div class="value"><?=$red?></div></div>
    <div class="stat-card accent"><div class="label">🟡 Yellow (Urgent)</div><div class="value"><?=$yellow?></div></div>
    <div class="stat-card success"><div class="label">🟢 Green (Non-urgent)</div><div class="value"><?=$green?></div></div>
</div>
<div class="card">
    <div class="card-title">🚀 Quick Actions</div>
    <a href="<?=BASE_URL?>/pages/triage/register.php" class="btn btn-danger">🚑 Rapid ER Registration</a>
    <a href="<?=BASE_URL?>/pages/triage/cases.php" class="btn btn-primary">📋 Active Cases</a>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
