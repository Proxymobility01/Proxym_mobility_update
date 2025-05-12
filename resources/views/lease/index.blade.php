@extends('layouts.app')

@section('content')
<style>
/* Couleurs d'application */
:root {
    --primary: #DCDB32;
    --secondary: #101010;
    --tertiary: #F3F3F3;
    --background: #ffffff;
    --text: #101010;
    --sidebar: #F8F8F8;
}

/* Styles pour les cartes de statistiques */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    display: flex;
    align-items: center;
    background-color: var(--background);
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background-color: var(--tertiary);
    border-radius: 50%;
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.total .stat-icon {
    color: var(--primary);
}

.success .stat-icon {
    color: #28a745;
}

.danger .stat-icon {
    color: #dc3545;
}

.amount .stat-icon {
    color: #007bff;
}

.stat-details {
    flex-grow: 1;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
}

.stat-label {
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-text {
    color: #6c757d;
    font-size: 0.9em;
}

/* Barre de recherche et filtres */
.search-bar {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.search-group {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.search-group input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 300px;
}

.search-btn {
    background-color: var(--tertiary);
    border: 1px solid #ddd;
    border-left: none;
    padding: 8px 12px;
    cursor: pointer;
}

.filter-group {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

.filter-group select, .filter-group input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-left: 10px;
    margin-bottom: 10px;
}

.date-picker-container, .date-range-container {
    display: none;
    margin-left: 10px;
    margin-bottom: 10px;
}

/* Table */
.table-container {
    overflow-x: auto;
}

.date-header {
    background-color: var(--tertiary);
    padding: 10px 15px;
    font-weight: bold;
    border-radius: 4px;
    margin: 20px 0 10px 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

thead {
    background-color: var(--tertiary);
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

tr:hover {
    background-color: var(--tertiary);
}

/* Toast */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    padding: 12px 20px;
    border-radius: 4px;
    margin-top: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: opacity 0.3s ease;
}

.toast-success {
    background-color: var(--primary);
    color: var(--secondary);
}

.toast-error {
    background-color: #dc3545;
    color: white;
}

.toast-warning {
    background-color: #ffc107;
    color: var(--secondary);
}

/* Content header */
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.date {
    color: #6c757d;
    font-size: 0.9em;
}

/* Status badges */
.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.status-badge.paid {
    background-color: rgba(40, 167, 69, 0.2);
    color: #28a745;
}

.status-badge.unpaid {
    background-color: rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

/* Loader */
.loader-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 30px;
}

.loader {
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--primary);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.no-results {
    text-align: center;
    padding: 20px;
    color: #6c757d;
    font-style: italic;
}
</style>

<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>{{ $pageTitle }}</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-drivers">{{ $totalDrivers }}</div>
                <div class="stat-label">Chauffeurs Associés</div>
                <div class="stat-text">Total des chauffeurs</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="paid-leases">{{ $paidLeasesCount }}</div>
                <div class="stat-label">Leases Payés</div>
                <div class="stat-text" id="paid-time-label">aujourd'hui</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="unpaid-leases">{{ $unpaidLeasesCount }}</div>
                <div class="stat-label">Leases Impayés</div>
                <div class="stat-text" id="unpaid-time-label">aujourd'hui</div>
            </div>
        </div>

        <div class="stat-card amount">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-amount">{{ number_format($totalAmount, 2) }} FCFA</div>
                <div class="stat-label">Montant Total</div>
                <div class="stat-text" id="amount-time-label">aujourd'hui</div>
            </div>
        </div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-lease" placeholder="Rechercher un lease...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-group">
            <select id="status-filter">
                <option value="all">Tous les leases</option>
                <option value="paid">Payés</option>
                <option value="unpaid">Impayés</option>
            </select>
            
            <select id="agence-filter">
                <option value="all">Toutes les stations</option>
                @foreach($agences as $agence)
                <option value="{{ $agence->id }}">{{ $agence->nom_agence }}</option>
                @endforeach
            </select>
            
            <select id="swappeur-filter">
                <option value="all">Tous les swappeurs</option>
                @foreach($swappeurs as $swappeur)
                <option value="{{ $swappeur->id }}">{{ $swappeur->nom }} {{ $swappeur->prenom }}</option>
                @endforeach
            </select>
            
            <select id="time-filter">
                <option value="today">Aujourd'hui</option>
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
                <option value="year">Cette année</option>
                <option value="custom">Date spécifique</option>
                <option value="daterange">Plage de dates</option>
                <option value="all">Tout l'historique</option>
            </select>
            
            <div id="date-picker-container" class="date-picker-container">
                <input type="date" id="custom-date" class="custom-date">
            </div>
            
            <div id="date-range-container" class="date-range-container">
                <input type="date" id="start-date" class="start-date" placeholder="Date début">
                <input type="date" id="end-date" class="end-date" placeholder="Date fin">
            </div>
        </div>
    </div>

    <!-- Zone de contenu des leases -->
    <div id="leases-content">
        <!-- Les leases seront chargés ici dynamiquement -->
        <div class="loader-container">
            <div class="loader"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation et variables
    const searchInput = document.getElementById('search-lease');
    const statusFilter = document.getElementById('status-filter');
    const timeFilter = document.getElementById('time-filter');
    const agenceFilter = document.getElementById('agence-filter');
    const swappeurFilter = document.getElementById('swappeur-filter');
    const customDatePicker = document.getElementById('custom-date');
    const datePickerContainer = document.getElementById('date-picker-container');
    const startDatePicker = document.getElementById('start-date');
    const endDatePicker = document.getElementById('end-date');
    const dateRangeContainer = document.getElementById('date-range-container');
    const leasesContent = document.getElementById('leases-content');
    const paidTimeLabel = document.getElementById('paid-time-label');
    const unpaidTimeLabel = document.getElementById('unpaid-time-label');
    const amountTimeLabel = document.getElementById('amount-time-label');
    
    // Afficher la date actuelle dans l'en-tête
    const dateElement = document.getElementById('date');
    const today = new Date();
    dateElement.textContent = today.toLocaleDateString('fr-FR');
    
    // Initialiser les dates des sélecteurs
    customDatePicker.valueAsDate = today;
    startDatePicker.valueAsDate = new Date(today.getFullYear(), today.getMonth(), 1);
    endDatePicker.valueAsDate = today;

    // Gérer l'affichage des sélecteurs de date
    timeFilter.addEventListener('change', function() {
        datePickerContainer.style.display = 'none';
        dateRangeContainer.style.display = 'none';
        
        if (this.value === 'custom') {
            datePickerContainer.style.display = 'block';
        } else if (this.value === 'daterange') {
            dateRangeContainer.style.display = 'block';
        }
    });

    // Fonctions pour calculer les statistiques
    function calculateStats() {
        // Préparer les données
        const data = {
            timeFilter: timeFilter.value,
            agence: agenceFilter.value,
            swappeur: swappeurFilter.value
        };
        
        // Ajouter les dates en fonction du filtre
        if (timeFilter.value === 'custom') {
            data.customDate = customDatePicker.value;
        } else if (timeFilter.value === 'daterange') {
            data.startDate = startDatePicker.value;
            data.endDate = endDatePicker.value;
        }
        
        // Récupérer les données
        fetch('/api/leases/stats', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            // Mettre à jour les compteurs
            document.getElementById('total-drivers').textContent = data.totalDrivers;
            document.getElementById('paid-leases').textContent = data.paidLeases;
            document.getElementById('unpaid-leases').textContent = data.unpaidLeases;
            document.getElementById('total-amount').textContent = data.totalAmount + ' FCFA';
            
            // Mettre à jour les libellés de période
            paidTimeLabel.textContent = data.timeLabel;
            unpaidTimeLabel.textContent = data.timeLabel;
            amountTimeLabel.textContent = data.timeLabel;
        })
        .catch(error => {
            console.error('Erreur lors du chargement des statistiques:', error);
            showToast('Erreur lors du chargement des statistiques.', 'error');
        });
    }

    // Fonction pour filtrer et afficher les leases
    function filterLeases() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const timeValue = timeFilter.value;
        const agenceValue = agenceFilter.value;
        const swappeurValue = swappeurFilter.value;
        
        // Préparer les données
        const data = {
            search: searchTerm,
            status: statusValue,
            time: timeValue,
            agence: agenceValue,
            swappeur: swappeurValue
        };
        
        // Ajouter les dates en fonction du filtre
        if (timeValue === 'custom') {
            data.customDate = customDatePicker.value;
        } else if (timeValue === 'daterange') {
            data.startDate = startDatePicker.value;
            data.endDate = endDatePicker.value;
        }
        
        // Afficher un indicateur de chargement
        leasesContent.innerHTML = '<div class="loader-container"><div class="loader"></div></div>';

        // Appel à l'API pour récupérer les données filtrées
        fetch('/api/leases/filter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            // Vider la zone de contenu
            leasesContent.innerHTML = '';
            
            // Récupérer les données par jour
            const leasesByDay = data.leasesByDay;
            const days = Object.keys(leasesByDay).sort().reverse(); // Trier par date décroissante
            
            if (days.length === 0) {
                leasesContent.innerHTML = '<div class="no-results">Aucun lease trouvé pour cette période.</div>';
                return;
            }
            
            // Pour chaque jour, créer une section avec un tableau
            days.forEach(day => {
                const leases = leasesByDay[day];
                if (leases.length === 0) return;
                
                // Formater la date pour l'affichage
                const dateParts = day.split('-');
                const formattedDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
                
                // Créer l'en-tête de la date
                const dateHeader = document.createElement('div');
                dateHeader.className = 'date-header';
                dateHeader.textContent = formattedDate;
                leasesContent.appendChild(dateHeader);
                
                // Créer le tableau pour ce jour
                const tableContainer = document.createElement('div');
                tableContainer.className = 'table-container';
                
                const table = document.createElement('table');
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>ID Chauffeur</th>
                            <th>Nom Chauffeur</th>
                            <th>ID Moto</th>
                            <th>VIN Moto</th>
                            <th>Montant Lease</th>
                            <th>Montant Batterie</th>
                            <th>Montant Total</th>
                            <th>Station</th>
                            <th>Statut</th>
                            <th>Swappeur</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                `;
                
                const tbody = table.querySelector('tbody');
                
                // Ajouter les lignes de leases
                leases.forEach(lease => {
                    const row = document.createElement('tr');
                    row.dataset.id = lease.id;
                    
                    row.innerHTML = `
                        <td>${lease.driver_id || 'Non défini'}</td>
                        <td>${lease.driver_name || 'Non défini'}</td>
                        <td>${lease.moto_id || 'Non défini'}</td>
                        <td>${lease.moto_vin || 'Non défini'}</td>
                        <td>${lease.montant_lease || '0.00'} FCFA</td>
                        <td>${lease.montant_battery || '0.00'} FCFA</td>
                        <td>${lease.total_lease || '0.00'} FCFA</td>
                        <td>${lease.station || 'Non applicable'}</td>
                        <td><span class="status-badge ${lease.status === 'paid' ? 'paid' : 'unpaid'}">${lease.status === 'paid' ? 'Payé' : 'Impayé'}</span></td>
                        <td>${lease.swappeur || 'Non applicable'}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                tableContainer.appendChild(table);
                leasesContent.appendChild(tableContainer);
            });
            
            // Mettre à jour les libellés de période
            if (data.timeLabel) {
                paidTimeLabel.textContent = data.timeLabel;
                unpaidTimeLabel.textContent = data.timeLabel;
                amountTimeLabel.textContent = data.timeLabel;
            }
            
            // Si aucun contenu n'a été ajouté
            if (leasesContent.children.length === 0) {
                leasesContent.innerHTML = '<div class="no-results">Aucun lease trouvé pour les critères sélectionnés.</div>';
            }
        })
        .catch(error => {
            console.error('Erreur lors du filtrage des données:', error);
            showToast('Erreur lors du filtrage des données.', 'error');
            leasesContent.innerHTML = '<div class="no-results">Erreur lors du chargement des données. Veuillez réessayer.</div>';
        });
    }

    // Fonction pour afficher les messages toast
    function showToast(message, type = 'info') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Fonction debounce pour limiter les appels lors de la recherche
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Événements pour les filtres et la recherche
    searchInput.addEventListener('input', debounce(filterLeases, 500));
    statusFilter.addEventListener('change', filterLeases);
    timeFilter.addEventListener('change', function() {
        if (datePickerContainer) datePickerContainer.style.display = 'none';
        if (dateRangeContainer) dateRangeContainer.style.display = 'none';
        
        if (this.value === 'custom') {
            datePickerContainer.style.display = 'block';
        } else if (this.value === 'daterange') {
            dateRangeContainer.style.display = 'block';
        }
        filterLeases();
        calculateStats();
    });
    agenceFilter.addEventListener('change', function() {
        filterLeases();
        calculateStats();
    });
    swappeurFilter.addEventListener('change', function() {
        filterLeases();
        calculateStats();
    });
    
    // Événements pour les changements de date
    customDatePicker.addEventListener('change', function() {
        if (timeFilter.value === 'custom') {
            filterLeases();
            calculateStats();
        }
    });
    
    startDatePicker.addEventListener('change', function() {
        if (timeFilter.value === 'daterange') {
            filterLeases();
            calculateStats();
        }
    });
    
    endDatePicker.addEventListener('change', function() {
        if (timeFilter.value === 'daterange') {
            filterLeases();
            calculateStats();
        }
    });

    // Afficher les messages de session Laravel
    @if(session('success'))
    showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
    showToast("{{ session('error') }}", 'error');
    @endif

    // Initialisation - Calculer les statistiques au chargement
    calculateStats();
    
    // Par défaut, filtrer pour afficher tous les leases
    filterLeases();
});
</script>
@endsection