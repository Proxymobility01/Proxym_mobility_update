@extends('layouts.app')

@section('title', 'Suivi des distances quotidiennes')

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

.distance .stat-icon {
    color: var(--primary);
}

.drivers .stat-icon {
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

.date-picker-container, .date-range-container, .time-range-container {
    display: none;
    margin-left: 10px;
    margin-bottom: 10px;
}

/* Table */
.table-container {
    overflow-x: auto;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
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

/* Button for recalculating */
.btn-recalculate {
    background-color: var(--primary);
    color: var(--secondary);
    margin-left: auto;
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

/* Badge styles */
.badge {
    display: inline-block;
    padding: 0.25em 0.6em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
}

.bg-success {
    background-color: #28a745;
    color: white;
}

.bg-warning {
    background-color: #ffc107;
    color: black;
}

.bg-danger {
    background-color: #dc3545;
    color: white;
}
</style>

<!-- CSRF Token pour les requêtes AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>Suivi des distances quotidiennes</h2>
        <div id="current-date" class="date">{{ now()->format('d/m/Y') }}</div>
        <button id="recalculateBtn" class="export-btn btn-recalculate">
            <i class="fas fa-sync-alt mr-2"></i> Recalculer les distances
        </button>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card distance">
            <div class="stat-icon">
                <i class="fas fa-road"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-distance">{{ number_format($stats['total_distance'] ?? 0, 2) }} KM</div>
                <div class="stat-label">Distance Totale</div>
                <div class="stat-text" id="distance-time-label">{{ $displayDate ?? "Aujourd'hui" }}</div>
            </div>
        </div>

        <div class="stat-card drivers">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-drivers">{{ $stats['total_drivers'] ?? 0 }}</div>
                <div class="stat-label">Chauffeurs Actifs</div>
                <div class="stat-text" id="drivers-time-label">{{ $displayDate ?? "Aujourd'hui" }}</div>
            </div>
        </div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-driver" placeholder="Rechercher un chauffeur...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-group">
            <select id="driver-filter">
                <option value="all">Tous les chauffeurs</option>
                @foreach($drivers ?? [] as $driver)
                <option value="{{ $driver->id }}">{{ $driver->prenom ?? '' }} {{ $driver->nom ?? $driver->id }}</option>
                @endforeach
            </select>
            
            <select id="time-filter">
                <option value="today" {{ ($timeFilter ?? 'today') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                <option value="week" {{ ($timeFilter ?? '') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                <option value="month" {{ ($timeFilter ?? '') == 'month' ? 'selected' : '' }}>Ce mois</option>
                <option value="year" {{ ($timeFilter ?? '') == 'year' ? 'selected' : '' }}>Cette année</option>
                <option value="custom" {{ ($timeFilter ?? '') == 'custom' ? 'selected' : '' }}>Date spécifique</option>
                <option value="daterange" {{ ($timeFilter ?? '') == 'daterange' ? 'selected' : '' }}>Plage de dates</option>
                <option value="all" {{ ($timeFilter ?? '') == 'all' ? 'selected' : '' }}>Tout l'historique</option>
            </select>
            
            <div id="date-picker-container" class="date-picker-container" style="{{ ($timeFilter ?? '') == 'custom' ? 'display: block;' : 'display: none;' }}">
                <input type="date" id="custom-date" value="{{ $customDate ?? now()->toDateString() }}">
            </div>
            
            <div id="date-range-container" class="date-range-container" style="{{ ($timeFilter ?? '') == 'daterange' ? 'display: block;' : 'display: none;' }}">
                <input type="date" id="start-date" value="{{ now()->subDays(7)->toDateString() }}">
                <input type="date" id="end-date" value="{{ now()->toDateString() }}">
            </div>
            
   
<a href="{{ route('distances.recalculer') }}" class="btn btn-warning" onclick="return confirm('⚠️ Êtes-vous sûr de vouloir recalculer les distances sur la plage définie ?')">
    🔁 Recalculer les distances (1er → 23 mai)
</a>


            
            <button id="show-time-filter" class="export-btn" style="background-color: var(--tertiary); color: var(--secondary); padding: 6px 12px; margin-left: 10px;">
                <i class="fas fa-clock mr-2"></i> Filtre horaire
            </button>
            
            <div id="time-range-container" class="time-range-container">
                <input type="time" id="start-time" value="{{ $startTime ?? '00:00' }}">
                <input type="time" id="end-time" value="{{ $endTime ?? '23:59' }}">
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

    <!-- Zone de contenu des distances -->
    <div id="distances-content">
        @if($distances->isEmpty())
            <div class="no-results">Aucune donnée disponible pour cette période.</div>
        @else
            <div class="table-container">
                <table class="distances-table">
                    <thead>
                        <tr>
                            <th>ID Chauffeur</th>
                            <th>Nom du Chauffeur</th>
                            <th>Téléphone</th>
                            <th>Distance (KM)</th>
                            <th>Dernière Position</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($distances as $distance)
                            <tr>
                                <td>{{ $distance->user->id }}</td>
                                <td>{{ $distance->user->prenom ?? '' }} {{ $distance->user->nom ?? 'N/A' }}</td>
                                <td>{{ $distance->user->phone ?? 'N/A' }}</td>
                                <td>{{ number_format($distance->total_distance_km, 2) }}</td>
                                <td>{{ $distance->last_location ?? 'N/A' }}</td>
                                <td>{{ $distance->updated_at->format('Y-m-d') }}</td>
                                <td>{{ $distance->updated_at->format('H:i:s') }}</td>
                                <td>
                                    @if($distance->total_distance_km > 50)
                                        <span class="badge bg-success">Actif</span>
                                    @elseif($distance->total_distance_km > 10)
                                        <span class="badge bg-warning">Modéré</span>
                                    @else
                                        <span class="badge bg-danger">Faible activité</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Scripts externes pour les exports -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.17.0/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation et variables
    const searchInput = document.getElementById('search-driver');
    const driverFilter = document.getElementById('driver-filter');
    const timeFilter = document.getElementById('time-filter');
    const distanceFilter = document.getElementById('distance-filter');
    const customDatePicker = document.getElementById('custom-date');
    const datePickerContainer = document.getElementById('date-picker-container');
    const startDatePicker = document.getElementById('start-date');
    const endDatePicker = document.getElementById('end-date');
    const dateRangeContainer = document.getElementById('date-range-container');
    const showTimeFilterBtn = document.getElementById('show-time-filter');
    const timeRangeContainer = document.getElementById('time-range-container');
    const startTimePicker = document.getElementById('start-time');
    const endTimePicker = document.getElementById('end-time');
    const distancesContent = document.getElementById('distances-content');
    const recalculateBtn = document.getElementById('recalculateBtn');
    
    // Export buttons
    const exportExcelBtn = document.getElementById('exportExcel');
    const exportPDFBtn = document.getElementById('exportPDF');
    const exportCSVBtn = document.getElementById('exportCSV');
    
    // Variables pour stocker les données actuelles
    let currentDistances = [];
    
    // Afficher/masquer le sélecteur d'heure
    if (showTimeFilterBtn) {
        showTimeFilterBtn.addEventListener('click', function() {
            if (timeRangeContainer.style.display === 'none' || timeRangeContainer.style.display === '') {
                timeRangeContainer.style.display = 'block';
                this.innerHTML = '<i class="fas fa-clock mr-2"></i> Masquer filtre horaire';
            } else {
                timeRangeContainer.style.display = 'none';
                this.innerHTML = '<i class="fas fa-clock mr-2"></i> Filtre horaire';
            }
        });
    }
    
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
            
            filterDistances();
        });
    }
    
    // Initialiser les dates si nécessaire
    const today = new Date();
    if (customDatePicker && !customDatePicker.value) customDatePicker.valueAsDate = today;
    if (startDatePicker && !startDatePicker.value) startDatePicker.valueAsDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 7);
    if (endDatePicker && !endDatePicker.value) endDatePicker.valueAsDate = today;
    
    // Recalculer les distances
    if (recalculateBtn) {
        recalculateBtn.addEventListener('click', function() {
            calculateDistances();
        });
    }
    
    // Événements pour les filtres
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterDistances, 500));
    }
    
    if (driverFilter) {
        driverFilter.addEventListener('change', filterDistances);
    }
    
    if (distanceFilter) {
        distanceFilter.addEventListener('change', filterDistances);
    }
    
    if (customDatePicker) {
        customDatePicker.addEventListener('change', filterDistances);
    }
    
    if (startDatePicker) {
        startDatePicker.addEventListener('change', filterDistances);
    }
    
    if (endDatePicker) {
        endDatePicker.addEventListener('change', filterDistances);
    }
    
    if (startTimePicker) {
        startTimePicker.addEventListener('change', filterDistances);
    }
    
    if (endTimePicker) {
        endTimePicker.addEventListener('change', filterDistances);
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
    
    // Fonction pour filtrer les distances
    function filterDistances() {
        // Afficher un indicateur de chargement
        if (distancesContent) {
            distancesContent.innerHTML = '<div class="loader-container"><div class="loader"></div></div>';
        }
        
        // Récupérer les valeurs des filtres
        const searchTerm = searchInput ? searchInput.value : '';
        const driver = driverFilter ? driverFilter.value : 'all';
        const time = timeFilter ? timeFilter.value : 'today';
        const distance = distanceFilter ? distanceFilter.value : 'all';
        const customDate = customDatePicker ? customDatePicker.value : '';
        const startDate = startDatePicker ? startDatePicker.value : '';
        const endDate = endDatePicker ? endDatePicker.value : '';
        const startTime = startTimePicker ? startTimePicker.value : '00:00';
        const endTime = endTimePicker ? endTimePicker.value : '23:59';
        
        // Préparer les données à envoyer
        const data = {
            search: searchTerm,
            driver: driver,
            time_filter: time,
            distance_filter: distance,
            start_time: startTime,
            end_time: endTime
        };
        
        // Ajouter les dates spécifiques si nécessaire
        if (time === 'custom' && customDate) {
            data.custom_date = customDate;
        } else if (time === 'daterange' && startDate && endDate) {
            data.start_date = startDate;
            data.end_date = endDate;
        }
        
        // Effectuer la requête AJAX
        fetch('/distances/filter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Mettre à jour les statistiques
            document.getElementById('total-distance').textContent = data.stats.total_distance + ' KM';
            document.getElementById('total-drivers').textContent = data.stats.total_drivers;
            document.getElementById('distance-time-label').textContent = data.stats.date_label;
            document.getElementById('drivers-time-label').textContent = data.stats.date_label;
            
            // Mettre à jour les distances dans le tableau
            updateDistancesTable(data.distances);
            
            // Stocker les données pour l'exportation
            storeDataForExport(data.distances);
        })
        .catch(error => {
            console.error('Erreur lors du filtrage des distances:', error);
            showToast('Erreur lors du filtrage des distances: ' + error.message, 'error');
            distancesContent.innerHTML = '<div class="no-results">Erreur lors du chargement des données. Veuillez réessayer.</div>';
        });
    }
    
    // Fonction pour calculer les distances
    function calculateDistances() {
        // Afficher un indicateur de chargement
        if (distancesContent) {
            distancesContent.innerHTML = '<div class="loader-container"><div class="loader"></div></div>';
        }
        
        // Récupérer les valeurs des filtres
        const time = timeFilter ? timeFilter.value : 'today';
        const customDate = customDatePicker ? customDatePicker.value : '';
        const startDate = startDatePicker ? startDatePicker.value : '';
        const endDate = endDatePicker ? endDatePicker.value : '';
        const startTime = startTimePicker ? startTimePicker.value : '00:00';
        const endTime = endTimePicker ? endTimePicker.value : '23:59';
        
        // Préparer les données à envoyer
        const data = {
            time_filter: time,
            start_time: startTime,
            end_time: endTime
        };
        
        // Ajouter les dates spécifiques si nécessaire
        if (time === 'custom' && customDate) {
            data.custom_date = customDate;
        } else if (time === 'daterange' && startDate && endDate) {
            data.start_date = startDate;
            data.end_date = endDate;
        }
        
        // Afficher un message de calcul en cours
        showToast('Calcul des distances en cours...', 'warning');
        
        // Effectuer la requête AJAX
        fetch('/distances/calculate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Recharger les données
                filterDistances();
            } else {
                showToast('Erreur lors du calcul des distances', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur lors du calcul des distances:', error);
            showToast('Erreur lors du calcul des distances: ' + error.message, 'error');
            distancesContent.innerHTML = '<div class="no-results">Erreur lors du calcul des distances. Veuillez réessayer.</div>';
        });
    }
    
    // Fonction pour mettre à jour le tableau des distances
    function updateDistancesTable(distances) {
        if (!distancesContent) return;
        
        if (!distances || distances.length === 0) {
            distancesContent.innerHTML = '<div class="no-results">Aucune donnée disponible pour cette période.</div>';
            return;
        }
        
        let tableHTML = `
            <div class="table-container">
                <table class="distances-table">
                    <thead>
                        <tr>
                            <th>ID Chauffeur</th>
                            <th>Nom du Chauffeur</th>
                            <th>Téléphone</th>
                            <th>Distance (KM)</th>
                            <th>Dernière Position</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        distances.forEach(distance => {
            let statusBadge = '';
            if (distance.status === 'Actif') {
                statusBadge = '<span class="badge bg-success">Actif</span>';
            } else if (distance.status === 'Modéré') {
                statusBadge = '<span class="badge bg-warning">Modéré</span>';
            } else {
                statusBadge = '<span class="badge bg-danger">Faible activité</span>';
            }
            
            tableHTML += `
                <tr>
                    <td>${distance.id}</td>
                    <td>${distance.name}</td>
                    <td>${distance.phone}</td>
                    <td>${distance.distance_km}</td>
                    <td>${distance.last_location}</td>
                    <td>${distance.date}</td>
                    <td>${distance.time}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
        
        tableHTML += `
                    </tbody>
                </table>
            </div>
        `;
        
        distancesContent.innerHTML = tableHTML;
    }
    
    // Stocker les données pour l'exportation
    function storeDataForExport(distances) {
        currentDistances = [];
        
        if (distances && distances.length > 0) {
            distances.forEach(distance => {
                currentDistances.push({
                    'ID Chauffeur': distance.id,
                    'Nom du Chauffeur': distance.name,
                    'Téléphone': distance.phone,
                    'Distance (KM)': distance.distance_km,
                    'Dernière Position': distance.last_location,
                    'Date': distance.date,
                    'Heure': distance.time,
                    'Statut': distance.status
                });
            });
        } else {
            // Essayer de récupérer les données depuis le tableau HTML
            const rows = document.querySelectorAll('.distances-table tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                currentDistances.push({
                    'ID Chauffeur': cells[0].textContent,
                    'Nom du Chauffeur': cells[1].textContent,
                    'Téléphone': cells[2].textContent,
                    'Distance (KM)': cells[3].textContent,
                    'Dernière Position': cells[4].textContent,
                    'Date': cells[5].textContent,
                    'Heure': cells[6].textContent,
                    'Statut': cells[7].textContent.trim()
                });
            });
        }
    }
    
    // Exportation Excel
    function exportToExcel() {
        if (currentDistances.length === 0) {
            storeDataForExport();
            
            if (currentDistances.length === 0) {
                showToast('Aucune donnée à exporter', 'warning');
                return;
            }
        }
        
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.json_to_sheet(currentDistances);
        
        XLSX.utils.book_append_sheet(wb, ws, 'Distances');
        
        const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
        const fileName = `distances_${timeStr}.xlsx`;
        XLSX.writeFile(wb, fileName);
        
        showToast('Export Excel réussi', 'success');
    }
    
    // Exportation PDF
    function exportToPDF() {
        if (currentDistances.length === 0) {
            storeDataForExport();
            
            if (currentDistances.length === 0) {
                showToast('Aucune donnée à exporter', 'warning');
                return;
            }
        }
        
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4'); // Paysage
        
        doc.setFontSize(18);
        doc.text('Rapport des Distances Quotidiennes', 14, 22);
        
        doc.setFontSize(10);
        doc.text(`Généré le ${new Date().toLocaleDateString('fr-FR')}`, 14, 30);
        
        const tableData = currentDistances.map(item => [
            item['ID Chauffeur'],
            item['Nom du Chauffeur'],
            item['Téléphone'],
            item['Distance (KM)'],
            item['Dernière Position'],
            item['Date'],
            item['Heure'],
            item['Statut']
        ]);
        
        const headers = [
            'ID Chauffeur',
            'Nom',
            'Téléphone',
            'Distance (KM)',
            'Dernière Position',
            'Date',
            'Heure',
            'Statut'
        ];
        
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
                doc.setFontSize(8);
                doc.text(
                    `Page ${doc.internal.getCurrentPageInfo().pageNumber} / ${doc.internal.getNumberOfPages()}`,
                    data.settings.margin.left,
                    doc.internal.pageSize.height - 10
                );
            }
        });
        
        const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
        const fileName = `distances_${timeStr}.pdf`;
        doc.save(fileName);
        
        showToast('Export PDF réussi', 'success');
    }
    
    // Exportation CSV
    function exportToCSV() {
        if (currentDistances.length === 0) {
            storeDataForExport();
            
            if (currentDistances.length === 0) {
                showToast('Aucune donnée à exporter', 'warning');
                return;
            }
        }
        
        const ws = XLSX.utils.json_to_sheet(currentDistances);
        const csv = XLSX.utils.sheet_to_csv(ws);
        
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
        const fileName = `distances_${timeStr}.csv`;
        
        saveAs(blob, fileName);
        
        showToast('Export CSV réussi', 'success');
    }
    
    // Afficher un toast
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
    
    // Fonction debounce pour limiter les appels
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
    
    // Stocker les données initiales pour l'exportation
    storeDataForExport();
});
</script>
@endsection