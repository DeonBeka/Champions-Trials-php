/**
 * Volunteer Connect - Sign Up Page JavaScript
 * Handles signup form interactions and validation
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeSignup();
});

/**
 * Initialize signup page functionality
 */
function initializeSignup() {
    setupUserTypeSelection();
    setupTagSelection();
    setupPasswordValidation();
    setupFormValidation();
    setupProgressIndicator();
}

/**
 * Setup user type selection
 */
function setupUserTypeSelection() {
    const userCards = document.querySelectorAll('.user-type-card');
    
    userCards.forEach(card => {
        card.addEventListener('click', function() {
            const userType = this.querySelector('input[type="radio"]').value;
            selectUserType(userType);
        });
    });
}

/**
 * Select user type and show relevant fields
 */
function selectUserType(userType) {
    // Update UI
    document.querySelectorAll('.user-type-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    const selectedCard = document.querySelector(`input[value="${userType}"]`).closest('.user-type-card');
    selectedCard.classList.add('selected');
    
    // Show/hide specific fields
    const volunteerFields = document.getElementById('volunteerFields');
    const organizationFields = document.getElementById('organizationFields');
    const bioLabel = document.querySelector('label[for="bio"]');
    
    if (userType === 'volunteer') {
        volunteerFields.style.display = 'block';
        organizationFields.style.display = 'none';
        if (bioLabel) {
            bioLabel.textContent = 'Bio/About *';
            document.querySelector('#bio').placeholder = 'Tell us about yourself and why you want to volunteer...';
        }
    } else {
        volunteerFields.style.display = 'none';
        organizationFields.style.display = 'block';
        if (bioLabel) {
            bioLabel.textContent = 'Bio/About *';
            document.querySelector('#bio').placeholder = 'Tell us about your organization and what you\'re looking for in volunteers...';
        }
    }
    
    // Update bio help text
    const bioHelp = document.querySelector('#bio + small');
    if (bioHelp) {
        bioHelp.textContent = userType === 'organization' ? 
            'Share your organization\'s story, impact, and what you\'re looking for in volunteers.' :
            'Help organizations get to know you and what drives your passion for volunteering.';
    }
    
    // Validate that a user type is selected
    const userTypeInput = document.querySelector(`input[name="user_type"][value="${userType}"]`);
    userTypeInput.checked = true;
    
    // Remove any validation errors
    removeFieldError(userTypeInput);
}

/**
 * Setup tag selection for skills and interests
 */
function setupTagSelection() {
    const clickableTags = document.querySelectorAll('.clickable-tag');
    
    clickableTags.forEach(tag => {
        tag.addEventListener('click', function(e) {
            e.preventDefault();
            toggleTag(this);
        });
    });
}

/**
 * Toggle tag selection
 */
function toggleTag(element) {
    const checkbox = element.querySelector('input[type="checkbox"]');
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
    }
    
    element.classList.toggle('selected');
    
    // Add selection animation
    element.style.transform = 'scale(0.95)';
    setTimeout(() => {
        element.style.transform = '';
    }, 100);
    
    // Update progress indicator
    updateProgressIndicator();
}

/**
 * Setup password validation and confirmation
 */
function setupPasswordValidation() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            
            // Clear confirmation error if password changes
            if (confirmPasswordInput.value) {
                validateField(confirmPasswordInput);
            }
        });
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            validateField(this);
        });
    }
}

/**
 * Check password strength with visual feedback
 */
function checkPasswordStrength(password) {
    const strengthIndicator = document.getElementById('passwordStrength');
    if (!strengthIndicator) return;
    
    let strength = 0;
    const checks = {
        length: password.length >= 8,
        longLength: password.length >= 12,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        numbers: /[0-9]/.test(password),
        special: /[^a-zA-Z0-9]/.test(password)
    };
    
    // Calculate strength
    Object.values(checks).forEach(passed => {
        if (passed) strength++;
    });
    
    // Update visual indicator
    strengthIndicator.className = 'password-strength';
    
    if (password.length === 0) {
        strengthIndicator.innerHTML = '';
    } else {
        const strengthBar = document.createElement('div');
        strengthBar.className = 'password-strength-bar';
        
        const strengthText = document.createElement('small');
        
        if (strength <= 2) {
            strengthBar.classList.add('strength-weak');
            strengthText.textContent = 'Weak password';
            strengthText.style.color = 'var(--danger-color)';
        } else if (strength <= 4) {
            strengthBar.classList.add('strength-medium');
            strengthText.textContent = 'Medium strength';
            strengthText.style.color = 'var(--warning-color)';
        } else {
            strengthBar.classList.add('strength-strong');
            strengthText.textContent = 'Strong password';
            strengthText.style.color = 'var(--success-color)';
        }
        
        strengthIndicator.innerHTML = '';
        strengthIndicator.appendChild(strengthBar);
        strengthIndicator.appendChild(strengthText);
        
        // Add requirements checklist
        const checklist = document.createElement('div');
        checklist.className = 'password-requirements';
        checklist.style.marginTop = '0.5rem';
        checklist.style.fontSize = '0.75rem';
        checklist.style.color = '#6b7280';
        
        const requirements = [
            { text: 'At least 8 characters', met: checks.length },
            { text: 'Uppercase letter', met: checks.uppercase },
            { text: 'Lowercase letter', met: checks.lowercase },
            { text: 'Number', met: checks.numbers },
            { text: 'Special character', met: checks.special }
        ];
        
        requirements.forEach(req => {
            const reqItem = document.createElement('div');
            reqItem.style.marginBottom = '0.25rem';
            reqItem.innerHTML = `<i class="fas fa-${req.met ? 'check' : 'times'}" style="color: ${req.met ? 'var(--success-color)' : 'var(--danger-color)'}"></i> ${req.text}`;
            checklist.appendChild(reqItem);
        });
        
        strengthIndicator.appendChild(checklist);
    }
}

/**
 * Setup enhanced form validation
 */
function setupFormValidation() {
    const form = document.getElementById('signupForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        if (!validateSignupForm()) {
            e.preventDefault();
        }
    });
    
    // Real-time validation for all fields
    form.querySelectorAll('input, textarea, select').forEach(field => {
        field.addEventListener('blur', function() {
            validateSignupField(this);
        });
        
        // Clear error on input
        field.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                removeFieldError(this);
            }
        });
    });
}

/**
 * Validate the entire signup form
 */
function validateSignupForm() {
    const form = document.getElementById('signupForm');
    if (!form) return false;
    
    let isValid = true;
    const errors = [];
    
    // Check user type selection
    const userType = document.querySelector('input[name="user_type"]:checked');
    if (!userType) {
        errors.push('Please select whether you are a volunteer or organization');
        isValid = false;
    }
    
    // Validate all fields
    form.querySelectorAll('input, textarea, select').forEach(field => {
        if (!validateSignupField(field)) {
            isValid = false;
        }
    });
    
    // Special validation for skills/interests
    if (userType && userType.value === 'volunteer') {
        const selectedSkills = document.querySelectorAll('#volunteerFields input[type="checkbox"]:checked');
        if (selectedSkills.length === 0) {
            errors.push('Please select at least one skill');
            isValid = false;
        }
    }
    
    // Show summary of errors if any
    if (!isValid && errors.length > 0) {
        showValidationSummary(errors);
    }
    
    return isValid;
}

/**
 * Validate individual signup field
 */
function validateSignupField(field) {
    const value = field.value.trim();
    const name = field.name;
    let isValid = true;
    let errorMessage = '';
    
    // Remove previous error
    removeFieldError(field);
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Email validation
    if (name === 'email' && value && !isValidEmail(value)) {
        isValid = false;
        errorMessage = 'Please enter a valid email address';
    }
    
    // Password validation
    if (name === 'password' && value) {
        if (value.length < 8) {
            isValid = false;
            errorMessage = 'Password must be at least 8 characters long';
        } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) {
            isValid = false;
            errorMessage = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
        }
    }
    
    // Password confirmation
    if (name === 'confirm_password' && value) {
        const password = document.getElementById('password').value;
        if (value !== password) {
            isValid = false;
            errorMessage = 'Passwords do not match';
        }
    }
    
    // Hours per week validation
    if (name === 'hours_per_week' && value) {
        const hours = parseInt(value);
        if (hours < 1 || hours > 40) {
            isValid = false;
            errorMessage = 'Hours per week must be between 1 and 40';
        }
    }
    
    // Show error if invalid
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

/**
 * Show validation summary
 */
function showValidationSummary(errors) {
    // Remove existing summary
    const existingSummary = document.querySelector('.validation-summary');
    if (existingSummary) {
        existingSummary.remove();
    }
    
    // Create summary
    const summary = document.createElement('div');
    summary.className = 'validation-summary message message-error';
    summary.innerHTML = `
        <h4>Please fix the following errors:</h4>
        <ul>
            ${errors.map(error => `<li>${error}</li>`).join('')}
        </ul>
    `;
    
    // Insert at the top of the form
    const form = document.getElementById('signupForm');
    form.insertBefore(summary, form.firstChild);
    
    // Scroll to summary
    summary.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/**
 * Setup progress indicator
 */
function setupProgressIndicator() {
    const form = document.getElementById('signupForm');
    if (!form) return;
    
    // Create progress indicator
    const progressContainer = document.createElement('div');
    progressContainer.className = 'signup-progress';
    progressContainer.innerHTML = `
        <div class="progress-header">
            <span>Profile Completion</span>
            <span class="progress-percentage">0%</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
    `;
    
    // Insert before the form
    form.parentNode.insertBefore(progressContainer, form);
    
    // Update progress on field changes
    form.querySelectorAll('input, textarea, select').forEach(field => {
        field.addEventListener('change', updateProgressIndicator);
        field.addEventListener('input', updateProgressIndicator);
    });
    
    // Initial update
    updateProgressIndicator();
}

/**
 * Update progress indicator
 */
function updateProgressIndicator() {
    const form = document.getElementById('signupForm');
    if (!form) return;
    
    const totalFields = form.querySelectorAll('input[required], textarea[required], select[required]').length;
    let filledFields = 0;
    
    // Count filled required fields
    form.querySelectorAll('input[required], textarea[required], select[required]').forEach(field => {
        if (field.type === 'radio') {
            if (form.querySelector(`input[name="${field.name}"]:checked`)) {
                filledFields++;
            }
        } else if (field.type === 'checkbox') {
            // For checkboxes, check if at least one in the group is checked
            const groupName = field.name;
            const checkedBoxes = form.querySelectorAll(`input[name="${groupName}"]:checked`);
            if (checkedBoxes.length > 0) {
                filledFields++;
            }
        } else if (field.value.trim()) {
            filledFields++;
        }
    });
    
    // Calculate percentage
    const percentage = Math.round((filledFields / totalFields) * 100);
    
    // Update UI
    const progressFill = document.querySelector('.progress-fill');
    const progressPercentage = document.querySelector('.progress-percentage');
    
    if (progressFill && progressPercentage) {
        progressFill.style.width = percentage + '%';
        progressPercentage.textContent = percentage + '%';
        
        // Update color based on percentage
        if (percentage < 50) {
            progressFill.style.background = 'var(--danger-color)';
        } else if (percentage < 80) {
            progressFill.style.background = 'var(--warning-color)';
        } else {
            progressFill.style.background = 'var(--success-color)';
        }
    }
}

/**
 * Show field error with animation
 */
function showFieldError(field, message) {
    field.classList.add('error');
    field.style.borderColor = 'var(--danger-color)';
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    
    field.parentElement.appendChild(errorElement);
    
    // Shake animation
    field.style.animation = 'shake 0.5s';
    setTimeout(() => {
        field.style.animation = '';
    }, 500);
}

/**
 * Remove field error
 */
function removeFieldError(field) {
    field.classList.remove('error');
    field.style.borderColor = '';
    
    const errorElement = field.parentElement.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

/**
 * Utility functions
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Add shake animation
if (!document.querySelector('#signup-shake-style')) {
    const style = document.createElement('style');
    style.id = 'signup-shake-style';
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .signup-progress {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .signup-progress .progress-bar {
            height: 8px;
            background: var(--border-color);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .signup-progress .progress-fill {
            height: 100%;
            background: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .validation-summary {
            margin-bottom: 1.5rem;
        }
        
        .validation-summary h4 {
            margin-bottom: 0.5rem;
        }
        
        .validation-summary ul {
            margin-left: 1.5rem;
        }
        
        .validation-summary li {
            margin-bottom: 0.25rem;
        }
        
        .password-requirements {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: var(--light-color);
            border-radius: var(--radius);
        }
        
        .field-error {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .form-input.error {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
    `;
    document.head.appendChild(style);
}

// Expose functions for global access
window.selectUserType = selectUserType;
window.toggleTag = toggleTag;