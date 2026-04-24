<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'Dashboard';
$ACTIVE_NAV = 'dashboard';
$did = $_SESSION['user_id'];

$today = $conn->query("SELECT a.*, p.full_name AS patient_name, p.patient_id FROM appointments a LEFT JOIN patients p ON a.patient_id=p.patient_id WHERE a.doctor_id='$did' AND a.appointment_date=CURDATE() ORDER BY a.appointment_time");
$pending = $conn->query("SELECT COUNT(*) c FROM appointments WHERE doctor_id='$did' AND status='pending'")->fetch_assoc()['c'];
$labs = $conn->query("SELECT COUNT(*) c FROM lab_tests WHERE doctor_id='$did' AND status='completed'")->fetch_assoc()['c'];
$totalPatients = $conn->query("SELECT COUNT(DISTINCT patient_id) c FROM appointments WHERE doctor_id='$did'")->fetch_assoc()['c'];

include __DIR__ . '/../../includes/layout.php';
?>

<div class="stats-grid mb-3">
    <div class="stat-card"><div class="label">Today's Appointments</div><div class="value"><?= $today->num_rows ?></div></div>
    <div class="stat-card accent"><div class="label">Pending Confirmations</div><div class="value"><?= $pending ?></div></div>
    <div class="stat-card success"><div class="label">Lab Results Ready</div><div class="value"><?= $labs ?></div></div>
    <div class="stat-card"><div class="label">Total Patients Seen</div><div class="value"><?= $totalPatients ?></div></div>
</div>

<div class="card mb-3">
    <div class="card-title">📅 Today's Schedule — <?= date('l, j F Y') ?></div>
    <?php if ($today->num_rows > 0): ?>
        <table class="data-table">
            <tr><th>Time</th><th>Patient</th><th>Reason</th><th>Type</th><th>Status</th><th>Actions</th></tr>
            <?php while ($a = $today->fetch_assoc()): ?>
                <tr>
                    <td><?= date('H:i', strtotime($a['appointment_time'])) ?></td>
                    <td><strong><?= e($a['patient_name']) ?></strong><br><small class="text-muted"><?= e($a['patient_id']) ?></small></td>
                    <td><?= e($a['reason']) ?></td>
                    <td><?= e($a['type']) ?></td>
                    <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                    <td>
                        <a href="<?= BASE_URL ?>/pages/doctor/patient_view.php?id=<?= e($a['patient_id']) ?>" class="btn btn-sm btn-outline">📋 View</a>
                        <?php if ($a['status']==='pending'): ?>
                            <a href="<?= BASE_URL ?>/api/doctor_actions.php?action=confirm_appt&id=<?= (int)$a['appointment_id'] ?>" class="btn btn-sm btn-primary">✓ Confirm</a>
                        <?php endif; ?>
                        <?php if ($a['type']==='virtual'): ?>
                            <a href="<?= BASE_URL ?>/pages/doctor/video_call.php?room=<?= e($a['room_id']) ?>&appt=<?= (int)$a['appointment_id'] ?>" class="btn btn-sm btn-accent">📹 Join</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?><p class="text-muted">No appointments scheduled for today.</p><?php endif; ?>
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-title">🔍 Quick Patient Search</div>
        <form action="<?= BASE_URL ?>/pages/doctor/patient_view.php" method="GET">
            <div class="form-group">
                <label>Enter Patient ID</label>
                <input type="text" name="id" placeholder="e.g. P000001" required>
            </div>
            <button class="btn btn-primary">🔎 Open Record</button>
        </form>
    </div>
    <div class="card">
        <div class="card-title">📊 Activity Summary</div>
        <p>Department: <strong><?= e($_SESSION['department'] ?? '') ?></strong></p>
        <p>Doctor ID: <strong><?= e($did) ?></strong></p>
        <p class="text-muted text-sm mt-2">Use the navigation on the left to access laboratory, pharmacy, imaging, and reports.</p>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
