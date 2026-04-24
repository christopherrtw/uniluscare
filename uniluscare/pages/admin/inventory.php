<?php
require_once __DIR__.'/../../config/db.php';
requireRole('admin');
$PAGE_TITLE='Resources'; $ACTIVE_NAV='inventory';
$meds = $conn->query("SELECT * FROM medicines ORDER BY stock_quantity ASC");
$items = $conn->query("SELECT * FROM inventory_items ORDER BY quantity ASC");
include __DIR__.'/../../includes/layout.php';
?>
<div class="card mb-3">
    <div class="card-title">💊 Medicine Stock</div>
    <table class="data-table">
        <tr><th>Name</th><th>Category</th><th>Stock</th><th>Reorder</th><th>Expiry</th><th>Status</th></tr>
        <?php while($m=$meds->fetch_assoc()):
            $low = $m['stock_quantity'] <= $m['reorder_level'];
            $exp = strtotime($m['expiry_date']) < strtotime('+30 days');
        ?>
            <tr><td><?=e($m['name'])?></td><td><?=e($m['category'])?></td>
            <td><?=$m['stock_quantity']?></td><td><?=$m['reorder_level']?></td>
            <td><?=e($m['expiry_date'])?></td>
            <td><?php if($low):?><span class="badge badge-yellow">LOW STOCK</span><?php endif;?>
            <?php if($exp):?><span class="badge badge-red">EXPIRING</span><?php endif;?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<div class="card">
    <div class="card-title">📦 Inventory Items</div>
    <table class="data-table">
        <tr><th>Name</th><th>Category</th><th>Qty</th><th>Reorder</th><th>Supplier</th><th>Status</th></tr>
        <?php while($i=$items->fetch_assoc()):
            $low=$i['quantity']<=$i['reorder_level'];?>
            <tr><td><?=e($i['name'])?></td><td><?=e($i['category'])?></td>
            <td><?=$i['quantity']?></td><td><?=$i['reorder_level']?></td>
            <td><?=e($i['supplier'])?></td>
            <td><?php if($low):?><span class="badge badge-red">LOW</span><?php else:?><span class="badge badge-green">OK</span><?php endif;?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
