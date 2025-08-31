<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="bi bi-graph-up text-primary"></i>
            Statistiken
        </h1>
    </div>
</div>

<!-- Revenue Stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 id="todayRevenue">-</h3>
                <p class="mb-0">Umsatz heute</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3 id="weekRevenue">-</h3>
                <p class="mb-0">Umsatz diese Woche</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3 id="monthRevenue">-</h3>
                <p class="mb-0">Umsatz diesen Monat</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Produkte</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="topProductsTable">
                        <thead>
                            <tr>
                                <th>Rang</th>
                                <th>Produkt</th>
                                <th>Buchungen</th>
                                <th>Umsatz</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="text-center">
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
                <h5 class="card-title mb-0">Kategorie-Verteilung</h5>
            </div>
            <div class="card-body">
                <div id="categoryChart">
                    <div class="text-center">
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
    loadStatistics();
});

async function loadStatistics() {
    const token = localStorage.getItem('adminToken');
    
    try {
        const [revenueResponse, topProductsResponse, categoriesResponse] = await Promise.all([
            fetch('/v1/stats/revenue', {
                headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' }
            }),
            fetch('/v1/stats/top-products', {
                headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' }
            }),
            fetch('/v1/stats/categories', {
                headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' }
            })
        ]);
        
        if (revenueResponse.ok) {
            const revenue = await revenueResponse.json();
            displayRevenue(revenue.data);
        }
        
        if (topProductsResponse.ok) {
            const topProducts = await topProductsResponse.json();
            displayTopProducts(topProducts.data || []);
        }
        
        if (categoriesResponse.ok) {
            const categories = await categoriesResponse.json();
            displayCategories(categories.data || []);
        }
        
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

function displayRevenue(revenue) {
    if (revenue) {
        document.getElementById('todayRevenue').textContent = 
            new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' })
                .format(revenue.today / 100);
        document.getElementById('weekRevenue').textContent = 
            new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' })
                .format(revenue.week / 100);
        document.getElementById('monthRevenue').textContent = 
            new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' })
                .format(revenue.month / 100);
    }
}

function displayTopProducts(products) {
    const tbody = document.querySelector('#topProductsTable tbody');
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Keine Daten vorhanden</td></tr>';
        return;
    }
    
    tbody.innerHTML = products.map((product, index) => `
        <tr>
            <td><span class="badge bg-primary">#${index + 1}</span></td>
            <td>${product.name}</td>
            <td>${product.totalBookings}</td>
            <td>${new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(product.totalRevenue / 100)}</td>
        </tr>
    `).join('');
}

function displayCategories(categories) {
    const container = document.getElementById('categoryChart');
    
    if (categories.length === 0) {
        container.innerHTML = '<div class="text-muted text-center">Keine Daten vorhanden</div>';
        return;
    }
    
    const total = categories.reduce((sum, cat) => sum + cat.totalBookings, 0);
    
    container.innerHTML = categories.map(category => {
        const percentage = Math.round((category.totalBookings / total) * 100);
        return `
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span>${getCategoryLabel(category.category)}</span>
                    <span>${percentage}%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: ${percentage}%"></div>
                </div>
                <small class="text-muted">${category.totalBookings} Buchungen</small>
            </div>
        `;
    }).join('');
}

function getCategoryLabel(category) {
    const labels = {
        'DRINKS': 'Getränke',
        'SNACKS': 'Snacks',
        'ACCESSORIES': 'Zubehör',
        'MEMBERSHIP': 'Mitgliedschaft'
    };
    return labels[category] || category;
}
</script>
