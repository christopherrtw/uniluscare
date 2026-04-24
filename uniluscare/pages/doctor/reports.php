<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'Reports'; $ACTIVE_NAV = 'reports';
$did = $_SESSION['user_id'];
$totalAppts = $conn->query("SELECT COUNT(*) c FROM appointments WHERE doctor_id='$did'")->fetch_assoc()['c'];
$totalRx = $conn->query("SELECT COUNT(*) c FROM prescriptions WHERE doctor_id='$did'")->fetch_assoc()['c'];
$totalLabs = $conn->query("SELECT COUNT(*) c FROM lab_tests WHERE doctor_id='$did'")->fetch_assoc()['c'];
$totalRecords = $conn->query("SELECT COUNT(*) c FROM medical_records WHERE doctor_id='$did'")->fetch_assoc()['c'];
include __DIR__ . '/../../includes/layout.php';
?>
<div class="stats-grid mb-3">
    <div class="stat-card"><div class="label">Total Appointments</div><div class="value"><?= $totalAppts ?></div></div>
    <div class="stat-card accent"><div class="label">Prescriptions Written</div><div class="value"><?= $totalRx ?></div></div>
    <div class="stat-card success"><div class="label">Lab Tests Ordered</div><div class="value"><?= $totalLabs ?></div></div>
    <div class="stat-card"><div class="label">Medical Records</div><div class="value"><?= $totalRecords ?></div></div>
</div>
<div class="card">
    <div class="card-title">📈 Clinical Activity Report</div>
    <p>This dashboard summarises your clinical work on UnilusCare. For detailed exports, contact the administrator.</p>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
