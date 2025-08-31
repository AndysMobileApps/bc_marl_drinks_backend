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
            <form id="productForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="productId" name="productId">
                    
                    <div class="mb-3">
                        <label for="productName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productIcon" class="form-label">Icon</label>
                        <div class="row">
                            <div class="col-md-8">
                                <input type="file" class="form-control" id="productIconFile" name="iconFile" accept="image/*">
                                <div class="form-text">Bild hochladen (JPG, PNG, GIF, WebP - max. 5MB)</div>
                            </div>
                            <div class="col-md-4">
                                <img id="iconPreview" src="" alt="Icon Vorschau" class="img-thumbnail" style="display: none; max-height: 64px;">
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Oder Icon-Pfad eingeben:</small>
                            <input type="text" class="form-control form-control-sm" id="productIcon" name="icon" placeholder="/images/icons/beer.png">
                        </div>
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
    // Check if admin is logged in
    const token = localStorage.getItem('adminToken');
    if (!token) {
        window.location.href = '/admin/login';
        return;
    }
    
    loadProducts();
    
    document.getElementById('productForm').addEventListener('submit', handleProductSubmit);
    document.getElementById('searchProducts').addEventListener('input', filterProducts);
    document.getElementById('categoryFilter').addEventListener('change', filterProducts);
    document.getElementById('statusFilter').addEventListener('change', filterProducts);
    document.getElementById('productIconFile').addEventListener('change', handleIconFileChange);
});

async function loadProducts() {
    const token = localStorage.getItem('adminToken');
    
    if (!token) {
        window.location.href = '/admin/login';
        return;
    }
    
    try {
        const response = await fetch('/v1/products', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.status === 401) {
            localStorage.removeItem('adminToken');
            window.location.href = '/admin/login';
            return;
        }
        
        if (response.ok) {
            const result = await response.json();
            allProducts = result.products || [];
            displayProducts(allProducts);
        } else {
            console.error('Error loading products:', response.status, response.statusText);
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
    
    // Hide icon preview
    document.getElementById('iconPreview').style.display = 'none';
    
    if (product) {
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productIcon').value = product.icon;
        document.getElementById('productPrice').value = product.priceCents / 100;
        document.getElementById('productCategory').value = product.category;
        document.getElementById('productActive').checked = product.active;
        
        // Show current icon
        if (product.icon) {
            const preview = document.getElementById('iconPreview');
            preview.src = product.icon;
            preview.style.display = 'block';
        }
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
    const token = localStorage.getItem('adminToken');
    const productId = formData.get('productId');
    
    if (!token) {
        window.location.href = '/admin/login';
        return;
    }
    
    // Check if a file is being uploaded
    const iconFile = formData.get('iconFile');
    const hasIconFile = iconFile && iconFile.size > 0;
    
    try {
        let response;
        
        if (hasIconFile) {
            // Use multipart form submission for file upload
            formData.set('price', formData.get('price')); // Keep price as string for backend conversion
            
            response = await fetch(productId ? `/v1/admin/products/${productId}/with-icon` : '/v1/admin/products/with-icon', {
                method: productId ? 'PATCH' : 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            });
        } else {
            // Use JSON submission for regular updates
            const productData = {
                name: formData.get('name'),
                icon: formData.get('icon'),
                priceCents: Math.round(parseFloat(formData.get('price')) * 100),
                category: formData.get('category'),
                active: formData.has('active')
            };
            
            response = await fetch(productId ? `/v1/admin/products/${productId}` : '/v1/admin/products', {
                method: productId ? 'PATCH' : 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(productData)
            });
        }
        
        if (response.status === 401) {
            localStorage.removeItem('adminToken');
            window.location.href = '/admin/login';
            return;
        }
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
            loadProducts();
        } else {
            console.error('Response status:', response.status, response.statusText);
            console.error('Response headers:', [...response.headers.entries()]);
            
            const responseText = await response.text();
            console.error('Full response text:', responseText);
            
            try {
                const error = JSON.parse(responseText);
                alert('Fehler beim Speichern: ' + (error.message || 'Unbekannter Fehler'));
                console.error('Parsed API Error:', error);
            } catch (jsonError) {
                console.error('Failed to parse JSON:', jsonError);
                alert('Server-Fehler: Antwort ist kein gültiges JSON. Siehe Konsole für Details.');
            }
        }
    } catch (error) {
        console.error('Network Error saving product:', error);
        console.error('Error stack:', error.stack);
        alert('Netzwerk-Fehler beim Speichern des Produkts: ' + error.message);
    }
}

function handleIconFileChange(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('iconPreview');
    
    if (file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Ungültiger Dateityp. Nur JPG, PNG, GIF und WebP sind erlaubt.');
            e.target.value = '';
            preview.style.display = 'none';
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Datei ist zu groß. Maximum 5MB erlaubt.');
            e.target.value = '';
            preview.style.display = 'none';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
        
        // Clear icon path field when file is selected
        document.getElementById('productIcon').value = '';
    } else {
        preview.style.display = 'none';
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
