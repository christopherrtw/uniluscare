<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('patient');
$PAGE_TITLE = 'Billing';
$ACTIVE_NAV = 'billing';
$pid = $_SESSION['user_id'];
$invoices = $conn->query("SELECT * FROM invoices WHERE patient_id='$pid' ORDER BY created_at DESC");
$total = $conn->query("SELECT SUM(total) AS t, SUM(CASE WHEN payment_status='paid' THEN total ELSE 0 END) AS paid FROM invoices WHERE patient_id='$pid'")->fetch_assoc();
include __DIR__ . '/../../includes/layout.php';
?>

<div class="stats-grid mb-3">
    <div class="stat-card"><div class="label">Total Billed</div><div class="value">K<?= number_format($total['t'] ?? 0, 2) ?></div></div>
    <div class="stat-card success"><div class="label">Paid</div><div class="value">K<?= number_format($total['paid'] ?? 0, 2) ?></div></div>
    <div class="stat-card danger"><div class="label">Outstanding</div><div class="value">K<?= number_format(($total['t'] ?? 0) - ($total['paid'] ?? 0), 2) ?></div></div>
</div>

<div class="card">
    <div class="card-title">💳 Invoice & Payment History</div>
    <?php if ($invoices->num_rows > 0): ?>
        <table class="data-table">
            <tr><th>Invoice #</th><th>Description</th><th>Amount</th><th>Tax</th><th>Total</th><th>Status</th><th>Method</th><th>Date</th></tr>
            <?php while ($i = $invoices->fetch_assoc()): ?>
                <tr>
                    <td>INV-<?= str_pad($i['invoice_id'],5,'0',STR_PAD_LEFT) ?></td>
                    <td><?= e($i['description']) ?></td>
                    <td>K<?= number_format($i['amount'],2) ?></td>
                    <td>K<?= number_format($i['tax'],2) ?></td>
                    <td><strong>K<?= number_format($i['total'],2) ?></strong></td>
                    <td><span class="badge badge-<?= $i['payment_status']==='paid'?'confirmed':'pending' ?>"><?= e($i['payment_status']) ?></span></td>
                    <td><?= e($i['payment_method'] ?: '—') ?></td>
                    <td><?= date('d M Y', strtotime($i['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?><p class="text-muted">No invoices on record.</p><?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
