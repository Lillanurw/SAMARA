// SAMARA Core JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // Confirmation Dialogs
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const message = button.getAttribute('data-confirm') || 'Apakah Anda yakin ingin melakukan tindakan ini?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
