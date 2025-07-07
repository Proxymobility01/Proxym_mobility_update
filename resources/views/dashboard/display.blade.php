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

<div id="start-screen" style="position: fixed; top:0; left:0; width:100%; height:100%; background:#111; color:white; display:flex; align-items:center; justify-content:center; flex-direction:column; z-index:9999;">
    <h1>PROXYM DASHBOARD</h1>
    <button id="start-btn" style="padding:20px 40px; font-size:24px;">Démarrer</button>
  </div>

<audio id="sound-cut" src="{{ asset('assets/sounds/alert_cut.mp3') }}"></audio>
<audio id="sound-recovery" src="{{ asset('assets/sounds/alert_recovery.mp3') }}"></audio>


    <div class="dashboard">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">
    <a href="{{ route('dashboard') }}">
        <img src="{{ asset('assets/images/logo.png') }}" alt="PROXYM Logo" class="logo-image">
    </a>
</div>
                <div>
                    <div class="logo-text">PROXYM </div>
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
    <div class="station-content {{ $index === 0 ? 'active' : '' }}" id="station-{{ $index }}">
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


document.getElementById('start-btn').addEventListener('click', () => {
    const audio = document.getElementById('sound-cut');
    audio.volume = 0.002;
    audio.play().then(() => {
        console.log("✅ Audio autorisé");
    }).catch(err => console.warn(err));

    document.getElementById('start-screen').remove();
    console.log("✅ Audio débloqué, dashboard démarré");
});




/* ==============================
    VARIABLES GLOBALES
============================== */

let lastSnapshot = null;
let lastEnergyStates = {};
let alertQueue = [];
let alertActive = false;
let currentStationIndex = 0;

let map = null;
let markers = {
    stations: [],
    batteries: [],
    motos: []
};

const stationContents = document.querySelectorAll('.station-content');
const statsSets = document.querySelectorAll('.stats-set');

let currentStatsIndex = 0;

/* ==============================
    INIT MAP
============================== */

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
}

/* ==============================
    MAP UPDATES
============================== */

function updateMapMarkers(data) {
    clearMarkers();

    if (data.stationsMap) {
        data.stationsMap.forEach(station => {
            if (station.latitude && station.longitude) {
                const marker = new google.maps.Marker({
                    position: {
                        lat: parseFloat(station.latitude),
                        lng: parseFloat(station.longitude)
                    },
                    map: map,
                    title: station.nom,
                    icon: window.mapIcons.station
                });
                markers.stations.push(marker);
            }
        });
    }

    if (data.batteriesWithLocation) {
        data.batteriesWithLocation.forEach(battery => {
            if (battery.latitude && battery.longitude) {
                const marker = new google.maps.Marker({
                    position: {
                        lat: parseFloat(battery.latitude),
                        lng: parseFloat(battery.longitude)
                    },
                    map: map,
                    title: `Batterie ${battery.mac_id}`,
                    icon: window.mapIcons.battery
                });
                markers.batteries.push(marker);
            }
        });
    }

    if (data.motosWithLocation) {
        data.motosWithLocation.forEach(moto => {
            if (moto.latitude && moto.longitude) {
                const marker = new google.maps.Marker({
                    position: {
                        lat: parseFloat(moto.latitude),
                        lng: parseFloat(moto.longitude)
                    },
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

                marker.addListener('mouseover', () => {
                    infoWindow.open(map, marker);
                });
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

/* ==============================
    STATS ROTATION
============================== */

function rotateStats() {
    statsSets[currentStatsIndex].classList.remove('active');
    currentStatsIndex = (currentStatsIndex + 1) % statsSets.length;
    statsSets[currentStatsIndex].classList.add('active');
}

setInterval(rotateStats, 30000);

/* ==============================
    STATION ROTATION
============================== */

function rotateStations() {
    if (alertActive) {
        console.log("⏸️ Rotation bloquée par alerte");
        return;
    }

    document.querySelectorAll('.station-content').forEach(c => c.classList.remove('active'));

    currentStationIndex = (currentStationIndex + 1) % stationContents.length;

    stationContents[currentStationIndex].classList.add('active');
}


setInterval(rotateStations, 20000);

/* ==============================
    TIME UPDATE
============================== */

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
updateTime();

/* ==============================
    FETCH AND UPDATE DASHBOARD
============================== */

function fetchAndUpdateDashboard() {
    fetch('/dashboard/full-data')
        .then(res => res.json())
        .then(data => {
            const dataHash = JSON.stringify(data);
            if (dataHash !== lastSnapshot) {
                lastSnapshot = dataHash;
                updateStatsUI(data);
                updateMapMarkers(data);
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

document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => {
        fetchAndUpdateDashboard();
        setInterval(fetchAndUpdateDashboard, 10000);
        console.log('🔄 Mise à jour automatique activée (10s)');
    }, 5000);
});

/* ==============================
    UPDATE UI FUNCTIONS
============================== */

function updateStatsUI(data) {
    const stats = data.summaryStats;
    const levelStats = data.levelStats;

    const statsSet1 = document.querySelectorAll('.stats-set')[0];
    const statsSet2 = document.querySelectorAll('.stats-set')[1];
    const statsSet3 = document.querySelectorAll('.stats-set')[2];

    if (statsSet1) {
        const cards = statsSet1.querySelectorAll('.stat-value');
        if (cards[0]) cards[0].textContent = data.monthlySwapCount;
        if (cards[1]) cards[1].textContent = data.chauffeursCount;
        if (cards[2]) cards[2].textContent = data.totalDistance + ' km';
    }

    if (statsSet2) {
        const cards = statsSet2.querySelectorAll('.stat-value');
        if (cards[0]) cards[0].textContent = stats.charged;
        if (cards[1]) cards[1].textContent = stats.charging;
        if (cards[2]) cards[2].textContent = stats.inactive;
        if (cards[3]) cards[3].textContent = stats.total;
    }

    if (statsSet3) {
        const cards = statsSet3.querySelectorAll('.stat-value');
        if (cards[0]) cards[0].textContent = levelStats.very_high.count;
        if (cards[1]) cards[1].textContent = levelStats.high.count;
        if (cards[2]) cards[2].textContent = levelStats.medium.count;
        if (cards[3]) cards[3].textContent = levelStats.low.count;
    }
}

/* ==============================
    SIDEBAR + ALERT LOGIC
============================== */

function updateSidebarUI(data) {
    if (!data.stations) return;

    const stationContents = document.querySelectorAll('.station-content');

    data.stations.forEach((station, index) => {
        const content = stationContents[index];
        if (!content) return;

        // Première initialisation → enregistrer l'état sans déclencher d'alerte
       if (lastEnergyStates[station.nom] === undefined) {
    lastEnergyStates[station.nom] = station.energy;

    // ✅ Appliquer le style initial immédiatement
    if (station.energy === 0) {
        content.classList.add('energy-off');
        content.classList.remove('energy-alert');
    } else {
        content.classList.remove('energy-off');
        content.classList.remove('energy-alert');
    }

    return;
}


        if (station.energy !== lastEnergyStates[station.nom]) {
    console.log(`🔔 Changement d'énergie sur ${station.nom}: ${lastEnergyStates[station.nom]} ➔ ${station.energy}`);

    if (station.energy === 0) {
        alertQueue.push({ index, content, type: 'cut' });
        runNextAlert();
    } else if (station.energy === 1) {
        alertQueue.push({ index, content, type: 'recovery' });
        runNextAlert();
    }
}

        lastEnergyStates[station.nom] = station.energy;

        // Update info UI
        const agencyName = content.querySelector('.agency-name');
        if (agencyName) agencyName.textContent = station.nom;

        const agencyLocation = content.querySelector('.agency-location');
        if (agencyLocation) {
            agencyLocation.innerHTML = `${station.ville} • ${station.batteries_total} Batteries Total , <span>Batterie en Charge : ${station.batteries_en_charge}</span>`;
        }

        const batteryLevels = content.querySelectorAll('.battery-level');
        if (batteryLevels.length > 1) {
            const levels = station.levels;
            const ranges = ['very_high', 'high', 'medium', 'low', 'critical'];
            for (let i = 0; i < ranges.length; i++) {
                const charging = batteryLevels[i + 1].querySelector('.battery-on-charge');
                const count = batteryLevels[i + 1].querySelector('.battery-count');
                if (charging) charging.textContent = levels[ranges[i]].charging;
                if (count) count.textContent = levels[ranges[i]].count;
            }
        }

        const compteurCards = content.querySelectorAll('.compteur-card .compteur-value');
        station.compteurs.forEach((valeur, i) => {
            if (compteurCards[i]) {
                compteurCards[i].textContent = valeur;
            }
        });
    });
}

/* ==============================
    ALERT HANDLING
============================== */

function runNextAlert() {
    if (alertActive || alertQueue.length === 0) {
        return;
    }

    alertActive = true;

    const { index, content, type } = alertQueue.shift();

    console.log(`⚡ Début alerte sur station ${index} (type=${type})`);

    // STOP tous les sons en cours (au cas où plusieurs alertes s'enchaînent)
    document.getElementById('sound-cut').pause();
    document.getElementById('sound-cut').currentTime = 0;
    document.getElementById('sound-recovery').pause();
    document.getElementById('sound-recovery').currentTime = 0;

    // Jouer le bon son
   // Jouer le bon son
if (type === 'cut') {
    const audio = document.getElementById('sound-cut');
    audio.volume = 1;
    audio.play().catch(e => console.warn(e));
} else if (type === 'recovery') {
    const audio = document.getElementById('sound-recovery');
    audio.volume = 1;
    audio.play().catch(e => console.warn(e));
}


    document.querySelectorAll('.station-content').forEach(c => {
        c.classList.remove('active', 'energy-alert', 'energy-off', 'energy-recovery');
    });

    content.classList.add('active');
    currentStationIndex = index;

    if (type === 'cut') {
        content.classList.add('energy-alert');
    } else if (type === 'recovery') {
        content.classList.add('energy-recovery');
    }

    content.scrollIntoView({ behavior: 'smooth', block: 'center' });

    let countdown = 10;
    const intervalId = setInterval(() => {
        console.log(`⏳ Alerte station ${index} (${type}) - temps restant : ${countdown}s`);
        countdown--;
        if (countdown <= 0) {
            clearInterval(intervalId);

            if (type === 'cut') {
                content.classList.remove('energy-alert');
                content.classList.add('energy-off');
            } else if (type === 'recovery') {
                content.classList.remove('energy-recovery');
                content.classList.remove('energy-off');
            }

            alertActive = false;
            console.log(`✅ Fin alerte station ${index} (${type})`);

            if (alertQueue.length > 0) {
                runNextAlert();
            } else {
                rotateStations();
            }
        }
    }, 1000);
}



</script>


<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBn88TP5X-xaRCYo5gYxvGnVy_0WYotZWo&callback=initMap" async defer></script>

</body>
</html>