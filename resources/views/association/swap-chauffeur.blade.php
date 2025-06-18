@extends('layouts.app')

@section('title', 'Swaps par Chauffeur')

@section('content')
<style>
/* Couleurs d'application */
:root {
    --primary: #DCDB32;
    --secondary: #101010;
    --tertiary: #F3F3F3;
    --background: #ffffff;
    --text: #101010;
}

/* Styles pour les cartes de statistiques */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

.chauffeurs .stat-icon {
    color: #007bff;
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
    color: var(--text);
}

.stat-label {
    font-weight: bold;
    margin-bottom: 5px;
    color: var(--text);
}

.stat-text {
    color: #6c757d;
    font-size: 0.9em;
}

/* Barre de recherche et filtres */
.search-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.search-group {
    display: flex;
    align-items: center;
}

.search-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px 0 0 4px;
    width: 300px;
    outline: none;
}

.search-btn {
    background-color: var(--primary);
    border: 1px solid var(--primary);
    color: var(--secondary);
    padding: 8px 12px;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-group select, .filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    outline: none;
}

.date-picker-container, .date-range-container {
    display: none;
    gap: 10px;
    align-items: center;
}

/* Export buttons */
.export-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.export-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-excel {
    background-color: #1D6F42;
    color: white;
}

.btn-excel:hover {
    background-color: #155a35;
}

.btn-pdf {
    background-color: #F40F02;
    color: white;
}

.btn-pdf:hover {
    background-color: #c70c01;
}

.btn-csv {
    background-color: #ffcc00;
    color: #333;
}

.btn-csv:hover {
    background-color: #e6b800;
}

/* Table */
.table-container {
    overflow-x: auto;
    margin-bottom: 20px;
}

.date-header {
    background-color: var(--tertiary);
    padding: 10px 15px;
    font-weight: bold;
    border-radius: 4px;
    margin: 20px 0 10px 0;
    color: var(--text);
}

table {
    width: 100%;
    border-collapse: collapse;
    background-color: var(--background);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

thead {
    background-color: var(--tertiary);
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    font-weight: bold;
    color: var(--text);
}

tr:hover {
    background-color: rgba(220, 219, 50, 0.1);
}

/* Performance badges */
.performance-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.performance-high {
    background-color: #d4edda;
    color: #155724;
}

.performance-medium {
    background-color: #fff3cd;
    color: #856404;
}

.performance-low {
    background-color: #f8d7da;
    color: #721c24;
}

/* Content header */
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.content-header h2 {
    color: var(--text);
    margin: 0;
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

/* Toast notifications */
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
    max-width: 300px;
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

/* Responsive */
@media (max-width: 768px) {
    .search-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-group input {
        width: 100%;
    }
    
    .filter-group {
        justify-content: center;
    }
    
    .export-buttons {
        justify-content: center;
    }
}
</style>

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
    </div>

    <!-- Cartes de statistiques -->
    <div class="stats-grid">
        <div class="stat-card swaps">
            <div class="stat-icon">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-swaps">{{ $totalSwaps }}</div>
                <div class="stat-label">Total Swaps</div>
                <div class="stat-text" id="swaps-time-label">aujourd'hui</div>
            </div>
        </div>

        <div class="stat-card chauffeurs">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-chauffeurs">{{ $totalChauffeurs }}</div>
                <div class="stat-label">Chauffeurs Actifs</div>
                <div class="stat-text" id="chauffeurs-time-label">aujourd'hui</div>
            </div>
        </div>

        <div class="stat-card amount">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-amount">0 FCFA</div>
                <div class="stat-label">Montant Total</div>
                <div class="stat-text" id="amount-time-label">aujourd'hui</div>
            </div>
        </div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-chauffeur" placeholder="Rechercher un chauffeur...">
            <button type="button" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-group">
            <select id="agence-filter">
                <option value="all">Toutes les agences</option>
                @foreach($agences as $agence)
                <option value="{{ $agence->id }}">{{ $agence->nom_agence }}</option>
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
                <label>Date:</label>
                <input type="date" id="custom-date">
            </div>
            
            <div id="date-range-container" class="date-range-container">
                <label>Du:</label>
                <input type="date" id="start-date">
                <label>Au:</label>
                <input type="date" id="end-date">
            </div>
        </div>
    </div>

    <!-- Boutons d'export -->
    <div class="export-buttons">
        <button id="exportExcel" class="export-btn btn-excel">
            <i class="fas fa-file-excel"></i>
            Exporter Excel
        </button>
        <button id="exportPDF" class="export-btn btn-pdf">
            <i class="fas fa-file-pdf"></i>
            Exporter PDF
        </button>
        <button id="exportCSV" class="export-btn btn-csv">
            <i class="fas fa-file-csv"></i>
            Exporter CSV
        </button>
    </div>

    <!-- Zone des données chauffeurs -->
    <div id="chauffeurs-content">
        <div class="loader-container">
            <div class="loader"></div>
        </div>
    </div>
</div>

<!-- Scripts pour les exports -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.17.0/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver@2.0.5/dist/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const searchInput = document.getElementById('search-chauffeur');
    const agenceFilter = document.getElementById('agence-filter');
    const timeFilter = document.getElementById('time-filter');
    const customDatePicker = document.getElementById('custom-date');
    const datePickerContainer = document.getElementById('date-picker-container');
    const startDatePicker = document.getElementById('start-date');
    const endDatePicker = document.getElementById('end-date');
    const dateRangeContainer = document.getElementById('date-range-container');
    const chauffeursContent = document.getElementById('chauffeurs-content');
    
    // Variables pour les données
    let currentData = [];
    
    // Afficher la date actuelle
    const dateElement = document.getElementById('date');
    const today = new Date();
    if (dateElement) {
        dateElement.textContent = today.toLocaleDateString('fr-FR');
    }
    
    // Initialiser les dates des sélecteurs
    if (customDatePicker) customDatePicker.valueAsDate = today;
    if (startDatePicker) startDatePicker.valueAsDate = new Date(today.getFullYear(), today.getMonth(), 1);
    if (endDatePicker) endDatePicker.valueAsDate = today;

    // Gestion des sélecteurs de date
    timeFilter.addEventListener('change', function() {
        datePickerContainer.style.display = 'none';
        dateRangeContainer.style.display = 'none';
        
        if (this.value === 'custom') {
            datePickerContainer.style.display = 'flex';
        } else if (this.value === 'daterange') {
            dateRangeContainer.style.display = 'flex';
        }
        
        loadChauffeursData();
    });

    // Fonction pour obtenir le badge de performance
    function getPerformanceBadge(swapCount) {
        if (swapCount >= 10) {
            return '<span class="performance-badge performance-high">Excellent</span>';
        } else if (swapCount >= 5) {
            return '<span class="performance-badge performance-medium">Bon</span>';
        } else {
            return '<span class="performance-badge performance-low">Faible</span>';
        }
    }

    // Fonction principale pour charger les données
    function loadChauffeursData() {
        const content = document.getElementById('chauffeurs-content');
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Préparer les données
        const data = {
            search: searchInput.value || '',
            agence: agenceFilter.value || 'all',
            time: timeFilter.value || 'today'
        };
        
        // Ajouter les dates selon le filtre
        if (timeFilter.value === 'custom' && customDatePicker.value) {
            data.customDate = customDatePicker.value;
        } else if (timeFilter.value === 'daterange' && startDatePicker.value && endDatePicker.value) {
            data.startDate = startDatePicker.value;
            data.endDate = endDatePicker.value;
        }

        // Afficher le loader
        content.innerHTML = '<div class="loader-container"><div class="loader"></div></div>';

        fetch('/api/swaps-chauffeur/filter', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
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
            // Stocker les données pour l'export
            storeDataForExport(data);
            
            // Vider le contenu
            content.innerHTML = '';
            
            if (!data.swapsByDay || Object.keys(data.swapsByDay).length === 0) {
                content.innerHTML = '<div class="no-results">Aucune donnée trouvée pour cette période.</div>';
                return;
            }

            // Trier les dates (plus récentes en premier)
            const sortedDates = Object.keys(data.swapsByDay).sort().reverse();
            
            // Créer les tableaux pour chaque jour
            sortedDates.forEach(date => {
                const chauffeurs = data.swapsByDay[date];
                if (chauffeurs.length === 0) return;
                
                // En-tête de date
                const dateHeader = document.createElement('div');
                dateHeader.className = 'date-header';
                dateHeader.textContent = new Date(date + 'T00:00:00').toLocaleDateString('fr-FR', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                content.appendChild(dateHeader);

                // Container du tableau
                const tableContainer = document.createElement('div');
                tableContainer.className = 'table-container';

                // Créer le tableau
                const table = document.createElement('table');
                
                // En-tête du tableau
                const thead = document.createElement('thead');
                thead.innerHTML = `
                    <tr>
                        <th>Chauffeur</th>
                        <th>Station</th>
                        <th>Nombre de Swaps</th>
                        <th>Montant Total (FCFA)</th>
                        <th>Performance</th>
                    </tr>
                `;
                table.appendChild(thead);

                // Corps du tableau
                const tbody = document.createElement('tbody');
                
                chauffeurs.forEach(chauffeur => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${chauffeur.chauffeur_name}</td>
                        <td>${chauffeur.station}</td>
                        <td><strong>${chauffeur.swap_count}</strong></td>
                        <td>${Number(chauffeur.total_amount).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>${getPerformanceBadge(chauffeur.swap_count)}</td>
                    `;
                    tbody.appendChild(row);
                });
                
                table.appendChild(tbody);
                tableContainer.appendChild(table);
                content.appendChild(tableContainer);
            });

            // Mettre à jour les statistiques
            document.getElementById('total-swaps').textContent = data.totalSwaps || 0;
            document.getElementById('total-chauffeurs').textContent = data.totalChauffeurs || 0;
            document.getElementById('total-amount').textContent = 
                Number(data.totalAmount || 0).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' FCFA';
            
            document.getElementById('swaps-time-label').textContent = data.timeLabel || 'aujourd\'hui';
            document.getElementById('chauffeurs-time-label').textContent = data.timeLabel || 'aujourd\'hui';
            document.getElementById('amount-time-label').textContent = data.timeLabel || 'aujourd\'hui';
        })
        .catch(error => {
            console.error('Erreur lors du chargement des données:', error);
            content.innerHTML = '<div class="no-results">Erreur lors du chargement des données. Veuillez réessayer.</div>';
            showToast('Erreur lors du chargement des données', 'error');
        });
    }

    // Stocker les données pour l'exportation
    function storeDataForExport(data) {
        currentData = [];
        
        if (!data.swapsByDay) return;
        
        Object.keys(data.swapsByDay).forEach(date => {
            const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('fr-FR');
            
            data.swapsByDay[date].forEach(chauffeur => {
                currentData.push({
                    'Date': formattedDate,
                    'Chauffeur': chauffeur.chauffeur_name,
                    'Station': chauffeur.station,
                    'Nombre de Swaps': chauffeur.swap_count,
                    'Montant Total (FCFA)': Number(chauffeur.total_amount).toFixed(2)
                });
            });
        });
    }

    // Fonction pour afficher les notifications toast
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

    // Fonctions d'export
    function exportToExcel() {
        if (currentData.length === 0) {
            showToast('Aucune donnée à exporter', 'warning');
            return;
        }
        
        try {
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(currentData);
            
            // Ajuster la largeur des colonnes
            const colWidths = [
                { wch: 15 }, // Date
                { wch: 25 }, // Chauffeur
                { wch: 20 }, // Station
                { wch: 15 }, // Nombre de Swaps
                { wch: 20 }  // Montant Total
            ];
            ws['!cols'] = colWidths;
            
            XLSX.utils.book_append_sheet(wb, ws, 'Swaps par Chauffeur');
            
            const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
            const fileName = `swaps_chauffeur_${timeStr}.xlsx`;
            XLSX.writeFile(wb, fileName);
            
            showToast('Export Excel réussi', 'success');
        } catch (error) {
            console.error('Erreur export Excel:', error);
            showToast('Erreur lors de l\'export Excel', 'error');
        }
    }
    
    function exportToCSV() {
        if (currentData.length === 0) {
            showToast('Aucune donnée à exporter', 'warning');
            return;
        }
        
        try {
            const ws = XLSX.utils.json_to_sheet(currentData);
            const csv = XLSX.utils.sheet_to_csv(ws);
            
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
            const fileName = `swaps_chauffeur_${timeStr}.csv`;
            
            saveAs(blob, fileName);
            
            showToast('Export CSV réussi', 'success');
        } catch (error) {
            console.error('Erreur export CSV:', error);
            showToast('Erreur lors de l\'export CSV', 'error');
        }
    }
    
    function exportToPDF() {
        if (currentData.length === 0) {
            showToast('Aucune donnée à exporter', 'warning');
            return;
        }
        
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4'); // Mode paysage
            
            // Titre
            doc.setFontSize(18);
            doc.text('Rapport des Swaps par Chauffeur', 14, 22);
            
            // Date de génération
            doc.setFontSize(10);
            doc.text(`Généré le ${new Date().toLocaleDateString('fr-FR')}`, 14, 30);
            
            // Préparer les données pour le tableau
            const tableData = currentData.map(row => [
                row['Date'],
                row['Chauffeur'],
                row['Station'],
                row['Nombre de Swaps'].toString(),
                row['Montant Total (FCFA)']
            ]);
            
            const headers = ['Date', 'Chauffeur', 'Station', 'Nb Swaps', 'Montant (FCFA)'];
            
            // Créer le tableau
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
                    fontSize: 9,
                    cellPadding: 3
                },
                columnStyles: {
                    0: { cellWidth: 25 }, // Date
                    1: { cellWidth: 60 }, // Chauffeur
                    2: { cellWidth: 50 }, // Station
                    3: { cellWidth: 25, halign: 'center' }, // Nb Swaps
                    4: { cellWidth: 35, halign: 'right' }   // Montant
                },
                didDrawPage: function(data) {
                    // Pied de page avec numérotation
                    doc.setFontSize(8);
                    const pageCount = doc.internal.getNumberOfPages();
                    const pageNumber = doc.internal.getCurrentPageInfo().pageNumber;
                    doc.text(
                        `Page ${pageNumber} / ${pageCount}`,
                        data.settings.margin.left,
                        doc.internal.pageSize.height - 10
                    );
                }
            });
            
            // Télécharger le PDF
            const timeStr = new Date().toISOString().replace(/[:.]/g, '-');
            const fileName = `swaps_chauffeur_${timeStr}.pdf`;
            doc.save(fileName);
            
            showToast('Export PDF réussi', 'success');
        } catch (error) {
            console.error('Erreur export PDF:', error);
            showToast('Erreur lors de l\'export PDF', 'error');
        }
    }

    // Fonction debounce pour la recherche
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Événements
    searchInput.addEventListener('input', debounce(loadChauffeursData, 500));
    agenceFilter.addEventListener('change', loadChauffeursData);
    customDatePicker.addEventListener('change', () => {
        if (timeFilter.value === 'custom') {
            loadChauffeursData();
        }
    });
    startDatePicker.addEventListener('change', () => {
        if (timeFilter.value === 'daterange') {
            loadChauffeursData();
        }
    });
    endDatePicker.addEventListener('change', () => {
        if (timeFilter.value === 'daterange') {
            loadChauffeursData();
        }
    });

    // Événements pour les boutons d'export
    document.getElementById('exportExcel').addEventListener('click', exportToExcel);
    document.getElementById('exportPDF').addEventListener('click', exportToPDF);
    document.getElementById('exportCSV').addEventListener('click', exportToCSV);

    // Messages de session Laravel
    @if(session('success'))
    showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
    showToast("{{ session('error') }}", 'error');
    @endif

    // Charger les données initiales
    loadChauffeursData();
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