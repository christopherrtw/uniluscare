<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('patient');

$PAGE_TITLE = 'Dashboard';
$ACTIVE_NAV = 'dashboard';
$pid = $_SESSION['user_id'];

// Latest vitals
$vitals = $conn->query("SELECT * FROM vitals WHERE patient_id='$pid' ORDER BY recorded_at DESC LIMIT 1")->fetch_assoc();

// Notifications
$notifs = $conn->query("SELECT * FROM notifications WHERE recipient_id='$pid' AND recipient_role='patient' ORDER BY created_at DESC LIMIT 10");

// Upcoming appointments
$appts = $conn->query("SELECT a.*, d.full_name AS doctor_name, d.department
    FROM appointments a LEFT JOIN doctors d ON a.doctor_id=d.doctor_id
    WHERE a.patient_id='$pid' AND a.appointment_date >= CURDATE()
    ORDER BY a.appointment_date, a.appointment_time LIMIT 5");

// All doctors for booking
$docs = $conn->query("SELECT * FROM doctors ORDER BY full_name");

$flash = $_GET['flash'] ?? '';

include __DIR__ . '/../../includes/layout.php';
?>

<?php if ($flash): ?>
    <div class="alert alert-success"><?= e($flash) ?></div>
<?php endif; ?>

<!-- Vitals Monitor -->
<div class="card mb-3">
    <div class="card-title">❤️ Vitals Monitor <span style="font-size:0.75rem; font-weight:400; color:var(--text-muted);">— Real-time</span></div>
    <?php if ($vitals): ?>
        <div class="stats-grid" style="margin-bottom:0;">
            <div class="stat-card">
                <div class="label">Blood Pressure</div>
                <div class="value" id="bpVal"><?= $vitals['bp_systolic'] ?>/<?= $vitals['bp_diastolic'] ?></div>
                <div class="text-muted text-sm">mmHg</div>
            </div>
            <div class="stat-card accent">
                <div class="label">Heart Rate</div>
                <div class="value" id="hrVal"><?= $vitals['heart_rate'] ?></div>
                <div class="text-muted text-sm">bpm</div>
            </div>
            <div class="stat-card success">
                <div class="label">Temperature</div>
                <div class="value" id="tempVal"><?= $vitals['temperature'] ?></div>
                <div class="text-muted text-sm">°C</div>
            </div>
            <div class="stat-card">
                <div class="label">Oxygen Saturation</div>
                <div class="value" id="spo2Val"><?= $vitals['oxygen_saturation'] ?>%</div>
                <div class="text-muted text-sm">SpO₂</div>
            </div>
            <div class="stat-card">
                <div class="label">Respiratory Rate</div>
                <div class="value"><?= $vitals['respiratory_rate'] ?></div>
                <div class="text-muted text-sm">breaths/min</div>
            </div>
        </div>
        <p class="text-muted text-sm mt-2">Last recorded: <?= date('j F Y H:i', strtotime($vitals['recorded_at'])) ?></p>
    <?php else: ?>
        <p class="text-muted">No vitals recorded yet. Your vitals will appear here after your first visit.</p>
    <?php endif; ?>
</div>

<div class="content-grid">
    <!-- Upcoming Appointments -->
    <div class="card">
        <div class="card-title">📅 Upcoming Appointments</div>
        <?php if ($appts->num_rows > 0): ?>
            <table class="data-table">
                <tr><th>Date</th><th>Time</th><th>Doctor</th><th>Reason</th><th>Status</th></tr>
                <?php while ($a = $appts->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($a['appointment_date'])) ?></td>
                        <td><?= date('H:i', strtotime($a['appointment_time'])) ?></td>
                        <td><?= e($a['doctor_name'] ?? '—') ?><br><small class="text-muted"><?= e($a['department'] ?? '') ?></small></td>
                        <td><?= e($a['reason']) ?></td>
                        <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p class="text-muted">No upcoming appointments.</p>
        <?php endif; ?>

        <h3 class="mt-3 mb-2" style="font-size:1.05rem;">Book a New Appointment</h3>
        <form method="POST" action="<?= BASE_URL ?>/api/patient_actions.php">
            <input type="hidden" name="action" value="book_appointment">
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
                    <label>Type</label>
                    <select name="type">
                        <option value="physical">In-person</option>
                        <option value="virtual">Virtual Consultation</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="appointment_date" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Time</label>
                    <input type="time" name="appointment_time" required>
                </div>
            </div>
            <div class="form-group">
                <label>Reason</label>
                <input type="text" name="reason" required>
            </div>
            <button class="btn btn-primary" type="submit">Book Appointment</button>
        </form>
    </div>

    <!-- Notifications -->
    <div class="card">
        <div class="card-title">🔔 Notifications</div>
        <?php if ($notifs->num_rows > 0): ?>
            <?php while ($n = $notifs->fetch_assoc()): ?>
                <div style="padding:0.8rem 0; border-bottom:1px solid var(--border);">
                    <div style="font-weight:600; font-size:0.92rem;"><?= e($n['title']) ?>
                        <span class="badge badge-info" style="font-size:0.65rem;"><?= e($n['type']) ?></span>
                    </div>
                    <div class="text-sm text-muted"><?= e($n['message']) ?></div>
                    <div class="text-sm text-muted" style="font-size:0.75rem; margin-top:0.25rem;">
                        <?= date('d M H:i', strtotime($n['created_at'])) ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted">No notifications yet.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Simulate "real-time" vitals - small fluctuations for a live feel
setInterval(() => {
    const bp = document.getElementById('bpVal');
    const hr = document.getElementById('hrVal');
    const t = document.getElementById('tempVal');
    const o = document.getElementById('spo2Val');
    if (hr) hr.textContent = <?= (int)($vitals['heart_rate'] ?? 72) ?> + Math.floor(Math.random() * 5 - 2);
    if (o) o.textContent = (<?= (int)($vitals['oxygen_saturation'] ?? 98) ?> + Math.floor(Math.random() * 2 - 1)) + '%';
}, 3500);
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
