<?php
// Include functions
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_message('Please login to access your account.', 'warning');
    redirect('login.php');
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get form data
    $first_name = isset($_POST['first_name']) ? clean_input($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? clean_input($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? clean_input($_POST['phone']) : '';
    $address = isset($_POST['address']) ? clean_input($_POST['address']) : '';
    $city = isset($_POST['city']) ? clean_input($_POST['city']) : '';
    $state = isset($_POST['state']) ? clean_input($_POST['state']) : '';
    $zip = isset($_POST['zip']) ? clean_input($_POST['zip']) : '';
    $country = isset($_POST['country']) ? clean_input($_POST['country']) : '';
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate form data
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = 'First name is required.';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } elseif ($email !== $user['email']) {
        // Check if email already exists
        if (email_exists($email)) {
            $errors[] = 'Email already exists.';
        }
    }
    
    // Validate password if user wants to change it
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'Current password is required to set a new password.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
        
        if (strlen($new_password) < 8) {
            $errors[] = 'New password must be at least 8 characters long.';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'New password and confirm password do not match.';
        }
    }
    
    // If no errors, update user profile
    if (empty($errors)) {
        $update_data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip' => $zip,
            'country' => $country
        ];
        
        // Add new password if provided
        if (!empty($new_password)) {
            $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        $updated = update_user($user_id, $update_data);
        
        if ($updated) {
            set_message('Your profile has been updated successfully.', 'success');
            redirect('account.php');
            exit;
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}

// Set page title
$page_title = 'My Account';
$meta_description = 'Manage your account at Velvet Vogue - Fashion E-commerce Store';
$meta_keywords = 'account, profile, orders, fashion, clothing, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Account</li>
        </ol>
    </div>
</nav>

<!-- Account Section -->
<section class="account-page py-5">
    <div class="container">
        <h1 class="mb-4">My Account</h1>
        
        <div class="row">
            <!-- Account Sidebar -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <div class="account-sidebar">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="account-user text-center mb-4">
                                <div class="account-avatar mb-3">
                                    <i class="fas fa-user-circle fa-5x text-secondary"></i>
                                </div>
                                <h5 class="mb-1"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h5>
                                <p class="text-muted mb-0"><?php echo $user['email']; ?></p>
                            </div>
                            
                            <div class="account-nav">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="account.php">
                                            <i class="fas fa-user me-2"></i> Profile
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="account_orders.php">
                                            <i class="fas fa-shopping-bag me-2"></i> Orders
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="wishlist.php">
                                            <i class="fas fa-heart me-2"></i> Wishlist
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="account_addresses.php">
                                            <i class="fas fa-map-marker-alt me-2"></i> Addresses
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="logout.php">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Need Help?</h5>
                            <p class="mb-3">If you have any questions or need assistance with your account, please contact our customer support team.</p>
                            <a href="contact.php" class="btn btn-outline-primary btn-sm">Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Content -->
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form action="account.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                                </div>
                            </div>
                            
                            <h5 class="mt-4 mb-3">Address Information</h5>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo $user['address']; ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo $user['city']; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="state" class="form-label">State/Province</label>
                                    <input type="text" class="form-control" id="state" name="state" value="<?php echo $user['state']; ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="zip" class="form-label">Zip/Postal Code</label>
                                    <input type="text" class="form-control" id="zip" name="zip" value="<?php echo $user['zip']; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <select class="form-select" id="country" name="country">
                                        <option value="">Select Country</option>
                                        <option value="United States" <?php echo ($user['country'] == 'United States') ? 'selected' : ''; ?>>United States</option>
                                        <option value="Canada" <?php echo ($user['country'] == 'Canada') ? 'selected' : ''; ?>>Canada</option>
                                        <option value="United Kingdom" <?php echo ($user['country'] == 'United Kingdom') ? 'selected' : ''; ?>>United Kingdom</option>
                                        <option value="Australia" <?php echo ($user['country'] == 'Australia') ? 'selected' : ''; ?>>Australia</option>
                                        <option value="Germany" <?php echo ($user['country'] == 'Germany') ? 'selected' : ''; ?>>Germany</option>
                                        <option value="France" <?php echo ($user['country'] == 'France') ? 'selected' : ''; ?>>France</option>
                                        <option value="Italy" <?php echo ($user['country'] == 'Italy') ? 'selected' : ''; ?>>Italy</option>
                                        <option value="Spain" <?php echo ($user['country'] == 'Spain') ? 'selected' : ''; ?>>Spain</option>
                                        <option value="Japan" <?php echo ($user['country'] == 'Japan') ? 'selected' : ''; ?>>Japan</option>
                                        <option value="China" <?php echo ($user['country'] == 'China') ? 'selected' : ''; ?>>China</option>
                                        <option value="India" <?php echo ($user['country'] == 'India') ? 'selected' : ''; ?>>India</option>
                                        <option value="Brazil" <?php echo ($user['country'] == 'Brazil') ? 'selected' : ''; ?>>Brazil</option>
                                    </select>
                                </div>
                            </div>
                            
                            <h5 class="mt-4 mb-3">Change Password</h5>
                            <p class="text-muted mb-3">Leave these fields empty if you don't want to change your password.</p>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                    <div class="form-text">Password must be at least 8 characters long.</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="account-summary mt-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-shopping-bag fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="mb-2">My Orders</h5>
                                    <p class="mb-3">View and track your orders</p>
                                    <a href="account_orders.php" class="btn btn-outline-primary btn-sm">View Orders</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-heart fa-3x text-danger"></i>
                                    </div>
                                    <h5 class="mb-2">My Wishlist</h5>
                                    <p class="mb-3">View your saved items</p>
                                    <a href="wishlist.php" class="btn btn-outline-primary btn-sm">View Wishlist</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="summary-icon mb-3">
                                        <i class="fas fa-map-marker-alt fa-3x text-success"></i>
                                    </div>
                                    <h5 class="mb-2">My Addresses</h5>
                                    <p class="mb-3">Manage your addresses</p>
                                    <a href="account_addresses.php" class="btn btn-outline-primary btn-sm">View Addresses</a>
                                </div>
                            </div>
                        </div>
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
