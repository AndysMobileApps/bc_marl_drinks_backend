<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="bi bi-people text-primary"></i>
                Benutzerverwaltung
            </h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openUserModal()">
                <i class="bi bi-person-plus"></i> Neuer Benutzer
            </button>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>E-Mail</th>
                                <th>Rolle</th>
                                <th>Guthaben</th>
                                <th>Status</th>
                                <th>Erstellt</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
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

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Benutzer bearbeiten</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <input type="hidden" id="userId" name="userId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="firstName" class="form-label">Vorname</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Nachname</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-Mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobilnummer</label>
                        <input type="tel" class="form-control" id="mobile" name="mobile">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Rolle</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="user">Benutzer</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="thresholdCents" class="form-label">Guthaben-Schwellwert (€)</label>
                                <input type="number" class="form-control" id="thresholdCents" name="thresholdCents" step="0.01" min="0">
                            </div>
                        </div>
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

<!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guthaben aufladen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="depositForm">
                <div class="modal-body">
                    <input type="hidden" id="depositUserId" name="userId">
                    <p>Guthaben aufladen für: <strong id="depositUserName"></strong></p>
                    
                    <div class="mb-3">
                        <label for="depositAmount" class="form-label">Betrag (€)</label>
                        <input type="number" class="form-control" id="depositAmount" name="amount" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="depositReference" class="form-label">Referenz (optional)</label>
                        <input type="text" class="form-control" id="depositReference" name="reference" placeholder="z.B. Bar-Einzahlung">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-success">Aufladen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    
    document.getElementById('userForm').addEventListener('submit', handleUserSubmit);
    document.getElementById('depositForm').addEventListener('submit', handleDepositSubmit);
});

async function loadUsers() {
    const token = localStorage.getItem('adminToken');
    
    try {
        const response = await fetch('/v1/admin/users', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            displayUsers(result.users || []);
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

function displayUsers(users) {
    const tbody = document.querySelector('#usersTable tbody');
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Keine Benutzer vorhanden</td></tr>';
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.firstName} ${user.lastName}</td>
            <td>${user.email}</td>
            <td>
                <span class="badge bg-${user.role === 'admin' ? 'warning' : 'primary'}">${user.role}</span>
            </td>
            <td>${new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(user.balanceCents / 100)}</td>
            <td>
                <span class="badge bg-${user.locked ? 'danger' : 'success'}">
                    ${user.locked ? 'Gesperrt' : 'Aktiv'}
                </span>
            </td>
            <td>${new Date(user.createdAt).toLocaleDateString('de-DE')}</td>
            <td>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-primary" onclick="editUser('${user.id}')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="openDepositModal('${user.id}', '${user.firstName} ${user.lastName}')">
                        <i class="bi bi-plus-circle"></i>
                    </button>
                    ${user.locked ? 
                        `<button class="btn btn-sm btn-outline-warning" onclick="unlockUser('${user.id}')"><i class="bi bi-unlock"></i></button>` :
                        ''
                    }
                </div>
            </td>
        </tr>
    `).join('');
}

function openUserModal(user = null) {
    const form = document.getElementById('userForm');
    form.reset();
    
    if (user) {
        document.getElementById('userId').value = user.id;
        document.getElementById('firstName').value = user.firstName;
        document.getElementById('lastName').value = user.lastName;
        document.getElementById('email').value = user.email;
        document.getElementById('mobile').value = user.mobile || '';
        document.getElementById('role').value = user.role;
        document.getElementById('thresholdCents').value = user.lowBalanceThresholdCents / 100;
    }
}

function openDepositModal(userId, userName) {
    document.getElementById('depositUserId').value = userId;
    document.getElementById('depositUserName').textContent = userName;
    document.getElementById('depositForm').reset();
    document.getElementById('depositUserId').value = userId;
    
    const depositModal = new bootstrap.Modal(document.getElementById('depositModal'));
    depositModal.show();
}

async function handleUserSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const userData = {
        firstName: formData.get('firstName'),
        lastName: formData.get('lastName'),
        email: formData.get('email'),
        mobile: formData.get('mobile'),
        role: formData.get('role'),
        lowBalanceThresholdCents: Math.round(parseFloat(formData.get('thresholdCents') || 0) * 100)
    };
    
    const token = localStorage.getItem('adminToken');
    const userId = formData.get('userId');
    
    try {
        const response = await fetch(userId ? `/v1/admin/users/${userId}` : '/v1/admin/users', {
            method: userId ? 'PATCH' : 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
            loadUsers();
        }
    } catch (error) {
        console.error('Error saving user:', error);
    }
}

async function handleDepositSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const depositData = {
        amountCents: Math.round(parseFloat(formData.get('amount')) * 100),
        reference: formData.get('reference')
    };
    
    const token = localStorage.getItem('adminToken');
    const userId = formData.get('userId');
    
    try {
        const response = await fetch(`/v1/admin/users/${userId}/deposit`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(depositData)
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('depositModal')).hide();
            loadUsers();
        }
    } catch (error) {
        console.error('Error depositing money:', error);
    }
}

async function unlockUser(userId) {
    const token = localStorage.getItem('adminToken');
    
    try {
        const response = await fetch(`/v1/admin/users/${userId}/unlock`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            loadUsers();
        }
    } catch (error) {
        console.error('Error unlocking user:', error);
    }
}
</script>
