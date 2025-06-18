@extends('layouts.app')

@section('title', 'Gestion des Swaps')

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
    grid-template-columns: repeat(2, 1fr);
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

.swaps .stat-icon {
    color: var(--primary);
}

.amount .stat-icon {
    color: #28a745;
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

/* Graphiques */
.charts-container {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.chart-card {
    background-color: var(--background);
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.chart-container {
    position: relative;
    height: 300px; /* Hauteur fixe du graphique */
    width: 100%;
}

.chart-title {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 15px;
    text-align: center;
}

.chart-actions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
}

.chart-actions button {
    margin-left: 5px;
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    background-color: var(--tertiary);
    cursor: pointer;
    font-size: 12px;
}

.chart-actions button:hover {
    background-color: #e0e0e0;
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

/* Export buttons */
.export-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.export-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-excel {
    background-color: #1D6F42;
    color: white;
}

.btn-pdf {
    background-color: #F40F02;
    color: white;
}

.btn-csv {
    background-color: #ffcc00;
    color: #333;
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

/* Action buttons */
.action-btn {
    padding: 8px 16px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-add {
    background-color: var(--primary);
    color: var(--secondary);
}

.btn-add:hover {
    background-color: #c2c12c;
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

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    position: relative;
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    border-radius: 8px;
    width: 80%;
    max-width: 600px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: bold;
    margin: 0;
}

.close-modal {
    font-size: 28px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
}

.close-modal:hover {
    color: #555;
}
</style>

<!-- CSRF Token pour les requêtes AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="main-content">

<div class="nav-tabs">
    <div class="nav-tab {{ request()->is('swaps') ? 'active' : '' }}" data-url="{{ route('swaps.index') }}">
        Gestion des Swaps
    </div>
    <div class="nav-tab {{ request()->is('swaps-chauffeur*') ? 'active' : '' }}" data-url="{{ route('swaps.chauffeur.index') }}">
        Nombre de Swaps par Chauffeur    
    </div>
   
</div>
    <!-- En-tête -->
    <div class="content-header">
        <h2>{{ $pageTitle }}</h2>
        <div id="date" class="date"></div>
        <button id="addSwapBtn" class="action-btn btn-add">
            <i class="fas fa-plus mr-2"></i> Nouveau Swap
        </button>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card swaps">
            <div class="stat-icon">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-swaps">{{ $totalSwaps }}</div>
                <div class="stat-label">Nombre de Swaps</div>
                <div class="stat-text" id="swaps-time-label">aujourd'hui</div>
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

    <!-- Graphique combiné -->
     <!--
    <div class="charts-container">
        <div class="chart-card">
            <div class="chart-title">Évolution des swaps et montants</div>
            <div class="chart-actions">
                <button id="exportChartPNG">Exporter PNG</button>
                <button id="exportChartJPG">Exporter JPG</button>
                <button id="exportChartPDF">Exporter PDF</button>
            </div>
            <div class="chart-container">
                <canvas id="combinedChart"></canvas>
            </div>
        </div>
    </div>

        -->

    <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-swap" placeholder="Rechercher un swap...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-group">
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
                <input type="date" id="start-date" class="start-date">
                <input type="date" id="end-date" class="end-date">
            </div>
        </div>
    </div>

    <!-- Boutons d'export -->
    <div class="export-buttons">
        <button id="exportExcel" class="export-btn btn-excel">
            <i class="fas fa-file-excel mr-2"></i> Exporter Excel
        </button>
        <button id="exportPDF" class="export-btn btn-pdf">
            <i class="fas fa-file-pdf mr-2"></i> Exporter PDF
        </button>
        <button id="exportCSV" class="export-btn btn-csv">
            <i class="fas fa-file-csv mr-2"></i> Exporter CSV
        </button>
    </div>

    <!-- Zone de contenu des swaps -->
    <div id="swaps-content">
        <!-- Les swaps seront chargés ici dynamiquement -->
        <div class="loader-container">
            <div class="loader"></div>
        </div>
    </div>

    <!-- Tableau statique pour afficher les swaps (option de secours) -->
    <div id="static-swaps-table" style="margin-top: 30px; display: none;">
        <h3>Liste des swaps</h3>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Moto</th>
                        <th>Batterie Entrée</th>
                        <th>Batterie Sortie</th>
                        <th>Prix</th>
                        <th>Swappeur</th>
                        <th>Station</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($swaps as $swap)
                    <tr>
                        <td>{{ $swap->id }}</td>
                        <td>
                            @if($swap->batteryMotoUserAssociation && $swap->batteryMotoUserAssociation->association && $swap->batteryMotoUserAssociation->association->validatedUser)
                                {{ $swap->batteryMotoUserAssociation->association->validatedUser->nom ?? 'Non défini' }}
                                {{ $swap->batteryMotoUserAssociation->association->validatedUser->prenom ?? '' }}
                            @else
                                Non défini
                            @endif
                        </td>
                        <td>
                            @if($swap->batteryMotoUserAssociation && $swap->batteryMotoUserAssociation->association && $swap->batteryMotoUserAssociation->association->motosValide)
                                {{ $swap->batteryMotoUserAssociation->association->motosValide->moto_unique_id ?? 'Non défini' }}
                            @else
                                Non défini
                            @endif
                        </td>
                        <td>{{ $swap->batteryIn->mac_id ?? 'Non défini' }}</td>
                        <td>{{ $swap->batteryOut->mac_id ?? 'Non défini' }}</td>
                        <td>{{ number_format($swap->swap_price, 2) }} FCFA</td>
                        <td>
                            @if($swap->swappeur)
                                {{ $swap->swappeur->nom ?? 'Non défini' }} {{ $swap->swappeur->prenom ?? '' }}
                            @else
                                Non défini (ID: {{ $swap->agent_user_id }})
                            @endif
                        </td>
                        <td>
                            @if($swap->swappeur && $swap->swappeur->agence)
                                {{ $swap->swappeur->agence->nom_agence ?? 'Non défini' }}
                            @else
                                Non défini
                            @endif
                        </td>
                        <td>{{ $swap->swap_date }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button id="toggle-dynamic-view" class="btn btn-primary">Revenir à la vue dynamique</button>
    </div>
</div>

<!-- Modal pour ajouter un nouveau swap -->
<div id="swapModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Ajouter un Nouveau Swap</h5>
            <span class="close-modal">&times;</span>
        </div>
        <form action="{{ route('swaps.handle') }}" method="POST" id="swapForm">
            @csrf
            <div class="form-group mb-3">
                <label for="moto_unique_id">ID Unique Moto</label>
                <input type="text" name="moto_unique_id" class="form-control" required>
            </div>

            <div class="form-group mb-3">
                <label for="battery_out">Batterie Sortante de la Moto</label>
                <input type="text" name="battery_out" class="form-control" placeholder="ID Unique ou MAC ID pour la batterie sortante" required>
            </div>

            <div class="form-group mb-3">
                <label for="battery_in">Batterie Entrante dans la Moto</label>
                <input type="text" name="battery_in" class="form-control" placeholder="ID Unique ou MAC ID pour la batterie entrante" required>
            </div>

            <div class="form-group mb-3">
                <label for="swappeur_id">Swappeur</label>
                <select name="swappeur_id" class="form-control" required>
                    <option value="">Sélectionner un swappeur</option>
                    @foreach($swappeurs as $swappeur)
                    <option value="{{ $swappeur->id }}">{{ $swappeur->nom }} {{ $swappeur->prenom }} ({{ $swappeur->agence->nom_agence ?? 'Sans agence' }})</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Faire le Swap</button>
        </form>
    </div>
</div>

<!-- Scripts externes pour les exports -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.17.0/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation et variables
    const searchInput = document.getElementById('search-swap');
    const agenceFilter = document.getElementById('agence-filter');
    const swappeurFilter = document.getElementById('swappeur-filter');
    const timeFilter = document.getElementById('time-filter');
    const customDatePicker = document.getElementById('custom-date');
    const datePickerContainer = document.getElementById('date-picker-container');
    const startDatePicker = document.getElementById('start-date');
    const endDatePicker = document.getElementById('end-date');
    const dateRangeContainer = document.getElementById('date-range-container');
    const swapsContent = document.getElementById('swaps-content');
    const swapsTimeLabel = document.getElementById('swaps-time-label');
    const amountTimeLabel = document.getElementById('amount-time-label');
    const staticSwapsTable = document.getElementById('static-swaps-table');
    const toggleDynamicView = document.getElementById('toggle-dynamic-view');
    
    // Export buttons
    const exportExcelBtn = document.getElementById('exportExcel');
    const exportPDFBtn = document.getElementById('exportPDF');
    const exportCSVBtn = document.getElementById('exportCSV');
    const exportChartPNGBtn = document.getElementById('exportChartPNG');
    const exportChartJPGBtn = document.getElementById('exportChartJPG');
    const exportChartPDFBtn = document.getElementById('exportChartPDF');
    
    // Variables pour stocker les données actuelles
    let currentSwaps = [];
    let currentChartData = {
        labels: [],
        swapsCount: [],
        swapsAmount: []
    };
    
    // Gestion du basculement entre vue statique et dynamique
    let showStaticTable = false;
    
    if (toggleDynamicView) {
        toggleDynamicView.addEventListener('click', function() {
            showStaticTable = false;
            staticSwapsTable.style.display = 'none';
            swapsContent.style.display = 'block';
            filterSwaps(); // Recharger les données dynamiques
        });
    }
    
    // Ajouter un bouton pour basculer vers la vue statique en cas d'erreur
    function addStaticViewButton() {
        const button = document.createElement('button');
        button.className = 'btn btn-warning mt-3';
        button.textContent = 'Afficher les données en mode statique';
        button.addEventListener('click', function() {
            showStaticTable = true;
            swapsContent.style.display = 'none';
            staticSwapsTable.style.display = 'block';
        });
        swapsContent.appendChild(button);
    }
    
    // Gestion du modal
    const modal = document.getElementById('swapModal');
    const addSwapBtn = document.getElementById('addSwapBtn');
    const closeModal = document.querySelector('.close-modal');
    
    if (addSwapBtn) {
        addSwapBtn.addEventListener('click', function() {
            modal.style.display = 'block';
        });
    }
    
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Afficher la date actuelle dans l'en-tête
    const dateElement = document.getElementById('date');
    const today = new Date();
    if (dateElement) {
        dateElement.textContent = today.toLocaleDateString('fr-FR');
    }
    
    // Initialiser les dates des sélecteurs
    if (customDatePicker) customDatePicker.valueAsDate = today;
    if (startDatePicker) startDatePicker.valueAsDate = new Date(today.getFullYear(), today.getMonth(), 1); // Premier jour du mois en cours
    if (endDatePicker) endDatePicker.valueAsDate = today;

    // Gérer l'affichage des sélecteurs de date
    if (timeFilter) {
        timeFilter.addEventListener('change', function() {
            if (datePickerContainer) datePickerContainer.style.display = 'none';
            if (dateRangeContainer) dateRangeContainer.style.display = 'none';
            
            if (this.value === 'custom') {
                if (datePickerContainer) datePickerContainer.style.display = 'block';
            } else if (this.value === 'daterange') {
                if (dateRangeContainer) dateRangeContainer.style.display = 'block';
            }
        });
    }

    // Variable pour le graphique combiné
    let combinedChart;
    
    // Initialiser le graphique combiné
    function initCombinedChart(chartData) {
        const ctx = document.getElementById('combinedChart')?.getContext('2d');
        if (!ctx) return;
        
        // Stocker les données du graphique
        currentChartData = chartData;
        
        // Si le graphique existe déjà, le détruire
        if (combinedChart) {
            combinedChart.destroy();
        }
        
        combinedChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels || [],
                datasets: [
                    {
                        label: 'Nombre de Swaps',
                        data: chartData.swapsCount || [],
                        borderColor: '#DCDB32',
                        backgroundColor: 'rgba(220, 219, 50, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Montant (FCFA)',
                        data: chartData.swapsAmount || [],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Nombre de Swaps'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Montant (FCFA)'
                        }
                    }
                }
            }
        });
    }
    
    // Mettre à jour le graphique combiné
    function updateCombinedChart(chartData) {
        if (!combinedChart || !chartData || !chartData.labels) return;
        
        // Stocker les données du graphique
        currentChartData = chartData;
        
        combinedChart.data.labels = chartData.labels;
        combinedChart.data.datasets[0].data = chartData.swapsCount;
        combinedChart.data.datasets[1].data = chartData.swapsAmount;
        combinedChart.update();
    }

    // Fonctions pour calculer les statistiques
    function calculateStats() {
        if (showStaticTable) return;
        
        // Préparer les données
        const data = {
            timeFilter: timeFilter ? timeFilter.value : 'today',
            agence: agenceFilter ? agenceFilter.value : 'all',
            swappeur: swappeurFilter ? swappeurFilter.value : 'all'
        };
        
        // Ajouter les dates en fonction du filtre
        if (timeFilter && timeFilter.value === 'custom' && customDatePicker) {
            data.customDate = customDatePicker.value;
        } else if (timeFilter && timeFilter.value === 'daterange' && startDatePicker && endDatePicker) {
            data.startDate = startDatePicker.value;
            data.endDate = endDatePicker.value;
        }
        
        // Récupérer les données
        fetch('/api/swaps/stats', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('Status Stats:', response.status);
            if (!response.ok) {
                throw new Error('Erreur HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Données stats reçues:', data);
            
            // Mettre à jour les compteurs
            const totalSwapsElement = document.getElementById('total-swaps');
            const totalAmountElement = document.getElementById('total-amount');
            
            if (totalSwapsElement) totalSwapsElement.textContent = data.totalSwaps;
            if (totalAmountElement) totalAmountElement.textContent = data.totalAmount + ' FCFA';
            
            // Mettre à jour les libellés de période
            if (swapsTimeLabel) swapsTimeLabel.textContent = data.timeLabel;
            if (amountTimeLabel) amountTimeLabel.textContent = data.timeLabel;
            
            // Mettre à jour le graphique
            updateCombinedChart(data.chartData);
        })
        .catch(error => {
            console.error('Erreur lors du chargement des statistiques:', error);
            showToast('Erreur lors du chargement des statistiques: ' + error.message, 'error');
            addStaticViewButton();
        });
    }

    // Fonction pour filtrer et afficher les swaps
    function filterSwaps() {
        if (showStaticTable) return;
        
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const agenceValue = agenceFilter ? agenceFilter.value : 'all';
        const swappeurValue = swappeurFilter ? swappeurFilter.value : 'all';
        const timeValue = timeFilter ? timeFilter.value : 'today';
        
        // Préparer les données
        const data = {
            search: searchTerm,
            agence: agenceValue,
            swappeur: swappeurValue,
            time: timeValue
        };
        
        // Ajouter les dates en fonction du filtre
        if (timeValue === 'custom' && customDatePicker) {
            data.customDate = customDatePicker.value;
        } else if (timeValue === 'daterange' && startDatePicker && endDatePicker) {
            data.startDate = startDatePicker.value;
            data.endDate = endDatePicker.value;
        }
        
        // Afficher un indicateur de chargement
        if (swapsContent) {
            swapsContent.innerHTML = '<div class="loader-container"><div class="loader"></div></div>';
        }

        // Appel à l'API pour récupérer les données filtrées
        fetch('/api/swaps/filter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('Status Filter:', response.status);
            if (!response.ok) {
                throw new Error('Erreur HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Données filtre reçues:', data);
            
            // Vérifier la structure des données
            if (!data.swapsByDay) {
                console.error('Structure de données incorrecte:', data);
                throw new Error('Structure de données incorrecte');
            }
            
            // Stocker les données pour l'exportation
            storeDataForExport(data);
            
            // Vider la zone de contenu
            if (swapsContent) {
                swapsContent.innerHTML = '';
            }
            
            // Récupérer les données par jour
            const swapsByDay = data.swapsByDay;
            const days = Object.keys(swapsByDay).sort().reverse(); // Trier par date décroissante
            
            if (days.length === 0 && swapsContent) {
                swapsContent.innerHTML = '<div class="no-results">Aucun swap trouvé pour cette période.</div>';
                addStaticViewButton();
                return;
            }
            
            // Pour chaque jour, créer une section avec un tableau
            days.forEach(day => {
                const swaps = swapsByDay[day];
                if (swaps.length === 0) return;
                
                // Formater la date pour l'affichage
                const dateParts = day.split('-');
                const formattedDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
                
                // Créer l'en-tête de la date
                const dateHeader = document.createElement('div');
                dateHeader.className = 'date-header';
                dateHeader.textContent = formattedDate;
                if (swapsContent) swapsContent.appendChild(dateHeader);
                
                // Créer le tableau pour ce jour
                const tableContainer = document.createElement('div');
                tableContainer.className = 'table-container';
                
                const table = document.createElement('table');
                table.className = 'swaps-table'; // Ajouter une classe pour l'export
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>ID Utilisateur</th>
                            <th>Nom Utilisateur</th>
                            <th>ID Moto</th>
                            <th>Modèle Moto</th>
                            <th>Batterie Entrée</th>
                            <th>% Batterie Entrée</th>
                            <th>Batterie Sortie</th>
                            <th>% Batterie Sortie</th>
                            <th>Prix du Swap</th>
                            <th>Station</th>
                            <th>Swappeur</th>
                            <th>Date du Swap</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                `;
                
                const tbody = table.querySelector('tbody');
                
                // Ajouter les lignes de swaps
                swaps.forEach(swap => {
                    const row = document.createElement('tr');
                    row.dataset.id = swap.id;
                    
                    row.innerHTML = `
                        <td>${swap.user_id || 'Non défini'}</td>
                        <td>${swap.user_name || 'Non défini'}</td>
                        <td>${swap.moto_id || 'Non défini'}</td>
                        <td>${swap.moto_model || 'Non défini'}</td>
                        <td>${swap.battery_in || 'Non défini'}</td>
                        <td>${swap.battery_in_soc || '0%'}</td>
                        <td>${swap.battery_out || 'Non défini'}</td>
                        <td>${swap.battery_out_soc || '0%'}</td>
                        <td>${swap.swap_price || '0 FCFA'}</td>
                        <td>${swap.station || 'Non défini'}</td>
                        <td>${swap.swappeur_name || 'Non défini'}</td>
                        <td>${swap.swap_date || 'Non défini'}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                tableContainer.appendChild(table);
                if (swapsContent) swapsContent.appendChild(tableContainer);
            });
            
            // Mettre à jour les compteurs et libellés
            const totalSwapsElement = document.getElementById('total-swaps');
            const totalAmountElement = document.getElementById('total-amount');
            
            if (totalSwapsElement) totalSwapsElement.textContent = data.totalSwaps;
            if (totalAmountElement) totalAmountElement.textContent = data.totalAmount + ' FCFA';
            
            if (swapsTimeLabel) swapsTimeLabel.textContent = data.timeLabel;
            if (amountTimeLabel) amountTimeLabel.textContent = data.timeLabel;
            
            // Mettre à jour le graphique
            updateCombinedChart(data.chartData);
            
            // Si aucun contenu n'a été ajouté
            if (swapsContent && swapsContent.children.length === 0) {
                swapsContent.innerHTML = '<div class="no-results">Aucun swap trouvé pour les critères sélectionnés.</div>';
                addStaticViewButton();
            }
        })
        .catch(error => {
            console.error('Erreur lors du filtrage des données:', error);
            showToast('Erreur lors du filtrage des données: ' + error.message, 'error');
            if (swapsContent) {
                swapsContent.innerHTML = '<div class="no-results">Erreur lors du chargement des données. Veuillez réessayer.</div>';
                addStaticViewButton();
            }
        });
    }

    // Stocker les données pour l'exportation
    function storeDataForExport(data) {
        // Créer un tableau plat pour l'exportation
        currentSwaps = [];
        
        const swapsByDay = data.swapsByDay;
        const days = Object.keys(swapsByDay);
        
        days.forEach(day => {
            const swaps = swapsByDay[day];
            swaps.forEach(swap => {
                currentSwaps.push({
                    'ID Utilisateur': swap.user_id,
                    'Nom Utilisateur': swap.user_name,
                    'ID Moto': swap.moto_id,
                    'Modèle Moto': swap.moto_model,
                    'Batterie Entrée': swap.battery_in,
                    '% Batterie Entrée': swap.battery_in_soc,
                    'Batterie Sortie': swap.battery_out,
                    '% Batterie Sortie': swap.battery_out_soc,
                    'Prix du Swap': swap.swap_price,
                    'Station': swap.station,
                    'Swappeur': swap.swappeur_name,
                    'Date du Swap': swap.swap_date
                });
            });
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

    // Export functions
    // Export Excel
    function exportToExcel() {
        if (currentSwaps.length === 0) {
            showToast('Aucune donnée à exporter', 'warning');
            return;
        }
        
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.json_to_sheet(currentSwaps);
        
        // Ajouter une feuille de données
        XLSX.utils.book_append_sheet(wb, ws, 'Swaps');
        
        // Générer le fichier Excel
        const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
        const fileName = `swaps_export_${timeStr}.xlsx`;
        XLSX.writeFile(wb, fileName);
        
        showToast('Export Excel réussi', 'success');
    }
    
    // Export CSV
    function exportToCSV() {
        if (currentSwaps.length === 0) {
            showToast('Aucune donnée à exporter', 'warning');
            return;
        }
        
        const ws = XLSX.utils.json_to_sheet(currentSwaps);
        const csv = XLSX.utils.sheet_to_csv(ws);
        
        // Créer un Blob pour le téléchargement
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
        const fileName = `swaps_export_${timeStr}.csv`;
        
        // Télécharger le fichier
        saveAs(blob, fileName);
        
        showToast('Export CSV réussi', 'success');
    }
    
    // Export PDF
    function exportToPDF() {
        if (currentSwaps.length === 0) {
            showToast('Aucune donnée à exporter', 'warning');
            return;
        }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4'); // Landscape
        
        // Ajouter un titre
        doc.setFontSize(18);
        doc.text('Rapport des Swaps', 14, 22);
        
        // Ajouter la date
        doc.setFontSize(10);
        doc.text(`Généré le ${new Date().toLocaleDateString('fr-FR')}`, 14, 30);
        
        // Transformez les données pour le tableau
        const tableData = currentSwaps.map(swap => [
            swap['ID Utilisateur'],
            swap['Nom Utilisateur'],
            swap['ID Moto'],
            swap['Modèle Moto'],
            swap['Batterie Entrée'],
            swap['% Batterie Entrée'],
            swap['Batterie Sortie'],
            swap['% Batterie Sortie'],
            swap['Prix du Swap'],
            swap['Station'],
            swap['Swappeur'],
            swap['Date du Swap']
        ]);
        
        // Définir les en-têtes de colonne
        const headers = [
            'ID Utilisateur',
            'Nom Utilisateur',
            'ID Moto',
            'Modèle',
            'Batterie In',
            '% In',
            'Batterie Out',
            '% Out',
            'Prix',
            'Station',
            'Swappeur',
            'Date'
        ];
        
        // Ajouter le tableau au PDF
        doc.autoTable({
            startY: 35,
            head: [headers],
            body: tableData,
            headStyles: {
                fillColor: [220, 219, 50],
                textColor: [16, 16, 16],
                fontStyle: 'bold'
            },
            styles: {
                fontSize: 8
            },
            didDrawPage: function(data) {
                // Ajouter un pied de page avec la numérotation
                doc.setFontSize(8);
                doc.text(
                    `Page ${doc.internal.getCurrentPageInfo().pageNumber} / ${doc.internal.getNumberOfPages()}`,
                    data.settings.margin.left,
                    doc.internal.pageSize.height - 10
                );
            }
        });
        
        // Générer et télécharger le PDF
        const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
        const fileName = `swaps_export_${timeStr}.pdf`;
        doc.save(fileName);
        
        showToast('Export PDF réussi', 'success');
    }
    
    // Export chart functions
    function exportChartAsPNG() {
        if (!combinedChart) {
            showToast('Aucun graphique à exporter', 'warning');
            return;
        }
        
        const canvas = document.getElementById('combinedChart');
        const link = document.createElement('a');
        const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
        
        link.download = `swaps_chart_${timeStr}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
        
        showToast('Export PNG réussi', 'success');
    }
    
    function exportChartAsJPG() {
        if (!combinedChart) {
            showToast('Aucun graphique à exporter', 'warning');
            return;
        }
        
        const canvas = document.getElementById('combinedChart');
        const link = document.createElement('a');
        const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
        
        link.download = `swaps_chart_${timeStr}.jpg`;
        link.href = canvas.toDataURL('image/jpeg');
        link.click();
        
        showToast('Export JPG réussi', 'success');
    }
    
    function exportChartAsPDF() {
        if (!combinedChart) {
            showToast('Aucun graphique à exporter', 'warning');
            return;
        }
        
        const canvas = document.getElementById('combinedChart');
        const imageData = canvas.toDataURL('image/png');
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4'); // Landscape
        
        // Ajouter un titre
        doc.setFontSize(18);
        doc.text('Graphique des Swaps', 14, 22);
        
        // Ajouter la date
        doc.setFontSize(10);
        doc.text(`Généré le ${new Date().toLocaleDateString('fr-FR')}`, 14, 30);
        
        // Ajouter l'image
        const imgWidth = 280; // Largeur de l'image en mm
        const imgHeight = canvas.height * imgWidth / canvas.width; // Hauteur proportionnelle
        
        doc.addImage(imageData, 'PNG', 14, 40, imgWidth, imgHeight);
        
        // Générer et télécharger le PDF
        const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
        const fileName = `swaps_chart_${timeStr}.pdf`;
        doc.save(fileName);
        
        showToast('Export PDF réussi', 'success');
    }

    // Événements pour les filtres et la recherche
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterSwaps, 500));
    }
    
    if (agenceFilter) {
        agenceFilter.addEventListener('change', filterSwaps);
    }
    
    if (swappeurFilter) {
        swappeurFilter.addEventListener('change', filterSwaps);
    }
    
    if (timeFilter) {
        timeFilter.addEventListener('change', function() {
            if (datePickerContainer) datePickerContainer.style.display = 'none';
            if (dateRangeContainer) dateRangeContainer.style.display = 'none';
            
            if (this.value === 'custom' && datePickerContainer) {
                datePickerContainer.style.display = 'block';
            } else if (this.value === 'daterange' && dateRangeContainer) {
                dateRangeContainer.style.display = 'block';
            }
            
            filterSwaps();
        });
    }
    
    // Événements pour les sélecteurs de date
    if (customDatePicker) {
        customDatePicker.addEventListener('change', function() {
            if (timeFilter && timeFilter.value === 'custom') {
                filterSwaps();
            }
        });
    }
    
    if (startDatePicker) {
        startDatePicker.addEventListener('change', function() {
            if (timeFilter && timeFilter.value === 'daterange') {
                filterSwaps();
            }
        });
    }
    
    if (endDatePicker) {
        endDatePicker.addEventListener('change', function() {
            if (timeFilter && timeFilter.value === 'daterange') {
                filterSwaps();
            }
        });
    }
    
    // Événements pour les boutons d'exportation
    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', exportToExcel);
    }
    
    if (exportPDFBtn) {
        exportPDFBtn.addEventListener('click', exportToPDF);
    }
    
    if (exportCSVBtn) {
        exportCSVBtn.addEventListener('click', exportToCSV);
    }
    
    if (exportChartPNGBtn) {
        exportChartPNGBtn.addEventListener('click', exportChartAsPNG);
    }
    
    if (exportChartJPGBtn) {
        exportChartJPGBtn.addEventListener('click', exportChartAsJPG);
    }
    
    if (exportChartPDFBtn) {
        exportChartPDFBtn.addEventListener('click', exportChartAsPDF);
    }

    // Afficher les messages de session Laravel
    @if(session('success'))
    showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
    showToast("{{ session('error') }}", 'error');
    @endif

    // Initialiser le graphique avec les données initiales
    try {
        const chartData = @json($chartData ?? ['labels' => [], 'swapsCount' => [], 'swapsAmount' => []]);
        initCombinedChart(chartData);
    } catch (error) {
        console.error('Erreur lors de l\'initialisation des graphiques:', error);
        showToast('Erreur lors de l\'initialisation des graphiques', 'error');
    }
    
    // Par défaut, filtrer pour afficher tous les swaps
    filterSwaps();
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