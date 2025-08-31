<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-box text-primary"></i>
                Produktverwaltung
            </h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openProductModal()">
                <i class="bi bi-plus-circle"></i> Neues Produkt
            </button>
        </div>
    </div>
</div>

<!-- Filter and Search -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchProducts" placeholder="Produkte suchen...">
        </div>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="categoryFilter">
            <option value="">Alle Kategorien</option>
            <option value="DRINKS">Getränke</option>
            <option value="SNACKS">Snacks</option>
            <option value="ACCESSORIES">Zubehör</option>
            <option value="MEMBERSHIP">Mitgliedschaft</option>
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="statusFilter">
            <option value="">Alle Status</option>
            <option value="active">Aktiv</option>
            <option value="inactive">Inaktiv</option>
        </select>
    </div>
</div>

<!-- Products Grid -->
<div class="row" id="productsGrid">
    <div class="col-12 text-center">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Lädt...</span>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Produkt bearbeiten</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm">
                <div class="modal-body">
                    <input type="hidden" id="productId" name="productId">
                    
                    <div class="mb-3">
                        <label for="productName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productIcon" class="form-label">Icon (Bildpfad)</label>
                        <input type="text" class="form-control" id="productIcon" name="icon" placeholder="/images/icons/beer.png">
                        <div class="form-text">Pfad zur Icon-Datei (z.B. /images/icons/beer.png)</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productPrice" class="form-label">Preis (€)</label>
                                <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productCategory" class="form-label">Kategorie</label>
                        <select class="form-select" id="productCategory" name="category" required>
                            <option value="">Kategorie wählen</option>
                            <option value="DRINKS">Getränke</option>
                            <option value="SNACKS">Snacks</option>
                            <option value="ACCESSORIES">Zubehör</option>
                            <option value="MEMBERSHIP">Mitgliedschaft</option>
                        </select>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="productActive" name="active">
                        <label class="form-check-label" for="productActive">
                            Produkt aktiv
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let allProducts = [];

document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    
    document.getElementById('productForm').addEventListener('submit', handleProductSubmit);
    document.getElementById('searchProducts').addEventListener('input', filterProducts);
    document.getElementById('categoryFilter').addEventListener('change', filterProducts);
    document.getElementById('statusFilter').addEventListener('change', filterProducts);
});

async function loadProducts() {
    const token = localStorage.getItem('adminToken');
    
    try {
        const response = await fetch('/v1/products', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            allProducts = result.products || [];
            displayProducts(allProducts);
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

function displayProducts(products) {
    const grid = document.getElementById('productsGrid');
    
    if (products.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center text-muted">Keine Produkte gefunden</div>';
        return;
    }
    
    grid.innerHTML = products.map(product => `
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 ${product.active ? '' : 'opacity-75'}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div><img src="${product.icon}" alt="${product.name}" width="48" height="48" class="rounded"></div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="editProduct('${product.id}')">
                                    <i class="bi bi-pencil"></i> Bearbeiten
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="toggleProductStatus('${product.id}', ${!product.active})">
                                    <i class="bi bi-${product.active ? 'eye-slash' : 'eye'}"></i> 
                                    ${product.active ? 'Deaktivieren' : 'Aktivieren'}
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <h5 class="card-title">${product.name}</h5>
                    
                    <div class="mb-2">
                        <span class="badge bg-secondary">${getCategoryLabel(product.category)}</span>
                        <span class="badge bg-${product.active ? 'success' : 'warning'}">
                            ${product.active ? 'Aktiv' : 'Inaktiv'}
                        </span>
                    </div>
                    
                    <div class="text-end">
                        <span class="h5 text-primary">
                            ${new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(product.priceCents / 100)}
                        </span>
                    </div>
                </div>
                
                <div class="card-footer bg-transparent">
                    <small class="text-muted">
                        Erstellt: ${new Date(product.createdAt).toLocaleDateString('de-DE')}
                    </small>
                </div>
            </div>
        </div>
    `).join('');
}

function filterProducts() {
    const searchTerm = document.getElementById('searchProducts').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const filteredProducts = allProducts.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchTerm);
        const matchesCategory = !categoryFilter || product.category === categoryFilter;
        const matchesStatus = !statusFilter || 
            (statusFilter === 'active' && product.active) ||
            (statusFilter === 'inactive' && !product.active);
        
        return matchesSearch && matchesCategory && matchesStatus;
    });
    
    displayProducts(filteredProducts);
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

function openProductModal(product = null) {
    const form = document.getElementById('productForm');
    form.reset();
    
    if (product) {
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productIcon').value = product.icon;
        document.getElementById('productPrice').value = product.priceCents / 100;
        document.getElementById('productCategory').value = product.category;
        document.getElementById('productActive').checked = product.active;
    } else {
        document.getElementById('productActive').checked = true;
    }
}

function editProduct(productId) {
    const product = allProducts.find(p => p.id === productId);
    if (product) {
        openProductModal(product);
        const productModal = new bootstrap.Modal(document.getElementById('productModal'));
        productModal.show();
    }
}

async function handleProductSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const productData = {
        name: formData.get('name'),
        icon: formData.get('icon'),
        priceCents: Math.round(parseFloat(formData.get('price')) * 100),
        category: formData.get('category'),
        active: formData.has('active')
    };
    
    const token = localStorage.getItem('adminToken');
    const productId = formData.get('productId');
    
    try {
        const response = await fetch(productId ? `/v1/admin/products/${productId}` : '/v1/admin/products', {
            method: productId ? 'PATCH' : 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(productData)
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
            loadProducts();
        }
    } catch (error) {
        console.error('Error saving product:', error);
    }
}

async function toggleProductStatus(productId, active) {
    const token = localStorage.getItem('adminToken');
    
    try {
        const response = await fetch(`/v1/admin/products/${productId}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ active })
        });
        
        if (response.ok) {
            loadProducts();
        }
    } catch (error) {
        console.error('Error toggling product status:', error);
    }
}
</script>
