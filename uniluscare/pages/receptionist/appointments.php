<?php
require_once __DIR__.'/../../config/db.php';
requireRole('receptionist');
$PAGE_TITLE='Appointments'; $ACTIVE_NAV='appointments';
$flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid = $_POST['patient_id']; $did = $_POST['doctor_id'];
    $date = $_POST['appointment_date']; $time = $_POST['appointment_time'];
    $reason = $_POST['reason']; $type = $_POST['type'] ?? 'physical';
    $room = 'uniluscare-'.uniqid();
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id,doctor_id,appointment_date,appointment_time,reason,type,status,room_id) VALUES (?,?,?,?,?,?,'confirmed',?)");
    $stmt->bind_param('sssssss',$pid,$did,$date,$time,$reason,$type,$room);
    $stmt->execute();
    // notify patient
    $n = $conn->prepare("INSERT INTO notifications (recipient_id,recipient_role,title,message,type) VALUES (?,'patient','Appointment Scheduled','Your appointment has been scheduled by reception.','appointment')");
    $n->bind_param('s',$pid); $n->execute();
    header("Location: ".BASE_URL."/pages/receptionist/appointments.php?flash=Appointment+scheduled"); exit;
}

$all = $conn->query("SELECT a.*, p.full_name AS pname, d.full_name AS dname FROM appointments a LEFT JOIN patients p ON a.patient_id=p.patient_id LEFT JOIN doctors d ON a.doctor_id=d.doctor_id ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 50");
$patients = $conn->query("SELECT patient_id, full_name FROM patients ORDER BY full_name");
$docs = $conn->query("SELECT doctor_id, full_name, department FROM doctors ORDER BY full_name");
include __DIR__.'/../../includes/layout.php';
?>
<?php if($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>
<div class="card mb-3">
    <div class="card-title">➕ Schedule New Appointment (walk-in or phone)</div>
    <form method="POST">
        <div class="form-row">
            <div class="form-group"><label>Patient</label>
                <select name="patient_id" required>
                    <option value="">-- Select Patient --</option>
                    <?php while($p=$patients->fetch_assoc()):?>
                        <option value="<?=e($p['patient_id'])?>"><?=e($p['full_name'])?> (<?=e($p['patient_id'])?>)</option>
                    <?php endwhile;?>
                </select>
            </div>
            <div class="form-group"><label>Doctor</label>
                <select name="doctor_id" required>
                    <option value="">-- Select Doctor --</option>
                    <?php while($d=$docs->fetch_assoc()):?>
                        <option value="<?=e($d['doctor_id'])?>"><?=e($d['full_name'])?> — <?=e($d['department'])?></option>
                    <?php endwhile;?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Date</label><input type="date" name="appointment_date" required min="<?=date('Y-m-d')?>"></div>
            <div class="form-group"><label>Time</label><input type="time" name="appointment_time" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Type</label><select name="type"><option value="physical">In-person</option><option value="virtual">Virtual</option></select></div>
            <div class="form-group"><label>Reason</label><input type="text" name="reason" required></div>
        </div>
        <button class="btn btn-primary" type="submit">Schedule</button>
    </form>
</div>
<div class="card">
    <div class="card-title">📅 Recent Appointments</div>
    <table class="data-table">
        <tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Type</th><th>Status</th><th>Action</th></tr>
        <?php while($a=$all->fetch_assoc()):?>
            <tr><td><?=date('d M',strtotime($a['appointment_date']))?></td>
            <td><?=date('H:i',strtotime($a['appointment_time']))?></td>
            <td><?=e($a['pname'])?></td><td><?=e($a['dname']?:'—')?></td>
            <td><?=e($a['type'])?></td>
            <td><span class="badge badge-<?=e($a['status'])?>"><?=e($a['status'])?></span></td>
            <td><?php if($a['status']!=='cancelled'):?><a href="<?=BASE_URL?>/api/reception_actions.php?action=cancel_appt&id=<?=(int)$a['appointment_id']?>" data-confirm="Cancel this appointment?" class="btn btn-sm btn-danger">Cancel</a><?php endif;?></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
