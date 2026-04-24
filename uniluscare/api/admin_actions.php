<?php
require_once __DIR__.'/../config/db.php';
requireRole('admin');
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$back = $_SERVER['HTTP_REFERER'] ?? BASE_URL.'/pages/admin/dashboard.php';

switch ($action) {
case 'delete_user':
    $tbl = preg_replace('/[^a-z_]/','',$_GET['table']);
    $col = preg_replace('/[^a-z_]/','',$_GET['col']);
    $id = $_GET['id'];
    $allowed = ['doctors','nurses','receptionists','pharmacists','lab_technicians','radiologists','inventory_managers','triage_officers'];
    if (in_array($tbl,$allowed)) {
        $stmt = $conn->prepare("DELETE FROM $tbl WHERE $col = ?");
        $stmt->bind_param('s',$id); $stmt->execute();
    }
    header("Location: $back?flash=User+disabled"); exit;

case 'backup':
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="uniluscare_backup_'.date('Ymd_His').'.sql"');
    $tables = $conn->query("SHOW TABLES");
    echo "-- UnilusCare Database Backup — ".date('c')."\n\n";
    while ($t = $tables->fetch_row()) {
        $tn = $t[0];
        $create = $conn->query("SHOW CREATE TABLE `$tn`")->fetch_row();
        echo "DROP TABLE IF EXISTS `$tn`;\n".$create[1].";\n\n";
        $rows = $conn->query("SELECT * FROM `$tn`");
        while ($r = $rows->fetch_assoc()) {
            $vals = array_map(function($v) use ($conn) {
                return $v === null ? 'NULL' : "'".$conn->real_escape_string($v)."'";
            }, array_values($r));
            echo "INSERT INTO `$tn` VALUES (".implode(',',$vals).");\n";
        }
        echo "\n";
    }
    exit;

case 'restore':
    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== 0) {
        header("Location: $back?flash=Upload+failed"); exit;
    }
    $sql = file_get_contents($_FILES['sql_file']['tmp_name']);
    $conn->multi_query($sql);
    while ($conn->more_results()) $conn->next_result();
    header("Location: $back?flash=Database+restored"); exit;

default:
    header("Location: $back"); exit;
}
