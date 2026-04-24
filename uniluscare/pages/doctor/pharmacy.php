<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('doctor');
$PAGE_TITLE = 'Pharmacy'; $ACTIVE_NAV = 'pharmacy';
$did = $_SESSION['user_id'];
$meds = $conn->query("SELECT * FROM medicines ORDER BY name");
$rxs = $conn->query("SELECT r.*, p.full_name AS pname FROM prescriptions r LEFT JOIN patients p ON r.patient_id=p.patient_id WHERE r.doctor_id='$did' ORDER BY r.created_at DESC LIMIT 20");
include __DIR__ . '/../../includes/layout.php';
?>
<div class="card mb-3">
    <div class="card-title">💊 Your Recent Prescriptions</div>
    <?php if ($rxs->num_rows > 0): ?>
    <table class="data-table">
        <tr><th>Date</th><th>Patient</th><th>Diagnosis</th><th>Status</th><th>PDF</th></tr>
        <?php while ($r = $rxs->fetch_assoc()): ?>
            <tr>
                <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                <td><?= e($r['pname']) ?></td>
                <td><?= e($r['diagnosis']) ?></td>
                <td><span class="badge badge-<?= $r['status']==='dispensed'?'confirmed':'pending' ?>"><?= e($r['status']) ?></span></td>
                <td><a href="<?= BASE_URL ?>/api/prescription_pdf.php?id=<?= (int)$r['prescription_id'] ?>" target="_blank" class="btn btn-sm btn-outline">📄</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?><p class="text-muted">No prescriptions yet.</p><?php endif; ?>
</div>
<div class="card">
    <div class="card-title">📦 Medicine Inventory (reference)</div>
    <table class="data-table">
        <tr><th>Medicine</th><th>Category</th><th>Stock</th><th>Unit Price</th><th>Expiry</th></tr>
        <?php while ($m = $meds->fetch_assoc()): ?>
            <tr>
                <td><?= e($m['name']) ?></td>
                <td><?= e($m['category']) ?></td>
                <td><?= $m['stock_quantity'] <= $m['reorder_level'] ? "<span class='badge badge-warning'>{$m['stock_quantity']}</span>" : $m['stock_quantity'] ?></td>
                <td>K<?= number_format($m['unit_price'],2) ?></td>
                <td><?= e($m['expiry_date']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
