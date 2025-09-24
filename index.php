<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StarWash - Professional Laundry & Dry Cleaning</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <h2>StarWash</h2>
                </div>
                <div class="nav-menu">
                    <a href="#home" class="nav-link">HOME</a>
                    <a href="#about" class="nav-link">ABOUT</a>
                    <a href="#services" class="nav-link">SERVICES</a>
                    <a href="#contact" class="nav-link">CONTACT</a>
                </div>
                <div class="nav-buttons">
                    <a href="pages/login.php" class="btn btn-outline">Sign In</a>
                    <a href="pages/register.php" class="btn btn-primary">Sign Up</a>
                </div>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">
                    LAUNDRY<br>
                    <span class="hero-highlight">DRY CLEANING</span>
                </h1>
                <p class="hero-description">
                    Professional laundry and dry cleaning services with premium care for your garments. 
                    Fast, reliable, and convenient pickup & delivery.
                </p>
                <button class="btn btn-primary btn-large">
                    LEARN MORE <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            <div class="hero-image">
                <div class="laundry-illustration">
                    <div class="washing-machine">
                        <div class="machine-body"></div>
                        <div class="machine-door"></div>
                        <div class="machine-controls"></div>
                    </div>
                    <div class="person-loading">
                        <div class="person"></div>
                    </div>
                    <div class="person-hanging">
                        <div class="person"></div>
                        <div class="clothesline">
                            <div class="clothes-item"></div>
                            <div class="clothes-item"></div>
                            <div class="clothes-item"></div>
                        </div>
                    </div>
                    <div class="laundry-basket"></div>
                    <div class="detergent-bottles">
                        <div class="bottle yellow"></div>
                        <div class="bottle blue"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-indicators">
            <span class="indicator active"></span>
            <span class="indicator"></span>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <h2>Our Services</h2>
                <p>Professional laundry solutions for all your needs</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <h3>Wash & Fold</h3>
                    <p>Professional washing and folding service with premium detergents</p>
                    <div class="service-price">From ₱15.99</div>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>Dry Cleaning</h3>
                    <p>Expert dry cleaning for delicate and formal garments</p>
                    <div class="service-price">From ₱25.99</div>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Express Service</h3>
                    <p>Quick turnaround for urgent cleaning needs</p>
                    <div class="service-price">From ₱19.99</div>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-iron"></i>
                    </div>
                    <h3>Ironing</h3>
                    <p>Professional ironing and pressing services</p>
                    <div class="service-price">From ₱12.99</div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Why Choose StarWash?</h2>
                    <div class="features">
                        <div class="feature">
                            <i class="fas fa-truck"></i>
                            <div>
                                <h4>Free Pickup & Delivery</h4>
                                <p>Convenient pickup and delivery at your doorstep</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>24-48 Hour Turnaround</h4>
                                <p>Quick and reliable service with flexible timing</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <h4>100% Satisfaction Guarantee</h4>
                                <p>We guarantee quality service or your money back</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-leaf"></i>
                            <div>
                                <h4>Eco-Friendly Process</h4>
                                <p>Environmentally safe cleaning products and methods</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>Get Started Today</h2>
                <p>Sign up now and experience premium laundry service</p>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Call Us</h4>
                            <p>+1 (555) 123-4567</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email Us</h4>
                            <p>info@starwash.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Visit Us</h4>
                            <p>123 Laundry Street, Clean City, CC 12345</p>
                        </div>
                    </div>
                </div>
                <div class="contact-actions">
                    <a href="pages/register.php" class="btn btn-primary btn-large">Get Started Now</a>
                    <a href="pages/login.php" class="btn btn-outline btn-large">Already a Member?</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>StarWash</h3>
                    <p>Professional laundry and dry cleaning services you can trust.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#">Wash & Fold</a></li>
                        <li><a href="#">Dry Cleaning</a></li>
                        <li><a href="#">Express Service</a></li>
                        <li><a href="#">Ironing</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Track Order</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 StarWash. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>