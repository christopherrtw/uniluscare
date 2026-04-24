<?php
require_once __DIR__ . '/config/db.php';

$success = ''; $error = ''; $newId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect fields
    $id_number   = trim($_POST['id_number'] ?? '');
    $id_type     = trim($_POST['id_type'] ?? 'NRC');
    $suffix      = trim($_POST['suffix'] ?? '');
    $full_name   = trim($_POST['full_name'] ?? '');
    $dob         = $_POST['dob'] ?? '';
    $gender      = $_POST['gender'] ?? '';
    $phone       = trim($_POST['phone'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $employer    = trim($_POST['employer'] ?? '');
    $insurance_company = trim($_POST['insurance_company'] ?? '');
    $insurance_number  = trim($_POST['insurance_number'] ?? '');
    $scheme_type       = trim($_POST['scheme_type'] ?? '');
    $reason_visit      = $_POST['reason_visit'] ?? '';

    // Validation
    if (!$id_number || !$full_name || !$dob || !$phone || !$email || !$address) {
        $error = 'Please fill in all required fields.';
    } elseif ($insurance_company && (!$insurance_number || !$scheme_type)) {
        $error = 'If you provide an insurance company, the insurance number and scheme type are required.';
    } else {
        $pid = generatePatientId($conn);
        $createdBy = $_SESSION['user_id'] ?? null;

        $stmt = $conn->prepare("INSERT INTO patients
            (patient_id, id_number, id_type, suffix, full_name, date_of_birth, gender, phone, email, address, employer, insurance_company, insurance_number, scheme_type, reason_visit, created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssssssssssssi',
            $pid, $id_number, $id_type, $suffix, $full_name, $dob, $gender, $phone, $email, $address,
            $employer, $insurance_company, $insurance_number, $scheme_type, $reason_visit, $createdBy);
        if ($stmt->execute()) {
            $success = "Account created successfully. The Patient ID is:";
            $newId = $pid;
        } else {
            $error = 'Could not create the account: ' . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UnilusCare — Register Patient</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .signup-card { max-width: 950px; width: 100%; background: #fff; border-radius: 18px; padding: 2.5rem; box-shadow: 0 25px 70px rgba(0,0,0,0.18); }
        .insurance-group { background: #f7f7fb; border-radius: 10px; padding: 1rem 1.2rem; border: 1px dashed #ccc; margin-top: 0.5rem; }
        .checkbox-group { display: flex; gap: 1.5rem; flex-wrap: wrap; margin-top: 0.5rem; }
        .checkbox-group label { display: flex; align-items: center; gap: 0.5rem; font-weight: 400; }
    </style>
</head>
<body class="auth-body" style="padding:3rem 1rem;">

<h1 class="auth-page-title">Patient Registration</h1>

<div class="signup-card">
    <?php if ($success): ?>
        <div class="alert alert-success">
            <strong><?= e($success) ?></strong>
            <div style="font-size:1.5rem; font-family: var(--font-heading); margin-top:0.5rem; color:var(--primary);"><?= e($newId) ?></div>
            <p class="mt-2">Please save this Patient ID. You will use it to sign in.</p>
            <div class="mt-2">
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">Go to Sign in</a>
                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'receptionist'): ?>
                    <a href="<?= BASE_URL ?>/pages/receptionist/dashboard.php" class="btn btn-outline">Back to Reception</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

        <p class="text-muted mb-3">Register a new patient visiting UnilusCare for the first time. Fields marked * are required.</p>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>ID Type *</label>
                    <select name="id_type" required>
                        <option>NRC</option>
                        <option>Passport</option>
                        <option>Driver's License</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ID Number *</label>
                    <input type="text" name="id_number" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Suffix</label>
                    <select name="suffix">
                        <option value="">-- Select --</option>
                        <option>Mr</option><option>Mrs</option><option>Ms</option>
                        <option>Dr</option><option>Miss</option><option>Prof</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" required max="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">-- Select --</option>
                        <option>Male</option><option>Female</option><option>Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" required placeholder="+260 ...">
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" required>
                </div>
            </div>

            <div class="form-group">
                <label>Physical Address *</label>
                <textarea name="address" required></textarea>
            </div>

            <div class="form-group">
                <label>Name of Employer (optional)</label>
                <input type="text" name="employer">
            </div>

            <div class="insurance-group">
                <h3 style="margin-bottom:0.5rem;color:var(--primary);">Insurance Details</h3>
                <p class="text-muted text-sm mb-2">Required only if you fill in the insurance company.</p>
                <div class="form-row">
                    <div class="form-group">
                        <label>Insurance Company</label>
                        <input type="text" name="insurance_company" id="insuranceCompany">
                    </div>
                    <div class="form-group">
                        <label>Insurance Number</label>
                        <input type="text" name="insurance_number" id="insuranceNumber">
                    </div>
                </div>
                <div class="form-group">
                    <label>Scheme Type</label>
                    <input type="text" name="scheme_type" id="schemeType" placeholder="e.g. NHIMA, Madison, Hollard...">
                </div>
            </div>

            <div class="form-group mt-2">
                <label>Reason for Visit *</label>
                <div class="checkbox-group">
                    <label><input type="radio" name="reason_visit" value="road traffic accident" required> Road Traffic Accident</label>
                    <label><input type="radio" name="reason_visit" value="accident at work"> Accident at Work</label>
                    <label><input type="radio" name="reason_visit" value="accident at home"> Accident at Home</label>
                    <label><input type="radio" name="reason_visit" value="other"> Other</label>
                </div>
            </div>

            <button class="auth-btn" type="submit">Create Account</button>
            <p class="auth-switch">Already have an account? <a href="<?= BASE_URL ?>/index.php">Log in</a></p>
        </form>

        <script>
        // Make insurance details mandatory only when a company is entered
        const ic = document.getElementById('insuranceCompany'),
              inum = document.getElementById('insuranceNumber'),
              scheme = document.getElementById('schemeType');
        ic.addEventListener('input', () => {
            const has = ic.value.trim() !== '';
            inum.required = has; scheme.required = has;
        });
        </script>
    <?php endif; ?>
</div>

</body>
</html>
