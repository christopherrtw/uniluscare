<?php
require_once __DIR__.'/../../config/db.php';
requireRole('admin');
$PAGE_TITLE='System Settings'; $ACTIVE_NAV='settings';
$flash = $_GET['flash'] ?? '';
if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['new_password'])) {
    $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $conn->query("UPDATE admins SET password='$hash' WHERE admin_id=".(int)$_SESSION['user_id']);
    header("Location: ".BASE_URL."/pages/admin/settings.php?flash=Password+updated"); exit;
}
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">⚙️ General Settings</div>
    <div class="form-row">
        <div><strong>Hospital Name:</strong> UnilusCare</div>
        <div><strong>Timezone:</strong> Africa/Lusaka</div>
        <div><strong>Currency:</strong> Zambian Kwacha (ZMW)</div>
        <div><strong>Language:</strong> English</div>
        <div><strong>Compliance:</strong> HPCZ aligned</div>
        <div><strong>Version:</strong> 1.0.0</div>
    </div>
</div>
<div class="card">
    <div class="card-title">🔒 Change Admin Password</div>
    <form method="POST">
        <div class="form-group"><label>New Password</label><input type="password" name="new_password" required minlength="6"></div>
        <button class="btn btn-primary" type="submit">Update Password</button>
    </form>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
