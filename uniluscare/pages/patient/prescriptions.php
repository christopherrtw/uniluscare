<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('patient');
$PAGE_TITLE = 'Prescriptions';
$ACTIVE_NAV = 'prescriptions';
$pid = $_SESSION['user_id'];

$presc = $conn->query("SELECT p.*, d.full_name AS doctor_name FROM prescriptions p LEFT JOIN doctors d ON p.doctor_id=d.doctor_id WHERE p.patient_id='$pid' ORDER BY p.created_at DESC");
$flash = $_GET['flash'] ?? '';

include __DIR__ . '/../../includes/layout.php';
?>

<?php if ($flash): ?><div class="alert alert-success"><?= e($flash) ?></div><?php endif; ?>

<div class="card">
    <div class="card-title">💊 Your Prescriptions</div>
    <?php if ($presc->num_rows > 0): ?>
        <?php while ($p = $presc->fetch_assoc()):
            $items = $conn->query("SELECT * FROM prescription_items WHERE prescription_id=" . (int)$p['prescription_id']);
        ?>
            <div style="border:1px solid var(--border); border-radius:var(--radius); padding:1.2rem; margin-bottom:1rem;">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap;">
                    <div>
                        <strong>Date:</strong> <?= date('d M Y', strtotime($p['created_at'])) ?><br>
                        <strong>Doctor:</strong> <?= e($p['doctor_name']) ?><br>
                        <strong>Diagnosis:</strong> <?= e($p['diagnosis']) ?>
                        <?php if ($p['icd10_code']): ?> <span class="badge badge-info">ICD-10: <?= e($p['icd10_code']) ?></span><?php endif; ?>
                    </div>
                    <div>
                        <span class="badge badge-<?= $p['status']==='active'?'confirmed':'pending' ?>"><?= e($p['status']) ?></span>
                    </div>
                </div>
                <table class="data-table mt-2">
                    <tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Instructions</th></tr>
                    <?php while ($i = $items->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= e($i['medicine_name']) ?></strong></td>
                            <td><?= e($i['dosage']) ?></td>
                            <td><?= e($i['frequency']) ?></td>
                            <td><?= e($i['duration']) ?></td>
                            <td><?= e($i['instructions']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <div class="mt-2" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <a href="<?= BASE_URL ?>/api/prescription_pdf.php?id=<?= (int)$p['prescription_id'] ?>" class="btn btn-sm btn-outline">📄 Download PDF</a>
                    <form method="POST" action="<?= BASE_URL ?>/api/patient_actions.php" style="display:inline;">
                        <input type="hidden" name="action" value="refill_request">
                        <input type="hidden" name="prescription_id" value="<?= (int)$p['prescription_id'] ?>">
                        <button class="btn btn-sm btn-primary">🔄 Request Refill</button>
                    </form>
                    <button class="btn btn-sm btn-outline" onclick="showDrugInfo(<?= (int)$p['prescription_id'] ?>)">ℹ️ Drug Info</button>
                </div>
                <div id="drugInfo<?= (int)$p['prescription_id'] ?>" class="hidden mt-2" style="padding:0.75rem; background:#f8f9fa; border-radius:var(--radius-sm);">
                    <strong>Drug information:</strong> Always take as directed. Common side effects may include nausea, dizziness, or allergic reaction. Store below 25°C. Contact your doctor if adverse effects occur.
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-muted">You have no prescriptions yet.</p>
    <?php endif; ?>
</div>

<script>
function showDrugInfo(id) {
    document.getElementById('drugInfo' + id).classList.toggle('hidden');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
