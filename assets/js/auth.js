// Authentication page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const authForm = document.querySelector('.auth-form');
    if (authForm) {
        authForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // Real-time validation
    const inputs = document.querySelectorAll('input[required]');
    inputs.forEach(input => {
        // Only validate on blur if the user entered something; do not show "required" when
        // a user simply focuses and leaves the field empty before submitting.
        input.addEventListener('blur', function() {
            if (this.value && this.value.trim() !== '') {
                validateField(this);
            } else {
                // clear any non-submission validation message on blur for empty fields
                clearFieldError(this);
            }
        });

        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
    
    // Role selection animation
    const roleOptions = document.querySelectorAll('.role-option input');
    roleOptions.forEach(option => {
        option.addEventListener('change', function() {
            // Remove selected class from all options
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to chosen option
            if (this.checked) {
                this.closest('.role-option').querySelector('.role-card').classList.add('selected');
            }
        });
    });
    
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
    }
    
    // Confirm password validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            validatePasswordMatch();
        });
    }
});

// Password toggle functionality
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = field.nextElementSibling;
    const icon = toggle.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form validation
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    // Additional validation for registration form
    if (form.querySelector('#confirm_password')) {
        if (!validatePasswordMatch()) {
            isValid = false;
        }
    }
    
    return isValid;
}

// Validate individual field
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        errorMessage = 'This field is required';
        isValid = false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            errorMessage = 'Please enter a valid email address';
            isValid = false;
        }
    }
    
    // Password validation
    if (field.type === 'password' && field.id === 'password' && value) {
        if (value.length < 6) {
            errorMessage = 'Password must be at least 6 characters long';
            isValid = false;
        }
    }
    
    // Username validation
    if (field.name === 'username' && value) {
        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
        if (!usernameRegex.test(value)) {
            errorMessage = 'Username must be 3-20 characters and contain only letters, numbers, and underscores';
            isValid = false;
        }
    }
    
    // Phone validation
    if (field.name === 'phone' && value) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
            errorMessage = 'Please enter a valid phone number';
            isValid = false;
        }
    }
    
    if (isValid) {
        clearFieldError(field);
    } else {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    clearFieldError(field);

    // Put the error message inside the .form-group so the .input-group size doesn't change
    const formGroup = field.closest('.form-group');
    const target = formGroup || field.closest('.input-group') || field.parentElement;
    if (formGroup) formGroup.classList.add('error');

    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;

    target.appendChild(errorElement);
}

// Clear field error
function clearFieldError(field) {
    const formGroup = field.closest('.form-group');
    const target = formGroup || field.closest('.input-group') || field.parentElement;
    if (formGroup) formGroup.classList.remove('error');

    const existingError = target.querySelector('.field-error');
    if (existingError) existingError.remove();
}

// Password strength checker
function checkPasswordStrength(password) {
    const strengthIndicator = document.querySelector('.password-strength');
    if (!strengthIndicator) return;
    
    let strength = 0;
    const checks = [
        password.length >= 8,
        /[a-z]/.test(password),
        /[A-Z]/.test(password),
        /\d/.test(password),
        /[^a-zA-Z\d]/.test(password)
    ];
    
    strength = checks.filter(check => check).length;
    
    const strengthLabels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const strengthColors = ['#ff4757', '#ff6b7a', '#ffa502', '#2ed573', '#1e90ff'];
    
    strengthIndicator.textContent = strengthLabels[strength - 1] || '';
    strengthIndicator.style.color = strengthColors[strength - 1] || '';
}

// Validate password match
function validatePasswordMatch() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (!password || !confirmPassword) return true;
    
    const isValid = password.value === confirmPassword.value;
    
    if (confirmPassword.value && !isValid) {
        showFieldError(confirmPassword, 'Passwords do not match');
    } else {
        clearFieldError(confirmPassword);
    }
    
    return isValid;
}

// Loading animation for form submission
function showFormLoading(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Please wait...';
    }
}

function hideFormLoading(form, originalText) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}