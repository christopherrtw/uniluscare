<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('patient');
$PAGE_TITLE = 'Telemedicine';
$ACTIVE_NAV = 'telemedicine';
$pid = $_SESSION['user_id'];

// Virtual appointments
$virtuals = $conn->query("SELECT a.*, d.full_name AS doctor_name, d.department FROM appointments a LEFT JOIN doctors d ON a.doctor_id=d.doctor_id WHERE a.patient_id='$pid' AND a.type='virtual' ORDER BY a.appointment_date DESC, a.appointment_time DESC");
$docs = $conn->query("SELECT * FROM doctors ORDER BY full_name");
$flash = $_GET['flash'] ?? '';
include __DIR__ . '/../../includes/layout.php';
?>

<?php if ($flash): ?><div class="alert alert-success"><?= e($flash) ?></div><?php endif; ?>

<div class="card mb-3">
    <div class="card-title">📹 Virtual Consultations</div>
    <p class="text-muted mb-2">Connect with your doctor remotely. Click "Join Call" to enter the consultation room.</p>

    <?php if ($virtuals->num_rows > 0): ?>
        <table class="data-table">
            <tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th><th>Action</th></tr>
            <?php while ($v = $virtuals->fetch_assoc()):
                $room = $v['room_id'] ?: 'uniluscare-' . $v['appointment_id'];
            ?>
                <tr>
                    <td><?= date('d M Y', strtotime($v['appointment_date'])) ?></td>
                    <td><?= date('H:i', strtotime($v['appointment_time'])) ?></td>
                    <td><?= e($v['doctor_name']) ?></td>
                    <td><span class="badge badge-<?= e($v['status']) ?>"><?= e($v['status']) ?></span></td>
                    <td>
                        <?php if ($v['status'] !== 'cancelled' && $v['status'] !== 'completed'): ?>
                            <a href="<?= BASE_URL ?>/pages/patient/video_call.php?room=<?= e($room) ?>&appt=<?= (int)$v['appointment_id'] ?>" class="btn btn-sm btn-primary">📞 Join Call</a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="text-muted">No virtual consultations booked.</p>
    <?php endif; ?>
</div>

<div class="card mb-3">
    <div class="card-title">📅 Book a Virtual Consultation</div>
    <form method="POST" action="<?= BASE_URL ?>/api/patient_actions.php">
        <input type="hidden" name="action" value="book_appointment">
        <input type="hidden" name="type" value="virtual">
        <div class="form-row">
            <div class="form-group">
                <label>Doctor</label>
                <select name="doctor_id" required>
                    <option value="">-- Select Doctor --</option>
                    <?php while ($d = $docs->fetch_assoc()): ?>
                        <option value="<?= e($d['doctor_id']) ?>"><?= e($d['full_name']) ?> — <?= e($d['department']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="appointment_date" required min="<?= date('Y-m-d') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Time</label>
                <input type="time" name="appointment_time" required>
            </div>
            <div class="form-group">
                <label>Reason</label>
                <input type="text" name="reason" required>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Book Virtual Consultation</button>
    </form>
</div>

<div class="card">
    <div class="card-title">⌚ Remote Monitoring</div>
    <p class="text-muted mb-2">Connect your wearable device for continuous vital monitoring.</p>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Heart Rate (live)</div>
            <div class="value" id="rmHR">72</div>
            <div class="text-muted text-sm">bpm — simulated</div>
        </div>
        <div class="stat-card accent">
            <div class="label">Steps Today</div>
            <div class="value" id="rmSteps">4,238</div>
        </div>
        <div class="stat-card success">
            <div class="label">SpO₂</div>
            <div class="value" id="rmSpO2">98%</div>
        </div>
        <div class="stat-card">
            <div class="label">Sleep (hrs)</div>
            <div class="value">7.2</div>
        </div>
    </div>
    <p class="text-sm text-muted mt-2">⚠️ Alerts will be automatically sent to your doctor if abnormal readings are detected.</p>
</div>

<script>
setInterval(() => {
    document.getElementById('rmHR').textContent = 68 + Math.floor(Math.random() * 10);
    document.getElementById('rmSteps').textContent = (4238 + Math.floor(Math.random() * 20)).toLocaleString();
    document.getElementById('rmSpO2').textContent = (97 + Math.floor(Math.random() * 2)) + '%';
}, 2500);
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
