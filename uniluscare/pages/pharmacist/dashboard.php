<?php
require_once __DIR__.'/../../config/db.php';
requireRole('pharmacist');
$PAGE_TITLE='Dashboard'; $ACTIVE_NAV='dashboard';
$pending = $conn->query("SELECT COUNT(*) c FROM prescriptions WHERE status='active'")->fetch_assoc()['c'];
$lowStock = $conn->query("SELECT COUNT(*) c FROM medicines WHERE stock_quantity<=reorder_level")->fetch_assoc()['c'];
$dispensed = $conn->query("SELECT COUNT(*) c FROM prescriptions WHERE status='dispensed'")->fetch_assoc()['c'];
$lowItems = $conn->query("SELECT * FROM medicines WHERE stock_quantity<=reorder_level ORDER BY stock_quantity ASC LIMIT 5");
include __DIR__.'/../../includes/layout.php';
?>
<div class="stats-grid mb-3">
    <div class="stat-card accent"><div class="label">Pending Prescriptions</div><div class="value"><?=$pending?></div></div>
    <div class="stat-card success"><div class="label">Dispensed</div><div class="value"><?=$dispensed?></div></div>
    <div class="stat-card danger"><div class="label">Low-Stock Alerts</div><div class="value"><?=$lowStock?></div></div>
</div>
<div class="card mb-3">
    <div class="card-title">⚠️ Low Stock Alerts</div>
    <?php if ($lowItems->num_rows > 0):?>
        <table class="data-table">
            <tr><th>Medicine</th><th>Stock</th><th>Reorder Level</th><th>Expiry</th></tr>
            <?php while($m=$lowItems->fetch_assoc()):?>
                <tr><td><?=e($m['name'])?></td>
                <td><span class="badge badge-red"><?=$m['stock_quantity']?></span></td>
                <td><?=$m['reorder_level']?></td>
                <td><?=e($m['expiry_date'])?></td></tr>
            <?php endwhile;?>
        </table>
    <?php else:?><p class="text-muted">All stock levels are OK.</p><?php endif;?>
</div>
<div class="card">
    <div class="card-title">🚀 Actions</div>
    <a href="<?=BASE_URL?>/pages/pharmacist/prescriptions.php" class="btn btn-primary">💊 View Prescriptions</a>
    <a href="<?=BASE_URL?>/pages/pharmacist/inventory.php" class="btn btn-primary">📦 Inventory</a>
    <a href="<?=BASE_URL?>/pages/pharmacist/billing.php" class="btn btn-primary">💳 Medicine Bills</a>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
