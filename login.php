<?php
// Include functions
require_once 'includes/functions.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('index.php');
    exit;
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? clean_input($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate form data
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username or email is required.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    
    // If no errors, attempt to login
    if (empty($errors)) {
        $user = login_user($username, $password, $remember);
        
        if ($user) {
            // Redirect based on user role
            if ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect('index.php');
            }
            exit;
        } else {
            $errors[] = 'Invalid username/email or password.';
        }
    }
}

// Set page title
$page_title = 'Login';
$meta_description = 'Login to your account at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'login, account, fashion, clothing, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Login</li>
        </ol>
    </div>
</nav>

<!-- Login Section -->
<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card card">
                    <div class="card-header bg-white text-center py-3">
                        <h1 class="h4 mb-0">Login to Your Account</h1>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username or Email</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($username) ? $username : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="forgot_password.php" class="text-decoration-none">Forgot your password?</a>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center py-3">
                        <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none">Register</a></p>
                    </div>
                </div>
                
                <div class="social-login mt-4">
                    <div class="text-center mb-3">
                        <p class="text-muted">Or login with</p>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fab fa-facebook-f me-2"></i> Facebook
                        </a>
                        <a href="#" class="btn btn-outline-danger">
                            <i class="fab fa-google me-2"></i> Google
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
