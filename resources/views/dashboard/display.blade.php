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
    // Variables globales pour les données initiales
    const initialStationsMap = @json($stationsMap ?? []);
    const initialBatteriesWithLocation = @json($batteriesWithLocation ?? []);
    const initialMotosWithLocation = @json($motosWithLocation ?? []);

    let lastSnapshot = null;
    let map = null;
    let markers = {
        stations: [],
        batteries: [],
        motos: []
    };

    function initMap() {
        const centerCoords = { lat: 4.04343000, lng: 9.74368000 };
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: centerCoords,
            styles: [
                { featureType: "transit", stylers: [{ visibility: "off" }] },
                { featureType: "poi.business", stylers: [{ visibility: "off" }] },
                { featureType: "poi.medical", stylers: [{ visibility: "off" }] },
                { featureType: "poi.school", stylers: [{ visibility: "off" }] },
                { featureType: "poi.sports_complex", stylers: [{ visibility: "off" }] },
                { featureType: "poi.place_of_worship", stylers: [{ visibility: "off" }] },
                { featureType: "administrative.neighborhood", elementType: "labels", stylers: [{ visibility: "on" }] },
                { featureType: "administrative.locality", elementType: "labels", stylers: [{ visibility: "on" }] },
                { featureType: "road", elementType: "labels", stylers: [{ visibility: "on" }] },
                { featureType: "road", elementType: "geometry", stylers: [{ visibility: "on" }] },
                { featureType: "landscape", elementType: "geometry", stylers: [{ visibility: "on" }] },
                { featureType: "water", stylers: [{ visibility: "on" }] }
            ]
        });

        window.mapIcons = {
            station: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="#000000" stroke="#FFFFFF" stroke-width="2"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(32, 32)
            },
            battery: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="6" width="16" height="12" rx="2" fill="#00AA00" stroke="#FFFFFF" stroke-width="1"/>
                        <rect x="20" y="9" width="2" height="6" fill="#00AA00"/>
                        <rect x="6" y="8" width="12" height="8" fill="#FFFFFF"/>
                        <rect x="7" y="9" width="10" height="6" fill="#00AA00"/>
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

        // Charger les données initiales
        updateMapMarkers({
            stationsMap: initialStationsMap,
            batteriesWithLocation: initialBatteriesWithLocation,
            motosWithLocation: initialMotosWithLocation
        });
    }

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
                    console.log('✔️ Données mises à jour à ' + new Date().toLocaleTimeString());
                } else {
                    console.log('⏸️ Pas de changement détecté');
                }
            })
            .catch(error => {
                console.error('❌ Erreur de récupération des données:', error);
            });
    }

    function updateStatsUI(data) {
        const stats = data.summaryStats;
        const levelStats = data.levelStats;
        
        // Première série de stats
        const statsSet1 = document.querySelectorAll('.stats-set')[0];
        if (statsSet1) {
            const cards = statsSet1.querySelectorAll('.stat-value');
            if (cards[0]) cards[0].textContent = data.monthlySwapCount;
            if (cards[1]) cards[1].textContent = data.chauffeursCount;
            if (cards[2]) cards[2].textContent = data.totalDistance + ' km';
        }

        // Deuxième série de stats
        const statsSet2 = document.querySelectorAll('.stats-set')[1];
        if (statsSet2) {
            const cards = statsSet2.querySelectorAll('.stat-value');
            if (cards[0]) cards[0].textContent = stats.charged;
            if (cards[1]) cards[1].textContent = stats.charging;
            if (cards[2]) cards[2].textContent = stats.inactive;
            if (cards[3]) cards[3].textContent = stats.total;
        }

        // Troisième série de stats
        const statsSet3 = document.querySelectorAll('.stats-set')[2];
        if (statsSet3) {
            const cards = statsSet3.querySelectorAll('.stat-value');
            if (cards[0]) cards[0].textContent = levelStats.very_high.count;
            if (cards[1]) cards[1].textContent = levelStats.high.count;
            if (cards[2]) cards[2].textContent = levelStats.medium.count;
            if (cards[3]) cards[3].textContent = levelStats.low.count;
        }
    }

    function updateMapUI(data) {
        if (!map) return;
        updateMapMarkers(data);
    }

    function updateMapMarkers(data) {
        clearMarkers();

        // Stations
        if (data.stationsMap) {
            data.stationsMap.forEach(station => {
                if (station.latitude && station.longitude) {
                    const marker = new google.maps.Marker({
                        position: { lat: parseFloat(station.latitude), lng: parseFloat(station.longitude) },
                        map: map,
                        title: station.nom,
                        icon: window.mapIcons.station
                    });
                    markers.stations.push(marker);
                }
            });
        }

        // Batteries
        if (data.batteriesWithLocation) {
            data.batteriesWithLocation.forEach(battery => {
                if (battery.latitude && battery.longitude) {
                    const marker = new google.maps.Marker({
                        position: { lat: parseFloat(battery.latitude), lng: parseFloat(battery.longitude) },
                        map: map,
                        title: `Batterie ${battery.mac_id}`,
                        icon: window.mapIcons.battery
                    });
                    markers.batteries.push(marker);
                }
            });
        }

        // Motos avec InfoWindows
        if (data.motosWithLocation) {
            data.motosWithLocation.forEach(moto => {
                if (moto.latitude && moto.longitude) {
                    const marker = new google.maps.Marker({
                        position: { lat: parseFloat(moto.latitude), lng: parseFloat(moto.longitude) },
                        map: map,
                        title: moto.driverInfo || moto.macid,
                        icon: window.mapIcons.moto
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div style="padding: 1px; font-family: Arial, sans-serif; min-width: 90px; font-size: 8px; line-height: 0.8;">
                                <h6 style="margin: 0; color: #333; font-size: 9px;">🏍️ ${moto.driverInfo || 'Moto'}</h6>
                                <div style="font-size: 7px; line-height: 0.7;">
                                    <p style="margin: 0; padding: 0;"><strong>ID:</strong> ${moto.macid || 'N/A'}</p>
                                    <p style="margin: 0; padding: 0;"><strong>Chauffeur:</strong> ${moto.driverInfo || 'Non assigné'}</p>
                                    <p style="margin: 0; padding: 0;"><strong>Statut:</strong> <span style="color: #00AA00;">En ligne</span></p>
                                </div>
                            </div>
                        `
                    });

                    // Afficher seulement au survol
                    marker.addListener('mouseover', () => {
                        infoWindow.open(map, marker);
                    });

                    // Masquer quand on sort du marqueur
                    marker.addListener('mouseout', () => {
                        infoWindow.close();
                    });

                    markers.motos.push({ marker, infoWindow });
                }
            });
        }
    }

    function clearMarkers() {
        markers.stations.forEach(marker => marker.setMap(null));
        markers.stations = [];
        
        markers.batteries.forEach(marker => marker.setMap(null));
        markers.batteries = [];
        
        markers.motos.forEach(item => {
            item.infoWindow.close();
            item.marker.setMap(null);
        });
        markers.motos = [];
    }

    function updateSidebarUI(data) {
        if (!data.stations) return;

        const stationContents = document.querySelectorAll('.station-content');
        
        data.stations.forEach((station, index) => {
            if (stationContents[index]) {
                const content = stationContents[index];
                
                // Mise à jour du nom de la station
                const agencyName = content.querySelector('.agency-name');
                if (agencyName) agencyName.textContent = station.nom;
                
                // Mise à jour de la localisation
                const agencyLocation = content.querySelector('.agency-location');
                if (agencyLocation) {
                    agencyLocation.innerHTML = `${station.ville} • ${station.batteries_total} Batteries Total , <span>Batterie en Charge : ${station.batteries_en_charge}</span>`;
                }
                
                // Mise à jour des niveaux de batterie
                const batteryLevels = content.querySelectorAll('.battery-level');
                if (batteryLevels.length > 1) {
                    // 90-100%
                    if (batteryLevels[1]) {
                        const charging = batteryLevels[1].querySelector('.battery-on-charge');
                        const count = batteryLevels[1].querySelector('.battery-count');
                        if (charging) charging.textContent = station.levels.very_high.charging;
                        if (count) count.textContent = station.levels.very_high.count;
                    }
                    // 70-90%
                    if (batteryLevels[2]) {
                        const charging = batteryLevels[2].querySelector('.battery-on-charge');
                        const count = batteryLevels[2].querySelector('.battery-count');
                        if (charging) charging.textContent = station.levels.high.charging;
                        if (count) count.textContent = station.levels.high.count;
                    }
                    // 40-70%
                    if (batteryLevels[3]) {
                        const charging = batteryLevels[3].querySelector('.battery-on-charge');
                        const count = batteryLevels[3].querySelector('.battery-count');
                        if (charging) charging.textContent = station.levels.medium.charging;
                        if (count) count.textContent = station.levels.medium.count;
                    }
                    // 10-40%
                    if (batteryLevels[4]) {
                        const charging = batteryLevels[4].querySelector('.battery-on-charge');
                        const count = batteryLevels[4].querySelector('.battery-count');
                        if (charging) charging.textContent = station.levels.low.charging;
                        if (count) count.textContent = station.levels.low.count;
                    }
                    // <10%
                    if (batteryLevels[5]) {
                        const charging = batteryLevels[5].querySelector('.battery-on-charge');
                        const count = batteryLevels[5].querySelector('.battery-count');
                        if (charging) charging.textContent = station.levels.critical.charging;
                        if (count) count.textContent = station.levels.critical.count;
                    }
                }
                
                // Mise à jour des compteurs
                const compteurCards = content.querySelectorAll('.compteur-card .compteur-value');
                station.compteurs.forEach((valeur, index) => {
                    if (compteurCards[index]) {
                        compteurCards[index].textContent = valeur;
                    }
                });
            }
        });
    }

    // Démarrer les mises à jour automatiques
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Dashboard temps réel initialisé');
        
        // Première mise à jour après 5 secondes
        setTimeout(() => {
            fetchAndUpdateDashboard();
            // Puis toutes les 10 secondes
            setInterval(fetchAndUpdateDashboard, 10000);
            console.log('🔄 Mise à jour automatique activée (10s)');
        }, 5000);
    });
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