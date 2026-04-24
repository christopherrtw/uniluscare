<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'Emergency'; $ACTIVE_NAV = 'emergency';
$cases = $conn->query("SELECT t.*, p.full_name AS pname FROM triage_cases t LEFT JOIN patients p ON t.patient_id=p.patient_id WHERE t.status='active' ORDER BY FIELD(priority,'red','yellow','green'), t.created_at DESC");
include __DIR__ . '/../../includes/layout.php';
?>
<div class="card">
    <div class="card-title">🚑 Active Emergency Cases</div>
    <?php if ($cases->num_rows > 0): ?>
        <table class="data-table">
            <tr><th>Priority</th><th>Patient</th><th>Complaint</th><th>Time</th><th>Action</th></tr>
            <?php while ($c = $cases->fetch_assoc()): ?>
                <tr>
                    <td><span class="badge badge-<?= e($c['priority']) ?>"><?= strtoupper(e($c['priority'])) ?></span></td>
                    <td><?= e($c['pname'] ?: $c['patient_name']) ?><br><small class="text-muted"><?= e($c['patient_id']) ?></small></td>
                    <td><?= e($c['chief_complaint']) ?></td>
                    <td><?= date('d M H:i', strtotime($c['created_at'])) ?></td>
                    <td><?php if ($c['patient_id']): ?><a href="<?= BASE_URL ?>/pages/doctor/patient_view.php?id=<?= e($c['patient_id']) ?>" class="btn btn-sm btn-primary">Open</a><?php endif; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?><p class="text-muted">No active emergencies.</p><?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
