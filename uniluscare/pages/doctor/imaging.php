<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'Imaging / PACS'; $ACTIVE_NAV = 'imaging';
$imgs = $conn->query("SELECT i.*, p.full_name AS pname FROM imaging_reports i LEFT JOIN patients p ON i.patient_id=p.patient_id ORDER BY i.created_at DESC LIMIT 50");
include __DIR__ . '/../../includes/layout.php';
?>
<div class="card">
    <div class="card-title">🩻 Medical Imaging Reports</div>
    <?php if ($imgs->num_rows > 0): ?>
    <table class="data-table">
        <tr><th>Patient</th><th>Type</th><th>Body Part</th><th>Findings</th><th>AI Analysis</th><th>Date</th><th>Image</th></tr>
        <?php while ($i = $imgs->fetch_assoc()): ?>
            <tr>
                <td><?= e($i['pname']) ?></td>
                <td><?= e($i['image_type']) ?></td>
                <td><?= e($i['body_part']) ?></td>
                <td><?= e(substr($i['findings'] ?? '—', 0, 60)) ?></td>
                <td><small><?= e(substr($i['ai_analysis'] ?? '—', 0, 80)) ?></small></td>
                <td><?= date('d M Y', strtotime($i['created_at'])) ?></td>
                <td><?php if ($i['image_file']): ?><a href="<?= BASE_URL ?>/assets/uploads/imaging/<?= e($i['image_file']) ?>" target="_blank" class="btn btn-sm btn-outline">View</a><?php endif; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?><p class="text-muted">No imaging reports.</p><?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
