// Main JavaScript for landing page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }
    
    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
            
            // Close mobile menu if open
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });
    
    // Hero indicators animation
    const indicators = document.querySelectorAll('.indicator');
    let currentIndicator = 0;
    
    function animateIndicators() {
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentIndicator);
        });
        currentIndicator = (currentIndicator + 1) % indicators.length;
    }
    
    // Change indicators every 5 seconds
    if (indicators.length > 0) {
        setInterval(animateIndicators, 5000);
    }
    
    // Scroll animations
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
    
    // Observe elements for animation
    const animatedElements = document.querySelectorAll('.service-card, .feature, .contact-item');
    animatedElements.forEach(element => {
        observer.observe(element);
    });
    
    // Parallax effect for hero section
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const heroImage = document.querySelector('.hero-image');
        
        if (heroImage) {
            const rate = scrolled * -0.5;
            heroImage.style.transform = `translateY(${rate}px)`;
        }
    });
    
    // Service card hover effects
    const serviceCards = document.querySelectorAll('.service-card');
    serviceCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Contact form submission (if exists)
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Add contact form submission logic here
            alert('Thank you for your message! We\'ll get back to you soon.');
        });
    }
});

// Utility functions
function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
        ${message}
    `;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Loading animation
function showLoading() {
    const loader = document.createElement('div');
    loader.className = 'loader';
    loader.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(loader);
    return loader;
}

function hideLoading(loader) {
    if (loader) {
        loader.remove();
    }
}

// Profile dropdown toggle and outside click close
function toggleProfileMenu() {
    const menu = document.getElementById('profileMenu');
    if (!menu) return;
    // Find the closest toggle inside the same profile-dropdown container (robust selection)
    let toggle = null;
    const dropdownContainer = document.getElementById('profileMenu') ? document.getElementById('profileMenu').closest('.profile-dropdown') : null;
    if (dropdownContainer) {
        toggle = dropdownContainer.querySelector('.dropdown-toggle');
    }
    const isShown = menu.classList.toggle('show');
    console.debug('toggleProfileMenu called, menu shown:', isShown);
    if (toggle) toggle.setAttribute('aria-expanded', isShown ? 'true' : 'false');
}

// User header dropdown shim: many user header markup uses toggleUserProfileMenu()
function toggleUserProfileMenu() {
    const menu = document.getElementById('userProfileMenu');
    if (!menu) return;
    menu.classList.toggle('show');
}

// Extend outside click handler to close both profile menus
document.addEventListener('click', function(e) {
    // Helper to close a menu if the click was outside it and its toggle
    function maybeClose(menuId) {
        const menu = document.getElementById(menuId);
        if (!menu || !menu.classList.contains('show')) return;
        // find toggle inside same container
        const container = menu.closest('.profile-dropdown') || menu.closest('.user-profile') || null;
        const toggle = container ? container.querySelector('.dropdown-toggle') : null;
        if (menu.contains(e.target) || (toggle && toggle.contains(e.target))) return;
        menu.classList.remove('show');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }

    maybeClose('profileMenu');
    maybeClose('userProfileMenu');
});

// Dropdown is toggled only by the .dropdown-toggle button (chevron). The profile pill
// no longer toggles the menu to avoid accidental opens when clicking the pill area.