<?php
require_once __DIR__.'/../../config/db.php';
requireRole('receptionist');
$PAGE_TITLE='Dashboard'; $ACTIVE_NAV='dashboard';
$todayAppts = $conn->query("SELECT COUNT(*) c FROM appointments WHERE appointment_date=CURDATE()")->fetch_assoc()['c'];
$newPatients = $conn->query("SELECT COUNT(*) c FROM patients WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$pending = $conn->query("SELECT COUNT(*) c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
$unpaid = $conn->query("SELECT COUNT(*) c FROM invoices WHERE payment_status='unpaid'")->fetch_assoc()['c'];

$upcoming = $conn->query("SELECT a.*, p.full_name AS pname, d.full_name AS dname FROM appointments a LEFT JOIN patients p ON a.patient_id=p.patient_id LEFT JOIN doctors d ON a.doctor_id=d.doctor_id WHERE a.appointment_date=CURDATE() ORDER BY a.appointment_time");
include __DIR__.'/../../includes/layout.php';
?>
<div class="stats-grid mb-3">
    <div class="stat-card"><div class="label">Today's Appointments</div><div class="value"><?=$todayAppts?></div></div>
    <div class="stat-card accent"><div class="label">New Patients Today</div><div class="value"><?=$newPatients?></div></div>
    <div class="stat-card"><div class="label">Pending Appointments</div><div class="value"><?=$pending?></div></div>
    <div class="stat-card danger"><div class="label">Unpaid Invoices</div><div class="value"><?=$unpaid?></div></div>
</div>
<div class="card mb-3">
    <div class="card-title">🚀 Quick Actions</div>
    <div style="display:flex;gap:0.7rem;flex-wrap:wrap;">
        <a href="<?=BASE_URL?>/pages/receptionist/register.php" class="btn btn-primary">🆕 Register New Patient</a>
        <a href="<?=BASE_URL?>/pages/receptionist/appointments.php" class="btn btn-primary">📅 Manage Appointments</a>
        <a href="<?=BASE_URL?>/pages/receptionist/billing.php" class="btn btn-primary">💳 Generate Invoice</a>
        <a href="<?=BASE_URL?>/pages/receptionist/checkin.php" class="btn btn-outline">🚪 Check-in/out</a>
    </div>
</div>
<div class="card">
    <div class="card-title">📅 Today's Schedule</div>
    <?php if ($upcoming->num_rows > 0):?>
        <table class="data-table">
            <tr><th>Time</th><th>Patient</th><th>Doctor</th><th>Reason</th><th>Status</th></tr>
            <?php while($a=$upcoming->fetch_assoc()):?>
                <tr><td><?=date('H:i',strtotime($a['appointment_time']))?></td>
                <td><?=e($a['pname'])?></td>
                <td><?=e($a['dname']?:'—')?></td>
                <td><?=e($a['reason'])?></td>
                <td><span class="badge badge-<?=e($a['status'])?>"><?=e($a['status'])?></span></td></tr>
            <?php endwhile;?>
        </table>
    <?php else:?><p class="text-muted">No appointments today.</p><?php endif;?>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
