<div class="row">
    <div class="col-12">
        <h1 class="h3 mb-4">
            <i class="bi bi-journal-bookmark text-primary"></i>
            Buchungen
        </h1>
    </div>
</div>

<!-- Filter -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchBookings" placeholder="Benutzer oder Produkt suchen...">
        </div>
    </div>
    <div class="col-md-2">
        <select class="form-select" id="statusFilter">
            <option value="">Alle Status</option>
            <option value="booked">Gebucht</option>
            <option value="voided">Storniert</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="date" class="form-control" id="dateFilter">
    </div>
    <div class="col-md-3">
        <button class="btn btn-outline-primary w-100" onclick="exportBookings()">
            <i class="bi bi-download"></i> CSV Export
        </button>
    </div>
</div>

<!-- Bookings Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>Datum/Zeit</th>
                                <th>Benutzer</th>
                                <th>Produkt</th>
                                <th>Menge</th>
                                <th>Einzelpreis</th>
                                <th>Gesamt</th>
                                <th>Status</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center">
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
let allBookings = [];

document.addEventListener('DOMContentLoaded', function() {
    loadBookings();
    
    document.getElementById('searchBookings').addEventListener('input', filterBookings);
    document.getElementById('statusFilter').addEventListener('change', filterBookings);
    document.getElementById('dateFilter').addEventListener('change', filterBookings);
});

async function loadBookings() {
    const token = localStorage.getItem('adminToken');
    
    try {
        const response = await fetch('/v1/bookings', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            allBookings = result.bookings || result.data || [];
            displayBookings(allBookings);
        }
    } catch (error) {
        console.error('Error loading bookings:', error);
    }
}

function displayBookings(bookings) {
    const tbody = document.querySelector('#bookingsTable tbody');
    
    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Keine Buchungen vorhanden</td></tr>';
        return;
    }
    
    tbody.innerHTML = bookings.map(booking => `
        <tr class="${booking.status === 'voided' ? 'table-warning' : ''}">
            <td>${new Date(booking.timestamp).toLocaleString('de-DE')}</td>
            <td>${booking.userId}</td>
            <td>${booking.productId}</td>
            <td>${booking.quantity}</td>
            <td>${new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(booking.unitPriceCents / 100)}</td>
            <td class="fw-bold">${new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(booking.totalCents / 100)}</td>
            <td>
                <span class="badge bg-${booking.status === 'booked' ? 'success' : 'warning'}">
                    ${booking.status === 'booked' ? 'Gebucht' : 'Storniert'}
                </span>
            </td>
            <td>
                ${booking.status === 'booked' ? 
                    `<button class="btn btn-sm btn-outline-danger" onclick="voidBooking('${booking.id}')">
                        <i class="bi bi-x-circle"></i> Stornieren
                    </button>` : 
                    booking.voidedAt ? 
                        `<small class="text-muted">Storniert am ${new Date(booking.voidedAt).toLocaleDateString('de-DE')}</small>` :
                        ''
                }
            </td>
        </tr>
    `).join('');
}

function filterBookings() {
    const searchTerm = document.getElementById('searchBookings').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    
    const filteredBookings = allBookings.filter(booking => {
        const matchesSearch = booking.userId.toLowerCase().includes(searchTerm) || 
                             booking.productId.toLowerCase().includes(searchTerm);
        const matchesStatus = !statusFilter || booking.status === statusFilter;
        const matchesDate = !dateFilter || 
            new Date(booking.timestamp).toDateString() === new Date(dateFilter).toDateString();
        
        return matchesSearch && matchesStatus && matchesDate;
    });
    
    displayBookings(filteredBookings);
}

async function voidBooking(bookingId) {
    if (!confirm('Sind Sie sicher, dass Sie diese Buchung stornieren möchten?')) {
        return;
    }
    
    const token = localStorage.getItem('adminToken');
    
    try {
        const response = await fetch(`/v1/bookings/${bookingId}/void`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            loadBookings();
        }
    } catch (error) {
        console.error('Error voiding booking:', error);
    }
}

async function exportBookings() {
    const token = localStorage.getItem('adminToken');
    
    try {
        const response = await fetch('/v1/export/bookings.csv', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `bookings-${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    } catch (error) {
        console.error('Error exporting bookings:', error);
    }
}
</script>
