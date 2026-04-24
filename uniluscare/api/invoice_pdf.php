<?php
require_once __DIR__.'/../config/db.php';
if (empty($_SESSION['role'])) { header('Location: '.BASE_URL.'/index.php'); exit; }
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT i.*,p.full_name AS pname,p.patient_id AS pid,p.insurance_company,p.insurance_number FROM invoices i LEFT JOIN patients p ON i.patient_id=p.patient_id WHERE invoice_id=?");
$stmt->bind_param('i',$id); $stmt->execute();
$i = $stmt->get_result()->fetch_assoc();
if (!$i) die('Invoice not found');
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invoice INV-<?=str_pad($id,5,'0',STR_PAD_LEFT)?></title>
<style>
body{font-family:Georgia,serif;max-width:800px;margin:2rem auto;padding:2rem;color:#222;}
.header{text-align:center;border-bottom:3px double #0b5e68;padding-bottom:1rem;margin-bottom:2rem;}
.header h1{color:#0b5e68;margin:0;}
.details{display:grid;grid-template-columns:1fr 1fr;gap:1rem;padding:1rem;background:#f5f7f8;border-radius:8px;margin-bottom:1.5rem;}
table{width:100%;border-collapse:collapse;margin:1rem 0;}
th{background:#0b5e68;color:#fff;padding:0.6rem;text-align:left;}
td{padding:0.6rem;border-bottom:1px solid #ddd;}
.total{text-align:right;font-size:1.2rem;font-weight:bold;margin-top:1rem;}
@media print{.noprint{display:none;}}
</style></head><body>
<div class="noprint" style="text-align:right;"><button onclick="window.print()" style="padding:0.5rem 1rem;background:#0b5e68;color:#fff;border:none;border-radius:6px;cursor:pointer;">🖨 Print / Save PDF</button></div>
<div class="header"><h1>UnilusCare</h1><p>Hospital Management System — Tax Receipt</p><p><strong>INVOICE INV-<?=str_pad($id,5,'0',STR_PAD_LEFT)?></strong></p></div>
<div class="details">
    <div><strong>Patient:</strong> <?=e($i['pname'])?></div>
    <div><strong>Patient ID:</strong> <?=e($i['pid'])?></div>
    <div><strong>Date:</strong> <?=date('d F Y', strtotime($i['created_at']))?></div>
    <div><strong>Payment Method:</strong> <?=e($i['payment_method'])?></div>
    <?php if($i['insurance_company']):?>
        <div><strong>Insurance:</strong> <?=e($i['insurance_company'])?></div>
        <div><strong>Insurance No:</strong> <?=e($i['insurance_number'])?></div>
    <?php endif;?>
</div>
<table>
<tr><th>Description</th><th>Amount</th></tr>
<tr><td><?=e($i['description'])?></td><td>K<?=number_format($i['amount'],2)?></td></tr>
<tr><td>Tax / VAT</td><td>K<?=number_format($i['tax'],2)?></td></tr>
<tr><td><strong>TOTAL</strong></td><td><strong>K<?=number_format($i['total'],2)?></strong></td></tr>
</table>
<p class="total">Status: <?=strtoupper(e($i['payment_status']))?></p>
<p style="font-size:0.75rem;color:#888;margin-top:3rem;text-align:center;">Thank you for choosing UnilusCare. Keep this receipt for your records.</p>
</body></html>
