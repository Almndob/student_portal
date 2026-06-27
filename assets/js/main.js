/**
 * Student Portal - Main JavaScript File
 */

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// Main initialization function
function initializeApp() {
    setupEventListeners();
    setupNotifications();
    setupCharts();
    
 startMessagePolling();
}

// Setup event listeners
function setupEventListeners() {
    // Sidebar active link
    const currentLocation = location.pathname;
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    
    menuItems.forEach(item => {
        if (item.getAttribute('href') === currentLocation) {
            item.classList.add('active');
        }
    });

    // Notification bell
    const notificationBell = document.querySelector('.notification-bell');
    if (notificationBell) {
        notificationBell.addEventListener('click', toggleNotifications);
    }

    // Profile dropdown
    const profilePicture = document.querySelector('.user-profile');
    if (profilePicture) {
        profilePicture.addEventListener('click', toggleProfileMenu);
    }

    // Form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });

    // Message input
    const messageForm = document.querySelector('.message-form');
    if (messageForm) {
        messageForm.addEventListener('submit', handleMessageSubmit);
    }
}

// Toggle notifications dropdown
function toggleNotifications(e) {
    e.stopPropagation();
    const dropdown = document.querySelector('.notification-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Toggle profile menu
function toggleProfileMenu(e) {
    e.stopPropagation();
    const menu = document.querySelector('.profile-menu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

// Handle form submission with validation
function handleFormSubmit(e) {
    const form = e.target;
    
    // Basic validation
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
            showNotification('Please fill all required fields', 'error');
        } else {
            input.classList.remove('error');
        }
    });

    if (!isValid) {
        e.preventDefault();
    }
}


function handleMessageSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const messageInput = form.querySelector('textarea');
    const message = messageInput.value.trim();

    if (!message) {
        showNotification('Please type a message', 'warning');
        return;
    }

    const formData = new FormData(form);
    
    fetch('/student_portal/includes/send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.message_data) {
            // مسح النص بعد الإرسال
            messageInput.value = '';

            // إضافة الرسالة مباشرة إلى الدردشة
            const container = document.getElementById('messagesArea');
            if (container) {
                const msg = data.message_data;
                const messageDiv = document.createElement('div');
                messageDiv.className = `message sent`;
                messageDiv.setAttribute('data-id', msg.id);
                messageDiv.innerHTML = `
                    <div class="message-content">
                        ${escapeHtml(msg.message)}
                        <div class="message-time">${formatTime(msg.created_at)}</div>
                    </div>
                `;
                container.appendChild(messageDiv);
                container.scrollTop = container.scrollHeight; // التمرير لأسفل
            }
        } else {
            showNotification(data.message || 'Error sending message', 'error');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        showNotification('Error sending message', 'error');
    });
}

function startMessagePolling() {
    setInterval(() => {
        loadMessages();
    }, 3000); // تحديث كل 3 ثواني
}

// Load messages dynamically
function loadMessages() {
   // const messageContainer = document.querySelector('messagesArea');
   const messageContainer = document.getElementById('messagesArea');

    if (!messageContainer) return;

    const receiverId = messageContainer.getAttribute('data-receiver-id');
    
    fetch(`/student_portal/includes/send_message.php?receiver_id=${receiverId}`)
    .then(response => response.json())
    .then(data => {
        if (data.messages) {
            renderMessages(data.messages);
        }
    })
    .catch(error => console.error('Error loading messages:', error));
}


function renderMessages(messages) {
    const container = document.getElementById('messagesArea'); 
    if (!container) return;

    const existingIds = Array.from(container.children).map(child => parseInt(child.getAttribute('data-id')));
    
    messages.forEach(msg => {
        if (!existingIds.includes(msg.id)) { // إضافة فقط الرسائل الجديدة
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${msg.is_sent ? 'sent' : 'received'}`;
            messageDiv.setAttribute('data-id', msg.id); // لتجنب التكرار
            messageDiv.innerHTML = `
                <div class="message-content">
                    ${escapeHtml(msg.message)}
                    <div class="message-time">${formatTime(msg.created_at)}</div>
                </div>
            `;
            container.appendChild(messageDiv);
        }
    });

    container.scrollTop = container.scrollHeight; // التمرير لأسفل
}

// Setup notifications
function setupNotifications() {
    // Auto-refresh notifications every 30 seconds
    setInterval(refreshNotifications, 30000);
}

// Refresh notifications
function refreshNotifications() {
    fetch('/student_portal/includes/get_notifications.php')
    .then(response => response.json())
    .then(data => {
        if (data.count > 0) {
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                badge.textContent = data.count;
            }
        }
    })
    .catch(error => console.error('Error refreshing notifications:', error));
}

// Setup charts (if Chart.js is available)
function setupCharts() {
    const chartElements = document.querySelectorAll('[data-chart]');
    
    if (chartElements.length > 0 && typeof Chart !== 'undefined') {
        chartElements.forEach(element => {
            const chartType = element.getAttribute('data-chart');
            const chartData = element.getAttribute('data-chart-data');
            
            if (chartData) {
                try {
                    const data = JSON.parse(chartData);
                    createChart(element, chartType, data);
                } catch (e) {
                    console.error('Error parsing chart data:', e);
                }
            }
        });
    }
}

// Create chart
function createChart(element, type, data) {
    const ctx = element.getContext('2d');
    
    const chartConfig = {
        type: type,
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        font: {
                            family: "'Poppins', sans-serif",
                            size: 12
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            family: "'Poppins', sans-serif"
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: "'Poppins', sans-serif"
                        }
                    }
                }
            }
        }
    };

    new Chart(ctx, chartConfig);
}

// Show notification toast
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} fade-in`;
    notification.innerHTML = `
        <i class="icon">${getIcon(type)}</i>
        <span>${message}</span>
    `;

    const container = document.querySelector('.content') || document.body;
    container.insertBefore(notification, container.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Get icon for notification type
function getIcon(type) {
    const icons = {
        'success': '✓',
        'error': '✕',
        'warning': '⚠',
        'info': 'ℹ'
    };
    return icons[type] || 'ℹ';
}

// Format time
function formatTime(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const diff = now - date;

    if (diff < 60000) {
        return 'just now';
    } else if (diff < 3600000) {
        return Math.floor(diff / 60000) + 'm ago';
    } else if (diff < 86400000) {
        return Math.floor(diff / 3600000) + 'h ago';
    } else {
        return date.toLocaleDateString();
    }
}

// Escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Logout function
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '/student_portal/logout.php';
    }
}

// Export to CSV
function exportToCSV(filename, data) {
    let csv = '';
    
    if (data.headers) {
        csv += data.headers.join(',') + '\n';
    }

    if (data.rows) {
        data.rows.forEach(row => {
            csv += row.join(',') + '\n';
        });
    }

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Print function
function printContent(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link rel="stylesheet" href="/student_portal/assets/css/style.css">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(element.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.notification-dropdown');
    const menu = document.querySelector('.profile-menu');
    
    if (dropdown && !dropdown.parentElement.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
    
    if (menu && !menu.parentElement.contains(event.target)) {
        menu.classList.add('hidden');
    }
});

// Prevent multiple form submissions
document.addEventListener('submit', function(e) {
    const button = e.target.querySelector('button[type="submit"]');
    if (button) {
        button.disabled = true;
        setTimeout(() => {
            button.disabled = false;
        }, 2000);
    }
});

