@extends('layouts.app')

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

/* Styles pour les modales */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: var(--background);
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 900px;
    max-width: 90%;
    border-radius: 8px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}

.close-modal {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: var(--secondary);
}

.modal-body {
    padding: 20px 0;
}

.modal.active {
    display: block;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: var(--secondary);
}

.form-group select,
.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #ddd;
    padding-top: 10px;
}

.btn {
    padding: 8px 15px;
    margin-left: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-primary {
    background-color: var(--primary);
    color: var(--secondary);
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

.pending .stat-icon {
    color: #ffc107;
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
}

.search-group {
    display: flex;
    align-items: center;
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
}

.add-btn {
    background-color: var(--primary);
    color: var(--secondary);
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.add-btn i {
    margin-right: 5px;
}

/* Table */
.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background-color: var(--tertiary);
}

th,
td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

tr:hover {
    background-color: var(--tertiary);
}

.action-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    margin-right: 5px;
}

.action-btn i {
    font-size: 16px;
}

.action-btn.edit-association i {
    color: #ffc107;
}

.action-btn.delete-association i {
    color: #dc3545;
}

.action-btn.view-association i {
    color: #17a2b8;
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

/* Badges pour le statut de batterie */
.battery-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.battery-status.online {
    background-color: rgba(40, 167, 69, 0.2);
    color: #28a745;
}

.battery-status.offline {
    background-color: rgba(108, 117, 125, 0.2);
    color: #6c757d;
}

.battery-level {
    display: inline-block;
    width: 50px;
    height: 16px;
    background-color: #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}

.battery-level-inner {
    height: 100%;
    border-radius: 8px;
    transition: width 0.3s ease;
}

.battery-level-text {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    color: #212529;
}

.battery-high .battery-level-inner {
    background-color: #28a745;
}

.battery-medium .battery-level-inner {
    background-color: #ffc107;
}

.battery-low .battery-level-inner {
    background-color: #dc3545;
}

/* Styles pour les tabs */
.nav-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.nav-tab {
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-right: 10px;
}

.nav-tab:hover {
    background-color: var(--tertiary);
}

.nav-tab.active {
    border-bottom-color: var(--primary);
    font-weight: bold;
}

/* Styles pour la modale d'ajout/édition d'association */
.association-form-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.selection-column {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
}

.selection-column h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 16px;
    border-bottom: 2px solid var(--primary);
    padding-bottom: 8px;
    display: inline-block;
}

.selection-search {
    margin-bottom: 15px;
}

.selection-search input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.selection-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.selection-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.selection-item:last-child {
    border-bottom: none;
}

.selection-item:hover {
    background-color: #f8f9fa;
}

.selection-item.selected {
    background-color: rgba(220, 219, 50, 0.1);
}

.selection-item input[type="radio"] {
    margin-right: 10px;
}

.selection-item-details {
    flex-grow: 1;
}

.selection-item-title {
    font-weight: bold;
    margin-bottom: 2px;
}

.selection-item-subtitle {
    font-size: 0.8em;
    color: #6c757d;
}

.battery-info {
    display: flex;
    align-items: center;
    margin-top: 4px;
}

.selection-footer {
    margin-top: 15px;
    color: #6c757d;
    font-size: 0.9em;
}
</style>

@section('content')
<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>{{ $pageTitle }}</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Onglets de navigation -->
    
<!-- Onglets de navigation -->
<div class="nav-tabs">
    <div class="nav-tab {{ Request::is('associations') || (Request::is('associations/*') && !Request::is('associations/batteries*')) ? 'active' : '' }}" data-tab="moto-user">Associations Moto-Utilisateur</div>
    <div class="nav-tab {{ Request::is('associations/batteries*') ? 'active' : '' }}" data-tab="battery-user">Associations Batterie-Utilisateur</div>
</div>

    <!-- Barre de recherche et ajout -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-association" placeholder="Rechercher une association...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-group">
            <button id="add-association-btn" class="add-btn">
                <i class="fas fa-plus"></i>
                Associer Batterie & Moto
            </button>
        </div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-link"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-associations">{{ $associations->count() }}</div>
                <div class="stat-label">Total des associations</div>
                <div class="stat-text">Batteries associées</div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-motorcycle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-motos">{{ $motos->count() }}</div>
                <div class="stat-label">Motos disponibles</div>
                <div class="stat-text">Avec utilisateur associé</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-batteries">{{ $batteries->count() }}</div>
                <div class="stat-label">Batteries</div>
                <div class="stat-text">Disponibles pour l'association</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-battery-quarter"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="low-batteries">{{ $lowBatteries }}</div>
                <div class="stat-label">Batteries faibles</div>
                <div class="stat-text">Niveau < 20%</div>
            </div>
        </div>
    </div>

    <!-- Tableau des associations -->
   <!-- Dans votre fichier Blade, modifiez la structure du tableau comme suit -->

<!-- Tableau des associations -->
<div class="table-container">
    <table id="associations-table">
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Moto</th>
                <th>VIN</th> <!-- Nouvelle colonne pour le VIN -->
                <th>Batterie</th>
                <th>MAC ID</th> <!-- Nouvelle colonne pour le MAC ID -->
                <th>État</th>
                <th>Niveau</th>
                <th>Date d'association</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="associations-table-body">
            @foreach($associations as $association)
            <tr data-id="{{ $association->id }}">
                <td>{{ $association->association->validatedUser->nom ?? 'N/A' }} {{ $association->association->validatedUser->prenom ?? '' }}</td>
                <td>{{ $association->association->motosValide->model ?? 'N/A' }} ({{ $association->association->motosValide->moto_unique_id ?? 'N/A' }})</td>
                <td>{{ $association->association->motosValide->vin ?? 'N/A' }}</td> <!-- VIN de la moto -->
                <td>{{ $association->batterie->batterie_unique_id ?? 'N/A' }}</td>
                <td>{{ $association->batterie->mac_id ?? 'N/A' }}</td> <!-- MAC ID de la batterie -->
                <td>
                    <span class="battery-status offline" data-mac-id="{{ $association->batterie->mac_id ?? '' }}">Offline</span>
                </td>
                <td>
                    <div class="battery-level battery-medium" data-mac-id="{{ $association->batterie->mac_id ?? '' }}">
                        <div class="battery-level-inner" style="width: 0%"></div>
                        <div class="battery-level-text">0%</div>
                    </div>
                </td>
                <td>{{ \Carbon\Carbon::parse($association->created_at)->format('d/m/Y') }}</td>
                <td style="display: flex;">
                    <button class="action-btn view-association" title="Voir les détails" data-mac-id="{{ $association->batterie->mac_id ?? '' }}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit-association" title="Modifier l'association">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-association" title="Supprimer l'association">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modale d'ajout/édition d'association -->
<div class="modal" id="association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Associer Batterie & Moto</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="association-form">
                @csrf
                <input type="hidden" id="association-id" name="id">
                <input type="hidden" id="form-method" name="_method" value="POST">

                <div class="association-form-container">
                    <!-- Sélection de la moto avec utilisateur -->
                    <div class="selection-column">
                        <h3>Sélectionner une Moto</h3>
                        <div class="selection-search">
                            <input type="text" id="search-moto" placeholder="Rechercher une moto...">
                        </div>
                        <div class="selection-list" id="motos-list">
                            <!-- Les motos seront chargées dynamiquement ici -->
                        </div>
                        <div class="selection-footer">
                            <small>Seules les motos déjà associées à un utilisateur sont affichées.</small>
                        </div>
                    </div>

                    <!-- Sélection de la batterie -->
                    <div class="selection-column">
                        <h3>Sélectionner une Batterie</h3>
                        <div class="selection-search">
                            <input type="text" id="search-battery" placeholder="Rechercher une batterie...">
                        </div>
                        <div class="selection-list" id="batteries-list">
                            <!-- Les batteries seront chargées dynamiquement ici -->
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="save-association" class="btn btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

<!-- Modale de détails de la batterie -->
<div class="modal" id="battery-details-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Détails de la Batterie</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div id="battery-info-container">
                <!-- Les détails de la batterie seront chargés dynamiquement ici -->
                <div class="loading">Chargement des données...</div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Fermer</button>
        </div>
    </div>
</div>

<!-- Modale de suppression d'association -->
<div class="modal" id="delete-association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Supprimer l'association</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette association ?</p>
            <div class="association-details">
                <p><strong>Utilisateur :</strong> <span id="delete-user-name"></span></p>
                <p><strong>Moto :</strong> <span id="delete-moto-id"></span></p>
                <p><strong>Batterie :</strong> <span id="delete-battery-id"></span></p>
            </div>
            <form id="delete-association-form" method="POST">
                @csrf
                @method('DELETE')
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-delete-association" class="btn btn-primary">Supprimer</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ------------------------------------------------------------
    // Initialisation et variables
    // ------------------------------------------------------------
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const searchInput = document.getElementById('search-association');
    const addAssociationBtn = document.getElementById('add-association-btn');
    const associationModal = document.getElementById('association-modal');
    const batteryDetailsModal = document.getElementById('battery-details-modal');
    const deleteAssociationModal = document.getElementById('delete-association-modal');
    const associationsTableBody = document.getElementById('associations-table-body');
    
    // Élements de recherche
    const searchMotoInput = document.getElementById('search-moto');
    const searchBatteryInput = document.getElementById('search-battery');
    
    // Listes des éléments sélectionnables
    const motosList = document.getElementById('motos-list');
    const batteriesList = document.getElementById('batteries-list');
    
    // Données en cache
    let cachedMotos = [];
    let cachedBatteries = [];
    let selectedMotoId = null;
    let selectedBatteryId = null;

    // Afficher la date actuelle
    const dateElement = document.getElementById('date');
    const today = new Date();
    dateElement.textContent = today.toLocaleDateString('fr-FR');

    // ------------------------------------------------------------
    // Fonctions de chargement des données
    // ------------------------------------------------------------
    
    // Charger les motos disponibles
    function loadAvailableMotos() {
        fetch('/associations/batteries/motos/available')
            .then(response => response.json())
            .then(result => {
                if (result.data) {
                    cachedMotos = result.data;
                    renderMotosList(result.data);
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des motos:', error);
                showToast('Erreur lors du chargement des motos disponibles.', 'error');
            });
    }
    
    // Charger les batteries disponibles
function loadAvailableBatteries(includeBatteryId = null) {
    let url = '/associations/batteries/available';
    
    // Si nous sommes en mode édition, inclure la batterie actuellement associée
    if (includeBatteryId) {
        url += `?include_battery_id=${includeBatteryId}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(result => {
            if (result.data) {
                cachedBatteries = result.data;
                renderBatteriesList(result.data);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des batteries:', error);
            showToast('Erreur lors du chargement des batteries disponibles.', 'error');
        });
}
    // Charger les données BMS pour toutes les batteries en une seule fois
    function loadAllBatteriesStatus() {
        const batteryElements = document.querySelectorAll('[data-mac-id]');
        const macIds = new Set();
        
        batteryElements.forEach(element => {
            const macId = element.getAttribute('data-mac-id');
            if (macId && macId.trim() !== '') {
                macIds.add(macId);
            }
        });
        
        if (macIds.size === 0) return;
        
        const macIdArray = Array.from(macIds);
        
        // Appel API en lot pour toutes les batteries
        fetch('/associations/batteries/bms/bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ mac_ids: macIdArray })
        })
        .then(response => response.json())
        .then(result => {
            if (result.data) {
                // Mettre à jour tous les états de batterie en une seule fois
                updateBatteriesStatus(result.data);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des statuts de batteries:', error);
        });
    }
    
    // Mettre à jour les états des batteries dans l'interface
    function updateBatteriesStatus(batteriesData) {
        for (const macId in batteriesData) {
            const batteryData = batteriesData[macId];
            
            // Mettre à jour le statut (online/offline)
            const statusElements = document.querySelectorAll(`.battery-status[data-mac-id="${macId}"]`);
            statusElements.forEach(element => {
                element.textContent = batteryData.status;
                element.className = `battery-status ${batteryData.status.toLowerCase()}`;
            });
            
            // Mettre à jour le niveau de batterie
            const levelElements = document.querySelectorAll(`.battery-level[data-mac-id="${macId}"]`);
            levelElements.forEach(element => {
                const levelInner = element.querySelector('.battery-level-inner');
                const levelText = element.querySelector('.battery-level-text');
                
                if (levelInner && levelText) {
                    const socValue = batteryData.soc || 0;
                    levelInner.style.width = `${socValue}%`;
                    levelText.textContent = `${Math.round(socValue)}%`;
                    
                    // Appliquer la classe en fonction du niveau
                    element.className = 'battery-level';
                    if (socValue < 20) {
                        element.classList.add('battery-low');
                    } else if (socValue < 50) {
                        element.classList.add('battery-medium');
                    } else {
                        element.classList.add('battery-high');
                    }
                }
            });
        }
    }

    // ------------------------------------------------------------
    // Fonctions de rendu
    // ------------------------------------------------------------
    
    // Afficher la liste des motos
    function renderMotosList(motos) {
        motosList.innerHTML = '';
        
        if (motos.length === 0) {
            motosList.innerHTML = '<div class="selection-item">Aucune moto disponible</div>';
            return;
        }
        
        motos.forEach(moto => {
            const motoItem = document.createElement('div');
            motoItem.className = 'selection-item';
            if (selectedMotoId === moto.moto_unique_id) {
                motoItem.classList.add('selected');
            }
            
            motoItem.innerHTML = `
                <input type="radio" name="moto_unique_id" id="moto-${moto.moto_unique_id}" 
                    value="${moto.moto_unique_id}" ${selectedMotoId === moto.moto_unique_id ? 'checked' : ''}>
                <div class="selection-item-details">
                    <div class="selection-item-title">${moto.model} (${moto.moto_unique_id})</div>
                    <div class="selection-item-subtitle">
                        VIN: ${moto.vin || 'N/A'}
                        ${moto.user ? `| Utilisateur: ${moto.user.nom} ${moto.user.prenom}` : ''}
                    </div>
                </div>
            `;
            
            motoItem.addEventListener('click', function() {
                document.querySelectorAll('#motos-list .selection-item').forEach(item => {
                    item.classList.remove('selected');
                });
                
                motoItem.classList.add('selected');
                const radio = motoItem.querySelector('input[type="radio"]');
                radio.checked = true;
                selectedMotoId = moto.moto_unique_id;
            });
            
            motosList.appendChild(motoItem);
        });
    }
    
    // Afficher la liste des batteries
    function renderBatteriesList(batteries) {
        batteriesList.innerHTML = '';
        
        if (batteries.length === 0) {
            batteriesList.innerHTML = '<div class="selection-item">Aucune batterie disponible</div>';
            return;
        }
        
        batteries.forEach(battery => {
            const batteryItem = document.createElement('div');
            batteryItem.className = 'selection-item';
            if (selectedBatteryId === battery.batterie_unique_id) {
                batteryItem.classList.add('selected');
            }
            
            // Déterminer la classe pour le niveau de batterie
            let batteryLevelClass = 'battery-high';
            if (battery.pourcentage < 20) {
                batteryLevelClass = 'battery-low';
            } else if (battery.pourcentage < 50) {
                batteryLevelClass = 'battery-medium';
            }
            
            batteryItem.innerHTML = `
                <input type="radio" name="battery_unique_id" id="battery-${battery.batterie_unique_id}" 
                    value="${battery.batterie_unique_id}" ${selectedBatteryId === battery.batterie_unique_id ? 'checked' : ''}>
                <div class="selection-item-details">
                    <div class="selection-item-title">Batterie ${battery.batterie_unique_id}</div>
                    <div class="selection-item-subtitle">MAC: ${battery.mac_id || 'N/A'}</div>
                    <div class="battery-info">
                        <span class="battery-status ${battery.status}">${battery.status}</span>
                        <div class="battery-level ${batteryLevelClass}" style="margin-left: 10px;">
                            <div class="battery-level-inner" style="width: ${battery.pourcentage}%"></div>
                            <div class="battery-level-text">${Math.round(battery.pourcentage)}%</div>
                        </div>
                    </div>
                </div>
            `;
            
            batteryItem.addEventListener('click', function() {
                document.querySelectorAll('#batteries-list .selection-item').forEach(item => {
                    item.classList.remove('selected');
                });
                
                batteryItem.classList.add('selected');
                const radio = batteryItem.querySelector('input[type="radio"]');
                radio.checked = true;
                selectedBatteryId = battery.batterie_unique_id;
            });
            
            batteriesList.appendChild(batteryItem);
        });
    }
    
    // Filtrer les motos dans la liste
    function filterMotosList(searchTerm) {
        searchTerm = searchTerm.toLowerCase();
        
        if (!cachedMotos.length) return;
        
        const filteredMotos = cachedMotos.filter(moto => {
            return moto.moto_unique_id.toLowerCase().includes(searchTerm) || 
                   moto.model.toLowerCase().includes(searchTerm) ||
                   (moto.vin && moto.vin.toLowerCase().includes(searchTerm)) ||
                   (moto.user && (`${moto.user.nom} ${moto.user.prenom}`).toLowerCase().includes(searchTerm));
        });
        
        renderMotosList(filteredMotos);
    }
    
    // Filtrer les batteries dans la liste
    function filterBatteriesList(searchTerm) {
        searchTerm = searchTerm.toLowerCase();
        
        if (!cachedBatteries.length) return;
        
        const filteredBatteries = cachedBatteries.filter(battery => {
            return battery.batterie_unique_id.toLowerCase().includes(searchTerm) || 
                   (battery.mac_id && battery.mac_id.toLowerCase().includes(searchTerm));
        });
        
        renderBatteriesList(filteredBatteries);
    }
    
    // Filtrer le tableau principal
    function filterAssociationsTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = associationsTableBody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            row.style.display = textContent.includes(searchTerm) ? '' : 'none';
        });
    }

    // ------------------------------------------------------------
    // Fonctions de gestion des modales
    // ------------------------------------------------------------
    
    // Ouvrir la modale d'ajout d'association
    function openAddAssociationModal() {
        selectedMotoId = null;
        selectedBatteryId = null;
        
        document.getElementById('modal-title').textContent = 'Associer Batterie & Moto';
        document.getElementById('association-id').value = '';
        document.getElementById('form-method').value = 'POST';
        
        // Charger les données fraîches
        loadAvailableMotos();
        loadAvailableBatteries();
        
        associationModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
   // Ouvrir la modale d'édition d'association
function openEditAssociationModal(row) {
    const id = row.dataset.id;
    
    // Récupérer les détails de l'association
    fetch(`/associations/batteries/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modal-title').textContent = 'Modifier l\'association';
            document.getElementById('association-id').value = id;
            document.getElementById('form-method').value = 'PUT';
            
            selectedMotoId = data.moto_unique_id;
            selectedBatteryId = data.battery_unique_id;
            
            // Charger les motos disponibles
            loadAvailableMotos();
            
            // Charger les batteries disponibles, y compris la batterie actuellement associée
            loadAvailableBatteries(data.battery_id);
            
            associationModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Erreur lors du chargement des détails de l\'association:', error);
            showToast('Erreur lors du chargement des détails de l\'association.', 'error');
        });
}
    
    // Ouvrir la modale de détails de la batterie
    function openBatteryDetailsModal(macId) {
        if (!macId) {
            showToast('Cette batterie n\'a pas d\'identifiant MAC.', 'error');
            return;
        }
        
        const container = document.getElementById('battery-info-container');
        container.innerHTML = '<div class="loading">Chargement des données...</div>';
        
        batteryDetailsModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Charger les détails BMS de la batterie
        fetch(`/associations/batteries/bms/${macId}`)
            .then(response => response.json())
            .then(data => {
                renderBatteryDetails(container, data);
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails de la batterie:', error);
                container.innerHTML = '<div class="error">Erreur lors du chargement des données. La batterie est peut-être hors ligne.</div>';
            });
    }
    
    // Ouvrir la modale de suppression d'association
    function openDeleteAssociationModal(row) {
        const id = row.dataset.id;
        const user = row.cells[0].textContent;
        const moto = row.cells[1].textContent;
        const battery = row.cells[2].textContent;
        
        document.getElementById('delete-user-name').textContent = user;
        document.getElementById('delete-moto-id').textContent = moto;
        document.getElementById('delete-battery-id').textContent = battery;
        
        const form = document.getElementById('delete-association-form');
        form.action = `/associations/batteries/${id}`;
        
        deleteAssociationModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Fermer toutes les modales
    function closeAllModals() {
        [associationModal, batteryDetailsModal, deleteAssociationModal].forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
    
    // Rendre les détails de la batterie
    function renderBatteryDetails(container, data) {
        // Formater les données pour l'affichage
        const batteryLevelClass = data.soc < 20 ? 'battery-low' : (data.soc < 50 ? 'battery-medium' : 'battery-high');
        
        container.innerHTML = `
            <div class="battery-details">
                <div class="battery-details-header">
                    <h3>Batterie ${data.id}</h3>
                    <div class="battery-status ${data.status.toLowerCase()}">${data.status}</div>
                </div>
                
                <div class="battery-details-grid">
                    <div class="battery-details-card">
                        <h4>Informations Générales</h4>
                        <table class="battery-info-table">
                            <tr>
                                <td>MAC ID:</td>
                                <td>${data.mac_id}</td>
                            </tr>
                            <tr>
                                <td>État de charge:</td>
                                <td>
                                    <div class="battery-level ${batteryLevelClass}">
                                        <div class="battery-level-inner" style="width: ${data.soc}%"></div>
                                        <div class="battery-level-text">${Math.round(data.soc)}%</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Tension:</td>
                                <td>${data.voltage} V</td>
                            </tr>
                            <tr>
                                <td>Courant:</td>
                                <td>${data.current} A</td>
                            </tr>
                            <tr>
                                <td>Cycles:</td>
                                <td>${data.cycles}</td>
                            </tr>
                            <tr>
                                <td>État de fonctionnement:</td>
                                <td>${data.batteryStatus}</td>
                            </tr>
                            <tr>
                                <td>Dernière mise à jour:</td>
                                <td>${data.updatedAt}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="battery-details-card">
                        <h4>Graphiques</h4>
                        <div class="battery-chart" id="battery-soc-chart"></div>
                    </div>
                </div>
                
                <div class="battery-cell-voltages">
                    <h4>Tensions des cellules</h4>
                    <div class="cell-voltages-grid">
                        ${data.cellVoltages.map(cell => `
                            <div class="cell-voltage-item cell-${cell.status}">
                                <div class="cell-number">Cellule ${cell.number}</div>
                                <div class="cell-value">${cell.voltage} V</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
        
        // Initialiser les graphiques si nécessaire
        if (data.chartData && data.chartData.timeLabels.length > 0) {
            setTimeout(() => {
                initBatteryChart(data.chartData);
            }, 100);
        }
    }
    
    // Initialiser le graphique de la batterie
    function initBatteryChart(chartData) {
        // Cette fonction pourrait être implémentée avec Chart.js ou une autre bibliothèque
        // Pour simplifier, on laisse cette partie en commentaire
        console.log('Données pour le graphique:', chartData);
    }

    // ------------------------------------------------------------
    // Fonctions de soumission de formulaires
    // ------------------------------------------------------------
    
    // Enregistrer ou mettre à jour une association
    function saveAssociation() {
        const isEdit = document.getElementById('form-method').value === 'PUT';
        const associationId = document.getElementById('association-id').value;
        
        if (!selectedMotoId) {
            showToast('Veuillez sélectionner une moto.', 'error');
            return;
        }
        
        if (!selectedBatteryId) {
            showToast('Veuillez sélectionner une batterie.', 'error');
            return;
        }
        
        const data = {
            moto_unique_id: selectedMotoId,
            battery_unique_id: selectedBatteryId,
            _token: csrfToken
        };
        
        const url = isEdit ? `/associations/batteries/${associationId}` : '/associations/batteries';
        const method = isEdit ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                closeAllModals();
                
                // Recharger la page pour afficher les changements
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'enregistrement de l\'association:', error);
            showToast('Erreur lors de l\'enregistrement de l\'association.', 'error');
        });
    }
    
    // Supprimer une association
    function deleteAssociation() {
        const form = document.getElementById('delete-association-form');
        const url = form.action;
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                closeAllModals();
                
                // Recharger la page pour afficher les changements
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la suppression de l\'association:', error);
            showToast('Erreur lors de la suppression de l\'association.', 'error');
        });
    }

    // ------------------------------------------------------------
    // Fonctions utilitaires
    // ------------------------------------------------------------
    
    // Afficher un message toast
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

    // ------------------------------------------------------------
    // Gestion des messages de session Laravel
    // ------------------------------------------------------------
    @if(session('success'))
    showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
    showToast("{{ session('error') }}", 'error');
    @endif

    // ------------------------------------------------------------
    // Attacher les événements
    // ------------------------------------------------------------
    
    // Événements pour la barre de recherche principale
    searchInput.addEventListener('input', filterAssociationsTable);
    
    // Événements pour les recherches dans les modales
    searchMotoInput.addEventListener('input', function() {
        filterMotosList(this.value);
    });
    
    searchBatteryInput.addEventListener('input', function() {
        filterBatteriesList(this.value);
    });
    
    // Événements pour les boutons d'action
    addAssociationBtn.addEventListener('click', openAddAssociationModal);
    
    document.getElementById('save-association').addEventListener('click', saveAssociation);
    document.getElementById('confirm-delete-association').addEventListener('click', deleteAssociation);
    
    // Événements pour les actions du tableau
    document.querySelectorAll('.edit-association').forEach(btn => {
        btn.addEventListener('click', function() {
            openEditAssociationModal(this.closest('tr'));
        });
    });
    
    document.querySelectorAll('.delete-association').forEach(btn => {
        btn.addEventListener('click', function() {
            openDeleteAssociationModal(this.closest('tr'));
        });
    });
    
    document.querySelectorAll('.view-association').forEach(btn => {
        btn.addEventListener('click', function() {
            const macId = this.getAttribute('data-mac-id');
            openBatteryDetailsModal(macId);
        });
    });
    
    // Événements pour fermer les modales
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });
    
    // Fermer les modales en cliquant en dehors
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeAllModals();
            }
        });
    });
    
    // Événements pour les onglets
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Gestion de la navigation par onglets
            document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            if (tabName === 'moto-user') {
                window.location.href = '/associations'; // Rediriger vers la page des associations moto-utilisateur
            }
        });
    });
    
    // ------------------------------------------------------------
    // Initialisation
    // ------------------------------------------------------------
    
    // Charger les statuts de batterie pour les éléments du tableau
    loadAllBatteriesStatus();
    
    // Rafraîchir les statuts toutes les 30 secondes
    setInterval(loadAllBatteriesStatus, 30000);
});
</script>
@endsection