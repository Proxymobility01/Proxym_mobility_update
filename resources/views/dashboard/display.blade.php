<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROXYM Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard_style.css') }}">
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">⚡</div>
                <div>
                    <div class="logo-text">PROXYM</div>
                    <div class="logo-subtitle">BATTERY MANAGEMENT</div>
                </div>
            </div>
            <div class="status-time">
                <div class="status">
                    <div class="status-dot"></div>
                    <span>Status</span>
                    <strong>ONLINE</strong>
                </div>
                <div class="time-container">
                    <div class="time" id="current-time">11:31:32</div>
                    <div class="date">Jun 14, 2025</div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="stats-container">
                <div class="stats-set active">
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-value">{{ $monthlySwapCount }}</div>
                        <div class="stat-label">Swaps</div>
                        <div style="font-size: 20px; color: #00d4ff; margin-top: 5px;">Ce Mois</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value">{{ $chauffeursCount }}</div>
                        <div class="stat-label">Chauffeurs</div>
                        <div style="font-size: 20px; color: #4fc3f7; margin-top: 5px;">Chauffeurs Actifs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📍</div>
                        <div class="stat-value">{{ $totalDistance }} km</div>
                        <div class="stat-label">Distance</div>
                        <div style="font-size: 20px; color: #ffd700; margin-top: 5px;">Distance Parcourue</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-value">N/A</div>
                        <div class="stat-label">Montant Total Swaps</div>
                        <div style="font-size: 20px; color: #ff6b35; margin-top: 5px;">Ce Mois</div>
                    </div>
                </div>
                
                <div class="stats-set">
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-value">{{ $summaryStats['charged'] }}</div>
                        <div class="stat-label">Batteries Chargées</div>
                        <div style="font-size: 20px; color: #00d4ff; margin-top: 5px;">Toute la Flotte</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value">{{ $summaryStats['charging'] }}</div>
                        <div class="stat-label">En Charge</div>
                        <div style="font-size: 20px; color: #4fc3f7; margin-top: 5px;">Toute la Flotte</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📍</div>
                        <div class="stat-value">{{ $summaryStats['inactive'] }}</div>
                        <div class="stat-label">Batteries Offline</div>
                        <div style="font-size: 20px; color: #ffd700; margin-top: 5px;">Toute la Fotte</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-value">{{ $summaryStats['total'] }}</div>
                        <div class="stat-label">Total Batteries</div>
                        <div style="font-size: 20px; color: #ff6b35; margin-top: 5px;">Toute la Flotte</div>
                    </div>
                </div>
                
                <div class="stats-set">
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-value">{{ $levelStats['very_high']['count'] }}</div>
                        <div class="stat-label">90% - 100% </div>
                        <div style="font-size: 20px; color: #00d4ff; margin-top: 5px;">Toute la Flotte</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value">{{ $levelStats['high']['count'] }}</div>
                        <div class="stat-label">70% - 90%</div>
                        <div style="font-size: 20px; color: #4fc3f7; margin-top: 5px;">Toute la Flotte</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📍</div>
                        <div class="stat-value">{{ $levelStats['medium']['count'] }}</div>
                        <div class="stat-label">40% - 70%</div>
                        <div style="font-size: 20px; color: #ffd700; margin-top: 5px;">Toute la Flotte</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-value">{{ $levelStats['low']['count'] }}</div>
                        <div class="stat-label">10% - 40%</div>
                        <div style="font-size: 20px; color: #f5f2f1; margin-top: 5px;">Toute la Flotte</div>
                    </div>
                </div>
            </div>

            <div class="map-section">
    <div class="map-header">
        <div class="location-icon">📍</div>
        <strong>Carte des Stations</strong>
    </div>
    <div id="map" style="height: 100%; width: 100%; border-radius: 20px; overflow: hidden;"></div>
</div>
        </div>

        <div class="sidebar">
            <div class="station-overview">
              @foreach($stations as $index => $station)
    <div class="station-content {{ $index === 0 ? 'active' : '' }}">
        <div class="agency-header">
            <div class="agency-title">
                <div class="agency-icon">🏢</div>
                <strong>Agency Overview</strong>
            </div>
            <div class="online-badge">Online</div>
        </div>
        <div class="agency-info">
            <div class="agency-name">{{ $station['nom'] }}</div>
            <div class="agency-location">{{ $station['ville'] }} • {{ $station['batteries_total'] }} Batteries Total , <span>Batterie en Charge : {{ $station['batteries_en_charge'] }}</span></div>
        </div>

        <div class="battery-header">
            <div class="battery-icon">🔋</div>
            <strong>BATTERY STATUS</strong>
        </div>
        <div class="battery-levels">
            <div class="battery-level">
                <span>Paliers</span>
                <div class="battery-on-charge-title">En Charge</div>
                <div class="battery-count">Total</div>
            </div>
            <div class="battery-level">
                <span>90% - 100%</span>
                <div class="battery-on-charge">{{ $station['levels']['very_high']['charging'] }}</div>
                <div class="battery-count">{{ $station['levels']['very_high']['count'] }}</div>
            </div>
            <div class="battery-level">
                <span>70% - 90%</span>
                <div class="battery-on-charge">{{ $station['levels']['high']['charging'] }}</div>
                <div class="battery-count">{{ $station['levels']['high']['count'] }}</div>
            </div>
            <div class="battery-level">
                <span>40% - 70%</span>
                <div class="battery-on-charge">{{ $station['levels']['medium']['charging'] }}</div>
                <div class="battery-count">{{ $station['levels']['medium']['count'] }}</div>
            </div>
            <div class="battery-level">
                <span>10 - 40%</span>
                <div class="battery-on-charge">{{ $station['levels']['low']['charging'] }}</div>
                <div class="battery-count">{{ $station['levels']['low']['count'] }}</div>
            </div>
            <div class="battery-level">
                <span>< 10%</span>
                <div class="battery-on-charge">{{ $station['levels']['critical']['charging'] }}</div>
                <div class="battery-count">{{ $station['levels']['critical']['count'] }}</div>
            </div>
        </div>

        <div class="compteurs-container">
            <div class="compteurs-set active">
                <div class="compteurs-set">
                    @foreach($station['compteurs'] as $key => $valeur)
                        <div class="compteur-card">
                            <div class="compteur-value">{{ $valeur }}</div>
                            <div class="compteur-label">Compteur {{ $key + 1 }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endforeach

                </div>
            </div>
        </div>
    </div>

    <div class="redmi-watermark">Flotte Batteries, Motos, Stations</div>
<script>
    function initMap() {
        const centerCoords = { lat: 4.04343000, lng: 9.74368000 };
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: centerCoords,
            styles: [
                // Masquer tous les points d'intérêt
                { featureType: "poi", stylers: [{ visibility: "off" }] },
                // Masquer les transports en commun
                { featureType: "transit", stylers: [{ visibility: "off" }] },
                // Masquer les labels des routes
                { featureType: "road", elementType: "labels", stylers: [{ visibility: "off" }] },
                // Masquer les commerces
                { featureType: "establishment", stylers: [{ visibility: "off" }] },
                // Masquer les parcs
                { featureType: "landscape.man_made", stylers: [{ visibility: "off" }] },
                // Masquer les labels administratifs
                { featureType: "administrative", elementType: "labels", stylers: [{ visibility: "off" }] },
                // Garder seulement les routes et quartiers de base
                { featureType: "road", elementType: "geometry", stylers: [{ visibility: "on" }] },
                { featureType: "landscape", elementType: "geometry", stylers: [{ visibility: "on" }] },
                { featureType: "water", stylers: [{ visibility: "on" }] }
            ]
        });

        const stations = @json($stationsMap);
        const batteries = @json($batteriesWithLocation);
        const motos = @json($motosWithLocation);

        // Icônes personnalisées avec couleurs
        const icons = {
            station: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
  <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
</svg>
                `),
                scaledSize: new google.maps.Size(32, 32)
            },
            battery: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                   <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-battery-charging" viewBox="0 0 16 16">
                        <path d="M9.585 2.568a.5.5 0 0 1 .226.58L8.677 6.832h1.99a.5.5 0 0 1 .364.843l-5.334 5.667a.5.5 0 0 1-.842-.49L5.99 9.167H4a.5.5 0 0 1-.364-.843l5.333-5.667a.5.5 0 0 1 .616-.09z"/>
                        <path d="M2 4h4.332l-.94 1H2a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h2.38l-.308 1H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2"/>
                        <path d="M2 6h2.45L2.908 7.639A1.5 1.5 0 0 0 3.313 10H2zm8.595-2-.308 1H12a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H9.276l-.942 1H12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/>
                        <path d="M12 10h-1.783l1.542-1.639q.146-.156.241-.34zm0-3.354V6h-.646a1.5 1.5 0 0 1 .646.646M16 8a1.5 1.5 0 0 1-1.5 1.5v-3A1.5 1.5 0 0 1 16 8"/>
                    
                    </svg>
                `),
                scaledSize: new google.maps.Size(26, 26)
            },
            moto: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="5" cy="19" r="3" fill="#FFD700" stroke="#000000" stroke-width="2"/>
                        <circle cx="19" cy="19" r="3" fill="#FFD700" stroke="#000000" stroke-width="2"/>
                        <path d="M8 19l3-6m-6 0h10l3 2m-8 0L14 7l3 4h4" stroke="#FFD700" stroke-width="3" fill="none"/>
                        <path d="M12 4l-1 2m2 0l1-2m-2 2v2" stroke="#FFD700" stroke-width="2" fill="none"/>
                        <circle cx="12" cy="4" r="1" fill="#FFD700"/>
                        <rect x="10" y="8" width="4" height="2" rx="1" fill="#FFD700"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(40, 40)
            }
        };

        // 🏠 STATIONS (Icône bleue)
        stations.forEach(station => {
            if (station.latitude && station.longitude) {
                new google.maps.Marker({
                    position: { lat: parseFloat(station.latitude), lng: parseFloat(station.longitude) },
                    map,
                    title: station.nom,
                    icon: icons.station
                });
            }
        });

        // 🔋 BATTERIES (Icône verte)
        batteries.forEach(battery => {
            if (battery.latitude && battery.longitude) {
                new google.maps.Marker({
                    position: { lat: parseFloat(battery.latitude), lng: parseFloat(battery.longitude) },
                    map,
                    title: `Batterie ${battery.mac_id}`,
                    icon: icons.battery
                });
            }
        });

        // 🏍️ MOTOS (Icône jaune)
        motos.forEach(moto => {
            if (moto.latitude && moto.longitude) {
                new google.maps.Marker({
                    position: { lat: parseFloat(moto.latitude), lng: parseFloat(moto.longitude) },
                    map,
                    title: moto.driverInfo || moto.macid,
                    icon: icons.moto
                });
            }
        });
    }











    let lastSnapshot = null;

function fetchAndUpdateDashboard() {
    fetch('/dashboard/full-data')
        .then(res => res.json())
        .then(data => {
            const dataHash = JSON.stringify(data);

            if (dataHash !== lastSnapshot) {
                lastSnapshot = dataHash;
                updateStatsUI(data);
                updateMapUI(data);
                updateSidebarUI(data);
                console.log('✔️ Données mises à jour');
            } else {
                console.log('⏸️ Pas de changement détecté');
            }
        })
        .catch(error => {
            console.error('Erreur de récupération des données:', error);
        });
}

setInterval(fetchAndUpdateDashboard, 10000); // vérifie toutes les 10s

</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBn88TP5X-xaRCYo5gYxvGnVy_0WYotZWo&callback=initMap" async defer></script>

    <script>
        // Update time every second
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }

        setInterval(updateTime, 1000);
        updateTime(); // Initial call

        // Stats rotation - 10 seconds
        let currentStatsIndex = 0;
        const statsSets = document.querySelectorAll('.stats-set');
        
        function rotateStats() {
            statsSets[currentStatsIndex].classList.remove('active');
            currentStatsIndex = (currentStatsIndex + 1) % statsSets.length;
            statsSets[currentStatsIndex].classList.add('active');
        }

        setInterval(rotateStats, 30000);

        // Station rotation - 5 seconds  
        let currentStationIndex = 0;
        const stationContents = document.querySelectorAll('.station-content');
        
        function rotateStations() {
            stationContents[currentStationIndex].classList.remove('active');
            currentStationIndex = (currentStationIndex + 1) % stationContents.length;
            stationContents[currentStationIndex].classList.add('active');
        }

        setInterval(rotateStations, 20000);
    </script>
</body>
</html>