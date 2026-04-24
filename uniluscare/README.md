# UnilusCare — Hospital Management System

A complete PHP + MySQL hospital management system with 10 role-based portals, AI-assisted diagnosis, fully working Jitsi-powered telemedicine, pharmacy/inventory/lab/radiology modules, billing with insurance, and HPCZ-aligned data practices.

---

## 🏥 Features

- **10 user roles** — Patient, Doctor, Admin, Receptionist, Nurse, Pharmacist, Lab Technician, Radiologist, Inventory Manager, ER Triage Officer
- **Login matches the reference image** — two-panel card, stethoscope illustration, three-dot menu that opens the admin login (no Google/Facebook sign-up)
- **Patients log in with their Patient ID only**; doctors with their Doctor ID + department dropdown; only the admin has a password
- **Reception-only patient registration** with conditional insurance validation
- **Real-time vitals monitor** (simulated live fluctuations), full EHR, prescriptions with PDF download
- **AI symptom checker** and **imaging analysis** for both patient and doctor
- **Telemedicine video calls** powered by Jitsi Meet — works out of the box, no server configuration
- **File upload/download** — medical imaging (X-ray/MRI/CT), lab result files
- **Streamlined billing** — cash / card / insurance with printable receipts
- **Insurance claim tracking** and **low-stock / expiry alerts**
- **Notifications system** — appointments, lab results, medications, emergencies
- **Database backup & restore** from the admin panel

---

## ⚙️ Setup Instructions

### Requirements
- XAMPP, WAMP, MAMP, or any PHP 7.4+ and MySQL 5.7+ environment
- A modern browser

### Installation

1. **Copy the project** into your web root:
   - XAMPP: `C:\xampp\htdocs\uniluscare\`
   - WAMP: `C:\wamp64\www\uniluscare\`
   - Linux: `/var/www/html/uniluscare/`

2. **Start MySQL and Apache** in your XAMPP / WAMP control panel.

3. **Create the database**:
   - Open `http://localhost/phpmyadmin`
   - Click "Import"
   - Select `sql/uniluscare.sql`
   - Click "Go"

4. **Configure credentials** (only if different from XAMPP defaults):
   - Edit `config/db.php`
   - Update `DB_USER` and `DB_PASS` if needed

5. **Open the system**:
   - `http://localhost/uniluscare/`

---

## 🔑 Default Login Credentials

| Role | ID / Username | Password |
|------|---------------|----------|
| **Admin** (three-dot menu) | `admin` | `Admin@123` |
| Patient | `P000001` or `P000002` | — |
| Doctor | `D0001` through `D0005` + any department | — |
| Receptionist | `R0001` | — |
| Nurse | `N0001` | — |
| Pharmacist | `PH001` | — |
| Lab Technician | `L0001` | — |
| Radiologist | `RD001` | — |
| Inventory Manager | `IM001` | — |
| ER Triage Officer | `T0001` | — |

---

## 📹 How the Telemedicine Module Works

The video consultation uses **Jitsi Meet** (`meet.jit.si`) via the public External API, embedded in an iframe. This is the simplest 100% working approach:

- **No server setup required** — Jitsi handles WebRTC signalling, TURN, STUN, and media relay.
- When a patient clicks "Join Call" on a virtual appointment, they're sent to `pages/patient/video_call.php` which embeds a Jitsi room named with the appointment ID (e.g. `uniluscare-42`).
- The doctor, on their dashboard, sees the same appointment with a "Join" button → they enter the same room → video call established instantly.
- The browser will ask for camera/microphone permissions on first use.
- **Requirement:** the user needs an internet connection and a camera-enabled device. Works on desktop and mobile.

### To self-host Jitsi (optional, for full data sovereignty):
1. Follow the guide at https://jitsi.github.io/handbook/docs/devops-guide/devops-guide-quickstart
2. Replace `meet.jit.si` in `pages/patient/video_call.php` and `pages/doctor/video_call.php` with your own Jitsi domain.

---

## 📁 Project Structure

```
uniluscare/
├── index.php                    ← Main login (matches reference image)
├── admin_login.php              ← Admin-only password login
├── signup.php                   ← Patient registration
├── logout.php
├── config/db.php                ← Database connection + helpers
├── sql/uniluscare.sql           ← Full schema + sample data
├── assets/
│   ├── css/style.css            ← Main stylesheet
│   ├── css/auth.css             ← Login/signup styling
│   ├── js/app.js
│   └── uploads/                 ← medical, lab, imaging, profiles
├── includes/
│   ├── layout.php               ← Role-aware sidebar & topbar
│   └── footer.php
├── api/                         ← All action handlers
│   ├── login.php
│   ├── patient_actions.php
│   ├── doctor_actions.php
│   ├── admin_actions.php
│   ├── reception_actions.php
│   ├── prescription_pdf.php
│   └── invoice_pdf.php
└── pages/
    ├── patient/   (dashboard, ai_diagnosis, records, prescriptions, telemedicine, video_call, billing, engagement)
    ├── doctor/    (dashboard, ai_diagnosis, emergency, laboratory, pharmacy, imaging, telemedicine, video_call, billing, icd10, reports, patient_view)
    ├── admin/     (dashboard, users, analytics, inventory, billing, settings, backup, security)
    ├── receptionist/ (dashboard, register, appointments, checkin, billing)
    ├── nurse/     (dashboard, vitals, medication, notes)
    ├── pharmacist/ (dashboard, prescriptions, inventory, billing)
    ├── lab/       (dashboard, tests, results)
    ├── radiologist/ (dashboard, imaging, upload)
    ├── inventory/ (dashboard, items, orders, expiry)
    └── triage/    (dashboard, register, cases)
```

---

## 🛡️ Security & HPCZ Compliance

- Admin password hashed with bcrypt
- Role-based access enforced on every page via `requireRole()`
- All form data passed through `htmlspecialchars()` via `e()` helper
- Prepared statements used for all user-supplied inputs
- Session management with PHP's built-in session handling
- File uploads sanitised — original filenames rewritten
- Audit fields (`created_at`, `created_by`) on all key tables

---

## 🔧 Customisation Tips

- **Change hospital name/branding**: Edit `SITE_NAME` in `config/db.php` and the brand in `includes/layout.php`
- **Change colour scheme**: Edit CSS variables at the top of `assets/css/style.css`
- **Adjust logo**: Replace the SVG in the `.sidebar-brand` in `includes/layout.php`
- **Change currency**: Search for `K` (Kwacha symbol) in the codebase
- **Backup schedule**: Admin → Backup & Restore — download backups manually or via cron

---

## 💡 Testing the Full Flow

1. **Sign in as Admin** (`admin` / `Admin@123`) — check hospital analytics
2. **Sign in as Receptionist** (`R0001`) — register a new patient from `signup.php`
3. **Sign in as the new patient** with the assigned ID → book an appointment with `D0001`
4. **Sign in as Nurse** (`N0001`) → record vitals for the patient
5. **Sign in as Dr Dzimadzi** (`D0001` + General Medicine) → open the patient's record → write a prescription → request a lab test
6. **Sign in as Lab Tech** (`L0001`) → submit the result
7. **Sign in as Pharmacist** (`PH001`) → dispense the prescription
8. **Book a virtual appointment** → doctor and patient both join the Jitsi call
9. **Sign in as Triage Officer** (`T0001`) → register an emergency case

Everything is wired up end-to-end.

---

## 📝 Known Limitations & Production Notes

- Default credentials are set for demo purposes — **change them before going live**
- For production, add HTTPS, stronger session expiry, CSRF tokens, rate limiting, and a proper logging framework
- The AI symptom checker is a rule-based demo — for clinical use, integrate a validated service
- For DICOM-compliant PACS, integrate Orthanc or dcm4che alongside the current imaging module
- For a self-hosted telemedicine setup, replace Jitsi public instance with your own

---

## 📧 Support

Need to extend or customise? The codebase is heavily commented and structured so new features slot in cleanly. Each role has its own folder under `pages/` and its own action API under `api/`.

Happy building! 🏥
