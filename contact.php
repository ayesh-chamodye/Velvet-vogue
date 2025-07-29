<?php
// Include functions
require_once 'includes/functions.php';

// Process contact form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? clean_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? clean_input($_POST['subject']) : '';
    $message = isset($_POST['message']) ? clean_input($_POST['message']) : '';
    
    // Validate form data
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }
    
    // If no errors, save contact message
    if (empty($errors)) {
        $saved = save_contact_message($name, $email, $subject, $message);
        
        if ($saved) {
            set_message('Your message has been sent successfully. We will get back to you soon!', 'success');
            redirect('contact.php');
            exit;
        } else {
            $errors[] = 'Failed to send message. Please try again.';
        }
    }
}

// Set page title
$page_title = 'Contact Us';
$meta_description = 'Contact Velvet Vogue - Fashion E-commerce Store for any queries or support';
$meta_keywords = 'contact, support, help, fashion, clothing, Velvet Vogue';

// Include header
include_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
        </ol>
    </div>
</nav>

<!-- Contact Section -->
<section class="contact-page py-5">
    <div class="container">
        <h1 class="mb-4 text-center">Contact Us</h1>
        
        <div class="row">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="contact-info">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="mb-3">Get in Touch</h4>
                            <p>Have questions about our products, orders, or anything else? We're here to help! Fill out the form and we'll get back to you as soon as possible.</p>
                            
                            <div class="contact-item d-flex align-items-center mb-3">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                                </div>
                                <div class="contact-text">
                                    <h5 class="mb-1">Address</h5>
                                    <p class="mb-0">123 Fashion Street, Style City, SC 12345</p>
                                </div>
                            </div>
                            
                            <div class="contact-item d-flex align-items-center mb-3">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-phone-alt fa-2x text-primary"></i>
                                </div>
                                <div class="contact-text">
                                    <h5 class="mb-1">Phone</h5>
                                    <p class="mb-0">+1 (555) 123-4567</p>
                                </div>
                            </div>
                            
                            <div class="contact-item d-flex align-items-center mb-3">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-envelope fa-2x text-primary"></i>
                                </div>
                                <div class="contact-text">
                                    <h5 class="mb-1">Email</h5>
                                    <p class="mb-0">support@velvetvogue.com</p>
                                </div>
                            </div>
                            
                            <div class="contact-item d-flex align-items-center">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-clock fa-2x text-primary"></i>
                                </div>
                                <div class="contact-text">
                                    <h5 class="mb-1">Business Hours</h5>
                                    <p class="mb-0">Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM<br>Sunday: Closed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3">Connect With Us</h4>
                            <p>Follow us on social media for the latest updates, promotions, and fashion inspiration.</p>
                            
                            <div class="social-links">
                                <a href="#" class="social-link me-2"><i class="fab fa-facebook-f fa-2x"></i></a>
                                <a href="#" class="social-link me-2"><i class="fab fa-instagram fa-2x"></i></a>
                                <a href="#" class="social-link me-2"><i class="fab fa-twitter fa-2x"></i></a>
                                <a href="#" class="social-link me-2"><i class="fab fa-pinterest fa-2x"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-youtube fa-2x"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="contact-form card">
                    <div class="card-body">
                        <h4 class="mb-3">Send Us a Message</h4>
                        
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form action="contact.php" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Your Email *</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? $subject : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($message) ? $message : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="privacy" name="privacy" required>
                                <label class="form-check-label" for="privacy">I agree to the <a href="privacy.php" class="text-decoration-none">Privacy Policy</a></label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Google Map -->
        <div class="store-map mt-5">
            <h3 class="mb-4">Our Location</h3>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387193.30591910525!2d-74.25986652425023!3d40.69714941680757!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2s!4v1656426296438!5m2!1sen!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="faq-section mt-5">
            <h3 class="mb-4">Frequently Asked Questions</h3>
            
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeading1">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                            How can I track my order?
                        </button>
                    </h2>
                    <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can track your order by logging into your account and visiting the "My Orders" section. There, you'll find a list of all your orders and their current status. Alternatively, you can use the tracking number provided in your shipping confirmation email.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeading2">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                            What is your return policy?
                        </button>
                    </h2>
                    <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We offer a 30-day return policy for all unworn, unwashed, and undamaged items with original tags attached. Returns must be initiated within 30 days of receiving your order. Please visit our Returns & Exchanges page for more information and to start the return process.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeading3">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                            How long does shipping take?
                        </button>
                    </h2>
                    <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Standard shipping typically takes 3-5 business days within the continental US. Express shipping is available for 1-2 business days delivery. International shipping times vary by location but generally take 7-14 business days. Please note that these are estimates and actual delivery times may vary.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeading4">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                            Do you offer international shipping?
                        </button>
                    </h2>
                    <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, we ship to most countries worldwide. International shipping rates and delivery times vary by location. Please note that customers are responsible for any customs duties, taxes, or import fees that may apply in their country.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeading5">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                            How do I find my size?
                        </button>
                    </h2>
                    <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We provide detailed size guides for all our products. You can find the size guide link on each product page. If you're between sizes or unsure, feel free to contact our customer service team for personalized assistance with sizing.
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
