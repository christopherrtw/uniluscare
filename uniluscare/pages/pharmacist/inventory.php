<?php
require_once __DIR__.'/../../config/db.php';
requireRole('pharmacist');
$PAGE_TITLE='Inventory'; $ACTIVE_NAV='inventory';
$flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name=$_POST['name']; $cat=$_POST['category']; $mfr=$_POST['manufacturer']; $batch=$_POST['batch_number'];
    $qty=(int)$_POST['stock_quantity']; $ro=(int)$_POST['reorder_level']; $price=(float)$_POST['unit_price']; $exp=$_POST['expiry_date'];
    $stmt = $conn->prepare("INSERT INTO medicines (name,category,manufacturer,batch_number,stock_quantity,reorder_level,unit_price,expiry_date) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param('ssssiids',$name,$cat,$mfr,$batch,$qty,$ro,$price,$exp);
    $stmt->execute();
    header("Location: ".BASE_URL."/pages/pharmacist/inventory.php?flash=Medicine+added"); exit;
}

$meds = $conn->query("SELECT * FROM medicines ORDER BY stock_quantity ASC");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">➕ Add Medicine to Stock</div>
    <form method="POST">
        <div class="form-row">
            <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Category</label><input type="text" name="category"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Manufacturer</label><input type="text" name="manufacturer"></div>
            <div class="form-group"><label>Batch Number</label><input type="text" name="batch_number"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Stock Quantity</label><input type="number" name="stock_quantity" required></div>
            <div class="form-group"><label>Reorder Level</label><input type="number" name="reorder_level" value="20"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Unit Price (ZMW)</label><input type="number" step="0.01" name="unit_price"></div>
            <div class="form-group"><label>Expiry Date</label><input type="date" name="expiry_date" required></div>
        </div>
        <button class="btn btn-primary" type="submit">Add Medicine</button>
    </form>
</div>
<div class="card">
    <div class="card-title">📦 Current Inventory</div>
    <table class="data-table">
        <tr><th>Name</th><th>Category</th><th>Stock</th><th>Reorder</th><th>Unit Price</th><th>Expiry</th><th>Alert</th></tr>
        <?php while($m=$meds->fetch_assoc()):
            $low = $m['stock_quantity']<=$m['reorder_level'];
            $exp = strtotime($m['expiry_date'])<strtotime('+30 days');?>
            <tr><td><?=e($m['name'])?></td><td><?=e($m['category'])?></td>
            <td><?=$m['stock_quantity']?></td><td><?=$m['reorder_level']?></td>
            <td>K<?=number_format($m['unit_price'],2)?></td>
            <td><?=e($m['expiry_date'])?></td>
            <td><?php if($low):?><span class="badge badge-yellow">LOW</span><?php endif;?>
            <?php if($exp):?><span class="badge badge-red">EXPIRING</span><?php endif;?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
