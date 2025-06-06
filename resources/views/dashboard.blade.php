
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard de Gestion des Stations de Swap</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.min.js"></script>
    <style>
        :root {
            --primary: #DCDB32;
            --secondary: #101010;
            --tertiary: #F3F3F3;
            --background: #ffffff;
            --text: #101010;
            --sidebar: #F8F8F8;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
            --info: #3498db;
            --inactive: #95a5a6;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.15);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--background) 0%, var(--tertiary) 100%);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 600;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, var(--secondary) 0%, #2c2c2c 100%);
            color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .header h1 {
            font-size: clamp(24px, 4vw, 36px);
            margin-bottom: 10px;
            background: linear-gradient(45deg, var(--primary), #f1c40f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header .subtitle {
            opacity: 0.8;
            font-size: 16px;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        select, input {
            padding: 12px 16px;
            border: 2px solid var(--tertiary);
            border-radius: 8px;
            background: white;
            font-size: 14px;
            transition: var(--transition);
            min-width: 180px;
        }

        select:focus, input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(220, 219, 50, 0.1);
        }

        .btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary) 0%, #c4c327 100%);
            color: var(--secondary);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 219, 50, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--tertiary) 0%, #e8e8e8 100%);
            color: var(--secondary);
        }

        .grid {
            display: grid;
            gap: 25px;
            margin-bottom: 30px;
        }

        .grid-4 {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        }

        .grid-full {
            grid-template-columns: 1fr;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), #f1c40f);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-value {
            font-size: clamp(32px, 5vw, 48px);
            font-weight: 700;
            margin: 15px 0;
            font-family: 'Orbitron', sans-serif;
        }

        .card-subtitle {
            font-size: 14px;
            color: var(--inactive);
            margin-bottom: 10px;
        }

        .kpi-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .overview-stats {
            background: linear-gradient(135deg, var(--sidebar) 0%, white 100%);
            border-left: 5px solid var(--primary);
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
        }

        .overview-title {
            font-size: 20px;
            margin-bottom: 15px;
            color: var(--secondary);
        }

        .stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-label {
            font-weight: 600;
        }

        .stat-value {
            font-weight: 700;
            font-family: 'Orbitron', sans-serif;
        }

        .charged { color: var(--success); }
        .charging { color: var(--info); }
        .discharged { color: var(--danger); }
        .inactive { color: var(--inactive); }
        .not-charging { color: var(--warning); }

        .battery-level-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--tertiary);
        }

        .battery-level-item:last-child {
            border-bottom: none;
        }

        .level-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .level-name {
            font-weight: 600;
            font-size: 14px;
        }

        .charging-status {
            font-size: 12px;
            color: var(--inactive);
        }

        .level-stats {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
        }

        .level-count {
            font-weight: 700;
            font-size: 18px;
            font-family: 'Orbitron', sans-serif;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }

        .doughnut-container {
            position: relative;
            height: 250px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .station-table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .station-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px; /* Largeur minimale pour forcer le scroll horizontal */
        }

        .station-table th,
        .station-table td {
            padding: 12px 8px;
            text-align: center;
            border-bottom: 1px solid var(--tertiary);
            white-space: nowrap;
        }

        .station-table th:first-child,
        .station-table td:first-child {
            text-align: left;
            position: sticky;
            left: 0;
            background: white;
            z-index: 10;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            min-width: 140px;
        }

        .station-table th {
            background: var(--sidebar);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 15;
        }

        .station-table th:first-child {
            background: var(--sidebar);
            z-index: 20;
        }

        .station-table tr:hover {
            background: var(--tertiary);
        }

        .station-table tr:hover td:first-child {
            background: var(--tertiary);
        }

        .paliers-badges {
            display: flex;
            gap: 3px;
            justify-content: center;
            flex-wrap: nowrap;
            min-width: 160px;
        }

        .paliers-badges .badge {
            padding: 2px 6px;
            font-size: 10px;
            min-width: 20px;
            border-radius: 3px;
        }

        .station-name {
            font-weight: 600;
            font-family: 'Orbitron', sans-serif;
        }

        .progress-bar {
            width: 80px;
            height: 6px;
            background: var(--tertiary);
            border-radius: 3px;
            overflow: hidden;
            margin: 5px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--success), var(--primary));
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success { background: var(--success); color: white; }
        .badge-warning { background: var(--warning); color: white; }
        .badge-danger { background: var(--danger); color: white; }
        .badge-info { background: var(--info); color: white; }

        @media (max-width: 1400px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 1600px) {
            /* Layout spécial pour très grands écrans */
            .large-screen-layout {
                display: grid;
                grid-template-columns: 1fr 2fr;
                gap: 30px;
                align-items: start;
            }
            
            .battery-levels-large {
                position: sticky;
                top: 20px;
            }
            
            .station-table-large {
                max-height: 600px;
                overflow-y: auto;
            }
            
            .container {
                max-width: 95%;
            }
            
            .grid-4 {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (min-width: 2560px) {
            /* Layout pour écrans 4K et 50" */
            :root {
                font-size: 18px;
            }
            
            .container {
                max-width: 90%;
                padding: 40px;
            }
            
            .card {
                padding: 35px;
            }
            
            .card-value {
                font-size: clamp(48px, 6vw, 72px);
            }
            
            .overview-stats {
                padding: 35px;
            }
            
            .grid {
                gap: 35px;
            }
            
            .station-table th,
            .station-table td {
                padding: 16px 12px;
                font-size: 14px;
            }
            
            .paliers-badges .badge {
                padding: 4px 8px;
                font-size: 12px;
            }
        }

        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            select, input {
                min-width: 100%;
            }
            
            .card {
                padding: 20px;
            }
            
            .stats-row {
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: slideInUp 0.6s ease-out;
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--tertiary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #c4c327;
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <div class="container">
        <!-- Gestion d'erreurs -->
        @if(isset($error))
        <div class="alert alert-danger text-center">
            <h4 class="mb-3">❌ Une erreur est survenue</h4>
            <p>{{ $error }}</p>
            <a href="{{ route('dashboard.index') }}" class="btn btn-primary mt-3">
                Retour au dashboard
            </a>
        </div>
        @endif

        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-bolt"></i> Dashboard Gestion Stations de Swap</h1>
            <div class="subtitle">Surveillance en temps réel des batteries de motos électriques</div>
        </div>

        <!-- Filtres -->
        <form id="filter-form" method="GET" action="{{ route('dashboard.filter') }}">
            <div class="filters">
                <div class="filter-group">
                    <label>Station</label>
                    <select id="stationFilter" name="station">
                        <option value="all" {{ $selectedStation == 'all' ? 'selected' : '' }}>Toutes les stations</option>
                        @foreach($stations as $station)
                            <option value="{{ $station->id_agence }}" {{ $selectedStation == $station->id_agence ? 'selected' : '' }}>
                                {{ $station->nom_agence }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Période</label>
                    <select id="timeFilter" name="time_filter">
                        <option value="day" {{ $timeFilter == 'day' ? 'selected' : '' }}>Par jour</option>
                        <option value="week" {{ $timeFilter == 'week' ? 'selected' : '' }}>Par semaine</option>
                        <option value="month" {{ $timeFilter == 'month' ? 'selected' : '' }}>Par mois</option>
                        <option value="year" {{ $timeFilter == 'year' ? 'selected' : '' }}>Par année</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Filtre période</label>
                    <select id="periodFilter" name="period_filter">
                        <option value="current" {{ $periodFilter == 'current' ? 'selected' : '' }}>Période actuelle</option>
                        <option value="previous" {{ $periodFilter == 'previous' ? 'selected' : '' }}>Période précédente</option>
                        <option value="last3" {{ $periodFilter == 'last3' ? 'selected' : '' }}>3 dernières périodes</option>
                        <option value="last6" {{ $periodFilter == 'last6' ? 'selected' : '' }}>6 dernières périodes</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                </div>
            </div>
        </form>

        <!-- Vue d'ensemble statistique -->
        <div class="overview-stats">
            <h3 class="overview-title">Vue d'ensemble statistique - 
                <span id="selectedStationName">
                    @if($selectedStation == 'all')
                        Toutes les stations
                    @else
                        {{ $stations->firstWhere('id_agence', $selectedStation)->nom_agence ?? 'Station inconnue' }}
                    @endif
                </span>
            </h3>
            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-label">Stock total:</span>
                    <span class="stat-value" id="totalBatteries">{{ $summaryStats['total'] }}</span>
                    <span>batteries</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Batteries actives:</span>
                    <span class="stat-value" id="activeBatteries">{{ $summaryStats['active_total'] }}</span>
                    <span>(<span id="activePercentage">{{ $summaryStats['total'] > 0 ? round(($summaryStats['active_total'] / $summaryStats['total']) * 100) : 0 }}</span>%)</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Batteries inactives:</span>
                    <span class="stat-value inactive" id="inactiveBatteries">{{ $summaryStats['inactive'] }}</span>
                    <span>(<span id="inactivePercentage">{{ $summaryStats['total'] > 0 ? round(($summaryStats['inactive'] / $summaryStats['total']) * 100) : 0 }}</span>%)</span>
                </div>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-label">Chargées:</span>
                    <span class="stat-value charged" id="chargedBatteries">{{ $summaryStats['charged'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">En charge:</span>
                    <span class="stat-value charging" id="chargingBatteries">{{ $summaryStats['charging'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Déchargées:</span>
                    <span class="stat-value discharged" id="dischargedBatteries">{{ $summaryStats['discharged'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Pas en charge:</span>
                    <span class="stat-value not-charging" id="notChargingBatteries">{{ $summaryStats['not_charging'] }}</span>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-section">
            <div class="grid grid-4">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Batteries Chargées</div>
                            <div class="card-subtitle">SOC ≥ 95%</div>
                        </div>
                        <div class="kpi-icon" style="background: var(--success);">
                            <i class="fas fa-battery-full"></i>
                        </div>
                    </div>
                    <div class="card-value charged" id="kpiCharged">{{ $summaryStats['charged'] }}</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">En Charge</div>
                            <div class="card-subtitle">En cours de charge</div>
                        </div>
                        <div class="kpi-icon" style="background: var(--info);">
                            <i class="fas fa-charging-station"></i>
                        </div>
                    </div>
                    <div class="card-value charging" id="kpiCharging">{{ $summaryStats['charging'] }}</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Déchargées</div>
                            <div class="card-subtitle">Nécessitent une charge</div>
                        </div>
                        <div class="kpi-icon" style="background: var(--danger);">
                            <i class="fas fa-battery-empty"></i>
                        </div>
                    </div>
                    <div class="card-value discharged" id="kpiDischarged">{{ $summaryStats['discharged'] }}</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Pas en Charge</div>
                            <div class="card-subtitle">Disponibles non connectées</div>
                        </div>
                        <div class="kpi-icon" style="background: var(--warning);">
                            <i class="fas fa-plug"></i>
                        </div>
                    </div>
                    <div class="card-value not-charging" id="kpiNotCharging">{{ $summaryStats['not_charging'] }}</div>
                </div>
            </div>
        </div>

        <!-- Main content layout -->
        <div id="mainContentLayout" class="grid grid-2">
            <!-- Répartition par niveau de charge -->
            <div class="levels-section">
                <div class="card battery-levels-large">
                    <div class="card-header">
                        <div class="card-title">Répartition par Niveau de Charge</div>
                    </div>
                    
                    <div id="batteryLevels">
                        <div class="battery-level-item">
                            <div class="level-info">
                                <div class="level-name">90-100% (Chargées)</div>
                                <div class="charging-status">En charge: <span class="charging">{{ $levelStats['very_high']['charging'] }}</span> | Pas en charge: <span class="not-charging">{{ $levelStats['very_high']['not_charging'] }}</span></div>
                            </div>
                            <div class="level-stats">
                                <div class="level-count charged">{{ $levelStats['very_high']['count'] }}</div>
                                <span class="badge badge-success">{{ $levelStats['very_high']['percentage'] }}%</span>
                            </div>
                        </div>
                        
                        <div class="battery-level-item">
                            <div class="level-info">
                                <div class="level-name">70-90% (Élevé)</div>
                                <div class="charging-status">En charge: <span class="charging">{{ $levelStats['high']['charging'] }}</span> | Pas en charge: <span class="not-charging">{{ $levelStats['high']['not_charging'] }}</span></div>
                            </div>
                            <div class="level-stats">
                                <div class="level-count">{{ $levelStats['high']['count'] }}</div>
                                <span class="badge badge-info">{{ $levelStats['high']['percentage'] }}%</span>
                            </div>
                        </div>
                        
                        <div class="battery-level-item">
                            <div class="level-info">
                                <div class="level-name">40-70% (Moyen)</div>
                                <div class="charging-status">En charge: <span class="charging">{{ $levelStats['medium']['charging'] }}</span> | Pas en charge: <span class="not-charging">{{ $levelStats['medium']['not_charging'] }}</span></div>
                            </div>
                            <div class="level-stats">
                                <div class="level-count">{{ $levelStats['medium']['count'] }}</div>
                                <span class="badge badge-warning">{{ $levelStats['medium']['percentage'] }}%</span>
                            </div>
                        </div>
                        
                        <div class="battery-level-item">
                            <div class="level-info">
                                <div class="level-name">10-40% (Faible)</div>
                                <div class="charging-status">En charge: <span class="charging">{{ $levelStats['low']['charging'] }}</span> | Pas en charge: <span class="not-charging">{{ $levelStats['low']['not_charging'] }}</span></div>
                            </div>
                            <div class="level-stats">
                                <div class="level-count">{{ $levelStats['low']['count'] }}</div>
                                <span class="badge badge-danger">{{ $levelStats['low']['percentage'] }}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="doughnut-container">
                        <canvas id="batteryLevelsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- État par station -->
            <div class="stations-section">
                <div class="card station-table-large">
                    <div class="card-header">
                        <div class="card-title">État des Batteries par Station</div>
                        <button class="btn btn-secondary" onclick="exportStationData()">
                            <i class="fas fa-file-excel"></i> Exporter
                        </button>
                    </div>
                    
                    <div class="station-table-container">
                        <table class="station-table" id="stationTable">
                            <thead>
                                <tr>
                                    <th>Station</th>
                                    <th>Chargées</th>
                                    <th>En charge</th>
                                    <th>Déchargées</th>
                                    <th>Inactives</th>
                                    <th>Total</th>
                                    <th>
                                        <div class="paliers-column">
                                            <div class="palier-label">Paliers de Charge</div>
                                            <div style="display: flex; gap: 2px; font-size: 8px;">
                                                <span>90-100</span>
                                                <span>70-90</span>
                                                <span>40-70</span>
                                                <span>10-40</span>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stationStats as $station)
                                <tr>
                                    <td class="station-name">{{ $station['name'] }}</td>
                                    <td class="charged">{{ $station['stats']['charged'] }}</td>
                                    <td class="charging">{{ $station['stats']['charging'] }}</td>
                                    <td class="discharged">{{ $station['stats']['discharged'] }}</td>
                                    <td class="inactive">{{ $station['stats']['inactive'] }}</td>
                                    <td><strong>{{ $station['stats']['total'] }}</strong></td>
                                    <td>
                                        <div class="paliers-badges">
                                            <span class="badge badge-success">{{ $station['paliers']['90-100'] }}</span>
                                            <span class="badge badge-info">{{ $station['paliers']['70-90'] }}</span>
                                            <span class="badge badge-warning">{{ $station['paliers']['40-70'] }}</span>
                                            <span class="badge badge-danger">{{ $station['paliers']['10-40'] }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Évolution des Swaps -->
        <div class="swaps-section">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Évolution des Swaps & Montants Générés</div>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn" onclick="refreshSwapChart()">
                            <i class="fas fa-sync"></i> Actualiser
                        </button>
                        <button class="btn btn-secondary" onclick="exportSwapData()">
                            <i class="fas fa-file-excel"></i> Exporter données
                        </button>
                    </div>
                </div>
                
                <div class="filters" style="margin: 20px 0;">
                    <div class="filter-group">
                        <label>Station Swap</label>
                        <select id="swapStationFilter">
                            <option value="all">Toutes les stations</option>
                            @foreach($stations as $station)
                                <option value="{{ $station->id_agence }}">{{ $station->nom_agence }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Vue</label>
                        <select id="swapTimeFilter">
                            <option value="day">Jour (semaine courante)</option>
                            <option value="week">Semaines (du mois)</option>
                            <option value="month" selected>Mois (de l'année)</option>
                            <option value="year">Années</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Année</label>
                        <select id="swapYearFilter">
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="swapEvolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration et données passées du backend
        const dashboardConfig = {
            csrfToken: '{{ csrf_token() }}',
            routes: {
                filter: '{{ route("dashboard.filter") }}',
                inactiveBatteries: '{{ route("dashboard.inactive-batteries") }}',
                index: '{{ route("dashboard.index") }}'
            },
            selectedStation: '{{ $selectedStation }}',
            timeFilter: '{{ $timeFilter }}',
            periodFilter: '{{ $periodFilter }}'
        };

        // Données du backend converties en JavaScript
        const backendData = {
            summaryStats: @json($summaryStats),
            levelStats: @json($levelStats),
            stationStats: @json($stationStats),
            swapChart: @json($swapChart),
            stations: @json($stations),
            filteredData: @json($filteredData ?? null)
        };

        // Variables globales pour les graphiques
        let batteryLevelsChart;
        let swapChart;

        // Fonction pour mettre à jour l'affichage selon les données du backend ou filtrées
        function updateDisplay(useFilteredData = false) {
            const data = useFilteredData && backendData.filteredData ? 
                backendData.filteredData : backendData;
            
            if (useFilteredData && backendData.filteredData) {
                // Utiliser les données filtrées
                updateDisplayWithData(backendData.filteredData.summary_stats, backendData.filteredData.level_stats);
                document.getElementById('selectedStationName').textContent = backendData.filteredData.station_name;
            } else {
                // Utiliser les données globales
                updateDisplayWithData(backendData.summaryStats, backendData.levelStats);
                updateStationName();
            }
            
            // Mise à jour des graphiques
            updateBatteryLevelsChart();
        }

        function updateDisplayWithData(summaryStats, levelStats) {
            // Mise à jour des statistiques générales
            document.getElementById('totalBatteries').textContent = summaryStats.total;
            document.getElementById('activeBatteries').textContent = summaryStats.active_total;
            document.getElementById('inactiveBatteries').textContent = summaryStats.inactive;
            document.getElementById('chargedBatteries').textContent = summaryStats.charged;
            document.getElementById('chargingBatteries').textContent = summaryStats.charging;
            document.getElementById('dischargedBatteries').textContent = summaryStats.discharged;
            document.getElementById('notChargingBatteries').textContent = summaryStats.not_charging;
            
            // Calcul des pourcentages
            const activePercentage = summaryStats.total > 0 ? 
                Math.round((summaryStats.active_total / summaryStats.total) * 100) : 0;
            const inactivePercentage = 100 - activePercentage;
            document.getElementById('activePercentage').textContent = activePercentage;
            document.getElementById('inactivePercentage').textContent = inactivePercentage;
            
            // Mise à jour des KPI
            document.getElementById('kpiCharged').textContent = summaryStats.charged;
            document.getElementById('kpiCharging').textContent = summaryStats.charging;
            document.getElementById('kpiDischarged').textContent = summaryStats.discharged;
            document.getElementById('kpiNotCharging').textContent = summaryStats.not_charging;
            
            // Mise à jour de la répartition par niveaux
            updateBatteryLevelsDisplay(levelStats);
        }

        function updateStationName() {
            const selectedStation = dashboardConfig.selectedStation;
            if (selectedStation === 'all') {
                document.getElementById('selectedStationName').textContent = 'Toutes les stations';
            } else {
                const station = backendData.stations.find(s => s.id_agence == selectedStation);
                document.getElementById('selectedStationName').textContent = station ? station.nom_agence : 'Station inconnue';
            }
        }

        function updateBatteryLevelsDisplay(levelStats) {
            const container = document.getElementById('batteryLevels');
            const levelMapping = {
                'very_high': { name: '90-100% (Chargées)', class: 'badge-success' },
                'high': { name: '70-90% (Élevé)', class: 'badge-info' },
                'medium': { name: '40-70% (Moyen)', class: 'badge-warning' },
                'low': { name: '10-40% (Faible)', class: 'badge-danger' }
            };
            
            container.innerHTML = '';
            
            Object.entries(levelMapping).forEach(([key, config]) => {
                const level = levelStats[key];
                const item = document.createElement('div');
                item.className = 'battery-level-item';
                item.innerHTML = `
                    <div class="level-info">
                        <div class="level-name">${config.name}</div>
                        <div class="charging-status">En charge: <span class="charging">${level.charging}</span> | Pas en charge: <span class="not-charging">${level.not_charging}</span></div>
                    </div>
                    <div class="level-stats">
                        <div class="level-count">${level.count}</div>
                        <span class="badge ${config.class}">${level.percentage}%</span>
                    </div>
                `;
                container.appendChild(item);
            });
        }

        function updateBatteryLevelsChart() {
            const ctx = document.getElementById('batteryLevelsChart').getContext('2d');
            
            if (batteryLevelsChart) {
                batteryLevelsChart.destroy();
            }
            
            const levelStats = backendData.filteredData ? 
                backendData.filteredData.level_stats : backendData.levelStats;
            
            const data = {
                labels: ['90-100%', '70-90%', '40-70%', '10-40%'],
                datasets: [{
                    data: [
                        levelStats.very_high.count,
                        levelStats.high.count,
                        levelStats.medium.count,
                        levelStats.low.count
                    ],
                    backgroundColor: [
                        '#2ecc71',
                        '#3498db', 
                        '#f39c12',
                        '#e74c3c'
                    ],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            };
            
            batteryLevelsChart = new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label;
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} batteries (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        function initSwapChart() {
            const ctx = document.getElementById('swapEvolutionChart').getContext('2d');
            
            swapChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: backendData.swapChart.labels,
                    datasets: [
                        {
                            label: 'Nombre de Swaps',
                            data: backendData.swapChart.swaps,
                            backgroundColor: 'rgba(220, 219, 50, 0.8)',
                            borderColor: '#DCDB32',
                            borderWidth: 2,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Montant (FCFA)',
                            data: backendData.swapChart.amounts,
                            type: 'line',
                            borderColor: '#2ecc71',
                            backgroundColor: 'rgba(46, 204, 113, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
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
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Nombre de Swaps'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Montant (FCFA)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('fr-FR').format(value);
                                }
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
                                    const label = context.dataset.label;
                                    const value = context.parsed.y;
                                    if (label === 'Montant (FCFA)') {
                                        return `${label}: ${new Intl.NumberFormat('fr-FR').format(value)} FCFA`;
                                    }
                                    return `${label}: ${value}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Fonction pour rafraîchir le graphique des swaps
        function refreshSwapChart() {
            showLoading(true);
            const station = document.getElementById('swapStationFilter').value;
            const timeFilter = document.getElementById('swapTimeFilter').value;
            const year = document.getElementById('swapYearFilter').value;
            
            // Ici vous pouvez faire un appel AJAX pour récupérer de nouvelles données
            // Pour l'instant, on simule
            setTimeout(() => {
                showLoading(false);
                console.log('Graphique actualisé pour:', { station, timeFilter, year });
            }, 1000);
        }

        // Fonctions d'export
        function exportStationData() {
            console.log('Export des données des stations...');
            // Implémenter l'export Excel
        }

        function exportSwapData() {
            console.log('Export des données des swaps...');
            // Implémenter l'export Excel
        }

        // Fonction pour afficher/masquer le loading
        function showLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = show ? 'flex' : 'none';
        }

        // Fonction pour ajuster le layout selon la taille d'écran
        function adjustLayoutForScreenSize() {
            const mainContentLayout = document.getElementById('mainContentLayout');
            
            if (window.innerWidth >= 1920) {
                mainContentLayout.className = 'large-screen-main-layout';
                document.body.classList.add('ultra-wide-layout');
            } else if (window.innerWidth >= 1440) {
                mainContentLayout.className = 'large-screen-main-layout';
                document.body.classList.remove('ultra-wide-layout');
            } else {
                mainContentLayout.className = 'grid grid-2';
                document.body.classList.remove('ultra-wide-layout');
            }
            
            // Ajustements de police selon la taille
            if (window.innerWidth >= 3840) {
                document.documentElement.style.fontSize = '18px';
                document.querySelector('.container').style.maxWidth = '95%';
            } else if (window.innerWidth >= 2560) {
                document.documentElement.style.fontSize = '16px';
                document.querySelector('.container').style.maxWidth = '95%';
            } else if (window.innerWidth >= 1920) {
                document.documentElement.style.fontSize = '15px';
                document.querySelector('.container').style.maxWidth = '98%';
            } else {
                document.documentElement.style.fontSize = '14px';
                document.querySelector('.container').style.maxWidth = '1400px';
            }
        }

        // Gestionnaires d'événements
        document.getElementById('stationFilter').addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });

        window.addEventListener('resize', adjustLayoutForScreenSize);
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            updateDisplay();
            initSwapChart();
            adjustLayoutForScreenSize();
            
            // Animation d'entrée pour les cartes
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            console.log('Dashboard initialisé avec les données:', backendData);
        });
    </script>
</body>
</html>
