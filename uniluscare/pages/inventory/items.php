<?php
require_once __DIR__.'/../../config/db.php';
requireRole('inventory');
$PAGE_TITLE='Items & Stock'; $ACTIVE_NAV='items';
$flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $act = $_POST['act'] ?? 'add';
    if ($act==='add') {
        $n=$_POST['name']; $c=$_POST['category']; $q=(int)$_POST['quantity']; $r=(int)$_POST['reorder_level'];
        $p=(float)$_POST['unit_price']; $s=$_POST['supplier']; $e=$_POST['expiry_date']?:null;
        $stmt = $conn->prepare("INSERT INTO inventory_items (name,category,quantity,reorder_level,unit_price,supplier,expiry_date) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('ssiidss',$n,$c,$q,$r,$p,$s,$e);
        $stmt->execute();
    } elseif ($act==='restock') {
        $id=(int)$_POST['id']; $add=(int)$_POST['add'];
        $conn->query("UPDATE inventory_items SET quantity=quantity+$add, last_restocked=NOW() WHERE item_id=$id");
    }
    header("Location: ".BASE_URL."/pages/inventory/items.php?flash=Updated"); exit;
}
$items = $conn->query("SELECT * FROM inventory_items ORDER BY name");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">➕ Add New Item</div>
    <form method="POST">
        <input type="hidden" name="act" value="add">
        <div class="form-row">
            <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Category</label><input type="text" name="category"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Quantity</label><input type="number" name="quantity" required></div>
            <div class="form-group"><label>Reorder Level</label><input type="number" name="reorder_level" value="10"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Unit Price</label><input type="number" step="0.01" name="unit_price"></div>
            <div class="form-group"><label>Supplier</label><input type="text" name="supplier"></div>
        </div>
        <div class="form-group"><label>Expiry (if applicable)</label><input type="date" name="expiry_date"></div>
        <button class="btn btn-primary" type="submit">Add Item</button>
    </form>
</div>
<div class="card">
    <div class="card-title">📦 Current Items</div>
    <table class="data-table">
        <tr><th>Name</th><th>Category</th><th>Qty</th><th>Reorder</th><th>Supplier</th><th>Restock</th></tr>
        <?php while($i=$items->fetch_assoc()):?>
            <tr><td><?=e($i['name'])?></td><td><?=e($i['category'])?></td>
            <td><?=$i['quantity']<=$i['reorder_level']?"<span class='badge badge-red'>{$i['quantity']}</span>":$i['quantity']?></td>
            <td><?=$i['reorder_level']?></td>
            <td><?=e($i['supplier'])?></td>
            <td>
                <form method="POST" style="display:flex;gap:0.3rem;">
                    <input type="hidden" name="act" value="restock">
                    <input type="hidden" name="id" value="<?=(int)$i['item_id']?>">
                    <input type="number" name="add" placeholder="+ qty" style="width:70px;padding:0.3rem;" required>
                    <button class="btn btn-sm btn-primary">+</button>
                </form>
            </td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
