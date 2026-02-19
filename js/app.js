// ============================================
// Plant Nursery Management System — App Module
// ============================================

const API_BASE = '/api';

// ---- API Helper ----
const api = {
    async get(endpoint) {
        const res = await fetch(`${API_BASE}/${endpoint}`);
        if (!res.ok) throw new Error(`API Error: ${res.status}`);
        return res.json();
    },
    async post(endpoint, data) {
        const res = await fetch(`${API_BASE}/${endpoint}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.error || `API Error: ${res.status}`);
        }
        return res.json();
    },
    async put(endpoint, data) {
        const res = await fetch(`${API_BASE}/${endpoint}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.error || `API Error: ${res.status}`);
        }
        return res.json();
    },
    async del(endpoint) {
        const res = await fetch(`${API_BASE}/${endpoint}`, { method: 'DELETE' });
        if (!res.ok) throw new Error(`API Error: ${res.status}`);
        return res.json();
    }
};

// ---- Toast Notifications ----
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const icons = { success: '✓', error: '✕', warning: '⚠' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${icons[type] || '●'}</span>
        <span class="toast-message">${message}</span>
    `;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ---- Modal Manager ----
function openModal(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// ---- Sidebar Toggle (mobile) ----
function initSidebar() {
    const toggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    // Mark active nav link
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
}

// ---- Skeleton Loaders ----
function showSkeleton(container, count = 3, type = 'card') {
    let html = '';
    for (let i = 0; i < count; i++) {
        html += `<div class="skeleton skeleton-${type}"></div>`;
    }
    container.innerHTML = html;
}

// ---- Lazy Load / Intersection Observer ----
function lazyLoadCards(container, renderFn, items, batchSize = 12) {
    let loaded = 0;

    function loadBatch() {
        const batch = items.slice(loaded, loaded + batchSize);
        batch.forEach(item => {
            const card = renderFn(item);
            container.insertAdjacentHTML('beforeend', card);
        });
        loaded += batch.length;

        // Animate in
        requestAnimationFrame(() => {
            container.querySelectorAll('.plant-card:not(.loaded)').forEach((el, i) => {
                setTimeout(() => {
                    el.classList.add('loaded');
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, i * 50);
            });
        });
    }

    // Initial load
    loadBatch();

    // Sentinel
    if (loaded < items.length) {
        const sentinel = document.createElement('div');
        sentinel.id = 'lazy-sentinel';
        sentinel.style.height = '1px';
        container.after(sentinel);

        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && loaded < items.length) {
                loadBatch();
                if (loaded >= items.length) observer.disconnect();
            }
        }, { rootMargin: '200px' });

        observer.observe(sentinel);
    }
}

// ---- Format helpers ----
function formatCurrency(amount) {
    return '₹' + parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function getStockBadge(qty, threshold) {
    if (qty <= 0) return '<span class="badge-status out-of-stock">Out of Stock</span>';
    if (qty <= threshold) return '<span class="badge-status low-stock">Low Stock</span>';
    return '<span class="badge-status in-stock">In Stock</span>';
}

function getStatusBadge(status) {
    return `<span class="badge-status ${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
}

function getCategoryBadge(category) {
    return `<span class="badge-category ${category}">${category}</span>`;
}

function getPlantEmoji(category) {
    const emojis = { flower: '🌸', fruit: '🍎', indoor: '🌿', outdoor: '🌳' };
    return emojis[category] || '🌱';
}

// ---- Debounce ----
function debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

// ---- Confirm Dialog ----
function confirmAction(message) {
    return new Promise(resolve => {
        if (confirm(message)) resolve(true);
        else resolve(false);
    });
}

// ---- Build sidebar HTML ----
function getSidebarHTML(activePage) {
    return `
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="logo-icon">🌿</div>
            <div>
                <h1>GreenHaven</h1>
                <p>Nursery Manager</p>
            </div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="index.html" class="nav-link ${activePage === 'index.html' ? 'active' : ''}">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="plants.html" class="nav-link ${activePage === 'plants.html' ? 'active' : ''}">
                <span class="nav-icon">🌱</span> Plants
            </a>
            <a href="inventory.html" class="nav-link ${activePage === 'inventory.html' ? 'active' : ''}">
                <span class="nav-icon">📦</span> Inventory
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Sales</div>
            <a href="orders.html" class="nav-link ${activePage === 'orders.html' ? 'active' : ''}">
                <span class="nav-icon">🛒</span> Orders
            </a>
            <a href="customers.html" class="nav-link ${activePage === 'customers.html' ? 'active' : ''}">
                <span class="nav-icon">👥</span> Customers
            </a>
            <a href="bills.html" class="nav-link ${activePage === 'bills.html' ? 'active' : ''}">
                <span class="nav-icon">🧾</span> Bills
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Care</div>
            <a href="schedules.html" class="nav-link ${activePage === 'schedules.html' ? 'active' : ''}">
                <span class="nav-icon">🗓️</span> Schedules
            </a>
        </div>
    </nav>
    <div class="sidebar-footer">
        <a href="#" class="nav-link" onclick="logout(); return false;">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </div>
    `;
}

// ---- Auth Guard ----
async function checkAuth() {
    try {
        const data = await api.get('admin.php?action=status');
        if (!data.logged_in) {
            window.location.href = 'login.html';
            return false;
        }
        return true;
    } catch (e) {
        window.location.href = 'login.html';
        return false;
    }
}

// ---- Init page with sidebar ----
async function initPage(pageName) {
    // Check authentication first
    const isAuthenticated = await checkAuth();
    if (!isAuthenticated) return;

    const sidebar = document.querySelector('.sidebar');
    if (sidebar) sidebar.innerHTML = getSidebarHTML(pageName);
    initSidebar();
}

// ---- Logout ----
async function logout() {
    try {
        await api.post('admin.php?action=logout', {});
    } catch (e) { }
    window.location.href = 'login.html';
}

// ---- DOM Ready ----
document.addEventListener('DOMContentLoaded', () => {
    const page = window.location.pathname.split('/').pop() || 'index.html';
    if (page !== 'login.html') {
        initPage(page);
    }
});
