<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'Telemedicine'; $ACTIVE_NAV = 'telemedicine';
$did = $_SESSION['user_id'];
$vc = $conn->query("SELECT a.*, p.full_name AS pname FROM appointments a LEFT JOIN patients p ON a.patient_id=p.patient_id WHERE a.doctor_id='$did' AND a.type='virtual' ORDER BY a.appointment_date DESC, a.appointment_time DESC");
include __DIR__ . '/../../includes/layout.php';
?>
<div class="card">
    <div class="card-title">📹 Your Virtual Consultations</div>
    <?php if ($vc->num_rows > 0): ?>
    <table class="data-table">
        <tr><th>Date</th><th>Time</th><th>Patient</th><th>Status</th><th>Action</th></tr>
        <?php while ($v = $vc->fetch_assoc()):
            $room = $v['room_id'] ?: 'uniluscare-' . $v['appointment_id']; ?>
            <tr>
                <td><?= date('d M Y', strtotime($v['appointment_date'])) ?></td>
                <td><?= date('H:i', strtotime($v['appointment_time'])) ?></td>
                <td><?= e($v['pname']) ?><br><small class="text-muted"><?= e($v['patient_id']) ?></small></td>
                <td><span class="badge badge-<?= e($v['status']) ?>"><?= e($v['status']) ?></span></td>
                <td><a href="<?= BASE_URL ?>/pages/doctor/video_call.php?room=<?= e($room) ?>&appt=<?= (int)$v['appointment_id'] ?>" class="btn btn-sm btn-primary">📹 Join</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?><p class="text-muted">No virtual consultations scheduled.</p><?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
