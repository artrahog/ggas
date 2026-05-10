document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alertEl) => {
        setTimeout(() => {
            const closeButton = alertEl.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 4000);
    });
});
