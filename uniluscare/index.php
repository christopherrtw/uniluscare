<?php
require_once __DIR__ . '/config/db.php';

// If already logged in, redirect
if (!empty($_SESSION['role'])) {
    $role = $_SESSION['role'];
    header("Location: " . BASE_URL . "/pages/{$role}/dashboard.php");
    exit;
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UnilusCare — Sign in</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">

<h1 class="auth-page-title">Sign in Page</h1>

<div class="auth-card">

    <!-- three-dots menu → admin login -->
    <button type="button" class="admin-dots" onclick="window.location.href='<?= BASE_URL ?>/admin_login.php'" title="Admin login">
        <span></span><span></span><span></span>
    </button>

    <!-- LEFT PANEL -->
    <div class="auth-left">
        <div class="auth-logo">
            <svg viewBox="0 0 24 24"><path d="M12 2 L14 8 L20 8 L15 12 L17 18 L12 14.5 L7 18 L9 12 L4 8 L10 8 Z"/></svg>
        </div>
        <p class="auth-tagline">We at UnilusCare are always fully focused on your health.</p>

        <div class="auth-illustration">
            <!-- inline stethoscope illustration -->
            <svg viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg" style="max-width:100%;max-height:260px;">
                <ellipse cx="110" cy="180" rx="85" ry="18" fill="#8a8ac7" opacity="0.45"/>
                <ellipse cx="110" cy="172" rx="78" ry="14" fill="#6f6fc0"/>
                <ellipse cx="110" cy="168" rx="78" ry="14" fill="#ffffff"/>
                <!-- stethoscope tubes -->
                <path d="M80 40 Q70 80 80 115 Q90 145 110 155" stroke="#2c2c50" stroke-width="5" fill="none" stroke-linecap="round"/>
                <path d="M140 40 Q150 80 140 115 Q130 145 110 155" stroke="#2c2c50" stroke-width="5" fill="none" stroke-linecap="round"/>
                <!-- ear tips -->
                <circle cx="80" cy="38" r="7" fill="#2c2c50"/>
                <circle cx="140" cy="38" r="7" fill="#2c2c50"/>
                <!-- chest piece -->
                <circle cx="110" cy="158" r="16" fill="#2c2c50"/>
                <circle cx="110" cy="158" r="10" fill="#5a5ab0"/>
                <circle cx="110" cy="158" r="5" fill="#2c2c50"/>
            </svg>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="auth-right">
        <span class="lang-picker">English (US) ▾</span>

        <h2>Welcome to UnilusCare</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <!-- role tabs -->
        <div class="auth-role-tabs">
            <button type="button" class="active" data-role="patient">Patient</button>
            <button type="button" data-role="doctor">Doctor</button>
            <button type="button" data-role="staff">Other staff</button>
        </div>

        <!-- PATIENT FORM -->
        <form class="auth-form" id="form-patient" method="POST" action="<?= BASE_URL ?>/api/login.php">
            <input type="hidden" name="role" value="patient">
            <input type="text" name="patient_id" placeholder="Patient ID (e.g. P000001)" required autocomplete="off">
            <button type="submit" class="auth-btn">Sign in as Patient</button>
            <p class="auth-switch">First time here? <a href="<?= BASE_URL ?>/signup.php">Register as a new patient</a> <br><span style="font-size:0.8rem;color:#999;">(Registration is handled by reception)</span></p>
        </form>

        <!-- DOCTOR FORM -->
        <form class="auth-form hidden" id="form-doctor" method="POST" action="<?= BASE_URL ?>/api/login.php">
            <input type="hidden" name="role" value="doctor">
            <input type="text" name="doctor_id" placeholder="Doctor ID (e.g. D0001)" required autocomplete="off">
            <select name="department" required>
                <option value="">-- Select Department --</option>
                <option>General Medicine</option>
                <option>Cardiology</option>
                <option>Pediatrics</option>
                <option>Surgery</option>
                <option>Obstetrics & Gynecology</option>
                <option>Orthopedics</option>
                <option>Neurology</option>
                <option>Dermatology</option>
                <option>Psychiatry</option>
                <option>Emergency Medicine</option>
                <option>Radiology</option>
                <option>Oncology</option>
            </select>
            <button type="submit" class="auth-btn">Sign in as Doctor</button>
        </form>

        <!-- OTHER STAFF FORM -->
        <form class="auth-form hidden" id="form-staff" method="POST" action="<?= BASE_URL ?>/api/login.php">
            <input type="hidden" name="role" value="staff">
            <select name="staff_role" required>
                <option value="">-- Select your role --</option>
                <option value="receptionist">Receptionist</option>
                <option value="nurse">Nurse</option>
                <option value="pharmacist">Pharmacist</option>
                <option value="lab">Lab Technician</option>
                <option value="radiologist">Radiologist</option>
                <option value="inventory">Inventory Manager</option>
                <option value="triage">ER Triage Officer</option>
            </select>
            <input type="text" name="staff_id" placeholder="Staff ID (e.g. R0001, N0001, PH001...)" required autocomplete="off">
            <button type="submit" class="auth-btn">Sign in</button>
        </form>

        <p class="auth-switch" style="margin-top:1.5rem;">
            Administrator? <a href="<?= BASE_URL ?>/admin_login.php">Click here</a>
        </p>
    </div>
</div>

<script>
// Role-tab switching
document.querySelectorAll('.auth-role-tabs button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.auth-role-tabs button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const role = btn.dataset.role;
        document.querySelectorAll('.auth-form').forEach(f => f.classList.add('hidden'));
        document.getElementById('form-' + role).classList.remove('hidden');
    });
});
</script>

</body>
</html>
