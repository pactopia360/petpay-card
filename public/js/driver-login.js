document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.querySelector('[data-password-toggle]');
    const goBackButton = document.querySelector('[data-login-go-back]');

    passwordToggle?.addEventListener('click', () => {
        if (!passwordInput) {
            return;
        }

        const shouldShow = passwordInput.type === 'password';

        passwordInput.type = shouldShow ? 'text' : 'password';
        passwordToggle.textContent = shouldShow ? 'Ocultar' : 'Ver';
        passwordToggle.setAttribute(
            'aria-label',
            shouldShow
                ? 'Ocultar contraseña'
                : 'Mostrar contraseña'
        );
    });

    goBackButton?.addEventListener('click', () => {
        if (window.history.length > 1) {
            window.history.back();
            return;
        }

        window.location.href = '/';
    });
});
