const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

if (registerBtn && container) {
    registerBtn.addEventListener('click', () => {
        container.classList.add("active");
    });
}

if (loginBtn && container) {
    loginBtn.addEventListener('click', () => {
        container.classList.remove("active");
    });
}

const passwordInput = document.getElementById('password');
const togglePassword = document.querySelector('.toggle-password');

if (passwordInput && togglePassword) {
    togglePassword.addEventListener('click', () => {
        const isVisible = passwordInput.type === 'text';
        passwordInput.type = isVisible ? 'password' : 'text';
        togglePassword.setAttribute('aria-pressed', String(!isVisible));
        togglePassword.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
        togglePassword.setAttribute('title', isVisible ? 'Show password' : 'Hide password');
    });
}