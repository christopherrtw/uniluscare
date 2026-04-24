<?php
require_once __DIR__.'/../../config/db.php';
requireRole('inventory');
$PAGE_TITLE='Purchase Orders'; $ACTIVE_NAV='orders';
$iid = $_SESSION['user_id']; $flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $item=(int)$_POST['item_id']; $qty=(int)$_POST['quantity']; $sup=$_POST['supplier'];
    $stmt = $conn->prepare("INSERT INTO purchase_orders (item_id,quantity,supplier,created_by) VALUES (?,?,?,?)");
    $stmt->bind_param('iiss',$item,$qty,$sup,$iid);
    $stmt->execute();
    header("Location: ".BASE_URL."/pages/inventory/orders.php?flash=Order+created"); exit;
}
$items = $conn->query("SELECT item_id,name FROM inventory_items ORDER BY name");
$orders = $conn->query("SELECT po.*, i.name AS iname FROM purchase_orders po LEFT JOIN inventory_items i ON po.item_id=i.item_id ORDER BY po.created_at DESC");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">📝 Create Purchase Order</div>
    <form method="POST">
        <div class="form-row">
            <div class="form-group"><label>Item</label>
                <select name="item_id" required>
                    <option value="">-- Select --</option>
                    <?php while($i=$items->fetch_assoc()):?>
                        <option value="<?=$i['item_id']?>"><?=e($i['name'])?></option>
                    <?php endwhile;?>
                </select>
            </div>
            <div class="form-group"><label>Quantity</label><input type="number" name="quantity" required></div>
        </div>
        <div class="form-group"><label>Supplier</label><input type="text" name="supplier" required></div>
        <button class="btn btn-primary" type="submit">Create PO</button>
    </form>
</div>
<div class="card">
    <div class="card-title">📋 All Purchase Orders</div>
    <table class="data-table">
        <tr><th>PO #</th><th>Item</th><th>Qty</th><th>Supplier</th><th>Status</th><th>Date</th></tr>
        <?php while($o=$orders->fetch_assoc()):?>
            <tr><td>PO-<?=str_pad($o['po_id'],5,'0',STR_PAD_LEFT)?></td>
            <td><?=e($o['iname'])?></td><td><?=$o['quantity']?></td>
            <td><?=e($o['supplier'])?></td>
            <td><span class="badge badge-<?=$o['status']==='pending'?'warning':'confirmed'?>"><?=e($o['status'])?></span></td>
            <td><?=date('d M Y',strtotime($o['created_at']))?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
