/**
 * Online Bookstore - Main JavaScript
 * نظام المكتبة الإلكترونية - ملف جافاسكربت الرئيسي
 * Modern FAANG-Level Features
 */

// =============================================
// Mobile Menu Enhanced
// =============================================
function toggleMobileMenu() {
    const nav = document.getElementById('main-nav');
    const toggle = document.getElementById('mobile-menu-toggle');
    const body = document.body;
    const isActive = nav.classList.toggle('active');
    
    // Update ARIA attributes
    toggle.setAttribute('aria-expanded', isActive);
    toggle.setAttribute('aria-label', isActive ? 'Close Menu' : 'Open Menu');
    
    // Toggle body class for scroll lock
    if (isActive) {
        body.classList.add('menu-open');
    } else {
        body.classList.remove('menu-open');
    }
    
    // Animate toggle icon
    const icon = toggle.querySelector('i');
    if (icon) {
        icon.setAttribute('data-feather', isActive ? 'x' : 'menu');
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
    
    // Animate menu items with stagger
    if (isActive) {
        const navItems = nav.querySelectorAll('li');
        navItems.forEach((item, index) => {
            item.style.animation = `slideInMobile 0.3s ease ${index * 0.05}s forwards`;
        });
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(e) {
    const nav = document.getElementById('main-nav');
    const toggle = document.getElementById('mobile-menu-toggle');
    
    if (nav && toggle && nav.classList.contains('active')) {
        const isClickInsideNav = nav.contains(e.target);
        const isClickOnToggle = toggle.contains(e.target);
        
        if (!isClickInsideNav && !isClickOnToggle) {
            toggleMobileMenu();
        }
    }
});

// Close mobile menu when clicking on nav links
document.addEventListener('DOMContentLoaded', function() {
    const nav = document.getElementById('main-nav');
    if (nav) {
        const navLinks = nav.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768 && nav.classList.contains('active')) {
                    toggleMobileMenu();
                }
            });
        });
    }
});

// Close mobile menu on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const nav = document.getElementById('main-nav');
        const toggle = document.getElementById('mobile-menu-toggle');
        if (nav && nav.classList.contains('active')) {
            toggleMobileMenu();
            toggle.focus();
        }
    }
});

// Handle window resize
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        const nav = document.getElementById('main-nav');
        const body = document.body;
        
        // Close menu and reset body scroll if window is resized above mobile breakpoint
        if (window.innerWidth > 768 && nav && nav.classList.contains('active')) {
            nav.classList.remove('active');
            body.classList.remove('menu-open');
            
            const toggle = document.getElementById('mobile-menu-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
                const icon = toggle.querySelector('i');
                if (icon) {
                    icon.setAttribute('data-feather', 'menu');
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                }
            }
        }
    }, 250);
});

// =============================================
// Scroll-triggered Animations
// =============================================
class ScrollAnimations {
    constructor() {
        this.observer = null;
        this.init();
    }

    init() {
        // Intersection Observer for scroll animations
        const options = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // Optionally unobserve after animation
                    // this.observer.unobserve(entry.target);
                }
            });
        }, options);

        // Observe all elements with animate-on-scroll class
        this.observeElements();
    }

    observeElements() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        elements.forEach(el => this.observer.observe(el));
    }

    // Call this method when new content is added dynamically
    refresh() {
        this.observeElements();
    }
}

// =============================================
// Header Scroll Effect
// =============================================
let lastScrollTop = 0;
window.addEventListener('scroll', () => {
    const header = document.querySelector('.main-header');
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    // Add scrolled class for shadow
    if (scrollTop > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
    
    lastScrollTop = scrollTop;
}, { passive: true });

// =============================================
// Real-time Search Suggestions
// =============================================
class SearchSuggestions {
    constructor() {
        this.input = document.getElementById('search-input');
        this.container = document.getElementById('search-suggestions');
        this.debounceTimer = null;
        this.minChars = 2;
        
        if (this.input && this.container) {
            this.init();
        }
    }

    init() {
        this.input.addEventListener('input', (e) => {
            clearTimeout(this.debounceTimer);
            const query = e.target.value.trim();
            
            if (query.length >= this.minChars) {
                this.debounceTimer = setTimeout(() => {
                    this.fetchSuggestions(query);
                }, 300);
            } else {
                this.hideSuggestions();
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.input.contains(e.target) && !this.container.contains(e.target)) {
                this.hideSuggestions();
            }
        });

        // Keyboard navigation
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideSuggestions();
            }
        });
    }

    async fetchSuggestions(query) {
        try {
            // Simulate API call - replace with actual endpoint
            // const response = await fetch(`/api/search-suggestions.php?q=${encodeURIComponent(query)}`);
            // const data = await response.json();
            
            // Mock data for demonstration
            const mockData = [
                { title: 'البحث عن: ' + query, type: 'query' },
                { title: 'كتاب مشهور 1', author: 'المؤلف 1', type: 'book' },
                { title: 'كتاب مشهور 2', author: 'المؤلف 2', type: 'book' }
            ];
            
            this.showSuggestions(mockData);
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }

    showSuggestions(items) {
        if (items.length === 0) {
            this.hideSuggestions();
            return;
        }

        const html = items.map(item => {
            if (item.type === 'query') {
                return `
                    <div class="search-suggestion-item">
                        <i class="ph ph-magnifying-glass"></i>
                        <span>${item.title}</span>
                    </div>
                `;
            } else {
                return `
                    <div class="search-suggestion-item">
                        <i class="ph ph-book"></i>
                        <div>
                            <div><strong>${item.title}</strong></div>
                            <small>${item.author}</small>
                        </div>
                    </div>
                `;
            }
        }).join('');

        this.container.innerHTML = html;
        this.container.classList.add('active');
    }

    hideSuggestions() {
        this.container.classList.remove('active');
    }
}

// =============================================
// Image Lazy Loading (with fallback for older browsers)
// =============================================
function initLazyLoading() {
    if ('loading' in HTMLImageElement.prototype) {
        // Native lazy loading supported
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    } else {
        // Fallback to Intersection Observer
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => imageObserver.observe(img));
    }
}

// =============================================
// Modern Toast Notification System
// =============================================
class Toast Notification {
    constructor() {
        this.container = document.getElementById('toast-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    }

    show(message, type = 'info', title = null, duration = 4000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icons = {
            success: 'ph-check-circle',
            error: 'ph-x-circle',
            warning: 'ph-warning-circle',
            info: 'ph-info'
        };

        const titles = {
            success: 'نجح',
            error: 'خطأ',
            warning: 'تحذير',
            info: 'معلومة'
        };

        toast.innerHTML = `
            <i class="ph-duotone ${icons[type]} toast-icon"></i>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : `<div class="toast-title">${titles[type]}</div>`}
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="ph ph-x"></i>
            </button>
        `;

        this.container.appendChild(toast);

        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        return toast;
    }

    success(message, title = null, duration = 4000) {
        return this.show(message, 'success', title, duration);
    }

    error(message, title = null, duration = 5000) {
        return this.show(message, 'error', title, duration);
    }

    warning(message, title = null, duration = 4000) {
        return this.show(message, 'warning', title, duration);
    }

    info(message, title = null, duration = 4000) {
        return this.show(message, 'info', title, duration);
    }
}

// Create global toast instance
const toast = new ToastNotification();

// Legacy function for backward compatibility
function showNotification(message, type = 'info') {
    toast.show(message, type);
}

// =============================================
// Enhanced Form Validation
// =============================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        clearError(field);
        
        if (!field.value.trim()) {
            showError(field, 'هذا الحقل مطلوب');
            isValid = false;
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showError(field, 'البريد الإلكتروني غير صالح');
            isValid = false;
        }
    });
    
    // Phone validation
    const phoneFields = form.querySelectorAll('[name*="phone"]');
    phoneFields.forEach(field => {
        if (field.value && !isValidPhone(field.value)) {
            showError(field, 'رقم الهاتف غير صالح');
            isValid = false;
        }
    });
    
    return isValid;
}

function showError(field, message) {
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function clearError(field) {
    field.classList.remove('error');
    const existingError = field.parentNode.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidPhone(phone) {
    const re = /^[0-9+\-\s()]{10,15}$/;
    return re.test(phone);
}

// Credit Card Validation
function validateCreditCard(cardNumber) {
    // Remove spaces and dashes
    cardNumber = cardNumber.replace(/[\s-]/g, '');
    
    // Check if it's 16 digits
    if (!/^\d{16}$/.test(cardNumber)) {
        return false;
    }
    
    // Luhn algorithm
    let sum = 0;
    let isEven = false;
    
    for (let i = cardNumber.length - 1; i >= 0; i--) {
        let digit = parseInt(cardNumber[i], 10);
        
        if (isEven) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }
        
        sum += digit;
        isEven = !isEven;
    }
    
    return sum % 10 === 0;
}

function formatCreditCard(input) {
    let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = '';
    
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    
    input.value = formattedValue.substring(0, 19);
}

function formatExpiryDate(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    
    input.value = value;
}

// Cart Functions
function updateCartQuantity(itemId, quantity) {
    if (quantity < 1) {
        removeFromCart(itemId);
        return;
    }
    
    fetch('/customer/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Cart update error:', error);
        showNotification('حدث خطأ أثناء تحديث السلة', 'error');
    });
}

function addToCart(bookIsbn, quantity = 1) {
    fetch('/customer/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `book_isbn=${bookIsbn}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('تمت إضافة الكتاب إلى السلة', 'success');
            updateCartCount(data.cartCount);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Add to cart error:', error);
        showNotification('حدث خطأ أثناء الإضافة إلى السلة', 'error');
    });
}

function removeFromCart(itemId) {
    if (!confirm('هل تريد حذف هذا العنصر من السلة؟')) {
        return;
    }
    
    fetch('/customer/remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
            if (itemElement) {
                itemElement.remove();
            }
            updateCartDisplay(data);
            showNotification('تم حذف العنصر من السلة', 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Remove from cart error:', error);
        showNotification('حدث خطأ أثناء الحذف', 'error');
    });
}

function updateCartDisplay(data) {
    const totalElement = document.querySelector('.cart-summary-total span:last-child');
    if (totalElement) {
        totalElement.textContent = data.total + ' ريال سعودي';
    }
    
    updateCartCount(data.cartCount);
}

function updateCartCount(count) {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
        cartCountElement.style.display = count > 0 ? 'inline' : 'none';
    }
}

// Notification System
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">×</button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        padding: 15px 25px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 15px;
        z-index: 9999;
        animation: slideDown 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#d4edda';
        notification.style.color = '#155724';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#f8d7da';
        notification.style.color = '#721c24';
    } else {
        notification.style.backgroundColor = '#d1ecf1';
        notification.style.color = '#0c5460';
    }
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Search Functions
function searchBooks(query) {
    if (!query.trim()) {
        showNotification('الرجاء إدخال كلمة للبحث', 'error');
        return;
    }
    
    window.location.href = `/search.php?q=${encodeURIComponent(query)}`;
}

// Debounce Function
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

// Price Formatting
function formatPrice(price) {
    return parseFloat(price).toFixed(2) + ' ريال سعودي';
}

// Date Formatting (Arabic)
function formatArabicDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        calendar: 'gregory'
    };
    return date.toLocaleDateString('ar-SA', options);
}

// Confirmation Dialog
function confirmAction(message) {
    return confirm(message);
}

// Print Report
function printReport(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <title>تقرير - المكتبة الإلكترونية</title>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Cairo', sans-serif; direction: rtl; padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: right; }
                th { background-color: #006c35; color: white; }
                h1 { color: #006c35; }
                .report-header { text-align: center; margin-bottom: 20px; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class="report-header">
                <h1>📚 المكتبة الإلكترونية</h1>
                <p>التاريخ: ${formatArabicDate(new Date())}</p>
            </div>
            ${element.innerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Export to CSV
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = '\uFEFF' + csv.join('\n'); // BOM for Arabic support
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.csv';
    link.click();
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Scroll Animations
    const scrollAnimations = new ScrollAnimations();
    
    // Initialize Search Suggestions
    if (document.getElementById('search-input')) {
        new SearchSuggestions();
    }
    
    // Initialize Lazy Loading
    initLazyLoading();
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Form validation on submit
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form.id)) {
                e.preventDefault();
            }
        });
    });
    
    // Credit card formatting
    const cardInput = document.querySelector('[name="card_number"]');
    if (cardInput) {
        cardInput.addEventListener('input', function() {
            formatCreditCard(this);
        });
    }
    
    const expiryInput = document.querySelector('[name="expiry"]');
    if (expiryInput) {
        expiryInput.addEventListener('input', function() {
            formatExpiryDate(this);
        });
    }
    
    // Add animate-on-scroll class to cards and sections
    const animatableElements = document.querySelectorAll('.book-card, .card, .stat-card, .hero');
    animatableElements.forEach(el => {
        el.classList.add('animate-on-scroll');
    });
    scrollAnimations.refresh();
    
    // Keyboard shortcuts
    setupKeyboardShortcuts();
});

// =============================================
// Loading Overlay
// =============================================
const LoadingOverlay = {
    show() {
        let overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = '<div class="loading-spinner"><i class="ph-duotone ph-spinner"></i></div>';
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
        setTimeout(() => overlay.classList.add('active'), 10);
    },
    
    hide() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('active');
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }
    }
};

// =============================================
// Enhanced Modal System
// =============================================
class ModalManager {
    static open(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Focus first focusable element
        const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable) {
            setTimeout(() => focusable.focus(), 100);
        }
        
        // Store last focused element
        modal.dataset.lastFocus = document.activeElement;
        
        // Trap focus within modal
        this.trapFocus(modal);
    }
    
    static close(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Return focus to last element
        const lastFocus = document.querySelector(modal.dataset.lastFocus);
        if (lastFocus) {
            lastFocus.focus();
        }
    }
    
    static trapFocus(modal) {
        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        modal.addEventListener('keydown', function(e) {
            if (e.key !== 'Tab') return;
            
            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        });
        
        // Close on escape
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                ModalManager.close(modal.id);
            }
        });
    }
}

// Backward compatibility
function openModal(modalId) {
    ModalManager.open(modalId);
}

function closeModal(modalId) {
    ModalManager.close(modalId);
}

// =============================================
// Keyboard Shortcuts & Accessibility
// =============================================
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K: Focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // Ctrl/Cmd + /: Show keyboard shortcuts
        if ((e.ctrlKey || e.metaKey) && e.key === '/') {
            e.preventDefault();
            showKeyboardShortcuts();
        }
        
        // D: Toggle dark mode
        if (e.key === 'd' && !isInputFocused()) {
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.click();
            }
        }
    });
}

function isInputFocused() {
    const activeElement = document.activeElement;
    return activeElement && (
        activeElement.tagName === 'INPUT' ||
        activeElement.tagName === 'TEXTAREA' ||
        activeElement.isContentEditable
    );
}

function showKeyboardShortcuts() {
    toast.info(`
        <strong>اختصارات لوحة المفاتيح:</strong><br>
        Ctrl+K: البحث<br>
        D: تبديل الوضع الليلي<br>
        Esc: إغلاق النوافذ المنبثقة
    `, 'مساعدة', 8000);
}

// =============================================
// Enhanced Cart Functions with Loading
// =============================================
const CartManager = {
    async addItem(bookIsbn, quantity = 1) {
        LoadingOverlay.show();
        
        try {
            const response = await fetch('/customer/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `book_isbn=${bookIsbn}&quantity=${quantity}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                toast.success('تمت إضافة الكتاب إلى السلة');
                updateCartCount(data.cartCount);
                
                // Animate cart icon
                const cartIcon = document.querySelector('.cart-icon');
                if (cartIcon) {
                    cartIcon.style.animation = 'bounce 0.5s ease';
                    setTimeout(() => {
                        cartIcon.style.animation = '';
                    }, 500);
                }
            } else {
                toast.error(data.message || 'فشل في إضافة الكتاب');
            }
        } catch (error) {
            console.error('Cart error:', error);
            toast.error('حدث خطأ في الاتصال');
        } finally {
            LoadingOverlay.hide();
        }
    }
};

// =============================================
// Smooth Page Transitions
// =============================================
function setupPageTransitions() {
    // Add fade-out effect on page navigation
    document.querySelectorAll('a:not([target="_blank"])').forEach(link => {
        link.addEventListener('click', function(e) {
            // Skip for same-page anchors
            if (this.getAttribute('href').startsWith('#')) return;
            
            e.preventDefault();
            const href = this.getAttribute('href');
            
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.2s ease';
            
            setTimeout(() => {
                window.location.href = href;
            }, 200);
        });
    });
    
    // Fade in on page load
    window.addEventListener('load', () => {
        document.body.style.opacity = '1';
    });
}

// =============================================
// Performance Monitoring
// =============================================
if ('PerformanceObserver' in window) {
    // Monitor long tasks
    try {
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (entry.duration > 50) {
                    console.warn('Long task detected:', entry.duration + 'ms');
                }
            }
        });
        observer.observe({ entryTypes: ['longtask'] });
    } catch (e) {
        // Long task API not supported
    }
}

// Remove old animation style (already in CSS)
const oldStyle = document.querySelector('style');
if (oldStyle && oldStyle.textContent.includes('slideDown')) {
    oldStyle.remove();
}

// Export for use in other scripts
window.BookstoreApp = {
    toast,
    LoadingOverlay,
    ModalManager,
    CartManager,
    ThemeManager
};

