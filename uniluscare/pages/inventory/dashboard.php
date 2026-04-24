<?php
require_once __DIR__.'/../../config/db.php';
requireRole('inventory');
$PAGE_TITLE='Dashboard'; $ACTIVE_NAV='dashboard';
$tot = $conn->query("SELECT COUNT(*) c FROM inventory_items")->fetch_assoc()['c'];
$low = $conn->query("SELECT COUNT(*) c FROM inventory_items WHERE quantity<=reorder_level")->fetch_assoc()['c'];
$po = $conn->query("SELECT COUNT(*) c FROM purchase_orders WHERE status='pending'")->fetch_assoc()['c'];
$expSoon = $conn->query("SELECT COUNT(*) c FROM medicines WHERE expiry_date < DATE_ADD(CURDATE(),INTERVAL 30 DAY)")->fetch_assoc()['c'];
include __DIR__.'/../../includes/layout.php';
?>
<div class="stats-grid mb-3">
    <div class="stat-card"><div class="label">Total Items</div><div class="value"><?=$tot?></div></div>
    <div class="stat-card danger"><div class="label">Low Stock</div><div class="value"><?=$low?></div></div>
    <div class="stat-card accent"><div class="label">Pending Orders</div><div class="value"><?=$po?></div></div>
    <div class="stat-card danger"><div class="label">Expiring Soon</div><div class="value"><?=$expSoon?></div></div>
</div>
<div class="card">
    <div class="card-title">🚀 Actions</div>
    <a href="<?=BASE_URL?>/pages/inventory/items.php" class="btn btn-primary">📦 Manage Items</a>
    <a href="<?=BASE_URL?>/pages/inventory/orders.php" class="btn btn-primary">📝 Purchase Orders</a>
    <a href="<?=BASE_URL?>/pages/inventory/expiry.php" class="btn btn-primary">⏰ Expiry Tracking</a>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
