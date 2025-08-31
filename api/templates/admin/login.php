<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="card shadow" style="width: 100%; max-width: 400px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h1 class="display-6 fw-bold text-primary">🍺</h1>
                <h2 class="h4 text-muted">BC Marl Drinks</h2>
                <p class="text-muted">Admin-Bereich</p>
            </div>

            <div id="loginError" class="alert alert-danger d-none" role="alert">
                Ungültige Anmeldedaten
            </div>

            <form id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label">E-Mail</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="mobile" class="form-label">Mobilnummer</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-phone"></i>
                        </span>
                        <input type="tel" class="form-control" id="mobile" name="mobile" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="pin" class="form-label">PIN</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="pin" name="pin" maxlength="4" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Anmelden
                </button>
                
                <button type="button" class="btn btn-outline-secondary w-100 mt-2" onclick="quickAdminLogin()">
                    <i class="bi bi-lightning"></i> Schnell-Login (Admin)
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <a href="/" class="text-decoration-none">← Zurück zur API</a>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const loginData = {
        email: formData.get('email'),
        mobile: formData.get('mobile'),
        pin: formData.get('pin')
    };
    
    try {
        const response = await fetch('/v1/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(loginData)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            console.log('Login successful, storing token:', result.token.substring(0, 50) + '...');
            localStorage.setItem('adminToken', result.token);
            
            // Double-check token was stored
            const storedToken = localStorage.getItem('adminToken');
            console.log('Token stored successfully:', !!storedToken);
            console.log('Stored token length:', storedToken ? storedToken.length : 'N/A');
            
            // Add token to sessionStorage as backup
            sessionStorage.setItem('adminToken', result.token);
            
            // Redirect with slight delay to ensure storage completes
            setTimeout(() => {
                window.location.href = '/admin';
            }, 100);
        } else {
            const errorElement = document.getElementById('loginError');
            errorElement.textContent = result.message || 'Ungültige Anmeldedaten';
            errorElement.classList.remove('d-none');
        }
    } catch (error) {
        const errorElement = document.getElementById('loginError');
        errorElement.textContent = 'Verbindungsfehler. Bitte versuchen Sie es erneut.';
        errorElement.classList.remove('d-none');
    }
});

function quickAdminLogin() {
    console.log('=== QUICK ADMIN LOGIN ===');
    
    // Fill form with admin credentials
    document.getElementById('email').value = 'admin@bcmarl.de';
    document.getElementById('mobile').value = '01234567890';
    document.getElementById('pin').value = '1234';
    
    // Submit form programmatically
    document.getElementById('loginForm').dispatchEvent(new Event('submit'));
}
</script>
