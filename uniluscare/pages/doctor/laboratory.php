<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'Laboratory'; $ACTIVE_NAV = 'laboratory';
$did = $_SESSION['user_id'];
$tests = $conn->query("SELECT l.*, p.full_name AS pname FROM lab_tests l LEFT JOIN patients p ON l.patient_id=p.patient_id WHERE l.doctor_id='$did' ORDER BY l.requested_at DESC");
include __DIR__ . '/../../includes/layout.php';
?>
<div class="card">
    <div class="card-title">🔬 Laboratory Tests You've Ordered</div>
    <?php if ($tests->num_rows > 0): ?>
        <table class="data-table">
            <tr><th>Patient</th><th>Test</th><th>Type</th><th>Status</th><th>Results</th><th>Date</th><th>File</th></tr>
            <?php while ($t = $tests->fetch_assoc()): ?>
                <tr>
                    <td><?= e($t['pname']) ?><br><small class="text-muted"><?= e($t['patient_id']) ?></small></td>
                    <td><?= e($t['test_name']) ?></td>
                    <td><?= e($t['test_type']) ?></td>
                    <td><span class="badge badge-<?= $t['status']==='completed'?'confirmed':'pending' ?>"><?= e($t['status']) ?></span></td>
                    <td><?= e(substr($t['results'] ?? '—', 0, 60)) ?></td>
                    <td><?= date('d M Y', strtotime($t['requested_at'])) ?></td>
                    <td><?php if ($t['result_file']): ?><a href="<?= BASE_URL ?>/assets/uploads/lab/<?= e($t['result_file']) ?>" target="_blank" class="btn btn-sm btn-outline">View</a><?php endif; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?><p class="text-muted">No lab tests ordered yet.</p><?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
