<?php
require_once __DIR__.'/../../config/db.php';
requireRole('admin');
$PAGE_TITLE='Security & HPCZ'; $ACTIVE_NAV='security';
include __DIR__.'/../../includes/layout.php';
?>
<div class="card mb-3">
    <div class="card-title">🔒 HPCZ Compliance Overview</div>
    <ul style="line-height:2;padding-left:1.5rem;">
        <li>✅ Patient data encrypted at rest (AES-256) and in transit (TLS 1.3)</li>
        <li>✅ Role-based access control enforced on every page</li>
        <li>✅ Session timeout after 30 minutes of inactivity</li>
        <li>✅ Admin password hashed with bcrypt</li>
        <li>✅ Audit logging enabled for all data mutations</li>
        <li>✅ Data retention in accordance with Zambian Health Practitioners Council regulations</li>
        <li>✅ Secure file uploads with strict MIME validation</li>
        <li>✅ Two-factor authentication available for admin (recommended)</li>
    </ul>
</div>
<div class="card">
    <div class="card-title">🛡️ Security Recommendations</div>
    <p>Regularly rotate admin passwords. Review user access quarterly. Export audit logs monthly.</p>
</div>
<?php include __DIR__.'/../../includes/footer.php';?>
