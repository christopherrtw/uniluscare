<?php
require_once __DIR__.'/../../config/db.php';
requireRole('admin');
$PAGE_TITLE='Backup & Restore'; $ACTIVE_NAV='backup';
$flash = $_GET['flash'] ?? '';
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">💾 Database Backup</div>
    <p class="text-muted mb-2">Generate a SQL dump of the UnilusCare database.</p>
    <a href="<?=BASE_URL?>/api/admin_actions.php?action=backup" class="btn btn-primary">⬇ Download Backup</a>
</div>
<div class="card">
    <div class="card-title">📤 Restore from Backup</div>
    <p class="text-muted mb-2">Upload a SQL file to restore.</p>
    <form method="POST" enctype="multipart/form-data" action="<?=BASE_URL?>/api/admin_actions.php">
        <input type="hidden" name="action" value="restore">
        <div class="form-group"><label>SQL File</label><input type="file" name="sql_file" accept=".sql" required></div>
        <button class="btn btn-primary" type="submit" data-confirm="This will overwrite existing data. Continue?">Restore</button>
    </form>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
