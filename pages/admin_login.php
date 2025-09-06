<?php
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    redirect('?page=admin-dashboard');
}

$error = '';

if ($_POST) {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Token keamanan tidak valid';
    } else if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_data'] = [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'name' => $admin['name'],
                    'role' => $admin['role'],
                    'email' => $admin['email']
                ];
                
                set_flash_message('success', 'Selamat datang, ' . $admin['name']);
                redirect('?page=admin-dashboard');
            } else {
                $error = 'Username atau password salah';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Kelurahan Margasari</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            color: white;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .login-header h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .login-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }
        
        .login-header small {
            opacity: 0.7;
            position: relative;
            z-index: 1;
        }
        
        .login-body {
            padding: 50px 40px;
        }
        
        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.3rem rgba(13, 110, 253, 0.15);
            background: white;
            transform: translateY(-2px);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
        }
        
        .info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            border: 1px solid #dee2e6;
        }
        
        .info-box h6 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .back-link {
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .back-link:hover {
            color: #0d6efd;
            transform: translateX(-5px);
        }
        
        .password-toggle {
            cursor: pointer;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #0d6efd;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                padding: 15px;
            }
            
            .login-card {
                max-width: none;
                margin: 0;
            }
            
            .login-header {
                padding: 40px 30px;
            }
            
            .login-header h3 {
                font-size: 1.8rem;
            }
            
            .login-body {
                padding: 40px 30px;
            }
            
            .form-control {
                padding: 12px 18px;
                font-size: 1rem;
            }
            
            .btn-login {
                padding: 12px 25px;
                font-size: 1rem;
            }
            
            .info-box {
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .login-header {
                padding: 30px 20px;
            }
            
            .login-header h3 {
                font-size: 1.6rem;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .info-box {
                padding: 15px;
            }
        }
        
        /* High DPI screens */
        @media (min-width: 1200px) {
            .login-card {
                max-width: 550px;
            }
            
            .login-header {
                padding: 60px 50px;
            }
            
            .login-body {
                padding: 60px 50px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-user-shield fa-3x mb-3"></i>
                        <h3 class="mb-1">Admin Panel</h3>
                        <p class="mb-0 opacity-75">Kelurahan Margasari</p>
                        <small class="opacity-50">Balikpapan Barat</small>
                    </div>
                    
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">
                                    <i class="fas fa-user me-1"></i> Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                                       placeholder="Masukkan username"
                                       required autocomplete="username">
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-1"></i> Password
                                </label>
                                <div class="position-relative">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Masukkan password"
                                           required autocomplete="current-password">
                                    <span class="position-absolute top-50 end-0 translate-middle-y me-3 password-toggle" 
                                          onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-login w-100">
                                <i class="fas fa-sign-in-alt me-2"></i> Masuk
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="index.php" class="back-link">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Website
                            </a>
                        </div>
                        
                        <div class="info-box">
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-info-circle me-1"></i> Login Default
                            </h6>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted d-block">Username:</small>
                                    <code class="small">admin</code>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Password:</small>
                                    <code class="small">password</code>
                                </div>
                            </div>
                            <small class="text-warning d-block mt-2">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Segera ganti password setelah login pertama
                            </small>
                        </div>
                    </div>
                </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        // Auto focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading animation
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.5s ease';
                document.body.style.opacity = '1';
            }, 100);
            
            // Focus on username field
            document.getElementById('username').focus();
            
            // Add floating label effect
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
                
                // Initialize for pre-filled inputs
                if (input.value) {
                    input.parentElement.classList.add('focused');
                }
            });
        });
        
        // Enhanced form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const submitBtn = this.querySelector('.btn-login');
            
            if (!username || !password) {
                e.preventDefault();
                showAlert('Username dan password harus diisi!', 'error');
                return false;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Memproses...';
            submitBtn.disabled = true;
        });
        
        // Custom alert function
        function showAlert(message, type = 'info') {
            // Remove existing alerts
            const existingAlert = document.querySelector('.custom-alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-info';
            const icon = type === 'error' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertClass} alert-dismissible fade show custom-alert position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                <i class="${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv && alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Enter key to focus password field from username
            if (e.key === 'Enter' && document.activeElement.id === 'username') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
            
            // ESC to clear form
            if (e.key === 'Escape') {
                document.getElementById('loginForm').reset();
                document.getElementById('username').focus();
            }
        });
        
        // Add smooth transitions for form elements
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('keyup', function() {
                if (this.value.length > 0) {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });
        
        // Prevent form submission on empty fields with visual feedback
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('invalid', function(e) {
                e.preventDefault();
                this.classList.add('is-invalid');
                
                setTimeout(() => {
                    this.classList.remove('is-invalid');
                }, 3000);
            });
        });
    </script>
</body>
</html>