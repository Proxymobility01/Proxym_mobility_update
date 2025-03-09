<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app')
<!-- Extension de app.blade.php -->

@section('content')


<div class="main-content">
            <div class="header">
                <h1 class="title">Tableau de bord</h1>
                <div id="date" class="date"></div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-title">Motos actives</div>
                    <div class="stat-value">156</div>
                    <div class="stat-change">+12% ce mois</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Batteries en charge</div>
                    <div class="stat-value">47</div>
                    <div class="stat-change">23 stations actives</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Utilisateurs actifs</div>
                    <div class="stat-value">892</div>
                    <div class="stat-change">+89 nouveaux</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Km parcourus (24h)</div>
                    <div class="stat-value">3,847</div>
                    <div class="stat-change">économie: 769kg CO₂</div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-container">
                    <canvas id="batteryChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="usageChart"></canvas>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID Moto</th>
                        <th>Utilisateur</th>
                        <th>État Batterie</th>
                        <th>Statut</th>
                        <th>Localisation</th>
                        <th>Dernière activité</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>MOTO-2451</td>
                        <td>Sophie Laurent</td>
                        <td>
                            <div class="battery-level">
                                <div class="battery-fill" style="width: 85%"></div>
                            </div>
                            85%
                        </td>
                        <td><span class="status status-active">En service</span></td>
                        <td>Centre-ville</td>
                        <td>Il y a 2 min</td>
                    </tr>
                    <tr>
                        <td>MOTO-2452</td>
                        <td>Marc Dubois</td>
                        <td>
                            <div class="battery-level">
                                <div class="battery-fill" style="width: 23%"></div>
                            </div>
                            23%
                        </td>
                        <td><span class="status status-charging">En charge</span></td>
                        <td>Station Nord</td>
                        <td>Il y a 15 min</td>
                    </tr>
                    <tr>
                        <td>MOTO-2453</td>
                        <td>Non assigné</td>
                        <td>
                            <div class="battery-level">
                                <div class="battery-fill" style="width: 92%"></div>
                            </div>
                            92%
                        </td>
                        <td><span class="status status-inactive">Maintenance</span></td>
                        <td>Dépôt Principal</td>
                        <td>Il y a 1h</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @endsection