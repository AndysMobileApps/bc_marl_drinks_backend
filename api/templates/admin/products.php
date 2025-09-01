<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-box text-primary"></i>
                Produktverwaltung
            </h1>
            <div class="d-flex gap-2">
                <div id="bulkActions" class="btn-group" style="display: none;">
                    <button type="button" class="btn btn-outline-success" onclick="bulkActivateProducts()">
                        <i class="bi bi-check-circle"></i> Aktivieren
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="bulkDeactivateProducts()">
                        <i class="bi bi-pause-circle"></i> Deaktivieren
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="bulkDeleteProducts()">
                        <i class="bi bi-trash"></i> Löschen
                    </button>
                </div>
                <button type="button" class="btn btn-primary" onclick="createNewProduct()">
                    <i class="bi bi-plus-circle"></i> Neues Produkt
                </button>
            </div>
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



<!-- Products Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="selectAllProducts" onchange="toggleSelectAll()">
                                </th>
                                <th width="64">Icon</th>
                                <th>Name</th>
                                <th>Kategorie</th>
                                <th>Preis</th>
                                <th>Status</th>
                                <th width="80">Aktion</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="spinner-border" role="status">
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
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Produkt bearbeiten</h5>
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
                    <button type="button" class="btn btn-primary" onclick="submitProductForm()">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let allProducts = [];

document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    const token = localStorage.getItem('adminToken');
    if (!token) {
        window.location.href = '/admin/login';
        return;
    }
    
    // Load products
    loadProducts();
    
    // Setup form handling
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.onsubmit = function(e) {
            e.preventDefault();
            return false;
        };
    }
    
    // Setup event listeners
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
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}



function displayProducts(products) {
    const tbody = document.getElementById('productsTableBody');
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Keine Produkte gefunden</td></tr>';
        return;
    }
    
    tbody.innerHTML = products.map(product => `
        <tr class="${product.active ? '' : 'table-secondary'}">
            <td>
                <input type="checkbox" class="form-check-input product-checkbox" value="${product.id}" onchange="updateBulkActions()">
            </td>
            <td>
                <img src="${product.icon}" alt="${product.name}" width="48" height="48" class="rounded">
            </td>
            <td>
                <div class="fw-semibold">${product.name}</div>
                <small class="text-muted">ID: ${product.id.substring(0, 8)}...</small>
            </td>
            <td>
                <span class="badge bg-secondary">${getCategoryLabel(product.category)}</span>
            </td>
            <td>
                <span class="fw-bold text-primary">
                    ${new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(product.priceCents / 100)}
                </span>
            </td>
            <td>
                <span class="badge bg-${product.active ? 'success' : 'warning'}">
                    <i class="bi bi-${product.active ? 'check-circle' : 'pause-circle'}"></i>
                    ${product.active ? 'Aktiv' : 'Inaktiv'}
                </span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct('${product.id}')" title="Bearbeiten">
                    <i class="bi bi-pencil"></i>
                </button>
            </td>
        </tr>
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
    
    // Set modal title
    const modalTitle = document.getElementById('productModalTitle');
    modalTitle.textContent = product ? 'Produkt bearbeiten' : 'Neues Produkt erstellen';
    
    if (product) {
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
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

async function submitProductForm() {
    const form = document.getElementById('productForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const submitButton = document.querySelector('#productModal button[onclick="submitProductForm()"]');
    const token = localStorage.getItem('adminToken');
    const productId = formData.get('productId');
    
    if (!token) {
        window.location.href = '/admin/login';
        return;
    }
    
    // Disable submit button
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Speichere...';
    }
    
    try {
        let iconPath = null; // Will be set by file upload or remain null for default
        
        // Handle file upload if needed
        const iconFile = formData.get('iconFile');
        if (iconFile && iconFile.size > 0) {
            const iconFormData = new FormData();
            iconFormData.append('iconFile', iconFile);
            
            const iconResponse = await fetch('/v1/admin/products/upload-icon', {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` },
                body: iconFormData
            });
            
            if (iconResponse.ok) {
                const iconResult = await iconResponse.json();
                iconPath = iconResult.iconPath;
            }
        }
        
        // Create/update product
        const productData = {
            name: formData.get('name'),
            priceCents: Math.round(parseFloat(formData.get('price')) * 100),
            category: formData.get('category'),
            active: formData.has('active')
        };
        
        // Add icon path only if we have one (from upload or existing)
        if (iconPath) {
            productData.icon = iconPath;
        }
        
        const url = productId ? `/v1/admin/products/${productId}` : '/v1/admin/products';
        const response = await fetch(url, {
            method: productId ? 'PATCH' : 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(productData)
        });
        
        if (response.status === 401) {
            localStorage.removeItem('adminToken');
            window.location.href = '/admin/login';
            return;
        }
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
            loadProducts();
        } else {
            const error = await response.json().catch(() => ({ message: 'Server-Fehler' }));
            alert('Fehler beim Speichern: ' + (error.message || 'Unbekannter Fehler'));
        }
    } catch (error) {
        alert('Fehler beim Speichern des Produkts: ' + error.message);
    } finally {
        // Re-enable button
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Speichern';
        }
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
        
        // Icon file selected - path field is no longer needed
    } else {
        preview.style.display = 'none';
    }
}

function createNewProduct() {
    openProductModal();
    const productModal = new bootstrap.Modal(document.getElementById('productModal'));
    productModal.show();
}

// Bulk operations
function getSelectedProductIds() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function updateBulkActions() {
    const selectedIds = getSelectedProductIds();
    const bulkActions = document.getElementById('bulkActions');
    
    if (selectedIds.length > 0) {
        bulkActions.style.display = 'inline-flex';
        // Update button text with count
        document.querySelector('#bulkActions .btn:nth-child(1)').innerHTML = 
            `<i class="bi bi-check-circle"></i> Aktivieren (${selectedIds.length})`;
        document.querySelector('#bulkActions .btn:nth-child(2)').innerHTML = 
            `<i class="bi bi-pause-circle"></i> Deaktivieren (${selectedIds.length})`;
        document.querySelector('#bulkActions .btn:nth-child(3)').innerHTML = 
            `<i class="bi bi-trash"></i> Löschen (${selectedIds.length})`;
    } else {
        bulkActions.style.display = 'none';
    }
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.product-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllProducts');
    
    if (selectedIds.length === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (selectedIds.length === allCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllProducts');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    
    productCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkActions();
}

async function bulkActivateProducts() {
    await performBulkAction('activate', 'Produkte aktivieren');
}

async function bulkDeactivateProducts() {
    await performBulkAction('deactivate', 'Produkte deaktivieren');
}

async function bulkDeleteProducts() {
    const selectedIds = getSelectedProductIds();
    
    if (!confirm(`Möchten Sie wirklich ${selectedIds.length} Produkt(e) permanent löschen? Diese Aktion kann nicht rückgängig gemacht werden.`)) {
        return;
    }
    
    await performBulkAction('delete', 'Produkte löschen');
}

async function performBulkAction(action, actionLabel) {
    const selectedIds = getSelectedProductIds();
    const token = localStorage.getItem('adminToken');
    
    if (selectedIds.length === 0) {
        alert('Bitte wählen Sie mindestens ein Produkt aus.');
        return;
    }
    
    if (!token) {
        window.location.href = '/admin/login';
        return;
    }
    
    try {
        const response = await fetch('/v1/admin/products/bulk-update', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                productIds: selectedIds,
                action: action
            })
        });
        
        if (response.status === 401) {
            localStorage.removeItem('adminToken');
            window.location.href = '/admin/login';
            return;
        }
        
        if (response.ok) {
            const result = await response.json();
            
            if (result.errors && result.errors.length > 0) {
                alert(`${actionLabel} teilweise erfolgreich:\n\n${result.message}\n\nFehler:\n${result.errors.join('\n')}`);
            } else {
                alert(result.message);
            }
            
            // Clear selections and reload
            document.getElementById('selectAllProducts').checked = false;
            updateBulkActions();
            loadProducts();
        } else {
            const error = await response.json().catch(() => ({ message: 'Server-Fehler' }));
            alert(`Fehler beim ${actionLabel}: ` + (error.message || 'Unbekannter Fehler'));
        }
    } catch (error) {
        alert(`Netzwerk-Fehler beim ${actionLabel}: ` + error.message);
    }
}
</script>
