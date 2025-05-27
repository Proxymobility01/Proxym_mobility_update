@extends('layouts.app')

@section('content')

<div class="main-content">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard de Gestion des Batteries</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/fr.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap');

        :root {
            --primary-color: #DCDB32; /* Jaune vif/citron comme dans l'image */
            --secondary-color: #101010; /* Noir pour les textes et certains éléments */
            --background: #ffffff; /* Fond blanc */
            --sidebar: #F8F8F8; /* Gris très clair pour la barre latérale */
            --light-color: #F3F3F3; /* Gris clair pour certains éléments */
            --accent-color: #e74c3c; /* Pour les alertes/éléments négatifs */
            --success-color: #2ecc71; /* Pour les indicateurs positifs */
            --warning-color: #f39c12; /* Pour les avertissements */
            --info-color: #3498db; /* Pour les informations */
            --dark-color: #101010; /* Pour le texte principal */
            --text: #101010; /* Couleur de texte principale */
            --border-radius: 8px;
            --box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        h1, h2, h3, .card-title, .card-value, .station-name, .level-name, .tab {
            font-family: 'Orbitron', sans-serif;
        }

        body {
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        h1 {
            color: var(--dark-color);
            font-size: 24px;
            letter-spacing: 1px;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        select, input[type="month"], input[type="date"], input[type="number"] {
            padding: 8px 12px;
            border-radius: var(--border-radius);
            border: 1px solid #ddd;
            background-color: white;
            font-size: 14px;
            cursor: pointer;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-grid-full {
            grid-column: span 12;
            margin-bottom: 20px;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark-color);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .card-value {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
        }

        .card-footer {
            font-size: 13px;
            color: #777;
        }

        .kpi-card {
            grid-column: span 3;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .chart-card {
            grid-column: span 6;
            min-height: 300px;
        }

        .chart-card.full-width {
            grid-column: span 12;
        }

        .batteries-by-station {
            grid-column: span 6;
        }

        .battery-levels {
            grid-column: span 6;
        }

        .charged {
            color: var(--success-color);
        }

        .charging {
            color: var(--info-color);
        }

        .discharged {
            color: var(--accent-color);
        }

        .inactive {
            color: var(--warning-color);
        }
        
        .unknown {
            color: #95a5a6;
        }

        .total {
            color: var(--dark-color);
        }

        .progress-bar-container {
            height: 8px;
            background-color: #eee;
            border-radius: 4px;
            margin: 6px 0;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 4px;
        }

        .progress-bar.very-high {
            background-color: var(--success-color);
        }

        .progress-bar.high {
            background-color: #27ae60;
        }

        .progress-bar.medium {
            background-color: var(--warning-color);
        }

        .progress-bar.low {
            background-color: #e67e22;
        }

        .progress-bar.very-low, .progress-bar.unknown {
            background-color: #95a5a6;
        }

        .battery-level-item {
            margin-bottom: 15px;
        }

        .level-stats {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .level-name {
            font-weight: 600;
        }

        .level-value {
            font-weight: 700;
        }

        .station-list {
            margin-top: 15px;
        }

        .station-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .station-name {
            font-weight: 600;
        }

        .station-stats {
            display: flex;
            gap: 15px;
        }

        .station-stat {
            text-align: center;
            min-width: 60px;
        }

        .station-stat-value {
            font-weight: 700;
            font-size: 16px;
        }

        .station-stat-label {
            font-size: 12px;
            color: #777;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 600;
        }

        .tab.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .legend {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--light-color);
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .stats-overview {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
        }
        
        .stats-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        
        .btn-warning {
            color: #212529;
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        .ml-2 {
            margin-left: 0.5rem;
        }
        
        .modal-header {
            background-color: var(--warning-color);
            color: white;
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
        }
        
        .close {
            color: white;
            opacity: 0.8;
        }
        
        .close:hover {
            color: white;
            opacity: 1;
        }
        
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .alert {
            position: relative;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }
        
        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
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
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .badge-success {
            color: #fff;
            background-color: var(--success-color);
        }
        
        .badge-info {
            color: #fff;
            background-color: var(--info-color);
        }
        
        .badge-warning {
            color: #212529;
            background-color: var(--warning-color);
        }
        
        .badge-danger {
            color: #fff;
            background-color: var(--accent-color);
        }
        
        .mt-4 {
            margin-top: 1.5rem;
        }
        
        .mb-3 {
            margin-bottom: 1rem;
        }
        
        .mr-2 {
            margin-right: 0.5rem;
        }
        
        .py-5 {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <div class="loading-overlay">
        <div class="spinner"></div>
    </div>
    
    <div class="container">
        <header>
            <h1>Dashboard de Gestion des Batteries</h1>
            <div class="date-time" id="current-datetime">Date et heure</div>
        </header>
        
        @if(isset($error))
        <div class="alert alert-danger text-center">
            <h4 class="mb-3">❌ Une erreur est survenue</h4>
            <p>{{ $error ?? 'Erreur inconnue lors du chargement du dashboard.' }}</p>
            <a href="{{ route('dashboard.index') }}" class="btn btn-primary mt-3">
                Retour au dashboard
            </a>
        </div>
        @endif

        <form id="filter-form" method="GET" action="{{ route('dashboard.filter') }}">
            <div class="filters">
                <select id="station-filter" name="station">
                    <option value="all" {{ $selectedStation == 'all' ? 'selected' : '' }}>Toutes les stations</option>
                    @foreach($stations as $station)
                        <option value="{{ $station->id }}" {{ $selectedStation == $station->id ? 'selected' : '' }}>
                            {{ $station->nom_agence }}
                        </option>
                    @endforeach
                </select>
                <select id="time-filter" name="time_filter">
                    <option value="day" {{ $timeFilter == 'day' ? 'selected' : '' }}>Par jour</option>
                    <option value="week" {{ $timeFilter == 'week' ? 'selected' : '' }}>Par semaine</option>
                    <option value="month" {{ $timeFilter == 'month' ? 'selected' : '' }}>Par mois</option>
                    <option value="year" {{ $timeFilter == 'year' ? 'selected' : '' }}>Par année</option>
                </select>
                <select id="period-filter" name="period_filter">
                    <option value="current" {{ $periodFilter == 'current' ? 'selected' : '' }}>Période actuelle</option>
                    <option value="previous" {{ $periodFilter == 'previous' ? 'selected' : '' }}>Période précédente</option>
                    <option value="last3" {{ $periodFilter == 'last3' ? 'selected' : '' }}>3 dernières périodes</option>
                    <option value="last6" {{ $periodFilter == 'last6' ? 'selected' : '' }}>6 dernières périodes</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
            </div>
        </form>

        <!-- Vue d'ensemble statistique -->
        <div class="dashboard-grid-full">
            <div class="card stats-overview">
                <div class="stats-title">Vue d'ensemble statistique - {{ $selectedStation == 'all' ? 'Toutes les stations' : $stations->firstWhere('id', $selectedStation)->nom_agence }}</div>
                <div>
                    <p>
                        <strong>Stock total:</strong> {{ $summaryStats['total'] }} batteries
                        | <strong>Batteries actives:</strong> {{ $summaryStats['active_total'] }} ({{ $summaryStats['total'] > 0 ? round(($summaryStats['active_total'] / $summaryStats['total']) * 100) : 0 }}%)
                        | <strong>Batteries inactives:</strong> {{ $summaryStats['inactive'] }} ({{ $summaryStats['total'] > 0 ? round(($summaryStats['inactive'] / $summaryStats['total']) * 100) : 0 }}%)
                        @if($summaryStats['inactive'] > 0)
                        <button id="show-inactive-batteries" class="btn btn-warning btn-sm ml-2">
                            <i class="fas fa-list"></i> Voir liste
                        </button>
                        @endif
                    </p>
                    <p>
                        <strong>Chargées:</strong> <span class="charged">{{ $summaryStats['charged'] }}</span>
                        | <strong>En charge:</strong> <span class="charging">{{ $summaryStats['charging'] }}</span>
                        | <strong>Déchargées:</strong> <span class="discharged">{{ $summaryStats['discharged'] }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- KPI Cards Row -->
            <div class="card kpi-card">
                <div>
                    <div class="card-header">
                        <div class="card-title">Batteries Chargées</div>
                    </div>
                    <div class="card-value charged" id="charged-count">{{ $summaryStats['charged'] }}</div>
                </div>
                <div class="card-footer">SOC > 95%</div>
            </div>

            <div class="card kpi-card">
                <div>
                    <div class="card-header">
                        <div class="card-title">Batteries en Charge</div>
                    </div>
                    <div class="card-value charging" id="charging-count">{{ $summaryStats['charging'] }}</div>
                </div>
                <div class="card-footer">En cours de charge</div>
            </div>

            <div class="card kpi-card">
                <div>
                    <div class="card-header">
                        <div class="card-title">Batteries Déchargées</div>
                    </div>
                    <div class="card-value discharged" id="discharged-count">{{ $summaryStats['discharged'] }}</div>
                </div>
                <div class="card-footer">Nécessitent une charge</div>
            </div>

            <div class="card kpi-card">
                <div>
                    <div class="card-header">
                        <div class="card-title">Stock Total</div>
                    </div>
                    <div class="card-value total" id="total-count">{{ $summaryStats['total'] }}</div>
                </div>
                <div class="card-footer">Toutes les batteries</div>
            </div>

            <!-- Battery Levels Card -->
            <div class="card battery-levels">
                <div class="card-header">
                    <div class="card-title">Répartition par Niveau de Charge</div>
                </div>
                
                <div class="battery-level-item">
                    <div class="level-stats">
                        <div class="level-name">Très élevé (>90%)</div>
                        <div class="level-value" id="very-high-count">{{ $levelStats['very_high']['count'] }}</div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar very-high" id="very-high-bar" style="width: {{ $levelStats['very_high']['percentage'] }}%;"></div>
                    </div>
                </div>
                
                <div class="battery-level-item">
                    <div class="level-stats">
                        <div class="level-name">Élevé (>50%)</div>
                        <div class="level-value" id="high-count">{{ $levelStats['high']['count'] }}</div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar high" id="high-bar" style="width: {{ $levelStats['high']['percentage'] }}%;"></div>
                    </div>
                </div>
                
                <div class="battery-level-item">
                    <div class="level-stats">
                        <div class="level-name">Moyen (>30%)</div>
                        <div class="level-value" id="medium-count">{{ $levelStats['medium']['count'] }}</div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar medium" id="medium-bar" style="width: {{ $levelStats['medium']['percentage'] }}%;"></div>
                    </div>
                </div>
                
                <div class="battery-level-item">
                    <div class="level-stats">
                        <div class="level-name">Faible (<30%)</div>
                        <div class="level-value" id="low-count">{{ $levelStats['low']['count'] }}</div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar very-low" id="low-bar" style="width: {{ $levelStats['low']['percentage'] }}%;"></div>
                    </div>
                </div>
                
                <div class="battery-level-item">
                    <div class="level-stats">
                        <div class="level-name">Inconnu</div>
                        <div class="level-value" id="unknown-count">{{ $levelStats['unknown']['count'] }}</div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar unknown" id="unknown-bar" style="width: {{ $levelStats['unknown']['percentage'] }}%;"></div>
                    </div>
                </div>
                
                <div class="chart-container" style="margin-top: 20px; height: 180px;">
                    <canvas id="battery-levels-chart"></canvas>
                </div>
                
                <div class="level-stats" style="margin-top: 10px;">
                    <div><strong>Total:</strong></div>
                    <div class="level-value" id="total-level-count">{{ $levelStats['total'] }}</div>
                </div>
            </div>

            <!-- Batteries by Station Card - Version améliorée avec tableau -->
            <div class="card batteries-by-station">
                <div class="card-header">
                    <div class="card-title">État des Batteries par Station</div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportTableToExcel('station-stats-table', 'batteries_par_station')">
                            <i class="fas fa-file-excel"></i> Exporter
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover" id="station-stats-table">
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th class="text-center charged">Chargées</th>
                                <th class="text-center charging">En charge</th>
                                <th class="text-center discharged">Déchargées</th>
                                <th class="text-center inactive">Inactives</th>
                                <th class="text-center total">Total</th>
                                <th class="text-right">Taux Actives</th>
                            </tr>
                        </thead>
                        <tbody id="station-list">
                            @foreach($stationStats as $station)
                            <tr>
                                <td class="station-name">{{ $station['name'] }}</td>
                                <td class="text-center">
                                    <span class="station-stat-value charged">{{ $station['stats']['charged'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value charging">{{ $station['stats']['charging'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value discharged">{{ $station['stats']['discharged'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value inactive">{{ $station['stats']['inactive'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value total">{{ $station['stats']['total'] }}</span>
                                </td>
                                <td class="text-right">
                                    @php
                                        $activeRate = $station['stats']['total'] > 0 
                                            ? round(($station['stats']['charged'] + $station['stats']['charging'] + $station['stats']['discharged']) / $station['stats']['total'] * 100)
                                            : 0;
                                    @endphp
                                    <div class="progress" style="height: 5px; width: 80px; display: inline-block; vertical-align: middle; margin-right: 5px;">
                                        <div class="progress-bar 
                                            @if($activeRate > 80) bg-success 
                                            @elseif($activeRate > 50) bg-info 
                                            @elseif($activeRate > 30) bg-warning 
                                            @else bg-danger @endif" 
                                            role="progressbar" style="width: {{ $activeRate }}%">
                                        </div>
                                    </div>
                                    <span>{{ $activeRate }}%</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Version améliorée du graphique de Swaps avec support de semaines -->
            <div class="card chart-card full-width mt-4">
                <div class="card-header">
                    <div class="card-title">Évolution des Swaps & Montants générés</div>
                </div>
                <div class="filters mb-3" style="flex-wrap: wrap;">
                    <select id="swap-chart-station" class="mr-2">
                        <option value="all">Toutes les stations</option>
                        @foreach($stations as $station)
                            <option value="{{ $station->id }}">{{ $station->nom_agence }}</option>
                        @endforeach
                    </select>

                    <select id="swap-chart-time" class="mr-2">
                        <option value="day">Jour (semaine courante)</option>
                        <option value="week">Semaines (du mois)</option>
                        <option value="month" selected>Mois (de l'année)</option>
                        <option value="year">Années</option>
                        <option value="custom">Période personnalisée</option>
                    </select>

                    <select id="swap-chart-year" class="mr-2">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    
                    <input type="month" id="swap-chart-month" class="mr-2" value="{{ date('Y-m') }}">
                    
                    <div class="input-group date-range" style="display: inline-flex; width: auto;">
                        <input type="date" id="swap-chart-start" class="form-control mr-2" style="display:none;" placeholder="Date début">
                        <input type="date" id="swap-chart-end" class="form-control mr-2" style="display:none;" placeholder="Date fin">
                    </div>
                    
                    <button type="button" id="refresh-swap-chart" class="btn btn-sm btn-primary">
                        <i class="fas fa-sync"></i> Actualiser
                    </button>
                    <button type="button" id="export-swap-chart" class="btn btn-sm btn-outline-secondary ml-2">
                        <i class="fas fa-file-excel"></i> Exporter données
                    </button>
                </div>
                <div class="chart-container" style="height: 350px;">
                    <canvas id="swapEvolutionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Modal pour les batteries inactives -->
        <div class="modal fade" id="inactiveBatteriesModal" tabindex="-1" role="dialog" aria-labelledby="inactiveBatteriesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="inactiveBatteriesModalLabel">Batteries Inactives</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="inactive-batteries-loading" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Chargement...</span>
                            </div>
                        </div>
                        <div id="inactive-batteries-content" style="display: none;">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> <span id="inactive-batteries-count">0</span> batteries inactives détectées.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="inactive-batteries-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>MAC ID</th>
                                            <th>N° Série</th>
                                            <th>Station(s)</th>
                                            <th>Dernière communication</th>
                                            <th>Dernier SOC</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Les données seront chargées dynamiquement ici -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="inactive-batteries-error" class="alert alert-danger" style="display: none;">
                            Une erreur est survenue lors du chargement des batteries inactives.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" id="refresh-inactive-batteries">
                            <i class="fas fa-sync"></i> Actualiser
                        </button>
                        <button type="button" class="btn btn-success" id="export-inactive-batteries">
                            <i class="fas fa-file-excel"></i> Exporter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour les détails de batterie -->
        <div class="modal fade" id="batteryDetailsModal" tabindex="-1" role="dialog" aria-labelledby="batteryDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="batteryDetailsModalLabel">Détails de la Batterie</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="battery-details-loading" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Chargement...</span>
                            </div>
                        </div>
                        <div id="battery-details-content" style="display: none;">
                            <!-- Les données seront chargées dynamiquement ici -->
                        </div>
                        <div id="battery-details-error" class="alert alert-danger" style="display: none;">
                            Une erreur est survenue lors du chargement des détails de la batterie.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Enregistrer le plugin datalabels
            Chart.register(ChartDataLabels);
            
            // Mettre à jour la date et l'heure
            function updateDateTime() {
                const now = new Date();
                document.getElementById('current-datetime').textContent = now.toLocaleDateString('fr-FR', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
            
            updateDateTime();
            setInterval(updateDateTime, 60000);

            // Gestion des onglets
            document.querySelectorAll('.tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    // Retirer la classe active de tous les onglets et contenus
                    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                    
                    // Ajouter la classe active à l'onglet cliqué et au contenu correspondant
                    tab.classList.add('active');
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(`${tabId}-content`).classList.add('active');
                });
            });
            
            // Gestion de la modal des batteries inactives
            document.addEventListener('DOMContentLoaded', function() {
                // Référence à la modal
                const inactiveBatteriesModal = document.getElementById('inactiveBatteriesModal');
                
                // Si la modal n'existe pas, on sort
                if (!inactiveBatteriesModal) return;
                
                // Bouton pour ouvrir la modal
                const showInactiveBatteriesBtn = document.getElementById('show-inactive-batteries');
                if (showInactiveBatteriesBtn) {
                    showInactiveBatteriesBtn.addEventListener('click', function() {
                        // Initialiser la modal
                        $('#inactiveBatteriesModal').modal('show');
                        
                        // Charger les données
                        loadInactiveBatteries();
                    });
                }
                
                // Bouton pour actualiser les données
                const refreshInactiveBatteriesBtn = document.getElementById('refresh-inactive-batteries');
                if (refreshInactiveBatteriesBtn) {
                    refreshInactiveBatteriesBtn.addEventListener('click', function() {
                        loadInactiveBatteries();
                    });
                }
                
                // Bouton pour exporter les données
                const exportInactiveBatteriesBtn = document.getElementById('export-inactive-batteries');
                if (exportInactiveBatteriesBtn) {
                    exportInactiveBatteriesBtn.addEventListener('click', function() {
                        exportTableToExcel('inactive-batteries-table', 'batteries_inactives');
                    });
                }
            });
            
            // Fonction pour charger les batteries inactives
            function loadInactiveBatteries() {
                // Afficher le chargement
                document.getElementById('inactive-batteries-loading').style.display = 'block';
                document.getElementById('inactive-batteries-content').style.display = 'none';
                document.getElementById('inactive-batteries-error').style.display = 'none';
                
                // Faire la requête AJAX
                fetch('{{ route("api.inactive_batteries") }}')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur réseau');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Masquer le chargement
                        document.getElementById('inactive-batteries-loading').style.display = 'none';
                        
                        // Vérifier s'il y a une erreur
                        if (data.error) {
                            document.getElementById('inactive-batteries-error').textContent = data.error;
                            document.getElementById('inactive-batteries-error').style.display = 'block';
                            return;
                        }
                        
                        // Mettre à jour le compteur
                        document.getElementById('inactive-batteries-count').textContent = data.count;
                        
                        // Récupérer le tableau
                        const tableBody = document.querySelector('#inactive-batteries-table tbody');
                        tableBody.innerHTML = '';
                        
                        // Ajouter les données au tableau
                        if (data.inactiveBatteries && data.inactiveBatteries.length > 0) {
                            data.inactiveBatteries.forEach(battery => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${battery.id}</td>
                                    <td>${battery.mac_id}</td>
                                    <td>${battery.serial_number}</td>
                                    <td>${battery.stations}</td>
                                    <td>${battery.last_communication || 'Jamais'}</td>
                                    <td>${battery.last_soc}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-toggle="tooltip" title="Voir détails" 
                                            onclick="viewBatteryDetails(${battery.id})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                `;
                                tableBody.appendChild(row);
                            });
                            
                            // Initialiser les tooltips
                            $('[data-toggle="tooltip"]').tooltip();
                        } else {
                            // Aucune batterie inactive
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td colspan="7" class="text-center">Aucune batterie inactive trouvée.</td>
                            `;
                            tableBody.appendChild(row);
                        }
                        
                        // Afficher le contenu
                        document.getElementById('inactive-batteries-content').style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        document.getElementById('inactive-batteries-loading').style.display = 'none';
                        document.getElementById('inactive-batteries-error').style.display = 'block';
                    });
            }
            
            // Fonction pour voir les détails d'une batterie
            function viewBatteryDetails(batteryId) {
                // Ouvrir la modal
                $('#batteryDetailsModal').modal('show');
                
                // Afficher le chargement
                document.getElementById('battery-details-loading').style.display = 'block';
                document.getElementById('battery-details-content').style.display = 'none';
                document.getElementById('battery-details-error').style.display = 'none';
                
                // Faire la requête AJAX
                fetch(`{{ url('api/batteries') }}/${batteryId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur réseau');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Masquer le chargement
                        document.getElementById('battery-details-loading').style.display = 'none';
                        
                        // Vérifier s'il y a une erreur
                        if (data.error) {
                            document.getElementById('battery-details-error').textContent = data.error;
                            document.getElementById('battery-details-error').style.display = 'block';
                            return;
                        }
                        
                        // Mettre à jour le titre de la modal
                        document.getElementById('batteryDetailsModalLabel').textContent = `Détails de la Batterie #${data.id} (${data.mac_id})`;
                        
                        // Préparer le contenu HTML
                        let html = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Informations générales</h5>
                                    <table class="table table-striped">
                                        <tr>
                                            <th>ID</th>
                                            <td>${data.id}</td>
                                        </tr>
                                        <tr>
                                            <th>MAC ID</th>
                                            <td>${data.mac_id}</td>
                                        </tr>
                                        <tr>
                                            <th>Numéro de série</th>
                                            <td>${data.serial_number || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Stations</th>
                                            <td>${data.stations || 'Aucune'}</td>
                                        </tr>
                                        <tr>
                                            <th>Statut</th>
                                            <td>
                                                <span class="badge ${getBadgeClass(data.status)}">${getStatusLabel(data.status)}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">Informations BMS</h5>
                                    <table class="table table-striped">
                                        <tr>
                                            <th>Niveau de charge (SOC)</th>
                                            <td>${data.bms_data && data.bms_data.SOC ? data.bms_data.SOC + '%' : 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Statut de travail</th>
                                            <td>${data.bms_data && data.bms_data.WorkStatus ? getWorkStatusLabel(data.bms_data.WorkStatus) : 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Tension</th>
                                            <td>${data.bms_data && data.bms_data.Voltage ? data.bms_data.Voltage + 'V' : 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Courant</th>
                                            <td>${data.bms_data && data.bms_data.Current ? data.bms_data.Current + 'A' : 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Dernière communication</th>
                                            <td>${data.last_communication || 'Jamais'}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            ${data.swaps_history ? `
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="mb-3">Historique des swaps récents</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Station</th>
                                                    <th>Montant</th>
                                                    <th>SOC avant</th>
                                                    <th>SOC après</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${data.swaps_history.map(swap => `
                                                <tr>
                                                    <td>${swap.date}</td>
                                                    <td>${swap.station}</td>
                                                    <td>${swap.amount} FCFA</td>
                                                    <td>${swap.soc_before}%</td>
                                                    <td>${swap.soc_after}%</td>
                                                </tr>
                                                `).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                        `;
                        
                        // Mettre à jour le contenu
                        document.getElementById('battery-details-content').innerHTML = html;
                        document.getElementById('battery-details-content').style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        document.getElementById('battery-details-loading').style.display = 'none';
                        document.getElementById('battery-details-error').style.display = 'block';
                    });
            }
            
            // Fonction utilitaires pour les badges et labels
            function getBadgeClass(status) {
                switch (status) {
                    case 'charged': return 'badge-success';
                    case 'charging': return 'badge-info';
                    case 'discharged': return 'badge-danger';
                    case 'inactive': return 'badge-warning';
                    default: return 'badge-secondary';
                }
            }
            
            function getStatusLabel(status) {
                switch (status) {
                    case 'charged': return 'Chargée';
                    case 'charging': return 'En charge';
                    case 'discharged': return 'Déchargée';
                    case 'inactive': return 'Inactive';
                    default: return 'Inconnu';
                }
            }
            
            function getWorkStatusLabel(workStatus) {
                switch (workStatus) {
                    case '0': return 'Décharge';
                    case '1': return 'Charge';
                    case '2': return 'Idle';
                    default: return 'Inconnu';
                }
            }

            // Initialiser les graphiques
            let batteryLevelsChart;
            
            function initBatteryLevelsChart() {
                const ctx = document.getElementById('battery-levels-chart');
                
                batteryLevelsChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Très élevé (>90%)', 'Élevé (>50%)', 'Moyen (>30%)', 'Faible (<30%)', 'Inconnu'],
                        datasets: [{
                            data: [
                                {{ $levelStats['very_high']['count'] }}, 
                                {{ $levelStats['high']['count'] }}, 
                                {{ $levelStats['medium']['count'] }}, 
                                {{ $levelStats['low']['count'] }},
                                {{ $levelStats['unknown']['count'] }}
                            ],
                            backgroundColor: [
                                '#2ecc71',
                                '#27ae60',
                                '#f39c12',
                                '#e74c3c',
                                '#95a5a6'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            datalabels: {
                                formatter: (value, context) => {
                                    const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return percentage > 5 ? `${percentage}%` : '';
                                },
                                color: '#fff',
                                font: {
                                    weight: 'bold',
                                    size: 10
                                }
                            }
                        }
                    }
                });
            }
            
            // Gestion des filtres avec AJAX
            document.addEventListener('DOMContentLoaded', function() {
                // Filtres principaux du dashboard
                document.querySelectorAll('#station-filter, #time-filter, #period-filter').forEach(filter => {
                    filter.addEventListener('change', function() {
                        updateDashboard();
                    });
                });
                
                // Initialiser le graphique de niveaux de batterie
                initBatteryLevelsChart();
                
                // Initialiser les filtres du graphique d'évolution des swaps
                updateChartFiltersVisibility();
                
                // Bouton d'exportation des données du graphique
                const exportSwapChartBtn = document.getElementById('export-swap-chart');
                if (exportSwapChartBtn) {
                    exportSwapChartBtn.addEventListener('click', function() {
                        exportSwapChartData();
                    });
                }
            });
            
            function updateDashboard() {
                // Afficher le spinner de chargement
                document.querySelector('.loading-overlay').style.display = 'flex';
                
                // Récupérer les valeurs des filtres
                const stationFilter = document.getElementById('station-filter').value;
                const timeFilter = document.getElementById('time-filter').value;
                const periodFilter = document.getElementById('period-filter').value;
                
                // Préparer les données pour la requête AJAX
                const data = {
                    station: stationFilter,
                    time_filter: timeFilter,
                    period_filter: periodFilter,
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };
                
                // Envoyer la requête AJAX
                $.ajax({
                    url: "{{ route('dashboard.filter') }}",
                    type: 'GET',
                    data: data,
                    success: function(response) {
                        // Mettre à jour les KPI
                        document.getElementById('charged-count').textContent = response.batterySummary.charged;
                        document.getElementById('charging-count').textContent = response.batterySummary.charging;
                        document.getElementById('discharged-count').textContent = response.batterySummary.discharged;
                        document.getElementById('total-count').textContent = response.batterySummary.total;
                        
                        // Mettre à jour la vue d'ensemble
                        const statsOverview = document.querySelector('.stats-overview');
                        const selectedStationName = stationFilter === 'all' ? 'Toutes les stations' : document.querySelector(`#station-filter option[value="${stationFilter}"]`).textContent.trim();
                        
                        // Vérifier s'il y a des batteries inactives pour afficher ou masquer le bouton
                        const hasInactiveBatteries = response.batterySummary.inactive > 0;
                        
                        statsOverview.innerHTML = `
                            <div class="stats-title">Vue d'ensemble statistique - ${selectedStationName}</div>
                            <div>
                                <p>
                                    <strong>Stock total:</strong> ${response.batterySummary.total} batteries
                                    | <strong>Batteries actives:</strong> ${response.batterySummary.active_total} (${response.batterySummary.total > 0 ? Math.round((response.batterySummary.active_total / response.batterySummary.total) * 100) : 0}%)
                                    | <strong>Batteries inactives:</strong> ${response.batterySummary.inactive} (${response.batterySummary.total > 0 ? Math.round((response.batterySummary.inactive / response.batterySummary.total) * 100) : 0}%)
                                    ${hasInactiveBatteries ? `<button id="show-inactive-batteries" class="btn btn-warning btn-sm ml-2">
                                        <i class="fas fa-list"></i> Voir liste
                                    </button>` : ''}
                                </p>
                                <p>
                                    <strong>Chargées:</strong> <span class="charged">${response.batterySummary.charged}</span>
                                    | <strong>En charge:</strong> <span class="charging">${response.batterySummary.charging}</span>
                                    | <strong>Déchargées:</strong> <span class="discharged">${response.batterySummary.discharged}</span>
                                </p>
                            </div>
                        `;
                        
                        // Réattacher l'événement du bouton des batteries inactives s'il existe
                        if (hasInactiveBatteries) {
                            document.getElementById('show-inactive-batteries').addEventListener('click', function() {
                                $('#inactiveBatteriesModal').modal('show');
                                loadInactiveBatteries();
                            });
                        }
                        
                        // Mettre à jour les niveaux de batterie
                        document.getElementById('very-high-count').textContent = response.batteryLevels.very_high.count;
                        document.getElementById('high-count').textContent = response.batteryLevels.high.count;
                        document.getElementById('medium-count').textContent = response.batteryLevels.medium.count;
                        document.getElementById('low-count').textContent = response.batteryLevels.low.count;
                        document.getElementById('unknown-count').textContent = response.batteryLevels.unknown.count;
                        document.getElementById('total-level-count').textContent = response.batteryLevels.total;
                        
                        // Mettre à jour les barres de progression
                        document.getElementById('very-high-bar').style.width = response.batteryLevels.very_high.percentage + '%';
                        document.getElementById('high-bar').style.width = response.batteryLevels.high.percentage + '%';
                        document.getElementById('medium-bar').style.width = response.batteryLevels.medium.percentage + '%';
                        document.getElementById('low-bar').style.width = response.batteryLevels.low.percentage + '%';
                        document.getElementById('unknown-bar').style.width = response.batteryLevels.unknown.percentage + '%';
                        
                        // Mettre à jour le graphique des niveaux de batterie
                        batteryLevelsChart.data.datasets[0].data = [
                            response.batteryLevels.very_high.count,
                            response.batteryLevels.high.count,
                            response.batteryLevels.medium.count,
                            response.batteryLevels.low.count,
                            response.batteryLevels.unknown.count
                        ];
                        batteryLevelsChart.update();
                        
                        // Mettre à jour les statistiques par station (version tableau)
                        const stationListEl = document.getElementById('station-list');
                        stationListEl.innerHTML = '';
                        
                        response.stationStats.forEach(station => {
                            const activeRate = station.stats.total > 0 
                                ? Math.round(((station.stats.charged + station.stats.charging + station.stats.discharged) / station.stats.total) * 100)
                                : 0;
                            
                            const rateClass = activeRate > 80 ? 'bg-success' 
                                : activeRate > 50 ? 'bg-info'
                                : activeRate > 30 ? 'bg-warning'
                                : 'bg-danger';
                            
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td class="station-name">${station.name}</td>
                                <td class="text-center">
                                    <span class="station-stat-value charged">${station.stats.charged}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value charging">${station.stats.charging}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value discharged">${station.stats.discharged}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value inactive">${station.stats.inactive}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value total">${station.stats.total}</span>
                                </td>
                                <td class="text-right">
                                    <div class="progress" style="height: 5px; width: 80px; display: inline-block; vertical-align: middle; margin-right: 5px;">
                                        <div class="progress-bar ${rateClass}" role="progressbar" style="width: ${activeRate}%"></div>
                                    </div>
                                    <span>${activeRate}%</span>
                                </td>
                            `;
                            stationListEl.appendChild(row);
                        });
                        
                        // Masquer le spinner de chargement
                        document.querySelector('.loading-overlay').style.display = 'none';
                        
                        // Rafraîchir le graphique d'évolution des swaps
                        fetchSwapChart();
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur lors du chargement des données:', error);
                        // Masquer le spinner de chargement
                        document.querySelector('.loading-overlay').style.display = 'none';
                        alert('Une erreur est survenue lors de la mise à jour du dashboard. Veuillez réessayer.');
                    }
                });
            }
            
            // Actualisation automatique toutes les 30 secondes
            setInterval(function() {
                refreshRealTimeData();
            }, 30000);
            
            // Fonction pour rafraîchir uniquement les données en temps réel
            function refreshRealTimeData() {
                // Récupérer la valeur du filtre de station
                const stationFilter = document.getElementById('station-filter').value;
                
                // Préparer les données pour la requête AJAX
                const data = {
                    station: stationFilter,
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };
                
                // Envoyer la requête AJAX
                $.ajax({
                    url: "{{ route('dashboard.refresh') }}",
                    type: 'GET',
                    data: data,
                    success: function(response) {
                        // Mettre à jour les KPI
                        document.getElementById('charged-count').textContent = response.batterySummary.charged;
                        document.getElementById('charging-count').textContent = response.batterySummary.charging;
                        document.getElementById('discharged-count').textContent = response.batterySummary.discharged;
                        document.getElementById('total-count').textContent = response.batterySummary.total;
                        
                        // Mettre à jour la vue d'ensemble
                        const statsOverview = document.querySelector('.stats-overview');
                        const selectedStationName = stationFilter === 'all' ? 'Toutes les stations' : document.querySelector(`#station-filter option[value="${stationFilter}"]`).textContent.trim();
                        
                        // Vérifier s'il y a des batteries inactives pour afficher ou masquer le bouton
                        const hasInactiveBatteries = response.batterySummary.inactive > 0;
                        
                        statsOverview.innerHTML = `
                            <div class="stats-title">Vue d'ensemble statistique - ${selectedStationName}</div>
                            <div>
                                <p>
                                    <strong>Stock total:</strong> ${response.batterySummary.total} batteries
                                    | <strong>Batteries actives:</strong> ${response.batterySummary.active_total} (${response.batterySummary.total > 0 ? Math.round((response.batterySummary.active_total / response.batterySummary.total) * 100) : 0}%)
                                    | <strong>Batteries inactives:</strong> ${response.batterySummary.inactive} (${response.batterySummary.total > 0 ? Math.round((response.batterySummary.inactive / response.batterySummary.total) * 100) : 0}%)
                                    ${hasInactiveBatteries ? `<button id="show-inactive-batteries" class="btn btn-warning btn-sm ml-2">
                                        <i class="fas fa-list"></i> Voir liste
                                    </button>` : ''}
                                </p>
                                <p>
                                    <strong>Chargées:</strong> <span class="charged">${response.batterySummary.charged}</span>
                                    | <strong>En charge:</strong> <span class="charging">${response.batterySummary.charging}</span>
                                    | <strong>Déchargées:</strong> <span class="discharged">${response.batterySummary.discharged}</span>
                                </p>
                            </div>
                        `;
                        
                        // Réattacher l'événement du bouton des batteries inactives s'il existe
                        if (hasInactiveBatteries) {
                            document.getElementById('show-inactive-batteries').addEventListener('click', function() {
                                $('#inactiveBatteriesModal').modal('show');
                                loadInactiveBatteries();
                            });
                        }
                        
                        // Mettre à jour les niveaux de batterie
                        document.getElementById('very-high-count').textContent = response.batteryLevels.very_high.count;
                        document.getElementById('high-count').textContent = response.batteryLevels.high.count;
                        document.getElementById('medium-count').textContent = response.batteryLevels.medium.count;
                        document.getElementById('low-count').textContent = response.batteryLevels.low.count;
                        document.getElementById('unknown-count').textContent = response.batteryLevels.unknown.count;
                        document.getElementById('total-level-count').textContent = response.batteryLevels.total;
                        
                        // Mettre à jour les barres de progression
                        document.getElementById('very-high-bar').style.width = response.batteryLevels.very_high.percentage + '%';
                        document.getElementById('high-bar').style.width = response.batteryLevels.high.percentage + '%';
                        document.getElementById('medium-bar').style.width = response.batteryLevels.medium.percentage + '%';
                        document.getElementById('low-bar').style.width = response.batteryLevels.low.percentage + '%';
                        document.getElementById('unknown-bar').style.width = response.batteryLevels.unknown.percentage + '%';
                        
                        // Mettre à jour le graphique des niveaux de batterie
                        batteryLevelsChart.data.datasets[0].data = [
                            response.batteryLevels.very_high.count,
                            response.batteryLevels.high.count,
                            response.batteryLevels.medium.count,
                            response.batteryLevels.low.count,
                            response.batteryLevels.unknown.count
                        ];
                        batteryLevelsChart.update();
                        
                        // Mettre à jour les statistiques par station (version tableau)
                        const stationListEl = document.getElementById('station-list');
                        stationListEl.innerHTML = '';
                        
                        response.stationStats.forEach(station => {
                            const activeRate = station.stats.total > 0 
                                ? Math.round(((station.stats.charged + station.stats.charging + station.stats.discharged) / station.stats.total) * 100)
                                : 0;
                            
                            const rateClass = activeRate > 80 ? 'bg-success' 
                                : activeRate > 50 ? 'bg-info'
                                : activeRate > 30 ? 'bg-warning'
                                : 'bg-danger';
                            
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td class="station-name">${station.name}</td>
                                <td class="text-center">
                                    <span class="station-stat-value charged">${station.stats.charged}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value charging">${station.stats.charging}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value discharged">${station.stats.discharged}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value inactive">${station.stats.inactive}</span>
                                </td>
                                <td class="text-center">
                                    <span class="station-stat-value total">${station.stats.total}</span>
                                </td>
                                <td class="text-right">
                                    <div class="progress" style="height: 5px; width: 80px; display: inline-block; vertical-align: middle; margin-right: 5px;">
                                        <div class="progress-bar ${rateClass}" role="progressbar" style="width: ${activeRate}%"></div>
                                    </div>
                                    <span>${activeRate}%</span>
                                </td>
                            `;
                            stationListEl.appendChild(row);
                        });
                        
                        // Mettre à jour l'heure de la dernière mise à jour
                        console.log('Données mises à jour à ' + response.lastUpdate);
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur lors du rafraîchissement des données:', error);
                    }
                });
            }

            // Gestion du graphique d'évolution des swaps
            let swapChartInstance;
            let swapChartData = null;

            function renderSwapChart(data) {
                const ctx = document.getElementById('swapEvolutionChart').getContext('2d');
                if (swapChartInstance) swapChartInstance.destroy();
                
                // Stocker les données pour l'exportation
                swapChartData = data;

                swapChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Nombre de Swaps'
                                },
                                position: 'left'
                            },
                            y1: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Montant (FCFA)'
                                },
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Période'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.datasetIndex === 0) {
                                            label += context.parsed.y + ' swaps';
                                        } else {
                                            label += new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FCFA';
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            function fetchSwapChart() {
                // Afficher un indicateur de chargement
                const chartContainer = document.getElementById('swapEvolutionChart').closest('.chart-container');
                chartContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Chargement...</span></div></div>';
                
                const station = document.getElementById('swap-chart-station').value;
                const time_filter = document.getElementById('swap-chart-time').value;
                const year = document.getElementById('swap-chart-year').value;
                const month = document.getElementById('swap-chart-month').value;
                const start = document.getElementById('swap-chart-start').value;
                const end = document.getElementById('swap-chart-end').value;

                const params = {
                    station: station,
                    time_filter: time_filter,
                    year: year,
                    month: month,
                    start: start,
                    end: end
                };

                $.ajax({
                    url: "{{ route('dashboard.swap_evolution') }}",
                    type: 'GET',
                    data: params,
                    success: function(data) {
                        // Recréer le canvas
                        chartContainer.innerHTML = '<canvas id="swapEvolutionChart"></canvas>';
                        renderSwapChart(data);
                    },
                    error: function(xhr) {
                        chartContainer.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement du graphique.</div>';
                        console.error('Erreur:', xhr.responseJSON);
                    }
                });
            }

            function updateChartFiltersVisibility() {
                const type = document.getElementById('swap-chart-time').value;

                // Par défaut, cacher tous les filtres conditionnels
                document.getElementById('swap-chart-year').style.display = 'none';
                document.getElementById('swap-chart-month').style.display = 'none';
                document.getElementById('swap-chart-start').style.display = 'none';
                document.getElementById('swap-chart-end').style.display = 'none';
                
                // Afficher uniquement les filtres pertinents selon le type sélectionné
                switch(type) {
                    case 'day':
                        // Pas de filtre supplémentaire nécessaire pour la semaine courante
                        break;
                    case 'week':
                        document.getElementById('swap-chart-month').style.display = 'inline-block';
                        break;
                    case 'month':
                        document.getElementById('swap-chart-year').style.display = 'inline-block';
                        break;
                    case 'year':
                        // Pas de filtre supplémentaire nécessaire pour l'année
                        break;
                    case 'custom':
                        document.getElementById('swap-chart-start').style.display = 'inline-block';
                        document.getElementById('swap-chart-end').style.display = 'inline-block';
                        break;
                }
            }
            
            // Exporter les données du graphique d'évolution des swaps
            function exportSwapChartData() {
                if (!swapChartData) {
                    alert('Aucune donnée à exporter. Veuillez d\'abord charger le graphique.');
                    return;
                }
                
                // Créer un tableau CSV
                let csvContent = 'data:text/csv;charset=utf-8,';
                
                // Ajouter les en-têtes
                let headers = ['Période', 'Nombre de Swaps', 'Montant (FCFA)'];
                csvContent += headers.join(',') + '\r\n';
                
                // Ajouter les données
                swapChartData.labels.forEach((label, index) => {
                    let row = [
                        label,
                        swapChartData.datasets[0].data[index],
                        swapChartData.datasets[1].data[index]
                    ];
                    csvContent += row.join(',') + '\r\n';
                });
                
                // Créer un lien de téléchargement
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                
                // Nom du fichier basé sur les filtres sélectionnés
                const station = document.getElementById('swap-chart-station').value;
                const stationName = station === 'all' ? 'toutes_stations' : document.querySelector(`#swap-chart-station option[value="${station}"]`).textContent.trim().replace(/\s+/g, '_').toLowerCase();
                const timeFilter = document.getElementById('swap-chart-time').value;
                const now = new Date().toISOString().slice(0, 10);
                
                link.setAttribute('download', `evolution_swaps_${stationName}_${timeFilter}_${now}.csv`);
                
                // Cliquer sur le lien pour télécharger
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // Initialiser les écouteurs d'événements pour les filtres du graphique
            document.addEventListener('DOMContentLoaded', function() {
                const filterIds = [
                    'swap-chart-station', 
                    'swap-chart-time', 
                    'swap-chart-year', 
                    'swap-chart-month', 
                    'swap-chart-start', 
                    'swap-chart-end'
                ];
                
                filterIds.forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.addEventListener('change', function() {
                            updateChartFiltersVisibility();
                        });
                    }
                });
                
                // Bouton d'actualisation du graphique
                const refreshButton = document.getElementById('refresh-swap-chart');
                if (refreshButton) {
                    refreshButton.addEventListener('click', fetchSwapChart);
                }
                
                // Bouton d'exportation du graphique
                const exportButton = document.getElementById('export-swap-chart');
                if (exportButton) {
                    exportButton.addEventListener('click', exportSwapChartData);
                }
                
                // Initialisation des filtres et chargement du graphique
                updateChartFiltersVisibility();
                fetchSwapChart();
            });

            // Exporter des données (fonctionnalité additionnelle)
            function exportTableToExcel(tableID, filename = '') {
                const downloadLink = document.createElement("a");
                const dataType = 'application/vnd.ms-excel';
                const table = document.getElementById(tableID);
                const tableHTML = table.outerHTML.replace(/ /g, '%20');
                
                filename = filename ? filename + '.xls' : 'export_' + new Date().toISOString().slice(0,10) + '.xls';
                
                // Créer le lien de téléchargement
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
                downloadLink.download = filename;
                downloadLink.click();
            }
        </script>
    </div>
</div>
@endsection