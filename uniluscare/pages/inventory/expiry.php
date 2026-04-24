<?php
require_once __DIR__.'/../../config/db.php';
requireRole('inventory');
$PAGE_TITLE='Expiry Tracking'; $ACTIVE_NAV='expiry';
$meds = $conn->query("SELECT * FROM medicines WHERE expiry_date IS NOT NULL ORDER BY expiry_date ASC");
$itemsE = $conn->query("SELECT * FROM inventory_items WHERE expiry_date IS NOT NULL ORDER BY expiry_date ASC");
include __DIR__.'/../../includes/layout.php';
?>
<div class="card mb-3">
    <div class="card-title">💊 Medicine Expiry</div>
    <table class="data-table">
        <tr><th>Name</th><th>Stock</th><th>Expiry</th><th>Days Left</th><th>Status</th></tr>
        <?php while($m=$meds->fetch_assoc()):
            $days = (int)((strtotime($m['expiry_date']) - time()) / 86400); ?>
            <tr><td><?=e($m['name'])?></td><td><?=$m['stock_quantity']?></td>
            <td><?=e($m['expiry_date'])?></td><td><?=$days?></td>
            <td><?php if($days<0):?><span class="badge badge-red">EXPIRED</span>
            <?php elseif($days<30):?><span class="badge badge-yellow">EXPIRING</span>
            <?php else:?><span class="badge badge-green">OK</span><?php endif;?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<div class="card">
    <div class="card-title">📦 Inventory Items Expiry</div>
    <table class="data-table">
        <tr><th>Name</th><th>Qty</th><th>Expiry</th><th>Days</th></tr>
        <?php while($i=$itemsE->fetch_assoc()):
            $days = (int)((strtotime($i['expiry_date']) - time()) / 86400);?>
            <tr><td><?=e($i['name'])?></td><td><?=$i['quantity']?></td>
            <td><?=e($i['expiry_date'])?></td><td><?=$days?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
