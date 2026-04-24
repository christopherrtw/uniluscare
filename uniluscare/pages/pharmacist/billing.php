<?php
require_once __DIR__.'/../../config/db.php';
requireRole('pharmacist');
$PAGE_TITLE='Medicine Bills'; $ACTIVE_NAV='billing';
$pid = $_SESSION['user_id']; $flash = $_GET['flash'] ?? ''; $invId = $_GET['printed'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $patient = $_POST['patient_id']; $desc = $_POST['description'];
    $amount = (float)$_POST['amount']; $tax = (float)($_POST['tax'] ?? 0);
    $total = $amount + $tax;
    $method = $_POST['payment_method']; $status = $_POST['payment_status'];
    $stmt = $conn->prepare("INSERT INTO invoices (patient_id,description,amount,tax,total,payment_status,payment_method,created_by) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param('ssdddsss',$patient,$desc,$amount,$tax,$total,$status,$method,$pid);
    $stmt->execute();
    $newId = $stmt->insert_id;
    header("Location: ".BASE_URL."/pages/pharmacist/billing.php?flash=Bill+generated&printed=$newId"); exit;
}

$patients = $conn->query("SELECT patient_id,full_name FROM patients ORDER BY full_name");
$recent = $conn->query("SELECT i.*,p.full_name AS pname FROM invoices i LEFT JOIN patients p ON i.patient_id=p.patient_id WHERE i.created_by='$pid' ORDER BY i.created_at DESC LIMIT 20");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?> <?php if($invId):?><a href="<?=BASE_URL?>/api/invoice_pdf.php?id=<?=(int)$invId?>" target="_blank" class="btn btn-sm btn-outline">🖨 Print</a><?php endif;?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">💳 Generate Medicine Bill</div>
    <form method="POST">
        <div class="form-row">
            <div class="form-group"><label>Patient</label>
                <select name="patient_id" required>
                    <option value="">-- Select --</option>
                    <?php while($p=$patients->fetch_assoc()):?>
                        <option value="<?=e($p['patient_id'])?>"><?=e($p['full_name'])?></option>
                    <?php endwhile;?>
                </select>
            </div>
            <div class="form-group"><label>Description</label><input type="text" name="description" required placeholder="e.g. Amoxicillin 250mg x 21 + dispensing fee"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Amount (ZMW)</label><input type="number" step="0.01" name="amount" required></div>
            <div class="form-group"><label>Tax</label><input type="number" step="0.01" name="tax" value="0"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Payment Method</label>
                <select name="payment_method"><option value="cash">Cash</option><option value="card">Card</option><option value="insurance">Insurance</option></select>
            </div>
            <div class="form-group"><label>Status</label>
                <select name="payment_status"><option value="paid">Paid</option><option value="unpaid">Unpaid</option></select>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Generate Bill</button>
    </form>
</div>
<div class="card">
    <div class="card-title">📋 Bills You've Generated</div>
    <table class="data-table">
        <tr><th>#</th><th>Patient</th><th>Description</th><th>Total</th><th>Status</th><th>Print</th></tr>
        <?php while($i=$recent->fetch_assoc()):?>
            <tr><td>INV-<?=str_pad($i['invoice_id'],5,'0',STR_PAD_LEFT)?></td>
            <td><?=e($i['pname'])?></td><td><?=e($i['description'])?></td>
            <td>K<?=number_format($i['total'],2)?></td>
            <td><span class="badge badge-<?=$i['payment_status']==='paid'?'confirmed':'pending'?>"><?=e($i['payment_status'])?></span></td>
            <td><a href="<?=BASE_URL?>/api/invoice_pdf.php?id=<?=(int)$i['invoice_id']?>" target="_blank" class="btn btn-sm btn-outline">🖨</a></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
