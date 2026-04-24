<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('patient');
$PAGE_TITLE = 'Personal Records';
$ACTIVE_NAV = 'records';
$pid = $_SESSION['user_id'];

$patient = $conn->query("SELECT * FROM patients WHERE patient_id='$pid'")->fetch_assoc();
$records = $conn->query("SELECT m.*, d.full_name AS doctor_name FROM medical_records m LEFT JOIN doctors d ON m.doctor_id=d.doctor_id WHERE m.patient_id='$pid' ORDER BY m.visit_date DESC");
$labs    = $conn->query("SELECT * FROM lab_tests WHERE patient_id='$pid' ORDER BY requested_at DESC");
$imgs    = $conn->query("SELECT * FROM imaging_reports WHERE patient_id='$pid' ORDER BY created_at DESC");

include __DIR__ . '/../../includes/layout.php';
?>

<div class="card mb-3">
    <div class="card-title">👤 Patient Profile</div>
    <div class="form-row">
        <div><strong>Patient ID:</strong> <?= e($patient['patient_id']) ?></div>
        <div><strong>ID Number:</strong> <?= e($patient['id_number']) ?> (<?= e($patient['id_type']) ?>)</div>
        <div><strong>Name:</strong> <?= e($patient['suffix']) ?> <?= e($patient['full_name']) ?></div>
        <div><strong>DOB:</strong> <?= e($patient['date_of_birth']) ?></div>
        <div><strong>Phone:</strong> <?= e($patient['phone']) ?></div>
        <div><strong>Email:</strong> <?= e($patient['email']) ?></div>
        <div><strong>Address:</strong> <?= e($patient['address']) ?></div>
        <div><strong>Insurance:</strong> <?= e($patient['insurance_company'] ?: 'None') ?></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-title">📋 Medical History</div>
    <?php if ($records->num_rows > 0): ?>
        <table class="data-table">
            <tr><th>Date</th><th>Doctor</th><th>Diagnosis</th><th>Treatment</th></tr>
            <?php while ($r = $records->fetch_assoc()): ?>
                <tr>
                    <td><?= e($r['visit_date']) ?></td>
                    <td><?= e($r['doctor_name']) ?></td>
                    <td><?= e($r['diagnosis']) ?></td>
                    <td><?= e($r['treatment']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?><p class="text-muted">No medical records yet.</p><?php endif; ?>
</div>

<div class="card mb-3">
    <div class="card-title">🧪 Lab Results</div>
    <?php if ($labs->num_rows > 0): ?>
        <table class="data-table">
            <tr><th>Test</th><th>Status</th><th>Result</th><th>Date</th><th>Report</th></tr>
            <?php while ($l = $labs->fetch_assoc()): ?>
                <tr>
                    <td><?= e($l['test_name']) ?></td>
                    <td><span class="badge badge-<?= $l['status']==='completed'?'completed':'pending' ?>"><?= e($l['status']) ?></span></td>
                    <td><?= e(substr($l['results'] ?? '—', 0, 80)) ?></td>
                    <td><?= date('d M Y', strtotime($l['requested_at'])) ?></td>
                    <td><?php if ($l['result_file']): ?><a href="<?= BASE_URL ?>/assets/uploads/lab/<?= e($l['result_file']) ?>" target="_blank" class="btn btn-sm btn-outline">View</a><?php endif; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?><p class="text-muted">No lab results yet.</p><?php endif; ?>
</div>

<div class="card">
    <div class="card-title">🩻 Imaging Reports</div>
    <?php if ($imgs->num_rows > 0): ?>
        <table class="data-table">
            <tr><th>Type</th><th>Body Part</th><th>Findings</th><th>Date</th><th>Image</th></tr>
            <?php while ($im = $imgs->fetch_assoc()): ?>
                <tr>
                    <td><?= e($im['image_type']) ?></td>
                    <td><?= e($im['body_part']) ?></td>
                    <td><?= e(substr($im['findings'] ?? '—', 0, 100)) ?></td>
                    <td><?= date('d M Y', strtotime($im['created_at'])) ?></td>
                    <td><?php if ($im['image_file']): ?><a href="<?= BASE_URL ?>/assets/uploads/imaging/<?= e($im['image_file']) ?>" target="_blank" class="btn btn-sm btn-outline">View</a><?php endif; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?><p class="text-muted">No imaging reports yet.</p><?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
