<?php
require_once __DIR__ . '/config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param('s', $u);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    if ($admin && password_verify($p, $admin['password'])) {
        $_SESSION['role'] = 'admin';
        $_SESSION['user_id'] = $admin['admin_id'];
        $_SESSION['full_name'] = $admin['full_name'];
        header('Location: ' . BASE_URL . '/pages/admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UnilusCare — Administrator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body" style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.25) 0, transparent 45%), linear-gradient(180deg, #1a3a4a 0%, #0b5e68 100%);">

<h1 class="auth-page-title" style="color:#fff;">Administrator Access</h1>

<div class="auth-card" style="max-width:800px;">
    <div class="auth-left" style="background: linear-gradient(135deg,#0b5e68 0%,#074a52 100%);">
        <div class="auth-logo" style="background:#f28b30;">
            <svg viewBox="0 0 24 24" style="fill:#fff;"><path d="M12 2 L14 8 L20 8 L15 12 L17 18 L12 14.5 L7 18 L9 12 L4 8 L10 8 Z"/></svg>
        </div>
        <p class="auth-tagline" style="color:#fff;">Administrative control for the UnilusCare Hospital Management System.</p>
        <div class="auth-illustration">
            <svg viewBox="0 0 200 200" style="max-width:100%;max-height:220px;">
                <circle cx="100" cy="85" r="35" fill="#f28b30"/>
                <path d="M60 180 Q60 130 100 130 Q140 130 140 180 Z" fill="#f28b30"/>
                <circle cx="100" cy="85" r="28" fill="#fff"/>
                <path d="M88 82 L96 90 L112 74" stroke="#0b5e68" stroke-width="4" fill="none" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    <div class="auth-right">
        <h2>Admin Sign in</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form class="auth-form" method="POST">
            <input type="text" name="username" placeholder="Username" required autocomplete="off">
            <div class="password-field">
                <input type="password" name="password" id="adminPw" placeholder="Password" required>
                <button type="button" class="password-toggle" onclick="const f=document.getElementById('adminPw');f.type=f.type==='password'?'text':'password';">👁</button>
            </div>
            <button type="submit" class="auth-btn">Sign in</button>
            <p class="auth-switch" style="margin-top:1.5rem;">
                <a href="<?= BASE_URL ?>/index.php">← Back to main sign-in</a>
            </p>
            <p class="text-muted text-sm text-center mt-2">
                Default: admin / Admin@123
            </p>
        </form>
    </div>
</div>

</body>
</html>
