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
    grid-template-columns: repeat(3, 1fr);
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

.date-picker-container {
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

/* Modal */
.modal-header {
    background-color: var(--tertiary);
    border-bottom: 1px solid #ddd;
}

.modal-title {
    color: var(--text);
    font-weight: bold;
}

.modal-footer {
    background-color: var(--tertiary);
    border-top: 1px solid #ddd;
}

.checkbox-container {
    max-height: 300px;
    overflow-y: auto;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Actions */
.action-btn {
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    border: none;
    font-weight: bold;
}

.btn-primary {
    background-color: var(--primary);
    color: var(--secondary);
}

.btn-secondary {
    background-color: var(--tertiary);
    color: var(--secondary);
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




/* Styles pour la modal de détails */
.transaction-details {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.transaction-detail-item {
    display: flex;
    justify-content: space-between;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.transaction-detail-item:last-child {
    border-bottom: none;
}
</style>

<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>Ravitaillement des Batteries</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-entrepots">0</div>
                <div class="stat-label">Entrepôts</div>
                <div class="stat-text">Total des entrepôts</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-ravitaillements">0</div>
                <div class="stat-label">Batteries Ravitaillées</div>
                <div class="stat-text" id="ravitaillement-time-label">aujourd'hui</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-battery-empty"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="batteries-disponibles">0</div>
                <div class="stat-label">Batteries Disponibles</div>
                <div class="stat-text">non assignées</div>
            </div>
        </div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-battery" placeholder="Rechercher une batterie par ID ou VIN...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-group">
            <select id="entrepot-filter">
                <option value="all">Tous les entrepôts</option>
                @foreach($entrepots as $entrepot)
                    <option value="{{ $entrepot->id }}">{{ $entrepot->nom_entrepot }}</option>
                @endforeach
            </select>
            
            <select id="time-filter">
                <option value="today">Aujourd'hui</option>
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
                <option value="year">Cette année</option>
                <option value="custom">Date spécifique</option>
                <option value="all">Tout l'historique</option>
            </select>
            
            <div id="date-picker-container" class="date-picker-container">
                <input type="date" id="custom-date" class="custom-date">
            </div>
            
            <button id="add-ravitaillement" class="btn btn-primary ml-2">
                <i class="fas fa-plus-circle me-2"></i>Nouveau Ravitaillement
            </button>
        </div>
    </div>

    <!-- Zone de contenu des ravitaillements -->
    <div id="ravitaillements-content">
        <!-- Les ravitaillements seront chargés ici dynamiquement -->
        <div class="loader-container">
            <div class="loader"></div>
        </div>
    </div>



    
<!-- Modal de Ravitaillement -->
<div class="modal fade" id="ravitaillementModal" tabindex="-1" aria-labelledby="ravitaillementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ravitaillementModalLabel">Nouveau Ravitaillement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('ravitailler.batteries.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="entrepot" class="form-label fw-bold">Choisir un entrepôt</label>
                        <select name="entrepot_id" id="entrepot" class="form-select" required>
                            <option value="">Sélectionner un entrepôt</option>
                            @foreach($entrepots as $entrepot)
                                <option value="{{ $entrepot->id }}">{{ $entrepot->nom_entrepot }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rechercher une batterie</label>
                        <input type="text" class="form-control" id="modalBatterySearch" placeholder="Rechercher par ID ou VIN">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Batteries disponibles</label>
                        <div class="checkbox-container" id="modalBatteriesList">
                            @foreach($batteries as $battery)
                                <div class="form-check mb-2 battery-item">
                                    <input type="checkbox" class="form-check-input" name="batteries[]" value="{{ $battery->id }}" id="battery-{{ $battery->id }}">
                                    <label class="form-check-label" for="battery-{{ $battery->id }}">
                                        <span class="fw-bold">{{ $battery->batterie_unique_id }}</span>
                                        <br>
                                        <small class="text-muted">VIN: {{ $battery->mac_id }}</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ravitailler</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Ajouter ce code à la fin du body dans votre vue ravitaillement.blade.php -->

<!-- Modal de détails de transaction -->
<div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailsModalLabel">Détails de la Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="transaction-details">
                    <div class="transaction-detail-item">
                        <strong>ID de la transaction:</strong>
                        <span id="transaction-id"></span>
                    </div>
                    <div class="transaction-detail-item">
                        <strong>Entrepôt:</strong>
                        <span id="transaction-entrepot"></span>
                    </div>
                    <div class="transaction-detail-item">
                        <strong>ID Batterie:</strong>
                        <span id="transaction-battery-id"></span>
                    </div>
                    <div class="transaction-detail-item">
                        <strong>VIN Batterie:</strong>
                        <span id="transaction-battery-vin"></span>
                    </div>
                    <div class="transaction-detail-item">
                        <strong>Date et heure:</strong>
                        <span id="transaction-datetime"></span>
                    </div>
                    <div class="transaction-detail-item">
                        <strong>Type d'opération:</strong>
                        <span id="transaction-type">Ravitaillement</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>


</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation et variables
    const searchInput = document.getElementById('search-battery');
    const entrepotFilter = document.getElementById('entrepot-filter');
    const timeFilter = document.getElementById('time-filter');
    const customDatePicker = document.getElementById('custom-date');
    const datePickerContainer = document.getElementById('date-picker-container');
    const ravitaillementsContent = document.getElementById('ravitaillements-content');
    const addRavitaillementBtn = document.getElementById('add-ravitaillement');
    const modalBatterySearch = document.getElementById('modalBatterySearch');
    const modalBatteriesList = document.getElementById('modalBatteriesList');
    
    // Afficher la date actuelle dans l'en-tête
    const dateElement = document.getElementById('date');
    const today = new Date();
    dateElement.textContent = today.toLocaleDateString('fr-FR');
    
    // Initialiser la date du sélecteur à la date du jour
    customDatePicker.valueAsDate = today;

    // Gérer l'affichage du sélecteur de date
    timeFilter.addEventListener('change', function() {
        if (this.value === 'custom') {
            datePickerContainer.style.display = 'block';
        } else {
            datePickerContainer.style.display = 'none';
        }
    });
    
    // Ouvrir la modal de ravitaillement
    addRavitaillementBtn.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('ravitaillementModal'));
        modal.show();
    });
    
    // Filtrer les batteries dans la modal
    modalBatterySearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const batteries = modalBatteriesList.querySelectorAll('.battery-item');
        
        batteries.forEach(batteryItem => {
            const label = batteryItem.querySelector('.form-check-label').textContent.toLowerCase();
            batteryItem.style.display = label.includes(searchTerm) ? '' : 'none';
        });
    });

    // Fonctions pour calculer les statistiques
    function calculateStats() {
        // Préparer les données
        const data = {
            timeFilter: timeFilter.value
        };
        
        // Ajouter la date personnalisée si nécessaire
        if (timeFilter.value === 'custom') {
            data.customDate = customDatePicker.value;
        }
        
        // Récupérer les données
        fetch('/api/ravitaillements/stats', {
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
            document.getElementById('total-entrepots').textContent = data.entrepotCount;
            document.getElementById('total-ravitaillements').textContent = data.ravitaillementCount;
            document.getElementById('batteries-disponibles').textContent = data.availableBatteries;
            
            // Mettre à jour les libellés de période
            document.getElementById('ravitaillement-time-label').textContent = data.timeLabel;
        })
        .catch(error => {
            console.error('Erreur lors du chargement des statistiques:', error);
            showToast('Erreur lors du chargement des statistiques.', 'error');
        });
    }

    // Fonction pour filtrer et afficher les ravitaillements
    function filterRavitaillements() {
        const searchTerm = searchInput.value.toLowerCase();
        const entrepotValue = entrepotFilter.value;
        const timeValue = timeFilter.value;
        
        // Préparer les données
        const data = {
            search: searchTerm,
            entrepot_id: entrepotValue,
            date_filter: timeValue
        };
        
        // Ajouter la date personnalisée si nécessaire
        if (timeValue === 'custom') {
            data.custom_date = customDatePicker.value;
        }
        
        // Afficher un indicateur de chargement
        ravitaillementsContent.innerHTML = '<div class="loader-container"><div class="loader"></div></div>';

        // Appel à l'API pour récupérer les données filtrées
        fetch('/api/ravitaillements/filter', {
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
            ravitaillementsContent.innerHTML = '';
            
            // Récupérer les données par jour
            const ravitaillesByDay = data.ravitaillesByDay;
            const days = Object.keys(ravitaillesByDay).sort().reverse(); // Trier par date décroissante
            
            if (days.length === 0) {
                ravitaillementsContent.innerHTML = '<div class="no-results">Aucun ravitaillement trouvé pour cette période.</div>';
                return;
            }
            
            // Pour chaque jour, créer une section avec un tableau
            days.forEach(day => {
                const ravitailles = ravitaillesByDay[day];
                if (ravitailles.length === 0) return;
                
                // Formater la date pour l'affichage
                const dateParts = day.split('-');
                const formattedDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
                
                // Créer l'en-tête de la date
                const dateHeader = document.createElement('div');
                dateHeader.className = 'date-header';
                dateHeader.textContent = formattedDate;
                ravitaillementsContent.appendChild(dateHeader);
                
                // Créer le tableau pour ce jour
                const tableContainer = document.createElement('div');
                tableContainer.className = 'table-container';
                
                const table = document.createElement('table');
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Entrepôt</th>
                            <th>ID Batterie</th>
                            <th>VIN Batterie</th>
                            <th>Date d'Ajout</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                `;
                
                const tbody = table.querySelector('tbody');
                
                // Ajouter les lignes de ravitaillements
                ravitailles.forEach(ravitaille => {
                    const row = document.createElement('tr');
                    row.dataset.id = ravitaille.id;
                    
                    row.innerHTML = `
                        <td>${ravitaille.entrepot}</td>
                        <td>${ravitaille.batterie_id}</td>
                        <td>${ravitaille.batterie_vin}</td>
                        <td>${ravitaille.date}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                tableContainer.appendChild(table);
                ravitaillementsContent.appendChild(tableContainer);
            });
        })
        .catch(error => {
            console.error('Erreur lors du filtrage des données:', error);
            showToast('Erreur lors du filtrage des données.', 'error');
            ravitaillementsContent.innerHTML = '<div class="no-results">Erreur lors du chargement des données. Veuillez réessayer.</div>';
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
    searchInput.addEventListener('input', debounce(filterRavitaillements, 500));
    entrepotFilter.addEventListener('change', filterRavitaillements);
    timeFilter.addEventListener('change', function() {
        if (this.value === 'custom') {
            datePickerContainer.style.display = 'block';
        } else {
            datePickerContainer.style.display = 'none';
        }
        filterRavitaillements();
        calculateStats();
    });
    
    // Événement pour le changement de date personnalisée
    customDatePicker.addEventListener('change', function() {
        if (timeFilter.value === 'custom') {
            filterRavitaillements();
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
    
    // Par défaut, filtrer pour afficher tous les ravitaillements
    filterRavitaillements();
});
















// Afficher les détails d'une transaction
document.addEventListener('click', function(e) {
    // Vérifier si l'élément cliqué est une ligne de tableau
    if (e.target.closest('tr[data-id]')) {
        const row = e.target.closest('tr[data-id]');
        const id = row.dataset.id;
        
        // Récupérer les données de la ligne
        const entrepot = row.cells[0].textContent;
        const batterieId = row.cells[1].textContent;
        const batterieVin = row.cells[2].textContent;
        const dateTime = row.cells[3].textContent;
        
        // Mettre à jour la modal avec les données
        document.getElementById('transaction-id').textContent = id;
        document.getElementById('transaction-entrepot').textContent = entrepot;
        document.getElementById('transaction-battery-id').textContent = batterieId;
        document.getElementById('transaction-battery-vin').textContent = batterieVin;
        document.getElementById('transaction-datetime').textContent = dateTime;
        
        // Afficher la modal
        const modal = new bootstrap.Modal(document.getElementById('transactionDetailsModal'));
        modal.show();
    }
});
</script>


@endsection