<?php
require_once __DIR__.'/../../config/db.php';
requireRole('admin');
$PAGE_TITLE='Analytics'; $ACTIVE_NAV='analytics';

$byDept = $conn->query("SELECT d.department, COUNT(a.appointment_id) c FROM doctors d LEFT JOIN appointments a ON d.doctor_id=a.doctor_id GROUP BY d.department");
$revByMonth = $conn->query("SELECT DATE_FORMAT(created_at,'%Y-%m') m, SUM(total) t FROM invoices WHERE payment_status='paid' GROUP BY m ORDER BY m DESC LIMIT 6");
$topMeds = $conn->query("SELECT medicine_name, COUNT(*) c FROM prescription_items GROUP BY medicine_name ORDER BY c DESC LIMIT 10");
$reasonVisit = $conn->query("SELECT reason_visit, COUNT(*) c FROM patients GROUP BY reason_visit");
include __DIR__.'/../../includes/layout.php';
?>
<div class="card mb-3">
    <div class="card-title">📊 Appointments by Department</div>
    <table class="data-table">
        <tr><th>Department</th><th>Appointments</th></tr>
        <?php while($r=$byDept->fetch_assoc()):?>
            <tr><td><?=e($r['department'])?></td><td><?=$r['c']?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<div class="card mb-3">
    <div class="card-title">💰 Revenue (Last 6 Months)</div>
    <table class="data-table">
        <tr><th>Month</th><th>Revenue</th></tr>
        <?php while($r=$revByMonth->fetch_assoc()):?>
            <tr><td><?=e($r['m'])?></td><td>K<?=number_format($r['t'],2)?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<div class="content-grid">
    <div class="card">
        <div class="card-title">💊 Most Prescribed Medicines</div>
        <table class="data-table">
            <tr><th>Medicine</th><th>Count</th></tr>
            <?php while($r=$topMeds->fetch_assoc()):?>
                <tr><td><?=e($r['medicine_name'])?></td><td><?=$r['c']?></td></tr>
            <?php endwhile;?>
        </table>
    </div>
    <div class="card">
        <div class="card-title">🚑 Reasons for Visit</div>
        <table class="data-table">
            <tr><th>Reason</th><th>Count</th></tr>
            <?php while($r=$reasonVisit->fetch_assoc()):?>
                <tr><td><?=e($r['reason_visit']?:'—')?></td><td><?=$r['c']?></td></tr>
            <?php endwhile;?>
        </table>
    </div>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
