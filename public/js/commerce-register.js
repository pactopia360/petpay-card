document.addEventListener('DOMContentLoaded', () => {
    const toggleButtons = document.querySelectorAll('[data-password-toggle]');

    toggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const inputId = button.dataset.passwordToggle;
            const input = document.getElementById(inputId);

            if (!input) {
                return;
            }

            const showPassword = input.type === 'password';

            input.type = showPassword ? 'text' : 'password';
            button.textContent = showPassword ? 'Ocultar' : 'Ver';

            button.setAttribute(
                'aria-label',
                showPassword
                    ? 'Ocultar contraseña'
                    : 'Mostrar contraseña'
            );
        });
    });
});
