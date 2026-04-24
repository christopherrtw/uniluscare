<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('admin');
$PAGE_TITLE = 'Admin Dashboard'; $ACTIVE_NAV = 'dashboard';

$stats = [
    'patients'  => $conn->query("SELECT COUNT(*) c FROM patients")->fetch_assoc()['c'],
    'doctors'   => $conn->query("SELECT COUNT(*) c FROM doctors")->fetch_assoc()['c'],
    'appts'     => $conn->query("SELECT COUNT(*) c FROM appointments")->fetch_assoc()['c'],
    'today'     => $conn->query("SELECT COUNT(*) c FROM appointments WHERE appointment_date=CURDATE()")->fetch_assoc()['c'],
    'revenue'   => $conn->query("SELECT SUM(total) t FROM invoices WHERE payment_status='paid'")->fetch_assoc()['t'] ?: 0,
    'pending'   => $conn->query("SELECT SUM(total) t FROM invoices WHERE payment_status!='paid'")->fetch_assoc()['t'] ?: 0,
    'lowStock'  => $conn->query("SELECT COUNT(*) c FROM medicines WHERE stock_quantity <= reorder_level")->fetch_assoc()['c'],
    'emergencies' => $conn->query("SELECT COUNT(*) c FROM triage_cases WHERE status='active'")->fetch_assoc()['c'],
];

include __DIR__ . '/../../includes/layout.php';
?>
<div class="stats-grid mb-3">
    <div class="stat-card"><div class="label">Total Patients</div><div class="value"><?= $stats['patients'] ?></div></div>
    <div class="stat-card accent"><div class="label">Total Doctors</div><div class="value"><?= $stats['doctors'] ?></div></div>
    <div class="stat-card success"><div class="label">Today's Appointments</div><div class="value"><?= $stats['today'] ?></div></div>
    <div class="stat-card"><div class="label">Total Appointments</div><div class="value"><?= $stats['appts'] ?></div></div>
    <div class="stat-card success"><div class="label">Revenue (Paid)</div><div class="value">K<?= number_format($stats['revenue'],0) ?></div></div>
    <div class="stat-card danger"><div class="label">Outstanding</div><div class="value">K<?= number_format($stats['pending'],0) ?></div></div>
    <div class="stat-card danger"><div class="label">Low Stock Alerts</div><div class="value"><?= $stats['lowStock'] ?></div></div>
    <div class="stat-card accent"><div class="label">Active Emergencies</div><div class="value"><?= $stats['emergencies'] ?></div></div>
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-title">📊 Quick Actions</div>
        <div style="display:flex;flex-direction:column;gap:0.6rem;">
            <a href="<?= BASE_URL ?>/pages/admin/users.php" class="btn btn-primary">👥 Manage Users</a>
            <a href="<?= BASE_URL ?>/pages/admin/analytics.php" class="btn btn-primary">📈 View Analytics</a>
            <a href="<?= BASE_URL ?>/pages/admin/backup.php" class="btn btn-outline">💾 Backup Database</a>
            <a href="<?= BASE_URL ?>/pages/admin/settings.php" class="btn btn-outline">⚙️ System Settings</a>
        </div>
    </div>
    <div class="card">
        <div class="card-title">🏥 Hospital Overview</div>
        <p><strong>System:</strong> UnilusCare HMS v1.0</p>
        <p><strong>Compliance:</strong> HPCZ-aligned</p>
        <p><strong>Encryption:</strong> Data encrypted at rest & in transit</p>
        <p><strong>Bed Occupancy:</strong> 68% (simulated)</p>
        <p><strong>Avg. Wait Time:</strong> 18 minutes</p>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
