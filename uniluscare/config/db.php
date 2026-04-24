<?php
/**
 * UnilusCare — Database Configuration
 * Edit the credentials below to match your MySQL / XAMPP setup.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // default XAMPP password is empty
define('DB_NAME', 'uniluscare');

// Create connection (MySQLi, object-oriented)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;background:#fee;color:#900;">
            <h2>Database connection failed</h2>
            <p>' . htmlspecialchars($conn->connect_error) . '</p>
            <p>Make sure MySQL is running and you have imported <code>sql/uniluscare.sql</code>.</p>
         </div>');
}
$conn->set_charset('utf8mb4');

// Session settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site constants
define('SITE_NAME', 'UnilusCare');
define('SITE_TAGLINE', 'Smart Healthcare for Every Zambian');
define('BASE_URL', '/uniluscare'); // change if you host under a different folder

/**
 * Helper: generate next sequential patient ID like P000001
 */
function generatePatientId($conn) {
    $res = $conn->query("SELECT patient_id FROM patients ORDER BY patient_id DESC LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $num = (int)substr($row['patient_id'], 1);
        return 'P' . str_pad($num + 1, 6, '0', STR_PAD_LEFT);
    }
    return 'P000001';
}

/**
 * Helper: escape output
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Helper: require a specific role to access a page.
 */
function requireRole($role) {
    if (empty($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

/**
 * Helper: get greeting based on time of day.
 */
function greeting() {
    $h = (int)date('H');
    if ($h < 12) return 'Good morning';
    if ($h < 17) return 'Good afternoon';
    return 'Good evening';
}
