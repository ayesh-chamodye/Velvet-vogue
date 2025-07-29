<!DOCTYPE html>
<html>
<head>
    <title>Velvet Vogue Database Initialization</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #333; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Velvet Vogue Database Initialization</h1>
    <div id="results">
<?php
// Include database configuration
try {
    require_once '../config/database.php';
} catch (Exception $e) {
    echo "<div class='error'>Error loading database configuration: " . $e->getMessage() . "</div>";
    exit;
}

// Function to execute SQL safely
function executeSql($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        echo "<div class='error'>
            <h3>Error executing SQL:</h3>
            <p>" . mysqli_error($conn) . "</p>
            <pre>" . htmlspecialchars($sql) . "</pre>
            </div>";
        return false;
    }
    return true;
}

// Create tables one by one

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50),
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active'
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create users table");

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    parent_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create categories table");

// Create products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2),
    category_id INT,
    stock INT NOT NULL DEFAULT 0,
    sku VARCHAR(50) UNIQUE,
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create products table");

// Create product_images table
$sql = "CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create product_images table");

// Create product_attributes table
$sql = "CREATE TABLE IF NOT EXISTS product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    attribute_name VARCHAR(50) NOT NULL,
    attribute_value VARCHAR(100) NOT NULL,
    price_adjustment DECIMAL(10, 2) DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create product_attributes table");

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_address TEXT NOT NULL,
    billing_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    tracking_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create orders table");

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    attributes JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create order_items table");

// Create wishlist table
$sql = "CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, product_id)
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create wishlist table");

// Create reviews table
$sql = "CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create reviews table");

// Create banners table
$sql = "CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    image_url VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    position VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create banners table");

// Create newsletter_subscribers table
$sql = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('subscribed', 'unsubscribed') DEFAULT 'subscribed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create newsletter_subscribers table");

// Create contact_messages table
$sql = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create contact_messages table");

// Create chat_messages table
$sql = "CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_bot BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!executeSql($conn, $sql)) die("<div class='error'><h3>Fatal Error:</h3><p>Failed to create chat_messages table");

// Now add all foreign key constraints

// Add foreign key for categories (self-referencing)
$sql = "ALTER TABLE categories
        ADD CONSTRAINT fk_category_parent
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL";
if (!executeSql($conn, $sql)) echo "<p class='warning'>Warning: Could not add foreign key to categories table. This is normal if running for the first time.</p>";

// Add foreign key for products
$sql = "ALTER TABLE products
        ADD CONSTRAINT fk_product_category
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add foreign key to products table</p>";

// Add foreign key for product_images
$sql = "ALTER TABLE product_images
        ADD CONSTRAINT fk_product_images_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add foreign key to product_images table</p>";

// Add foreign key for product_attributes
$sql = "ALTER TABLE product_attributes
        ADD CONSTRAINT fk_product_attributes_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add foreign key to product_attributes table</p>";

// Add foreign key for orders
$sql = "ALTER TABLE orders
        ADD CONSTRAINT fk_order_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add foreign key to orders table</p>";

// Add foreign keys for order_items
$sql = "ALTER TABLE order_items
        ADD CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add order_id foreign key to order_items table</p>";

$sql = "ALTER TABLE order_items
        ADD CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add product_id foreign key to order_items table</p>";

// Add foreign keys for wishlist
$sql = "ALTER TABLE wishlist
        ADD CONSTRAINT fk_wishlist_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add user_id foreign key to wishlist table</p>";

$sql = "ALTER TABLE wishlist
        ADD CONSTRAINT fk_wishlist_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add product_id foreign key to wishlist table</p>";

// Add foreign keys for reviews
$sql = "ALTER TABLE reviews
        ADD CONSTRAINT fk_reviews_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add product_id foreign key to reviews table</p>";

$sql = "ALTER TABLE reviews
        ADD CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add user_id foreign key to reviews table</p>";

// Add foreign key for chat_messages
$sql = "ALTER TABLE chat_messages
        ADD CONSTRAINT fk_chat_messages_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not add foreign key to chat_messages table</p>";

// Insert default admin user
$sql = "INSERT INTO users (username, password, email, first_name, last_name, role) 
        VALUES ('admin', '$2y$10$8WxhV7rOGYlgYRCnKuhouOeEEFq1eBZQgXpEkYK.m7H/yWWJiNgwK', 'admin@velvetvogue.com', 'Admin', 'User', 'admin')
        ON DUPLICATE KEY UPDATE id=id";
if (!executeSql($conn, $sql)) echo "<p>Warning: Could not insert default admin user</p>";

echo "<h2 class='success'>Database initialization completed successfully!</h2>";

// Close connection
mysqli_close($conn);
?>
    </div>
    <p><a href="../index.php">Return to homepage</a></p>
</body>
</html>
