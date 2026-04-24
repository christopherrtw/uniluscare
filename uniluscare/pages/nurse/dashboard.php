<?php
require_once __DIR__.'/../../config/db.php';
requireRole('nurse');
$PAGE_TITLE='Dashboard'; $ACTIVE_NAV='dashboard';
$nid = $_SESSION['user_id'];
$recentVitals = $conn->query("SELECT COUNT(*) c FROM vitals WHERE recorded_by='$nid'")->fetch_assoc()['c'];
$todayAdmin = $conn->query("SELECT COUNT(*) c FROM medication_administration WHERE nurse_id='$nid' AND DATE(administered_at)=CURDATE()")->fetch_assoc()['c'];
$patients = $conn->query("SELECT patient_id,full_name FROM patients ORDER BY full_name LIMIT 10");
include __DIR__.'/../../includes/layout.php';
?>
<div class="stats-grid mb-3">
    <div class="stat-card"><div class="label">Vitals Recorded</div><div class="value"><?=$recentVitals?></div></div>
    <div class="stat-card accent"><div class="label">Meds Administered Today</div><div class="value"><?=$todayAdmin?></div></div>
</div>
<div class="card">
    <div class="card-title">🚀 Quick Actions</div>
    <div style="display:flex;gap:0.7rem;flex-wrap:wrap;">
        <a href="<?=BASE_URL?>/pages/nurse/vitals.php" class="btn btn-primary">❤️ Record Vitals</a>
        <a href="<?=BASE_URL?>/pages/nurse/medication.php" class="btn btn-primary">💊 Administer Medication</a>
        <a href="<?=BASE_URL?>/pages/nurse/notes.php" class="btn btn-primary">📝 Nursing Notes</a>
    </div>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
