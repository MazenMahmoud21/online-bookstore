<?php
/**
 * Login Page
 */

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit;
}


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Verification error. Please try again.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter your username and password.';
        } else {
            $customer = dbQuerySingle(
                "SELECT * FROM customers WHERE username = ? OR email = ?",
                [$username, $username]
            );
            
            if ($customer && verifyPassword($password, $customer['password_hash'])) {
                loginUser($customer);
                
                // Redirect based on role
                if ($customer['is_admin']) {
                    header('Location: ' . url('admin/dashboard.php'));
                } else {
                    header('Location: ' . url('index.php'));
                }
                exit;
            } else {
                $error = 'Incorrect username or password.';
            }
        }
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2><i data-feather="lock"></i> Login</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control" 
                       placeholder="Enter your username or email" required
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
        </form>
        
        <div class="auth-footer">
            <p><a href="<?php echo url('forgot_password.php'); ?>">Forgot your password?</a></p>
            <p>Don't have an account? <a href="<?php echo url('signup.php'); ?>">Create a new account</a></p>
        </div>
    </div>
    
    <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;">
        <strong>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 6px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="8"></line></svg>
            Demo Login Credentials:
        </strong>
        <p style="margin: 0;">
            <strong>Admin:</strong> admin / password
        </p>
        <p style="margin: 0;">
            <strong>Customer:</strong> mahmoud / password
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
