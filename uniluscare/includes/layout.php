<?php
/**
 * Shared sidebar & topbar for every role.
 * Set $PAGE_TITLE and $ACTIVE_NAV in the including page.
 */
if (empty($_SESSION['role'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
$role = $_SESSION['role'];
$fullName = $_SESSION['full_name'] ?? '';
$suffix = $_SESSION['suffix'] ?? '';

// Navigation per role
$nav = [
    'patient' => [
        ['dashboard',    'Dashboard',         '📊'],
        ['ai_diagnosis', 'AI Diagnosis',      '🧠'],
        ['records',      'Personal Records',  '📁'],
        ['prescriptions','Prescriptions',     '💊'],
        ['telemedicine', 'Telemedicine',      '📹'],
        ['billing',      'Billing',           '💳'],
        ['engagement',   'Patient Engagement','📚'],
    ],
    'doctor' => [
        ['dashboard',    'Dashboard',          '📊'],
        ['ai_diagnosis', 'AI Diagnosis',       '🧠'],
        ['emergency',    'Emergency',          '🚑'],
        ['laboratory',   'Laboratory',         '🔬'],
        ['pharmacy',     'Pharmacy',           '💊'],
        ['imaging',      'Imaging / PACS',     '🩻'],
        ['telemedicine', 'Telemedicine',       '📹'],
        ['billing',      'Billing',            '💳'],
        ['icd10',        'ICD-10 Codes',       '📋'],
        ['reports',      'Reports',            '📈'],
    ],
    'admin' => [
        ['dashboard',  'Dashboard',          '📊'],
        ['users',      'User Management',    '👥'],
        ['analytics',  'Analytics',          '📈'],
        ['inventory',  'Resources',          '📦'],
        ['billing',    'Billing & Insurance','💳'],
        ['settings',   'System Settings',    '⚙️'],
        ['backup',     'Backup & Restore',   '💾'],
        ['security',   'Security & HPCZ',    '🔒'],
    ],
    'receptionist' => [
        ['dashboard',    'Dashboard',       '📊'],
        ['register',     'Register Patient','🆕'],
        ['appointments', 'Appointments',    '📅'],
        ['checkin',      'Check-in/out',    '🚪'],
        ['billing',      'Billing',         '💳'],
    ],
    'nurse' => [
        ['dashboard',  'Dashboard',       '📊'],
        ['vitals',     'Record Vitals',   '❤️'],
        ['medication', 'Medications',     '💊'],
        ['notes',      'Nursing Notes',   '📝'],
    ],
    'pharmacist' => [
        ['dashboard',     'Dashboard',      '📊'],
        ['prescriptions', 'Prescriptions',  '💊'],
        ['inventory',     'Inventory',      '📦'],
        ['billing',       'Medicine Bills', '💳'],
    ],
    'lab' => [
        ['dashboard', 'Dashboard',       '📊'],
        ['tests',     'Test Requests',   '🧪'],
        ['results',   'Submit Results',  '📝'],
    ],
    'radiologist' => [
        ['dashboard', 'Dashboard',    '📊'],
        ['imaging',   'Imaging',      '🩻'],
        ['upload',    'Upload Report','⬆️'],
    ],
    'inventory' => [
        ['dashboard', 'Dashboard',        '📊'],
        ['items',     'Items & Stock',    '📦'],
        ['orders',    'Purchase Orders',  '📝'],
        ['expiry',    'Expiry Tracking',  '⏰'],
    ],
    'triage' => [
        ['dashboard', 'Dashboard',       '📊'],
        ['register',  'Rapid Register',  '🆕'],
        ['cases',     'Active Cases',    '🚑'],
    ],
];

$roleLabels = [
    'patient' => 'Patient', 'doctor' => 'Doctor', 'admin' => 'Administrator',
    'receptionist' => 'Receptionist', 'nurse' => 'Nurse', 'pharmacist' => 'Pharmacist',
    'lab' => 'Lab Technician', 'radiologist' => 'Radiologist',
    'inventory' => 'Inventory Manager', 'triage' => 'ER Triage Officer',
];

$greet = greeting();
$nameToShow = ($suffix ? $suffix . ' ' : '') . $fullName;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($PAGE_TITLE ?? 'Dashboard') ?> — UnilusCare</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="app-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="dot"></span> UnilusCare
        </div>
        <div style="padding: 0 0.5rem 1rem; font-size:0.8rem; color: rgba(255,255,255,0.65); letter-spacing: 0.05em; text-transform: uppercase;">
            <?= e($roleLabels[$role] ?? $role) ?>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($nav[$role] ?? [] as $item):
                $slug = $item[0]; $label = $item[1]; $icon = $item[2];
                $isActive = ($ACTIVE_NAV ?? '') === $slug ? 'active' : '';
                ?>
                <a href="<?= BASE_URL ?>/pages/<?= $role ?>/<?= $slug ?>.php" class="<?= $isActive ?>">
                    <span><?= $icon ?></span> <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">👤 <?= e($nameToShow ?: ($_SESSION['user_id'] ?? '')) ?></div>
            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm" style="background:rgba(255,255,255,0.15);color:#fff;width:100%;text-align:center;">🚪 Log out</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div style="display:flex; align-items:center; gap:1rem;">
                <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                <div class="greeting">
                    <?= e($greet) ?>, <?= e($nameToShow ?: ucfirst($role)) ?> 👋
                    <small><?= date('l, F j, Y — H:i') ?></small>
                </div>
            </div>
        </div>
