<?php
include 'partials/header.php';

// Initialize variables for form handling
$name = $email = $subject = $message = '';
$errors = [];
$success = '';
$is_submitting = false;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_submitting = true;
    // Sanitize and validate inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate name
    if (empty($name)) {
        $errors[] = 'Name is required.';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
        $errors[] = 'Name can only contain letters and spaces.';
    }

    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Validate subject
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }

    // Validate message
    if (empty($message)) {
        $errors[] = 'Message is required.';
    } elseif (strlen($message) < 10) {
        $errors[] = 'Message must be at least 10 characters long.';
    }

    // If no errors, process the form (send email)
    if (empty($errors)) {
        // Email configuration
        $to = 'info@fantepedia.com'; // Recipient email
        $email_subject = 'Contact Form Submission: ' . $subject;
        $email_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
        $headers = "From: $email\r\nReply-To: $email\r\n";

        // Send email
        if (mail($to, $email_subject, $email_body, $headers)) {
            $success = 'Thank you for your message! We will get back to you soon.';
            // Clear form fields
            $name = $email = $subject = $message = '';
        } else {
            $errors[] = 'Sorry, there was an error sending your message. Please try again later.';
        }
    }
}
?>

<!-- Contact Hero Section -->
<section class="contact-hero">
    <div class="container">
        <div class="contact-hero-content">
            <h1>Get In Touch With Fantepedia</h1>
            <p>We're here to help with your Fante language learning journey. Whether you have questions about our resources, want to contribute, or need support, our team is ready to assist you.</p>
        </div>
    </div>
</section>

<!-- Contact Main Content -->
<section class="contact-main">
    <div class="container">
        <!-- Contact Info, Form, Map Grid -->
        <div class="contact-main-grid">
            <!-- Contact Information -->
            <div class="contact-info-section contact-section-animate">
                <h2>Contact Information</h2>
                <div class="contact-info-grid">
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <i class="uil uil-envelope"></i>
                        </div>
                        <div class="contact-info-content">
                            <h3>Email Us</h3>
                            <a href="mailto:info@fantepedia.com">info@fantepedia.com</a>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <i class="uil uil-phone"></i>
                        </div>
                        <div class="contact-info-content">
                            <h3>Call Us</h3>
                            <p>+233 (543) 67-2521</p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <i class="uil uil-location-pin-alt"></i>
                        </div>
                        <div class="contact-info-content">
                            <h3>Visit Us</h3>
                            <p>123 Fante Street<br>Cape Coast, Ghana</p>
                        </div>
                    </div>
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <i class="uil uil-globe"></i>
                        </div>
                        <div class="contact-info-content">
                            <h3>Our Website</h3>
                            <a href="https://www.fantepedia.com" target="_blank">www.fantepedia.com</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-section contact-section-animate">
                <h2>Send Message</h2>
                
                <?php if (!empty($errors)): ?>
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>


                <?php if (!empty($success)): ?>
                    <div class="success-message-modern">
                        <i class="uil uil-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form class="modern-contact-form" method="POST" action="" id="contactForm">
                    <div class="form-floating-group">
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        <label for="name">Full Name</label>
                    </div>
                    
                    <div class="form-floating-group">
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <label for="email">Email Address</label>
                    </div>
                    
                    <div class="form-floating-group">
                        <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                        <label for="subject">Subject</label>
                    </div>
                    
                    <div class="form-floating-group">
                        <textarea id="message" name="message" required><?php echo htmlspecialchars($message); ?></textarea>
                        <label for="message">Your Message</label>
                    </div>
                    
                    <button type="submit" name="submit" class="form-submit-btn">
                        <span class="spinner"></span>
                        <span class="btn-text">Send Message</span>
                    </button>
                </form>
            </div>

            <!-- Contact Map -->
            <div class="contact-map-section contact-section-animate">
                <h2>Our Location</h2>
                <p>Find us in the heart of Cape Coast, Ghana</p>
                <div class="contact-map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.309692735034!2d-1.246445684659!3d5.105286496517!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNqLCsxMCcwMi4wIk4gMcKwMTQnNDcuNCJX!5e0!3m2!1sen!2sgh!4v1690000000000!5m2!1sen!2sgh" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- Testimonials -->
        <section class="testimonials contact-section-animate">
            <h2>What People Say</h2>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <div class="testimonial-avatar">
                        <img src="images/default-avatar.svg" alt="Kwame Appiah">
                    </div>
                    <p class="testimonial-text">"Fantepedia has been an incredible resource for learning Fante. The contact team is very responsive and helpful!"</p>
                    <div class="testimonial-author">Kwame Appiah</div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-avatar">
                        <img src="images/default-avatar.svg" alt="Abena Mensah">
                    </div>
                    <p class="testimonial-text">"Quick responses and excellent support. Perfect for anyone serious about learning Fante culture and language."</p>
                    <div class="testimonial-author">Abena Mensah</div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-avatar">
                        <img src="images/default-avatar.svg" alt="Kofi Boateng">
                    </div>
                    <p class="testimonial-text">"The resources are comprehensive and the support team helped me get started quickly. Highly recommended!"</p>
                    <div class="testimonial-author">Kofi Boateng</div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section contact-section-animate">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>How long does it take to hear back?</span>
                        <i class="uil uil-plus faq-toggle"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We typically respond within 24-48 hours during business days. For urgent matters, please call our support line.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span>What should I include in my message?</span>
                        <i class="uil uil-plus faq-toggle"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Please include your name, email, a clear subject, and detailed message. This helps us respond more effectively.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Can I contribute content to Fantepedia?</span>
                        <i class="uil uil-plus faq-toggle"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes! We welcome contributions. Contact us and we'll guide you through our submission process.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);
    
    // Observe all animated sections
    document.querySelectorAll('.contact-section-animate').forEach(el => {
        observer.observe(el);
    });
    
    // FAQ Accordion
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', function() {
            const item = this.parentElement;
            const answer = item.querySelector('.faq-answer');
            const toggle = this.querySelector('.faq-toggle');
            
            // Close other items
            document.querySelectorAll('.faq-item').forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.querySelector('.faq-question').classList.remove('active');
                    otherItem.querySelector('.faq-answer').classList.remove('active');
                }
            });
            
            // Toggle current item
            this.classList.toggle('active');
            answer.classList.toggle('active');
            toggle.classList.toggle('active');
        });
    });
    
    // Form handling
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.querySelector('.form-submit-btn');
    
    contactForm.addEventListener('submit', function(e) {
        submitBtn.classList.add('loading');
        submitBtn.querySelector('.btn-text').textContent = 'Sending...';
        submitBtn.querySelector('.spinner').style.display = 'inline-block';
    });
    
    // Floating labels
    ['input', 'textarea'].forEach(tag => {
        document.querySelectorAll(`.form-floating-group ${tag}`).forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.querySelector('label').style.color = 'rgba(255,255,255,0.7)';
                }
            });
        });
    });
});
</script>

<?php
include 'partials/footer.php';
?>

