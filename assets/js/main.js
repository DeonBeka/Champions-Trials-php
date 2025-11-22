/**
 * Volunteer Connect - Main JavaScript
 * Frontend functionality for the PHP/MySQL version
 */

// Global variables
let currentUser = null;
let notifications = [];

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    setupEventListeners();
    initializeTooltips();
    initializeModals();
});

/**
 * Initialize the application
 */
function initializeApp() {
    // Check for messages from PHP
    checkUrlMessages();
    
    // Initialize smooth scrolling
    initializeSmoothScroll();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Auto-hide messages after 5 seconds
    autoHideMessages();
    
    // Initialize lazy loading for images
    initializeLazyLoading();
}

/**
 * Setup global event listeners
 */
function setupEventListeners() {
    // Handle mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', toggleMobileMenu);
    }
    
    // Handle dropdown menus
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', handleDropdown);
    });
    
    // Handle tab switching
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', handleTabSwitch);
    });
    
    // Handle modal close buttons
    document.querySelectorAll('.modal-close, .modal-overlay').forEach(closeBtn => {
        closeBtn.addEventListener('click', closeModal);
    });
    
    // Handle ESC key for modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Handle form submissions with AJAX
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', handleAjaxForm);
    });
    
    // Handle search inputs with debouncing
    document.querySelectorAll('.search-input').forEach(input => {
        input.addEventListener('input', debounce(handleSearch, 300));
    });
    
    // Handle file uploads
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', handleFileUpload);
    });
    
    // Handle password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        if (input.id === 'password') {
            input.addEventListener('input', checkPasswordStrength);
        }
    });
}

/**
 * Check URL for success/error messages
 */
function checkUrlMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('success')) {
        showMessage(urlParams.get('success'), 'success');
    }
    
    if (urlParams.get('error')) {
        showMessage(urlParams.get('error'), 'error');
    }
    
    if (urlParams.get('logged_out')) {
        showMessage('You have been successfully logged out.', 'info');
    }
    
    if (urlParams.get('welcome')) {
        showMessage('Welcome to Volunteer Connect!', 'success');
    }
}

/**
 * Show a message to the user
 */
function showMessage(message, type = 'info') {
    const messageContainer = document.createElement('div');
    messageContainer.className = `message message-${type} fade-in`;
    
    const icon = getMessageIcon(type);
    messageContainer.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${escapeHtml(message)}</span>
        <button class="message-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Insert at the top of the main content
    const main = document.querySelector('main');
    if (main) {
        main.insertBefore(messageContainer, main.firstChild);
    }
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (messageContainer.parentNode) {
            messageContainer.remove();
        }
    }, 5000);
    
    // Scroll to top to show message
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Get icon for message type
 */
function getMessageIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Auto-hide existing messages
 */
function autoHideMessages() {
    document.querySelectorAll('.message').forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
}

/**
 * Toggle mobile menu
 */
function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (navLinks && toggle) {
        navLinks.classList.toggle('active');
        toggle.classList.toggle('active');
    }
}

/**
 * Handle dropdown menus
 */
function handleDropdown(e) {
    e.preventDefault();
    const dropdown = e.currentTarget.nextElementSibling;
    const allDropdowns = document.querySelectorAll('.dropdown-menu');
    
    // Close all other dropdowns
    allDropdowns.forEach(d => {
        if (d !== dropdown) {
            d.classList.remove('active');
        }
    });
    
    // Toggle current dropdown
    dropdown.classList.toggle('active');
}

/**
 * Handle tab switching
 */
function handleTabSwitch(e) {
    const tabContainer = e.currentTarget.parentElement;
    const tabContent = document.querySelector('.tab-content');
    
    // Remove active class from all tabs
    tabContainer.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Add active class to clicked tab
    e.currentTarget.classList.add('active');
    
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Show corresponding tab content
    const targetTab = e.currentTarget.getAttribute('data-target') || 
                     e.currentTarget.textContent.toLowerCase().replace(' ', '') + 'Tab';
    const targetContent = document.getElementById(targetTab);
    
    if (targetContent) {
        targetContent.style.display = 'block';
        targetContent.classList.add('fade-in');
    }
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

/**
 * Show tooltip
 */
function showTooltip(e) {
    const text = e.currentTarget.getAttribute('data-tooltip');
    if (!text) return;
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    
    document.body.appendChild(tooltip);
    
    const rect = e.currentTarget.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
    
    setTimeout(() => tooltip.classList.add('active'), 10);
}

/**
 * Hide tooltip
 */
function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

/**
 * Initialize modals
 */
function initializeModals() {
    // Handle modal triggers
    document.querySelectorAll('[data-modal]').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            showModal(modalId);
        });
    });
}

/**
 * Show modal
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus first input in modal
        const firstInput = modal.querySelector('input, textarea, button');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

/**
 * Close modal
 */
function closeModal() {
    const activeModal = document.querySelector('.modal.active');
    if (activeModal) {
        activeModal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Handle AJAX form submission
 */
function handleAjaxForm(e) {
    e.preventDefault();
    const form = e.currentTarget;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    
    // Create FormData
    const formData = new FormData(form);
    
    // Send AJAX request
    fetch(form.action, {
        method: form.method,
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message || 'Operation successful!', 'success');
            
            // Reset form if needed
            if (form.dataset.reset === 'true') {
                form.reset();
            }
            
            // Redirect if specified
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
            
            // Execute callback if specified
            if (form.dataset.callback) {
                window[form.dataset.callback](data);
            }
        } else {
            showMessage(data.message || 'An error occurred.', 'error');
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        showMessage('A network error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

/**
 * Handle search with debouncing
 */
function handleSearch(e) {
    const searchInput = e.currentTarget;
    const searchTerm = searchInput.value.trim();
    const searchType = searchInput.dataset.searchType || 'opportunities';
    
    if (searchTerm.length < 2) {
        hideSearchResults();
        return;
    }
    
    // Show loading indicator
    showSearchLoader(searchInput);
    
    // Perform search
    performSearch(searchTerm, searchType);
}

/**
 * Perform AJAX search
 */
function performSearch(term, type) {
    const searchUrl = `api/search.php?q=${encodeURIComponent(term)}&type=${type}`;
    
    fetch(searchUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        displaySearchResults(data, type);
    })
    .catch(error => {
        console.error('Search error:', error);
        hideSearchResults();
    });
}

/**
 * Display search results
 */
function displaySearchResults(results, type) {
    const container = document.querySelector('.search-results');
    if (!container) return;
    
    hideSearchLoader();
    
    if (results.length === 0) {
        container.innerHTML = '<div class="search-no-results">No results found</div>';
        container.style.display = 'block';
        return;
    }
    
    let html = '<div class="search-results-list">';
    results.forEach(result => {
        html += createSearchResultItem(result, type);
    });
    html += '</div>';
    
    container.innerHTML = html;
    container.style.display = 'block';
}

/**
 * Create search result item HTML
 */
function createSearchResultItem(result, type) {
    if (type === 'opportunities') {
        return `
            <div class="search-result-item">
                <h4><a href="opportunity-details.php?id=${result.id}">${escapeHtml(result.title)}</a></h4>
                <p>${escapeHtml(result.organization_name)}</p>
                <div class="search-result-meta">
                    <span><i class="fas fa-map-marker-alt"></i> ${escapeHtml(result.location)}</span>
                    <span><i class="fas fa-clock"></i> ${escapeHtml(result.time_commitment)}</span>
                </div>
            </div>
        `;
    } else if (type === 'volunteers') {
        return `
            <div class="search-result-item">
                <h4><a href="volunteer-profile.php?id=${result.id}">${escapeHtml(result.full_name)}</a></h4>
                <p>${escapeHtml(result.location)}</p>
                <div class="search-result-meta">
                    <span><i class="fas fa-clock"></i> ${escapeHtml(result.availability)}</span>
                </div>
            </div>
        `;
    }
}

/**
 * Hide search results
 */
function hideSearchResults() {
    const container = document.querySelector('.search-results');
    if (container) {
        container.style.display = 'none';
        container.innerHTML = '';
    }
}

/**
 * Show search loader
 */
function showSearchLoader(input) {
    const loader = document.createElement('div');
    loader.className = 'search-loader';
    loader.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    input.parentElement.appendChild(loader);
}

/**
 * Hide search loader
 */
function hideSearchLoader() {
    const loader = document.querySelector('.search-loader');
    if (loader) {
        loader.remove();
    }
}

/**
 * Handle file upload
 */
function handleFileUpload(e) {
    const input = e.currentTarget;
    const file = input.files[0];
    
    if (!file) return;
    
    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        showMessage('File size must be less than 5MB', 'error');
        input.value = '';
        return;
    }
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showMessage('Only JPG, PNG, GIF, and WebP images are allowed', 'error');
        input.value = '';
        return;
    }
    
    // Show preview if it's an image
    if (file.type.startsWith('image/')) {
        showImagePreview(file, input);
    }
}

/**
 * Show image preview
 */
function showImagePreview(file, input) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const preview = document.createElement('div');
        preview.className = 'image-preview';
        preview.innerHTML = `
            <img src="${e.target.result}" alt="Preview">
            <button type="button" class="remove-preview" onclick="this.parentElement.remove(); ${input.id}.value = '';">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Insert preview after input
        input.parentElement.appendChild(preview);
    };
    
    reader.readAsDataURL(file);
}

/**
 * Check password strength
 */
function checkPasswordStrength(e) {
    const password = e.currentTarget.value;
    const strengthIndicator = document.getElementById('passwordStrength');
    
    if (!strengthIndicator) return;
    
    let strength = 0;
    
    // Check length
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    
    // Check for lowercase, uppercase, numbers, special characters
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    // Update strength indicator
    strengthIndicator.className = 'password-strength';
    
    if (password.length === 0) {
        strengthIndicator.innerHTML = '';
    } else if (strength <= 2) {
        strengthIndicator.innerHTML = '<div class="password-strength-bar strength-weak"></div>';
        strengthIndicator.innerHTML += '<small style="color: var(--danger-color);">Weak password</small>';
    } else if (strength <= 4) {
        strengthIndicator.innerHTML = '<div class="password-strength-bar strength-medium"></div>';
        strengthIndicator.innerHTML += '<small style="color: var(--warning-color);">Medium strength</small>';
    } else {
        strengthIndicator.innerHTML = '<div class="password-strength-bar strength-strong"></div>';
        strengthIndicator.innerHTML += '<small style="color: var(--success-color);">Strong password</small>';
    }
}

/**
 * Initialize smooth scrolling
 */
function initializeSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('blur', function() {
                validateField(this);
            });
        });
    });
}

/**
 * Validate entire form
 */
function validateForm(form) {
    let isValid = true;
    
    form.querySelectorAll('input, textarea, select').forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    let isValid = true;
    let errorMessage = '';
    
    // Remove previous error
    removeFieldError(field);
    
    // Required validation
    if (required && !value) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Email validation
    if (type === 'email' && value && !isValidEmail(value)) {
        isValid = false;
        errorMessage = 'Please enter a valid email address';
    }
    
    // Password confirmation
    if (field.id === 'confirmPassword') {
        const password = document.getElementById('password').value;
        if (value && value !== password) {
            isValid = false;
            errorMessage = 'Passwords do not match';
        }
    }
    
    // Min/Max length
    if (value) {
        const minLength = field.getAttribute('minlength');
        const maxLength = field.getAttribute('maxlength');
        
        if (minLength && value.length < parseInt(minLength)) {
            isValid = false;
            errorMessage = `Minimum length is ${minLength} characters`;
        }
        
        if (maxLength && value.length > parseInt(maxLength)) {
            isValid = false;
            errorMessage = `Maximum length is ${maxLength} characters`;
        }
    }
    
    // Show error if invalid
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('error');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    
    field.parentElement.appendChild(errorElement);
}

/**
 * Remove field error
 */
function removeFieldError(field) {
    field.classList.remove('error');
    
    const errorElement = field.parentElement.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
}

/**
 * Initialize lazy loading for images
 */
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => {
            img.classList.add('lazy');
            imageObserver.observe(img);
        });
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

/**
 * Utility functions
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function formatDate(date, format = 'short') {
    const options = {
        short: { year: 'numeric', month: 'short', day: 'numeric' },
        long: { year: 'numeric', month: 'long', day: 'numeric' },
        time: { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }
    };
    
    return new Date(date).toLocaleDateString('en-US', options[format] || options.short);
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Expose global functions
window.showMessage = showMessage;
window.closeModal = closeModal;