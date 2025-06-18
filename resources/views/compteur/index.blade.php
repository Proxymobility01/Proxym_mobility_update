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
    --danger: #dc3545;
    --success: #28a745;
    --warning: #ffc107;
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
    color: var(--success);
}

.danger .stat-icon {
    color: var(--danger);
}

.warning .stat-icon {
    color: var(--warning);
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
.filter-bar {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.filter-group {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.filter-group select, .filter-group input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 150px;
}

.filter-btn {
    background-color: var(--primary);
    color: var(--secondary);
    border: none;
    padding: 8px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

.filter-btn:hover {
    background-color: #c8c72d;
}

.date-filter {
    display: none;
}

.date-filter.show {
    display: block;
}

/* Table */
.table-container {
    overflow-x: auto;
    background-color: var(--background);
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
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
    position: sticky;
    top: 0;
    background-color: var(--tertiary);
}

tr:hover {
    background-color: var(--tertiary);
}

/* Styles spéciaux pour les valeurs de puissance */
.power-value {
    font-weight: bold;
    padding: 4px 8px;
    border-radius: 4px;
}

.power-low {
    background-color: rgba(220, 53, 69, 0.2);
    color: var(--danger);
}

.power-normal {
    background-color: rgba(40, 167, 69, 0.2);
    color: var(--success);
}

.power-total {
    font-size: 1.1em;
    font-weight: bold;
}

/* Row danger styling */
.row-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

.row-danger:hover {
    background-color: rgba(220, 53, 69, 0.2);
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

.no-results {
    text-align: center;
    padding: 40px;
    color: #6c757d;
    font-style: italic;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filter-bar {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
        justify-content: space-between;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>Surveillance des compteurs par station</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number">{{ $readings->count() }}</div>
                <div class="stat-label">Total Relevés</div>
                <div class="stat-text">Compteurs surveillés</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number">{{ $readings->filter(function($r) { return ($r->kw1 + $r->kw2 + $r->kw3 + $r->kw4) >= 65; })->count() }}</div>
                <div class="stat-label">Compteurs OK</div>
                <div class="stat-text">≥ 65W</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number">{{ $readings->filter(function($r) { return ($r->kw1 + $r->kw2 + $r->kw3 + $r->kw4) < 65; })->count() }}</div>
                <div class="stat-label">Compteurs Faibles</div>
                <div class="stat-text">< 65W</div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-battery-half"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number">{{ $readings->sum('low_batteries') }}</div>
                <div class="stat-label">Batteries Faibles</div>
                <div class="stat-text">Total relevé</div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <form method="GET" action="{{ route('compteur.index') }}" class="filter-bar">
        <div class="filter-group">
            <select class="form-control" name="agence_id">
                <option value="all">Toutes les stations</option>
                @foreach($agences as $agence)
                    <option value="{{ $agence->id }}" {{ request('agence_id') == $agence->id ? 'selected' : '' }}>
                        {{ $agence->nom_agence }}
                    </option>
                @endforeach
            </select>

            <select class="form-control" name="user_id">
                <option value="all">Tous les swappeurs</option>
                @foreach($swappeurs as $swappeur)
                    <option value="{{ $swappeur->id }}" {{ request('user_id') == $swappeur->id ? 'selected' : '' }}>
                        {{ $swappeur->nom }} {{ $swappeur->prenom }}
                    </option>
                @endforeach
            </select>

            <select class="form-control" id="time-filter" name="time_filter">
                <option value="today" {{ request('time_filter') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                <option value="week" {{ request('time_filter') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                <option value="month" {{ request('time_filter') == 'month' ? 'selected' : '' }}>Ce mois</option>
                <option value="year" {{ request('time_filter') == 'year' ? 'selected' : '' }}>Cette année</option>
                <option value="custom" {{ request('time_filter') == 'custom' ? 'selected' : '' }}>Date spécifique</option>
                <option value="daterange" {{ request('time_filter') == 'daterange' ? 'selected' : '' }}>Plage de dates</option>
                <option value="all" {{ request('time_filter') == 'all' ? 'selected' : '' }}>Tout l'historique</option>
            </select>
        </div>

        <div class="filter-group">
            <div class="date-filter date-custom {{ request('time_filter') == 'custom' ? 'show' : '' }}">
                <input type="date" name="custom_date" class="form-control" value="{{ request('custom_date') }}" placeholder="Date spécifique">
            </div>

            <div class="date-filter date-range {{ request('time_filter') == 'daterange' ? 'show' : '' }}">
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Date début">
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="Date fin">
            </div>

            <button type="submit" class="filter-btn">
                <i class="fas fa-filter"></i> Filtrer
            </button>
        </div>
    </form>

    <!-- Tableau -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>kW1</th>
                    <th>kW2</th>
                    <th>kW3</th>
                    <th>kW4</th>
                    <th>Total kW</th>
                    <th>Batteries Chargées</th>
                    <th>Batteries Faibles</th>
                    <th>Station</th>
                    <th>Swappeur</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($readings as $reading)
                    @php
                        $totalKw = $reading->kw1 + $reading->kw2 + $reading->kw3 + $reading->kw4;
                        $isLow = $totalKw < 65;
                    @endphp
                    <tr class="{{ $isLow ? 'row-danger' : '' }}">
                        <td>
                            <span class="power-value {{ $reading->kw1 < 16 ? 'power-low' : 'power-normal' }}">
                                {{ $reading->kw1 }} W
                            </span>
                        </td>
                        <td>
                            <span class="power-value {{ $reading->kw2 < 16 ? 'power-low' : 'power-normal' }}">
                                {{ $reading->kw2 }} W
                            </span>
                        </td>
                        <td>
                            <span class="power-value {{ $reading->kw3 < 16 ? 'power-low' : 'power-normal' }}">
                                {{ $reading->kw3 }} W
                            </span>
                        </td>
                        <td>
                            <span class="power-value {{ $reading->kw4 < 16 ? 'power-low' : 'power-normal' }}">
                                {{ $reading->kw4 }} W
                            </span>
                        </td>
                        <td>
                            <span class="power-value power-total {{ $isLow ? 'power-low' : 'power-normal' }}">
                                {{ $totalKw }} W
                            </span>
                        </td>
                        <td>{{ $reading->charged_batteries }}</td>
                        <td>{{ $reading->low_batteries }}</td>
                        <td>{{ $reading->agence->nom_agence ?? 'N/A' }}</td>
                        <td>{{ $reading->user->nom ?? 'N/A' }} {{ $reading->user->prenom ?? '' }}</td>
                        <td>{{ date('d/m/Y H:i', strtotime($reading->created_at)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="no-results">
                            <i class="fas fa-info-circle"></i> Aucune donnée trouvée pour ces critères.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Afficher la date actuelle dans l'en-tête
    const dateElement = document.getElementById('date');
    const today = new Date();
    dateElement.textContent = today.toLocaleDateString('fr-FR');

    const timeFilter = document.getElementById('time-filter');
    const customDate = document.querySelector('.date-custom');
    const dateRange = document.querySelectorAll('.date-range');

    function toggleDateInputs() {
        // Masquer tous les champs de date
        customDate.classList.remove('show');
        dateRange.forEach(e => e.classList.remove('show'));

        // Afficher les champs appropriés
        if (timeFilter.value === 'custom') {
            customDate.classList.add('show');
        } else if (timeFilter.value === 'daterange') {
            dateRange.forEach(e => e.classList.add('show'));
        }
    }

    timeFilter.addEventListener('change', toggleDateInputs);

    // Initialiser à l'ouverture
    toggleDateInputs();

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

    // Afficher les messages de session Laravel
    @if(session('success'))
    showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
    showToast("{{ session('error') }}", 'error');
    @endif

    @if(session('warning'))
    showToast("{{ session('warning') }}", 'warning');
    @endif
});
</script>
@endsection