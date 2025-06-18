@extends('layouts.app')


<style>
/* ===============================
   Styles hérités de la vue batteries principale
   =============================== */
.main-content {
    padding: 20px;
    background-color: #f8f9fa;
    min-height: 100vh;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.content-header h2 {
    color: #2c3e50;
    font-weight: 600;
    margin: 0;
}

.date {
    color: #6c757d;
    font-size: 14px;
}



/* Styles pour les tabs  des navbar*/
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
    text-decoration: none;
    font-size: 1.5rem;
    color: var(--secondary);
}

.nav-tab:hover {
    background-color: var(--tertiary);
}

.nav-tab.active {
    border-bottom-color: var(--primary);
    font-weight: bold;
    color: var(--primary);
}

/* ===============================
   Barre de recherche et filtres modernisée
   =============================== */
.search-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.search-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 300px;
}

.search-group input {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.search-group input:focus {
    outline: none;
    border-color: #DCDB32;
    box-shadow: 0 0 0 3px rgba(220, 219, 50, 0.1);
}

.search-btn {
    padding: 12px 15px;
    background-color: #DCDB32;
    color: #101010;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-btn:hover {
    background-color: #c7c62d;
    transform: translateY(-1px);
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.select-status {
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    background-color: white;
    min-width: 200px;
    transition: border-color 0.2s ease;
}

.select-status:focus {
    outline: none;
    border-color: #DCDB32;
    box-shadow: 0 0 0 3px rgba(220, 219, 50, 0.1);
}

.add-btn {
    padding: 12px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.add-btn:hover {
    background-color: #218838;
    transform: translateY(-1px);
}

/* ===============================
   Cartes de statistiques 
   =============================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border-left: 4px solid;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-card.total {
    border-left-color: #3498db;
}

.stat-card.pending {
    border-left-color: #ffc107;
}

.stat-card.success {
    border-left-color: #28a745;
}

.stat-card.danger {
    border-left-color: #dc3545;
}

.stat-card.agency {
    border-left-color: #2ecc71;
}

.stat-card.charged {
    border-left-color: #27ae60;
}

.stat-card.discharged {
    border-left-color: #e74c3c;
}

.stat-card.medium {
    border-left-color: #17a2b8;
}

.stat-card .stat-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 24px;
    opacity: 0.3;
}

.stat-card.total .stat-icon {
    color: #3498db;
}

.stat-card.pending .stat-icon {
    color: #ffc107;
}

.stat-card.success .stat-icon {
    color: #28a745;
}

.stat-card.danger .stat-icon {
    color: #dc3545;
}

.stat-card.agency .stat-icon {
    color: #2ecc71;
}

.stat-card.charged .stat-icon {
    color: #27ae60;
}

.stat-card.discharged .stat-icon {
    color: #e74c3c;
}

.stat-card.medium .stat-icon {
    color: #17a2b8;
}

.stat-details {
    position: relative;
    z-index: 2;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
    line-height: 1;
}

.stat-label {
    font-size: 16px;
    font-weight: 600;
    color: #34495e;
    margin-bottom: 5px;
}

.stat-text {
    font-size: 12px;
    color: #7f8c8d;
}

/* ===============================
   Tableau modernisé
   =============================== */
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.head-table {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    background-color: #f8f9fa;
}

.head-table h2 {
    color: #2c3e50;
    font-weight: 600;
    margin: 0;
}

.head-table a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    background-color: #DCDB32;
    color: #101010;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s ease;
    margin-left: 10px;
}

.head-table a:hover {
    background-color: #c7c62d;
    transform: translateY(-1px);
}

#batteries-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin: 0;
}

#batteries-table thead th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 18px 15px;
    border: none;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#batteries-table tbody td {
    padding: 15px;
    border-top: 1px solid #e9ecef;
    vertical-align: middle;
    font-size: 14px;
}

#batteries-table tbody tr:hover {
    background-color: #f8f9fa;
  
}
#batteries-table th,
#batteries-table td {
    text-align: center;
    vertical-align: middle;
}

/* ===============================
   Badges et éléments visuels
   =============================== */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.bg-primary {
    background-color: #007bff !important;
    color: white;
}

.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
}

.bg-info {
    background-color: #17a2b8 !important;
    color: white;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: 600;
}

.status-badge.en_attente {
    background-color: #ffc107;
    color: #212529;
}

.status-badge.validé {
    background-color: #28a745;
    color: white;
}

.status-badge.rejeté {
    background-color: #dc3545;
    color: white;
}

/* ===============================
   Boutons d'action
   =============================== */
.action-btn {
    padding: 8px 10px;
    margin-right: 5px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.action-btn.edit-batterie {
    background-color: #17a2b8;
    color: white;
}

.action-btn.edit-batterie:hover {
    background-color: #138496;
}

.action-btn.validate-batterie {
    background-color: #28a745;
    color: white;
}

.action-btn.validate-batterie:hover {
    background-color: #218838;
}

.action-btn.delete-batterie {
    background-color: #dc3545;
    color: white;
}

.action-btn.delete-batterie:hover {
    background-color: #c82333;
}

.action-btn.bms {
    background-color: #6f42c1;
    color: white;
}

.action-btn.bms:hover {
    background-color: #5a32a3;
}

/* ===============================
   Barre de progression pour SOC
   =============================== */
.battery-level {
    width: 60px;
    height: 10px;
    background-color: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
    display: inline-block;
    margin-right: 10px;
    vertical-align: middle;
}

.battery-fill {
    height: 100%;
    background: linear-gradient(90deg, #dc3545 0%, #ffc107 30%, #28a745 70%);
    transition: width 0.3s ease;
}

/* ===============================
   Messages toast
   =============================== */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background-color: #DCDB32;
    color: #101010;
    padding: 12px 20px;
    border-radius: 8px;
    margin-top: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: opacity 0.3s ease;
}

.toast.toast-error {
    background-color: #dc3545;
    color: white;
}

.toast.toast-success {
    background-color: #28a745;
    color: white;
}

/* ===============================
   Responsive
   =============================== */
@media (max-width: 768px) {
    .search-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-group {
        min-width: auto;
    }
    
    .filter-group {
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .head-table {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .head-table a {
        margin-left: 0;
        justify-content: center;
    }
}

@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>


@section('content')

<div class="main-content">

 <!-- Onglets de navigation -->
    <!-- Onglets de navigation -->
<div class="nav-tabs">
    <div class="nav-tab {{ request()->is('batteries') ? 'active' : '' }}" data-url="{{ route('batteries.index') }}">
        Batteries Validées
    </div>
    <div class="nav-tab {{ request()->is('batteries/station*') ? 'active' : '' }}" data-url="{{ route('batteries.station.index') }}">
        Gestion Batteries Stations
    </div>
   
</div>

    <!-- En-tête -->
    <div class="content-header">
        <h1>Gestion des Batteries de Station</h1>
        <div class="date" id="date"></div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-batteries">{{ $totalBatteries }}</div>
                <div class="stat-label">Batteries Totales</div>
                <div class="stat-text">Toutes les batteries enregistrées</div>
            </div>
        </div>

        <div class="stat-card agency">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="agency-batteries">{{ $batteriesEnAgence }}</div>
                <div class="stat-label">En Agence</div>
                <div class="stat-text">Batteries affectées à une agence</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="charging-batteries">{{ $batteriesEnCharge }}</div>
                <div class="stat-label">En Charge</div>
                <div class="stat-text">Batteries en cours de charge</div>
            </div>
        </div>

        <div class="stat-card charged">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="charged-batteries">{{ $batteriesChargees }}</div>
                <div class="stat-label">Chargées > 95%</div>
                <div class="stat-text">Prêtes à l'utilisation</div>
            </div>
        </div>

        <div class="stat-card discharged">
            <div class="stat-icon">
                <i class="fas fa-battery-empty"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="discharged-batteries">{{ $batteriesDechargees }}</div>
                <div class="stat-label">Déchargées < 30%</div>
                <div class="stat-text">Nécessitent une recharge</div>
            </div>
        </div>

        <div class="stat-card medium">
            <div class="stat-icon">
                <i class="fas fa-battery-half"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="medium-batteries">{{ $batteriesEntre50Et80 }}</div>
                <div class="stat-label">SOC 50-80%</div>
                <div class="stat-text">Charge intermédiaire</div>
            </div>
        </div>
    </div>

     <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-batterie" placeholder="Rechercher MAC ID / ID batterie..." value="{{ $search }}">
            <button type="button" class="search-btn" id="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <div class="filter-group">
            <select id="agence-filter" class="select-status">
                <option value="">-- Toutes les agences --</option>
                @foreach ($agences as $agence)
                    <option value="{{ $agence->id }}" {{ $selectedAgence == $agence->id ? 'selected' : '' }}>
                        {{ $agence->nom_agence }}
                    </option>
                @endforeach
            </select>
            
            <select id="location-filter" class="select-status">
                <option value="">-- Toutes les batteries --</option>
                <option value="1" {{ $enAgence === "1" ? 'selected' : '' }}>En agence</option>
                <option value="0" {{ $enAgence === "0" ? 'selected' : '' }}>Hors agence</option>
            </select>
            
            <select id="status-filter" class="select-status">
                <option value="">-- Filtrer par état --</option>
                <option value="en_charge" {{ $filtre == 'en_charge' ? 'selected' : '' }}>En charge</option>
                <option value="decharge_faible" {{ $filtre == 'decharge_faible' ? 'selected' : '' }}>Décharge faible (&lt; 30%)</option>
                <option value="en_veille" {{ $filtre == 'en_veille' ? 'selected' : '' }}>En veille</option>
                <option value="en_ligne" {{ $filtre == 'en_ligne' ? 'selected' : '' }}>En ligne</option>
                <option value="hors_ligne" {{ $filtre == 'hors_ligne' ? 'selected' : '' }}>Hors ligne</option>
            </select>
            
            <button id="reset-filters" class="add-btn" style="background-color: #6c757d;">
                <i class="fas fa-undo"></i> Réinitialiser
            </button>
        </div>
    </div>

    <!-- Tableau des batteries -->
    <div class="table-container">
        <div class="head-table">
            <h2>Liste des Batteries de Station</h2>
            <div>
                <a href="{{ route('bms.index') }}">
                    <i class="fas fa-chart-line"></i>
                    Voir les Details du BMS
                </a>
                <a href="{{ route('batteries.map') }}">
                    <i class="fas fa-map-marker-alt"></i>
                    Voir sur la carte
                </a>
            </div>
        </div>
        
        <table id="batteries-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Identifiant</th>
                    <th>MAC ID</th>
                    <th>Fabriquant</th>
                    <th>SOC (%)</th>
                    <th>Statut</th>
                    <th>Connexion</th>
                    <th>Agence</th>
                </tr>
            </thead>
            <tbody id="batteries-table-body">
                @forelse ($batteries as $index => $battery)
                    <tr data-id="{{ $battery['id'] }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $battery['batterie_unique_id'] }}</strong>
                        </td>
                        <td>
                            <code>{{ $battery['mac_id'] }}</code>
                        </td>
                        <td>{{ $battery['fabriquant'] }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="battery-level">
                                    <div class="battery-fill" style="width: {{ $battery['soc'] }}%;"></div>
                                </div>
                                <span style="font-weight: 600; color: 
                                    @if($battery['soc'] > 75) #28a745
                                    @elseif($battery['soc'] > 50) #856404
                                    @elseif($battery['soc'] > 25) #bd2130
                                    @else #dc3545
                                    @endif;">{{ $battery['soc'] }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge @if($battery['status'] == 'En charge') bg-primary
                                @elseif($battery['status'] == 'En décharge') bg-warning
                                @elseif($battery['status'] == 'En veille') bg-info
                                @else bg-danger
                                @endif">
                                {{ $battery['status'] }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $battery['online_status'] === 'En ligne' ? 'bg-success' : 'bg-danger' }}">
                                <i class="fas {{ $battery['online_status'] === 'En ligne' ? 'fa-wifi' : 'fa-times' }}"></i>
                                {{ $battery['online_status'] }}
                            </span>
                        </td>
                        <td>
                            @if($battery['agence'] !== 'Non affectée')
                                <i class="fas fa-building text-success"></i>
                                {{ $battery['agence'] }}
                            @else
                                <span class="text-muted">
                                    <i class="fas fa-minus-circle"></i>
                                    Non affectée
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 40px;">
                            <i class="fas fa-battery-empty text-muted" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <p class="text-muted mb-0">Aucune batterie trouvée avec les critères sélectionnés.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Affichage de la date
    document.getElementById('date').textContent = new Date().toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });

    // Références DOM
    const searchInput = document.getElementById('search-batterie');
    const agenceFilter = document.getElementById('agence-filter');
    const locationFilter = document.getElementById('location-filter');
    const statusFilter = document.getElementById('status-filter');
    const resetButton = document.getElementById('reset-filters');
    const searchButton = document.getElementById('search-btn');
    const batteriesTableBody = document.getElementById('batteries-table-body');

    // Fonction pour construire l'URL avec les paramètres
    function buildFilterURL() {
        const params = new URLSearchParams();
        
        if (searchInput.value.trim()) {
            params.append('search', searchInput.value.trim());
        }
        
        if (agenceFilter.value) {
            params.append('agence_id', agenceFilter.value);
        }
        
        if (locationFilter.value) {
            params.append('en_agence', locationFilter.value);
        }
        
        if (statusFilter.value) {
            params.append('filtre', statusFilter.value);
        }

        return `{{ route('batteries.station.index') }}${params.toString() ? '?' + params.toString() : ''}`;
    }

    // Fonction pour appliquer les filtres
    function applyFilters() {
        window.location.href = buildFilterURL();
    }

    // Fonction pour charger les batteries via AJAX (optionnel pour une mise à jour dynamique)
    function loadBatteriesAjax() {
        const url = buildFilterURL();
        
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.json();
        })
        .then(data => {
            renderBatteries(data.batteries);
            updateStats(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement des batteries', 'error');
        });
    }

    // Afficher les batteries dans le tableau (pour AJAX)
    function renderBatteries(batteries) {
        batteriesTableBody.innerHTML = '';
        
        if (batteries.length === 0) {
            batteriesTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center" style="padding: 40px;">
                        <i class="fas fa-battery-empty text-muted" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <p class="text-muted mb-0">Aucune batterie trouvée avec les critères sélectionnés.</p>
                    </td>
                </tr>
            `;
            return;
        }

        batteries.forEach((battery, index) => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', battery.id);
            
            let socColor = '#dc3545';
            if (battery.soc > 75) socColor = '#28a745';
            else if (battery.soc > 50) socColor = '#856404';
            else if (battery.soc > 25) socColor = '#bd2130';

            let statusClass = 'bg-danger';
            if (battery.status === 'En charge') statusClass = 'bg-primary';
            else if (battery.status === 'En décharge') statusClass = 'bg-warning';
            else if (battery.status === 'En veille') statusClass = 'bg-info';

            row.innerHTML = `
                <td>${index + 1}</td>
                <td><strong>${battery.batterie_unique_id}</strong></td>
                <td><code>${battery.mac_id}</code></td>
                <td>${battery.fabriquant}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="battery-level">
                            <div class="battery-fill" style="width: ${battery.soc}%;"></div>
                        </div>
                        <span style="font-weight: 600; color: ${socColor};">${battery.soc}%</span>
                    </div>
                </td>
                <td>
                    <span class="badge ${statusClass}">
                        ${battery.status}
                    </span>
                </td>
                <td>
                    <span class="badge ${battery.online_status === 'En ligne' ? 'bg-success' : 'bg-danger'}">
                        <i class="fas ${battery.online_status === 'En ligne' ? 'fa-wifi' : 'fa-times'}"></i>
                        ${battery.online_status}
                    </span>
                </td>
                <td>
                    ${battery.agence !== 'Non affectée' ? 
                        `<i class="fas fa-building text-success"></i> ${battery.agence}` : 
                        `<span class="text-muted"><i class="fas fa-minus-circle"></i> Non affectée</span>`
                    }
                </td>
            `;
            
            batteriesTableBody.appendChild(row);
        });
    }

    // Mise à jour des statistiques (pour AJAX)
    function updateStats(data) {
        document.getElementById('total-batteries').textContent = data.totalBatteries || 0;
        document.getElementById('agency-batteries').textContent = data.batteriesEnAgence || 0;
        document.getElementById('charging-batteries').textContent = data.batteriesEnCharge || 0;
        document.getElementById('charged-batteries').textContent = data.batteriesChargees || 0;
        document.getElementById('discharged-batteries').textContent = data.batteriesDechargees || 0;
        document.getElementById('medium-batteries').textContent = data.batteriesEntre50Et80 || 0;
    }

    // Fonction pour afficher un toast
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

    // Événements pour les filtres
    searchButton.addEventListener('click', applyFilters);
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });

    agenceFilter.addEventListener('change', applyFilters);
    locationFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    // Bouton de réinitialisation
    resetButton.addEventListener('click', function() {
        window.location.href = '{{ route('batteries.station.index') }}';
    });

    // Auto-filtrage en temps réel (optionnel - commenté par défaut)
    /*
    searchInput.addEventListener('input', function() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            loadBatteriesAjax();
        }, 500);
    });
    */
});


  // Navigation par onglets
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });
    });
</script>

@endsection