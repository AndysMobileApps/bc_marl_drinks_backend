<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="bi bi-house-door text-primary"></i>
            Dashboard
        </h1>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title" id="totalUsers">-</h4>
                        <p class="card-text">Benutzer</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title" id="totalProducts">-</h4>
                        <p class="card-text">Produkte</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-box fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title" id="todayBookings">-</h4>
                        <p class="card-text">Buchungen heute</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-journal-bookmark fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title" id="todayRevenue">-</h4>
                        <p class="card-text">Umsatz heute</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-euro fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i>
                    Letzte Buchungen
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm" id="recentBookingsTable">
                        <thead>
                            <tr>
                                <th>Zeit</th>
                                <th>Benutzer</th>
                                <th>Produkt</th>
                                <th>Menge</th>
                                <th>Betrag</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Lädt...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i>
                    Top Produkte
                </h5>
            </div>
            <div class="card-body">
                <div id="topProductsList">
                    <div class="text-center text-muted">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Lädt...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

async function loadDashboardData() {
    const token = localStorage.getItem('adminToken');
    
    if (!token) {
        window.location.href = '/admin/login';
        return;
    }
    
    const headers = {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    };
    
    try {
        // Load stats
        const [usersResponse, productsResponse, bookingsResponse] = await Promise.all([
            fetch('/v1/admin/users', { headers }),
            fetch('/v1/products', { headers }),
            fetch('/v1/bookings', { headers })
        ]);
        
        if (usersResponse.ok) {
            const users = await usersResponse.json();
            document.getElementById('totalUsers').textContent = users.users?.length || 0;
        }
        
        if (productsResponse.ok) {
            const products = await productsResponse.json();
            document.getElementById('totalProducts').textContent = products.products?.length || 0;
        }
        
        if (bookingsResponse.ok) {
            const bookings = await bookingsResponse.json();
            const allBookings = bookings.bookings || bookings.data || [];
            const today = new Date().toDateString();
            const todayBookings = allBookings.filter(b => 
                new Date(b.timestamp).toDateString() === today
            );
            
            document.getElementById('todayBookings').textContent = todayBookings.length;
            
            const todayRevenue = todayBookings.reduce((sum, b) => sum + b.totalCents, 0);
            document.getElementById('todayRevenue').textContent = 
                new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' })
                    .format(todayRevenue / 100);
                    
            loadRecentBookings(allBookings.slice(0, 10));
        }
        
        // Load top products
        const topProductsResponse = await fetch('/v1/stats/top-products', { headers });
        if (topProductsResponse.ok) {
            const topProducts = await topProductsResponse.json();
            loadTopProducts(topProducts.data || topProducts.products || []);
        }
        
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

function loadRecentBookings(bookings) {
    const tbody = document.querySelector('#recentBookingsTable tbody');
    
    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Keine Buchungen vorhanden</td></tr>';
        return;
    }
    
    tbody.innerHTML = bookings.map(booking => `
        <tr>
            <td>${new Date(booking.timestamp).toLocaleString('de-DE')}</td>
            <td>${booking.userId}</td>
            <td>${booking.productId}</td>
            <td>${booking.quantity}</td>
            <td>${new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(booking.totalCents / 100)}</td>
        </tr>
    `).join('');
}

function loadTopProducts(products) {
    const container = document.getElementById('topProductsList');
    
    if (products.length === 0) {
        container.innerHTML = '<div class="text-muted">Keine Daten vorhanden</div>';
        return;
    }
    
    container.innerHTML = products.map((product, index) => `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <strong>${product.name}</strong>
                <small class="text-muted d-block">${product.totalBookings} Buchungen</small>
            </div>
            <span class="badge bg-primary">#${index + 1}</span>
        </div>
    `).join('');
}
</script>
