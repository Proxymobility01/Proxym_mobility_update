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
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal.active {
    display: block;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: none;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.modal-header {
    background-color: var(--tertiary);
    border-bottom: 1px solid #ddd;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 8px 8px 0 0;
}

.modal-title {
    color: var(--text);
    font-weight: bold;
    margin: 0;
}

.close-modal {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover,
.close-modal:focus {
    color: black;
    text-decoration: none;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    background-color: var(--tertiary);
    border-top: 1px solid #ddd;
    padding: 15px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-radius: 0 0 8px 8px;
}

.form-label {
    font-weight: bold;
    margin-bottom: 5px;
    display: block;
}

.form-select, .form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 15px;
}

.checkbox-container {
    max-height: 300px;
    overflow-y: auto;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-check {
    margin-bottom: 10px;
}

.form-check-input {
    margin-right: 8px;
}

.form-check-label {
    cursor: pointer;
}

/* Actions */
.btn {
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    border: none;
    font-weight: bold;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background-color: var(--primary);
    color: var(--secondary);
}

.btn-secondary {
    background-color: var(--tertiary);
    color: var(--secondary);
}

.btn:hover {
    opacity: 0.9;
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

/* Alert styles */
.alert {
    padding: 12px 15px;
    margin: 15px 0;
    border-radius: 4px;
    border: 1px solid;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}

.mb-3 {
    margin-bottom: 1rem;
}

.ml-2 {
    margin-left: 0.5rem;
}

.me-2 {
    margin-right: 0.5rem;
}

.fw-bold {
    font-weight: bold;
}

.text-muted {
    color: #6c757d;
}
</style>

<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>Ravitaillement des Batteries</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Onglets de navigation -->
    <div class="nav-tabs">
        <div class="nav-tab {{ Request::is('associations') || (Request::is('associations/*') && !Request::is('associations/batteries*')) ? 'active' : '' }}"
             data-tab="moto-user"
             data-url="{{ route('associations.index') }}">
            Associations Moto-Utilisateur
        </div>

        <div class="nav-tab {{ Request::is('associations/batteries*') ? 'active' : '' }}"
             data-tab="battery-user"
             data-url="{{ route('associations.batteries.index') }}">
            Associations Batterie-Utilisateur
        </div>

        <div class="nav-tab {{ Request::is('ravitaillements') || Request::is('ravitaillements/*') ? 'active' : '' }}"
             data-tab="ravitaillement"
             data-url="{{ route('ravitailler.batteries.index') }}">
            Ravitailler Une Station
        </div>
    </div>

    <!-- Messages de session -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning_batteries'))
        <div class="alert alert-warning">
            Les batteries suivantes ont été ignorées :
            <ul>
                @foreach(session('warning_batteries') as $bat)
                    <li>{{ $bat }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-entrepots">{{ $stats['total_batteries'] ?? 0 }}</div>
                <div class="stat-label">Total Batteries</div>
                <div class="stat-text">dans le système</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="batteries-entrepot">{{ $stats['batteries_entrepot'] ?? 0 }}</div>
                <div class="stat-label">Batteries en Entrepôt</div>
                <div class="stat-text">stockées</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-battery-empty"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="batteries-disponibles">{{ $stats['batteries_disponibles'] ?? 0 }}</div>
                <div class="stat-label">Batteries Disponibles</div>
                <div class="stat-text">non assignées</div>
            </div>
        </div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-battery" placeholder="Rechercher une batterie par ID ou VIN...">
            <button type="button" class="search-btn" id="search-btn">
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
        <!-- L'historique initial sera affiché ici -->
        @if($ravitailles->count() > 0)
            @php
                $groupedRavitailles = $ravitailles->groupBy(function($item) {
                    return $item->created_at->format('Y-m-d');
                });
            @endphp
            
            @foreach($groupedRavitailles as $date => $dailyRavitailles)
                <div class="date-header">
                    {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Entrepôt</th>
                                <th>ID Batterie</th>
                                <th>Distributeur</th>
                                <th>Date d'Ajout</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyRavitailles as $ravitaille)
                                <tr data-id="{{ $ravitaille->id }}">
                                    <td>{{ $ravitaille->entrepot->nom_entrepot ?? 'N/A' }}</td>
                                    <td>{{ $ravitaille->batteryValide->batterie_unique_id ?? $ravitaille->bat_entrante }}</td>
                                    <td>{{ $ravitaille->distributeur->name ?? 'N/A' }}</td>
                                    <td>{{ $ravitaille->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-secondary btn-sm view-details" 
                                                data-id="{{ $ravitaille->id }}"
                                                data-entrepot="{{ $ravitaille->entrepot->nom_entrepot ?? 'N/A' }}"
                                                data-battery="{{ $ravitaille->batteryValide->batterie_unique_id ?? $ravitaille->bat_entrante }}"
                                                data-date="{{ $ravitaille->created_at->format('d/m/Y H:i') }}">
                                            Détails
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
            
            <!-- Pagination -->
            <div class="pagination-container">
                {{ $ravitailles->links() }}
            </div>
        @else
            <div class="no-results">Aucun ravitaillement trouvé.</div>
        @endif
    </div>
</div>

<!-- Modal de Ravitaillement -->
<div id="ravitaillementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Nouveau Ravitaillement</h5>
            <span class="close-modal">&times;</span>
        </div>
        <form action="{{ route('ravitailler.batteries.store') }}" method="POST" id="ravitaillementForm">
            @csrf
            <div class="modal-body">
                <div class="mb-3">
                    <label for="entrepot" class="form-label">Choisir un entrepôt</label>
                    <select name="entrepot_id" id="entrepot" class="form-select" required>
                        <option value="">Sélectionner un entrepôt</option>
                        @foreach($entrepots as $entrepot)
                            <option value="{{ $entrepot->id }}">{{ $entrepot->nom_entrepot }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Rechercher une batterie</label>
                    <input type="text" class="form-control" id="modalBatterySearch" placeholder="Rechercher par ID ou VIN">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Batteries disponibles ({{ $batteries->count() }} disponibles)</label>
                    <div class="checkbox-container" id="modalBatteriesList">
                        @forelse($batteries as $battery)
                            <div class="form-check mb-2 battery-item">
                                <input type="checkbox" class="form-check-input" name="batteries[]" value="{{ $battery->id }}" id="battery-{{ $battery->id }}">
                                <label class="form-check-label" for="battery-{{ $battery->id }}">
                                    <strong>{{ $battery->batterie_unique_id }}</strong><br>
                                    <span class="text-muted">VIN: {{ $battery->mac_id }}</span>
                                </label>
                            </div>
                        @empty
                            <div class="no-results">Aucune batterie disponible pour le ravitaillement.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">Annuler</button>
                <button type="submit" class="btn btn-primary" id="submitRavitaillementBtn">Ravitailler</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de détails -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Détails du Ravitaillement</h5>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="transaction-details">
                <div class="transaction-detail-item">
                    <strong>ID de la transaction:</strong>
                    <span id="detail-id"></span>
                </div>
                <div class="transaction-detail-item">
                    <strong>Entrepôt:</strong>
                    <span id="detail-entrepot"></span>
                </div>
                <div class="transaction-detail-item">
                    <strong>ID Batterie:</strong>
                    <span id="detail-battery"></span>
                </div>
                <div class="transaction-detail-item">
                    <strong>Date et heure:</strong>
                    <span id="detail-date"></span>
                </div>
                <div class="transaction-detail-item">
                    <strong>Type d'opération:</strong>
                    <span>Ravitaillement</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-modal">Fermer</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    const searchInput = document.getElementById('search-battery');
    const searchBtn = document.getElementById('search-btn');
    const entrepotFilter = document.getElementById('entrepot-filter');
    const timeFilter = document.getElementById('time-filter');
    const customDatePicker = document.getElementById('custom-date');
    const datePickerContainer = document.getElementById('date-picker-container');
    const ravitaillementsContent = document.getElementById('ravitaillements-content');
    const addRavitaillementBtn = document.getElementById('add-ravitaillement');
    const modalBatterySearch = document.getElementById('modalBatterySearch');
    const modalBatteriesList = document.getElementById('modalBatteriesList');
    const ravitaillementModal = document.getElementById('ravitaillementModal');
    const detailsModal = document.getElementById('detailsModal');
    const ravitaillementForm = document.getElementById('ravitaillementForm');

    // Afficher la date actuelle
    const dateElement = document.getElementById('date');
    const today = new Date();
    dateElement.textContent = today.toLocaleDateString('fr-FR');
    customDatePicker.valueAsDate = today;

    // Gestion des modales
    function openModal(modal) {
        modal.classList.add('active');
    }

    function closeModal(modal) {
        modal.classList.remove('active');
    }

    // Event listeners pour les modales
    document.querySelectorAll('.close-modal').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });

    // Fermer les modales en cliquant à l'extérieur
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target);
        }
    });

    // Ouvrir la modal de ravitaillement
    addRavitaillementBtn.addEventListener('click', function() {
        openModal(ravitaillementModal);
    });

    // Gestion du sélecteur de date
    timeFilter.addEventListener('change', function() {
        if (this.value === 'custom') {
            datePickerContainer.style.display = 'block';
        } else {
            datePickerContainer.style.display = 'none';
        }
        filterRavitaillements();
    });

    // Recherche dans la modal
    modalBatterySearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const batteries = modalBatteriesList.querySelectorAll('.battery-item');
        
        batteries.forEach(batteryItem => {
            const label = batteryItem.querySelector('.form-check-label').textContent.toLowerCase();
            batteryItem.style.display = label.includes(searchTerm) ? 'block' : 'none';
        });
    });

    // Soumission du formulaire
    ravitaillementForm.addEventListener('submit', function(e) {
        const checkedBatteries = this.querySelectorAll('input[name="batteries[]"]:checked');
        const entrepotSelect = this.querySelector('#entrepot');
        
        if (!entrepotSelect.value) {
            e.preventDefault();
            showToast('Veuillez sélectionner un entrepôt.', 'error');
            return;
        }
        
        if (checkedBatteries.length === 0) {
            e.preventDefault();
            showToast('Veuillez sélectionner au moins une batterie.', 'error');
            return;
        }
        
        if (!confirm(`Confirmer le ravitaillement de ${checkedBatteries.length} batterie(s) ?`)) {
            e.preventDefault();
        }
    });

    // Filtrage des ravitaillements
    function filterRavitaillements() {
        const searchTerm = searchInput.value.toLowerCase();
        const entrepotValue = entrepotFilter.value;
        const timeValue = timeFilter.value;
        
        const data = {
            search: searchTerm,
            entrepot_id: entrepotValue,
            date_filter: timeValue
        };
        
        if (timeValue === 'custom') {
            data.custom_date = customDatePicker.value;
        }
        
        ravitaillementsContent.innerHTML = '<div class="loader-container"><div class="loader"></div></div>';

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
            displayFilteredResults(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors du filtrage des données.', 'error');
            ravitaillementsContent.innerHTML = '<div class="no-results">Erreur lors du chargement des données.</div>';
        });
    }

    function displayFilteredResults(data) {
        ravitaillementsContent.innerHTML = '';
        
        const ravitaillesByDay = data.ravitaillesByDay;
        const days = Object.keys(ravitaillesByDay).sort().reverse();
        
        if (days.length === 0) {
            ravitaillementsContent.innerHTML = '<div class="no-results">Aucun ravitaillement trouvé pour cette période.</div>';
            return;
        }
        
        days.forEach(day => {
            const ravitailles = ravitaillesByDay[day];
            if (ravitailles.length === 0) return;
            
            const dateParts = day.split('-');
            const formattedDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
            
            const dateHeader = document.createElement('div');
            dateHeader.className = 'date-header';
            dateHeader.textContent = formattedDate;
            ravitaillementsContent.appendChild(dateHeader);
            
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            
            const tbody = table.querySelector('tbody');
            
            ravitailles.forEach(ravitaille => {
                const row = document.createElement('tr');
                row.dataset.id = ravitaille.id;
                
                row.innerHTML = `
                    <td>${ravitaille.entrepot}</td>
                    <td>${ravitaille.batterie_id}</td>
                    <td>${ravitaille.batterie_vin}</td>
                    <td>${ravitaille.date}</td>
                    <td>
                        <button class="btn btn-secondary btn-sm view-details" 
                                data-id="${ravitaille.id}"
                                data-entrepot="${ravitaille.entrepot}"
                                data-battery="${ravitaille.batterie_id}"
                                data-date="${ravitaille.date}">
                            Détails
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
            
            tableContainer.appendChild(table);
            ravitaillementsContent.appendChild(tableContainer);
        });
    }

    // Affichage des détails
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-details')) {
            const btn = e.target;
            
            document.getElementById('detail-id').textContent = btn.dataset.id;
            document.getElementById('detail-entrepot').textContent = btn.dataset.entrepot;
            document.getElementById('detail-battery').textContent = btn.dataset.battery;
            document.getElementById('detail-date').textContent = btn.dataset.date;
            
            openModal(detailsModal);
        }
    });

    // Fonction pour afficher les toasts
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

    // Event listeners pour les filtres
    searchBtn.addEventListener('click', filterRavitaillements);
    searchInput.addEventListener('keyup', debounce(filterRavitaillements, 500));
    entrepotFilter.addEventListener('change', filterRavitaillements);
    customDatePicker.addEventListener('change', filterRavitaillements);

    // Fonction debounce
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Navigation par onglets
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });
    });

    // Messages de session
    @if(session('success'))
        showToast("{{ session('success') }}", 'success');
        // Recharger les données après un ravitaillement réussi
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    @endif

    @if(session('error'))
        showToast("{{ session('error') }}", 'error');
    @endif

    // Styles supplémentaires pour les détails de transaction
    const transactionDetailStyles = `
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
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .nav-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .nav-tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .nav-tab:hover {
            background-color: var(--tertiary);
        }
        
        .nav-tab.active {
            border-bottom-color: var(--primary);
            background-color: var(--tertiary);
            font-weight: bold;
        }
        
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .content-header h2 {
            margin: 0;
            color: var(--text);
        }
        
        .date {
            color: #6c757d;
            font-size: 14px;
        }
    `;
    
    // Ajouter les styles à la page
    const styleSheet = document.createElement('style');
    styleSheet.textContent = transactionDetailStyles;
    document.head.appendChild(styleSheet);
});

// Fonction pour mettre à jour les statistiques en temps réel
function updateStats() {
    fetch('/api/ravitaillements/stats', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            timeFilter: 'today'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.entrepotCount !== undefined) {
            document.getElementById('total-entrepots').textContent = data.entrepotCount;
        }
        if (data.availableBatteries !== undefined) {
            document.getElementById('batteries-disponibles').textContent = data.availableBatteries;
        }
    })
    .catch(error => {
        console.error('Erreur lors de la mise à jour des statistiques:', error);
    });
}

// Mettre à jour les statistiques toutes les 30 secondes
setInterval(updateStats, 30000);

// Fonction pour valider le formulaire avant soumission
function validateForm() {
    const entrepotSelect = document.getElementById('entrepot');
    const checkedBatteries = document.querySelectorAll('input[name="batteries[]"]:checked');
    
    let isValid = true;
    let errors = [];
    
    if (!entrepotSelect.value) {
        errors.push('Veuillez sélectionner un entrepôt.');
        isValid = false;
    }
    
    if (checkedBatteries.length === 0) {
        errors.push('Veuillez sélectionner au moins une batterie.');
        isValid = false;
    }
    
    if (!isValid) {
        errors.forEach(error => {
            showToast(error, 'error');
        });
    }
    
    return isValid;
}

// Fonction pour sélectionner/désélectionner toutes les batteries
function toggleAllBatteries() {
    const checkboxes = document.querySelectorAll('input[name="batteries[]"]:not([style*="display: none"])');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
}

// Ajouter un bouton pour sélectionner toutes les batteries
document.addEventListener('DOMContentLoaded', function() {
    const batteriesLabel = document.querySelector('label[for="modalBatteriesList"]') || 
                          document.querySelector('.form-label:last-of-type');
    
    if (batteriesLabel && batteriesLabel.textContent.includes('Batteries disponibles')) {
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'btn btn-secondary btn-sm ml-2';
        toggleButton.textContent = 'Tout sélectionner/désélectionner';
        toggleButton.onclick = toggleAllBatteries;
        
        batteriesLabel.appendChild(toggleButton);
    }
});

// Fonction pour compter les batteries sélectionnées
function updateSelectedCount() {
    const checkedBatteries = document.querySelectorAll('input[name="batteries[]"]:checked');
    const submitBtn = document.getElementById('submitRavitaillementBtn');
    
    if (submitBtn) {
        if (checkedBatteries.length > 0) {
            submitBtn.textContent = `Ravitailler (${checkedBatteries.length})`;
            submitBtn.disabled = false;
        } else {
            submitBtn.textContent = 'Ravitailler';
            submitBtn.disabled = false;
        }
    }
}

// Écouter les changements de sélection des batteries
document.addEventListener('change', function(e) {
    if (e.target.name === 'batteries[]') {
        updateSelectedCount();
    }
});

// Fonction pour réinitialiser le formulaire
function resetForm() {
    const form = document.getElementById('ravitaillementForm');
    if (form) {
        form.reset();
        updateSelectedCount();
    }
}

// Ajouter la fonctionnalité de réinitialisation
document.addEventListener('DOMContentLoaded', function() {
    const modalCloseButtons = document.querySelectorAll('.close-modal');
    modalCloseButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.closest('#ravitaillementModal')) {
                resetForm();
            }
        });
    });
});
</script>

@endsection