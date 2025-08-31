<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="bi bi-receipt text-primary"></i>
            Transaktionen
        </h1>
    </div>
</div>

<!-- Filter -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchTransactions" placeholder="Benutzer suchen...">
        </div>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="typeFilter">
            <option value="">Alle Typen</option>
            <option value="DEPOSIT">Einzahlung</option>
            <option value="DEBIT">Abbuchung</option>
            <option value="REVERSAL">Stornierung</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="date" class="form-control" id="dateFilter">
    </div>
</div>

<!-- Transactions Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Datum/Zeit</th>
                                <th>Benutzer</th>
                                <th>Typ</th>
                                <th>Betrag</th>
                                <th>Referenz</th>
                                <th>Von Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">
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

<script>
let allTransactions = [];

document.addEventListener('DOMContentLoaded', function() {
    loadTransactions();
    
    document.getElementById('searchTransactions').addEventListener('input', filterTransactions);
    document.getElementById('typeFilter').addEventListener('change', filterTransactions);
    document.getElementById('dateFilter').addEventListener('change', filterTransactions);
});

async function loadTransactions() {
    const token = localStorage.getItem('adminToken');
    
    try {
        const response = await fetch('/v1/export/transactions.csv', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            allTransactions = result.transactions || result.data || [];
            displayTransactions(allTransactions);
        }
    } catch (error) {
        console.error('Error loading transactions:', error);
    }
}

function displayTransactions(transactions) {
    const tbody = document.querySelector('#transactionsTable tbody');
    
    if (transactions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Keine Transaktionen vorhanden</td></tr>';
        return;
    }
    
    tbody.innerHTML = transactions.map(transaction => `
        <tr>
            <td>${new Date(transaction.timestamp).toLocaleString('de-DE')}</td>
            <td>${transaction.userId}</td>
            <td>
                <span class="badge bg-${getTypeColor(transaction.type)}">${getTypeLabel(transaction.type)}</span>
            </td>
            <td class="${transaction.amountCents >= 0 ? 'text-success' : 'text-danger'}">
                ${transaction.amountCents >= 0 ? '+' : ''}${new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(transaction.amountCents / 100)}
            </td>
            <td>${transaction.reference || '-'}</td>
            <td>${transaction.enteredByAdminId || '-'}</td>
        </tr>
    `).join('');
}

function filterTransactions() {
    const searchTerm = document.getElementById('searchTransactions').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    const filteredTransactions = allTransactions.filter(transaction => {
        const matchesSearch = transaction.userId.toLowerCase().includes(searchTerm);
        const matchesType = !typeFilter || transaction.type === typeFilter;
        const matchesDate = !dateFilter || 
            new Date(transaction.timestamp).toDateString() === new Date(dateFilter).toDateString();
        
        return matchesSearch && matchesType && matchesDate;
    });
    
    displayTransactions(filteredTransactions);
}

function getTypeColor(type) {
    const colors = {
        'DEPOSIT': 'success',
        'DEBIT': 'danger', 
        'REVERSAL': 'warning'
    };
    return colors[type] || 'secondary';
}

function getTypeLabel(type) {
    const labels = {
        'DEPOSIT': 'Einzahlung',
        'DEBIT': 'Abbuchung',
        'REVERSAL': 'Stornierung'
    };
    return labels[type] || type;
}
</script>
