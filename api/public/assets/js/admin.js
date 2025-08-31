/**
 * BC Marl Drinks Admin Interface JavaScript
 */

// Global admin utilities
window.AdminApp = {
    // API base URL
    apiBaseUrl: '/v1',
    
    // Get auth token
    getToken() {
        return localStorage.getItem('adminToken');
    },
    
    // Check if user is authenticated
    isAuthenticated() {
        const token = this.getToken();
        if (!token) return false;
        
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            return payload.exp > Date.now() / 1000;
        } catch (e) {
            return false;
        }
    },
    
    // Redirect to login if not authenticated
    requireAuth() {
        if (!this.isAuthenticated()) {
            window.location.href = '/admin/login';
            return false;
        }
        return true;
    },
    
    // Logout
    logout() {
        localStorage.removeItem('adminToken');
        window.location.href = '/admin/login';
    },
    
    // Make authenticated API request
    async apiRequest(endpoint, options = {}) {
        const token = this.getToken();
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                ...(token ? { 'Authorization': `Bearer ${token}` } : {})
            }
        };
        
        const finalOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        };
        
        try {
            const response = await fetch(`${this.apiBaseUrl}${endpoint}`, finalOptions);
            
            if (response.status === 401) {
                this.logout();
                return null;
            }
            
            return response;
        } catch (error) {
            console.error('API Request failed:', error);
            this.showError('Netzwerkfehler beim Laden der Daten');
            return null;
        }
    },
    
    // Show success message
    showSuccess(message) {
        this.showAlert(message, 'success');
    },
    
    // Show error message
    showError(message) {
        this.showAlert(message, 'danger');
    },
    
    // Show alert
    showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert.alert-floating');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create alert element
        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${type} alert-dismissible fade show alert-floating`;
        alertElement.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        `;
        alertElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertElement);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertElement.parentNode) {
                alertElement.remove();
            }
        }, 5000);
    },
    
    // Format currency
    formatCurrency(cents) {
        return new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR'
        }).format(cents / 100);
    },
    
    // Format date
    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('de-DE');
    },
    
    // Format datetime
    formatDateTime(dateString) {
        return new Date(dateString).toLocaleString('de-DE');
    },
    
    // Confirm dialog
    async confirm(message, title = 'Bestätigen') {
        return new Promise((resolve) => {
            const result = confirm(`${title}\n\n${message}`);
            resolve(result);
        });
    },
    
    // Show loading state
    showLoading(element) {
        const originalContent = element.innerHTML;
        element.innerHTML = `
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Lädt...</span>
            </div>
            Lädt...
        `;
        
        return () => {
            element.innerHTML = originalContent;
        };
    }
};

// Initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication on protected pages
    if (window.location.pathname.startsWith('/admin') && 
        !window.location.pathname.includes('/login')) {
        AdminApp.requireAuth();
    }
    
    // Setup logout handlers
    const logoutLinks = document.querySelectorAll('a[href="/admin/logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            AdminApp.logout();
        });
    });
    
    // Setup form validations
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Setup tooltips if Bootstrap tooltips are available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Auto-refresh data every 5 minutes on dashboard
    if (window.location.pathname === '/admin' || window.location.pathname === '/admin/') {
        setInterval(() => {
            if (typeof loadDashboardData === 'function') {
                loadDashboardData();
            }
        }, 5 * 60 * 1000); // 5 minutes
    }
});

// Utility functions for common operations
window.AdminUtils = {
    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Export table to CSV
    exportTableToCSV(tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        let csv = '';
        const rows = table.querySelectorAll('tr');
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const rowData = Array.from(cols).map(col => {
                return '"' + col.textContent.trim().replace(/"/g, '""') + '"';
            });
            csv += rowData.join(',') + '\n';
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename || 'export.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    },
    
    // Copy to clipboard
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            AdminApp.showSuccess('In Zwischenablage kopiert');
        } catch (err) {
            console.error('Failed to copy to clipboard:', err);
            AdminApp.showError('Fehler beim Kopieren in die Zwischenablage');
        }
    },
    
    // Validate email
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },
    
    // Format file size
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
};

// Error handling
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    AdminApp.showError('Ein unerwarteter Fehler ist aufgetreten');
});

window.addEventListener('error', function(event) {
    console.error('JavaScript error:', event.error);
    if (event.error && event.error.message.includes('fetch')) {
        AdminApp.showError('Netzwerkfehler - bitte versuchen Sie es später erneut');
    }
});
