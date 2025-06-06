@extends('layouts.app')

@section('content')
<style>
/* ===============================
   Styles pour la page des batteries
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

/* Cartes de statistiques */
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

/* Barre de filtres */
.filter-bar {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    margin-bottom: 25px;
}

.filter-row {
    display: grid;
    grid-template-columns: 1fr 1fr 2fr 1fr 1fr;
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-control {
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s ease;
    background-color: #fff;
}

.form-control:focus {
    outline: none;
    border-color: #DCDB32;
    box-shadow: 0 0 0 3px rgba(220, 219, 50, 0.1);
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary {
    background-color: #DCDB32;
    color: #101010;
}

.btn-primary:hover {
    background-color: #c7c62d;
    transform: translateY(-1px);
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-1px);
}

/* Tableau */
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.table-responsive {
    border-radius: 12px;
    overflow: hidden;
}

.table {
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table thead th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 18px 15px;
    border: none;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody td {
    padding: 15px;
    border-top: 1px solid #e9ecef;
    vertical-align: middle;
    font-size: 14px;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .table-responsive {
        font-size: 12px;
    }
    
    .stat-number {
        font-size: 2rem;
    }
}

@media (max-width: 992px) {
    .filter-row {
        grid-template-columns: 1fr 1fr;
    }
}
</style>



<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>Gestion des Batteries de Station</h2>
        <div class="date" id="date"></div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number">{{ $totalBatteries }}</div>
                <div class="stat-label">Batteries Totales</div>
                <div class="stat-text">Toutes les batteries enregistrées</div>
            </div>
        </div>

        <div class="stat-card agency">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number">{{ $batteriesEnAgence }}</div>
                <div class="stat-label">En Agence</div>
                <div class="stat-text">Batteries affectées à une agence</div>
            </div>
        </div>
        <div class="stat-card agency">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number">{{ $batteriesEnCharge  }}</div>
                <div class="stat-label">En Charge</div>
                <div class="stat-text">Batteries affectées à une agence</div>
            </div>
        </div>


        <div class="stat-card charged">
            <div class="stat-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number">{{ $batteriesChargees }}</div>
                <div class="stat-label">Chargées > 95%</div>
                <div class="stat-text">Prêtes à l'utilisation</div>
            </div>
        </div>

        <div class="stat-card discharged">
            <div class="stat-icon">
                <i class="fas fa-battery-empty"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number">{{ $batteriesDechargees }}</div>
                <div class="stat-label">Déchargées < 30%</div>
                <div class="stat-text">Nécessitent une recharge</div>
            </div>
        </div>

        <div class="stat-card medium">
            <div class="stat-icon">
                <i class="fas fa-battery-half"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number">{{ $batteriesEntre50Et80 }}</div>
                <div class="stat-label">SOC 50-80%</div>
                <div class="stat-text">Charge intermédiaire</div>
            </div>
        </div>
    </div>





    <!-- Barre de filtres -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('batteries.station.index') }}">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="agence_id">Agence</label>
                    <select name="agence_id" id="agence_id" class="form-control">
                        <option value="">-- Toutes les agences --</option>
                        @foreach ($agences as $agence)
                            <option value="{{ $agence->id }}" {{ $selectedAgence == $agence->id ? 'selected' : '' }}>
                                {{ $agence->nom_agence }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="en_agence">Localisation</label>
                    <select name="en_agence" id="en_agence" class="form-control">
                        <option value="">-- Toutes les batteries --</option>
                        <option value="1" {{ $enAgence === "1" ? 'selected' : '' }}>En agence</option>
                        <option value="0" {{ $enAgence === "0" ? 'selected' : '' }}>Hors agence</option>
                    </select>
                </div>
                    <div class="form-group mx-2">
    <select name="filtre" class="form-control">
        <option value="">-- Filtrer par état --</option>
        <option value="en_charge" {{ request('filtre') == 'en_charge' ? 'selected' : '' }}>En charge</option>
        <option value="decharge_faible" {{ request('filtre') == 'decharge_faible' ? 'selected' : '' }}>Décharge faible (&lt; 30%)</option>
        <option value="en_veille" {{ request('filtre') == 'en_veille' ? 'selected' : '' }}>En veille</option>
        <option value="en_ligne" {{ request('filtre') == 'en_ligne' ? 'selected' : '' }}>En ligne</option>
        <option value="hors_ligne" {{ request('filtre') == 'hors_ligne' ? 'selected' : '' }}>Hors ligne</option>
    </select>
</div>

                <div class="filter-group">
                    <label for="search">Recherche</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Recherche MAC ID / ID batterie" value="{{ $search }}">
                </div>

                <div class="filter-group">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>

                <div class="filter-group">
                    <a href="{{ route('batteries.station.index') }}" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Tableau des batteries -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table">
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
                <tbody>
                    @forelse ($batteries as $index => $battery)
                        <tr>
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
                                    <div style="width: 50px; height: 8px; background-color: #e9ecef; border-radius: 4px; overflow: hidden;">
                                        <div style="width: {{ $battery['soc'] }}%; height: 100%; background-color: 
                                            @if($battery['soc'] > 75) #28a745
                                            @elseif($battery['soc'] > 50) #ffc107
                                            @elseif($battery['soc'] > 25) #fd7e14
                                            @else #dc3545
                                            @endif; transition: width 0.3s ease;"></div>
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
});
</script>
@endsection