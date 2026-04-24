// UnilusCare — front-end helpers
document.addEventListener('click', (e) => {
    if (e.target.matches('[data-confirm]')) {
        if (!confirm(e.target.dataset.confirm)) { e.preventDefault(); }
    }
});

// Live clock in the topbar if present
function tickClock() {
    document.querySelectorAll('[data-clock]').forEach(el => {
        el.textContent = new Date().toLocaleString();
    });
}
setInterval(tickClock, 1000);

// Auto-close alerts after 5s
document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => a.style.display = 'none', 7000);
});
