// Toggle password visibility
const togglePassword = document.getElementById('togglePassword');
if (togglePassword) {
    togglePassword.addEventListener('click', function () {
        const passwordInput = document.getElementById('login-password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.style.display = 'none';
            eyeOffIcon.style.display = 'inline';
        } else {
            passwordInput.type = 'password';
            eyeIcon.style.display = 'inline';
            eyeOffIcon.style.display = 'none';
        }
    });
}

// Fungsi untuk menangani login
function handleLogin(event) {
    event.preventDefault();

    const submitBtn = event.target.querySelector('button[type="submit"]');

    // Prevent double submit
    if (submitBtn.disabled) return;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Loading...';

    const username  = document.getElementById('login-username').value.trim();
    const password  = document.getElementById('login-password').value;
    const csrfToken = document.getElementById('csrf_token').value;

    fetch('php/proses_login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}&csrf_token=${encodeURIComponent(csrfToken)}`,
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            window.showSuccessToast('Anda Berhasil Login!', "Login Success!");
            setTimeout(() => {
                window.location.href = 'index';
            }, 1000);
        } else {
            window.showErrorToast(data.message || 'Gagal login. Silakan cek kredensial Anda.', 'Login Failed');
            // Re-enable button on failure so user can try again
            submitBtn.disabled = false;
            submitBtn.textContent = 'Login';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.showErrorToast(error.message || 'A system error occurred', 'Error');
        // Re-enable button on error
        submitBtn.disabled = false;
        submitBtn.textContent = 'Login';
    });
}

// Tambahkan event listener untuk form login
document.getElementById('loginForm').addEventListener('submit', handleLogin);