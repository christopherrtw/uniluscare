<?php
require_once __DIR__.'/../../config/db.php';
requireRole('admin');
$PAGE_TITLE='Billing & Insurance'; $ACTIVE_NAV='billing';
$inv=$conn->query("SELECT i.*,p.full_name AS pname,p.insurance_company FROM invoices i LEFT JOIN patients p ON i.patient_id=p.patient_id ORDER BY i.created_at DESC LIMIT 100");
$ins=$conn->query("SELECT COUNT(*) c FROM invoices WHERE payment_method='insurance'")->fetch_assoc()['c'];
$insPending=$conn->query("SELECT COUNT(*) c FROM invoices WHERE insurance_claim_status='pending'")->fetch_assoc()['c'];
include __DIR__.'/../../includes/layout.php';
?>
<div class="stats-grid mb-3">
    <div class="stat-card"><div class="label">Insurance Claims</div><div class="value"><?=$ins?></div></div>
    <div class="stat-card accent"><div class="label">Pending Claims</div><div class="value"><?=$insPending?></div></div>
</div>
<div class="card">
    <div class="card-title">💳 All Invoices</div>
    <table class="data-table">
        <tr><th>Invoice</th><th>Patient</th><th>Description</th><th>Total</th><th>Status</th><th>Method</th><th>Insurance</th></tr>
        <?php while($i=$inv->fetch_assoc()):?>
            <tr><td>INV-<?=str_pad($i['invoice_id'],5,'0',STR_PAD_LEFT)?></td>
            <td><?=e($i['pname'])?></td><td><?=e($i['description'])?></td>
            <td>K<?=number_format($i['total'],2)?></td>
            <td><span class="badge badge-<?=$i['payment_status']==='paid'?'confirmed':'pending'?>"><?=e($i['payment_status'])?></span></td>
            <td><?=e($i['payment_method']?:'—')?></td>
            <td><?=e($i['insurance_company']?:'—')?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
