<?php
require_once __DIR__ . '/../../config/db.php';
requireRole('admin');
$PAGE_TITLE='User Management'; $ACTIVE_NAV='users';
$flash = $_GET['flash'] ?? '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $role = $_POST['role'];
    $id = trim($_POST['user_id']);
    $name = trim($_POST['full_name']);
    $phone = trim($_POST['phone'] ?? '');
    $tables = [
        'doctor'=>['doctors','doctor_id'],'nurse'=>['nurses','nurse_id'],
        'receptionist'=>['receptionists','receptionist_id'],'pharmacist'=>['pharmacists','pharmacist_id'],
        'lab'=>['lab_technicians','lab_tech_id'],'radiologist'=>['radiologists','radiologist_id'],
        'inventory'=>['inventory_managers','inv_manager_id'],'triage'=>['triage_officers','triage_id']];
    if (isset($tables[$role])) {
        [$t,$c] = $tables[$role];
        if ($role==='doctor') {
            $dept = $_POST['department']??''; $spec=$_POST['specialization']??'';
            $stmt = $conn->prepare("INSERT INTO doctors ($c,full_name,department,specialization,phone) VALUES (?,?,?,?,?)");
            $stmt->bind_param('sssss',$id,$name,$dept,$spec,$phone);
        } else {
            $stmt = $conn->prepare("INSERT INTO $t ($c,full_name,phone) VALUES (?,?,?)");
            $stmt->bind_param('sss',$id,$name,$phone);
        }
        $stmt->execute();
        header("Location: ".BASE_URL."/pages/admin/users.php?flash=User+added"); exit;
    }
}

$tables = [
    ['Doctors','doctors','doctor_id','department'],
    ['Receptionists','receptionists','receptionist_id',null],
    ['Nurses','nurses','nurse_id','ward'],
    ['Pharmacists','pharmacists','pharmacist_id',null],
    ['Lab Technicians','lab_technicians','lab_tech_id',null],
    ['Radiologists','radiologists','radiologist_id',null],
    ['Inventory Managers','inventory_managers','inv_manager_id',null],
    ['Triage Officers','triage_officers','triage_id',null],
];
include __DIR__.'/../../includes/layout.php';
?>
<?php if ($flash):?><div class="alert alert-success"><?=e($flash)?></div><?php endif;?>

<div class="card mb-3">
    <div class="card-title">➕ Add New Staff Member</div>
    <form method="POST">
        <div class="form-row">
            <div class="form-group"><label>Role</label>
                <select name="role" id="roleSel" required>
                    <option value="doctor">Doctor</option>
                    <option value="receptionist">Receptionist</option>
                    <option value="nurse">Nurse</option>
                    <option value="pharmacist">Pharmacist</option>
                    <option value="lab">Lab Technician</option>
                    <option value="radiologist">Radiologist</option>
                    <option value="inventory">Inventory Manager</option>
                    <option value="triage">ER Triage Officer</option>
                </select>
            </div>
            <div class="form-group"><label>Unique Staff ID</label><input type="text" name="user_id" required placeholder="e.g. D0010 / N0005"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Full Name</label><input type="text" name="full_name" required></div>
            <div class="form-group"><label>Phone</label><input type="tel" name="phone"></div>
        </div>
        <div id="docFields">
            <div class="form-row">
                <div class="form-group"><label>Department</label>
                    <select name="department">
                        <option>General Medicine</option><option>Cardiology</option><option>Pediatrics</option>
                        <option>Surgery</option><option>Obstetrics & Gynecology</option><option>Orthopedics</option>
                        <option>Neurology</option><option>Dermatology</option><option>Psychiatry</option>
                        <option>Emergency Medicine</option><option>Radiology</option><option>Oncology</option>
                    </select>
                </div>
                <div class="form-group"><label>Specialization</label><input type="text" name="specialization"></div>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Add Staff</button>
    </form>
</div>

<?php foreach ($tables as [$lbl,$tbl,$col,$extra]):
    $rows = $conn->query("SELECT * FROM $tbl ORDER BY $col"); ?>
<div class="card mb-3">
    <div class="card-title"><?=$lbl?> (<?=$rows->num_rows?>)</div>
    <table class="data-table">
        <tr><th>ID</th><th>Name</th><?php if ($extra):?><th><?=ucfirst($extra)?></th><?php endif;?><th>Phone</th><th>Action</th></tr>
        <?php while ($r=$rows->fetch_assoc()):?>
            <tr><td><?=e($r[$col])?></td><td><?=e($r['full_name'])?></td>
            <?php if($extra):?><td><?=e($r[$extra]??'')?></td><?php endif;?>
            <td><?=e($r['phone']??'')?></td>
            <td><a href="<?=BASE_URL?>/api/admin_actions.php?action=delete_user&table=<?=$tbl?>&col=<?=$col?>&id=<?=urlencode($r[$col])?>" data-confirm="Disable this user?" class="btn btn-sm btn-danger">🚫 Disable</a></td></tr>
        <?php endwhile;?>
    </table>
</div>
<?php endforeach;?>

<script>
document.getElementById('roleSel').addEventListener('change',e=>{
    document.getElementById('docFields').style.display = e.target.value==='doctor'?'':'none';
});
</script>
<?php include __DIR__.'/../../includes/footer.php';?>
