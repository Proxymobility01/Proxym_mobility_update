<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carte des Batteries - PixelStream</title>
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Reset et styles généraux */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* Header */
        .header {
            background-color: #101010;
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 10;
        }
        
        .header h1 {
            font-size: 22px;
            font-weight: 500;
        }
        
        .header-logo {
            display: flex;
            align-items: center;
        }
        
        .header-logo img {
            height: 36px;
            margin-right: 10px;
        }
        
        .header-actions a {
            color: #ddd;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .header-actions a:hover {
            background-color: #333;
        }
        
        .header-actions i {
            margin-right: 8px;
        }
        
        /* Page title */
        .page-title {
            padding: 15px 25px;
            background-color: white;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 5;
        }
        
        .page-title h2 {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }
        
        .return-link a {
            display: flex;
            align-items: center;
            color: #666;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .return-link a:hover {
            background-color: #f1f1f1;
        }
        
        .return-link i {
            margin-right: 8px;
        }
        
        /* Map container */
        .map-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            width: 350px;
            background-color: #fff;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 2;
        }
        
        .sidebar-header {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            background-color: #fafafa;
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .search-bar {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            background-color: #fff;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
        }
        
        .search-input:focus {
            border-color: #DCDB32;
            box-shadow: 0 0 0 3px rgba(220, 219, 50, 0.25);
        }
        
        .battery-count {
            background-color: #f5f5f5;
            padding: 8px 15px;
            font-size: 13px;
            color: #666;
            border-bottom: 1px solid #ddd;
        }
        
        .batteries-list {
            flex: 1;
            overflow-y: auto;
            padding: 0;
            overscroll-behavior: contain;
        }
        
        .battery-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
            will-change: background-color;
        }
        
        .battery-item:hover {
            background-color: #f5f5f5;
        }
        
        .battery-item.active {
            background-color: #DCDB32;
            color: #101010;
        }
        
        .battery-info {
            display: flex;
            flex-direction: column;
        }
        
        .battery-id {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .battery-details {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #777;
        }
        
        .battery-status {
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-charging {
            background-color: #28a745;
            color: white;
        }
        
        .status-discharging {
            background-color: #ffc107;
            color: #212529;
        }
        
        .status-inactive {
            background-color: #dc3545;
            color: white;
        }
        
        .status-idle {
            background-color: #6c757d;
            color: white;
        }
        
        .battery-indicator {
            position: relative;
            height: 8px;
            width: 100%;
            background-color: #e9ecef;
            border-radius: 10px;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .battery-level {
            height: 100%;
            background-color: #DCDB32;
            border-radius: 10px;
            transition: width 0.5s ease-out;
            will-change: width;
        }
        
        /* Map area */
        .map-area {
            flex: 1;
            position: relative;
            z-index: 1;
        }
        
        #map {
            height: 100%;
            width: 100%;
        }
        
        /* Info window */
        .info-window {
            padding: 5px;
            max-width: 300px;
        }
        
        .info-window h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
            color: #333;
        }
        
        .info-window p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }
        
        .no-result {
            padding: 20px;
            text-align: center;
            color: #777;
        }
        
        /* Loading spinner */
        .loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #DCDB32;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Toast notifications */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast {
            background-color: #DCDB32;
            color: #101010;
            padding: 12px 20px;
            border-radius: 4px;
            margin-top: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: opacity 0.3s ease-out;
            max-width: 300px;
        }
        
        .toast.info {
            background-color: #2196F3;
            color: white;
        }
        
        .toast.error {
            background-color: #dc3545;
            color: white;
        }
        
        .toast.success {
            background-color: #28a745;
            color: white;
        }
        
        /* Status dot animation */
        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
            background-color: #28a745;
            position: relative;
            top: -1px;
        }
        
        .status-dot.online {
            background-color: #28a745;
        }
        
        .status-dot.offline {
            background-color: #dc3545;
        }
        
        .status-dot.pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }
            70% {
                box-shadow: 0 0 0 5px rgba(40, 167, 69, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }
        
        /* Connection status */
        .connection-status {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            background-color: #f8f9fa;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        
        /* Toggle sidebar button (mobile only) */
        .toggle-sidebar {
            display: none;
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 100;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                left: -350px;
                height: 100%;
                transition: left 0.3s ease-out;
                box-shadow: 2px 0 10px rgba(0,0,0,0.2);
            }
            
            .sidebar.visible {
                left: 0;
            }
            
            .toggle-sidebar {
                display: block;
            }
            
            .page-title {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .return-link {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-logo">
            <img src="https://via.placeholder.com/36" alt="Logo">
            <h1>Proxym Gestion de Batteries</h1>
        </div>
        <div class="header-actions">
            <a href="/batteries"><i class="fas fa-list"></i> Liste des batteries</a>
        </div>
    </header>
    
    <!-- Page title -->
    <div class="page-title">
        <h2>Carte des Batteries</h2>
        <div class="return-link">
            <a href="/batteries">
                <i class="fas fa-arrow-left"></i> Retour à la liste des batteries
            </a>
        </div>
    </div>
    
    <!-- Toggle sidebar button (mobile only) -->
    <button class="toggle-sidebar" id="toggle-sidebar">
        <i class="fas fa-list"></i>
    </button>
    
    <!-- Map container -->
    <div class="map-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3>Liste des Batteries</h3>
            </div>
            <div class="search-bar">
                <input type="text" id="search-battery" class="search-input" placeholder="Rechercher une batterie...">
            </div>
            <div class="battery-count">
                <span id="batteries-count">0</span> batteries trouvées
            </div>
            <div class="batteries-list" id="batteries-list">
                <!-- La liste des batteries sera générée dynamiquement -->
                <div class="loading">
                    <div class="spinner"></div>
                </div>
            </div>
            <div class="connection-status">
                <span class="status-dot online pulse"></span>
                <span id="connection-status-text">Connexion active</span>
            </div>
        </div>
        
        <!-- Map Area -->
        <div class="map-area">
            <div id="map"></div>
        </div>
    </div>
    
    <!-- Toast container -->
    <div class="toast-container" id="toast-container"></div>
    
    <script>
       // Performance optimization: Use requestAnimationFrame for DOM updates
const rafThrottle = (fn) => {
    let rafId = null;
    return (...args) => {
        if (rafId !== null) {
            cancelAnimationFrame(rafId);
        }
        rafId = requestAnimationFrame(() => {
            fn(...args);
            rafId = null;
        });
    };
};

// Variables globales
let map;
let markers = [];
let activeMarker = null;
let infoWindows = [];
let batteriesData = [];
let updateInterval = null;
let lastUpdateTime = new Date().toISOString();
let connectionActive = true;
let failedUpdateAttempts = 0;
let searchDebounceTimeout = null;

// Configuration
const CONFIG = {
    updateInterval: 10000, // 10 secondes
    maxFailedAttempts: 3,
    initialZoom: 12,
    focusZoom: 15,
    defaultLocation: { lat: 4.0510, lng: 9.7080 }, // Douala, Cameroun
    markerAnimationDuration: 700
};

// Initialiser l'application quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Configuration de la barre latérale pour les appareils mobiles
    const toggleSidebarBtn = document.getElementById('toggle-sidebar');
    const sidebar = document.getElementById('sidebar');
    
    toggleSidebarBtn.addEventListener('click', function() {
        sidebar.classList.toggle('visible');
    });
    
    // Fermer la sidebar quand l'utilisateur clique sur une batterie (mobile)
    document.getElementById('batteries-list').addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('visible');
        }
    });
    
    // Recherche de batteries
    const searchInput = document.getElementById('search-battery');
    searchInput.addEventListener('input', function(e) {
        // Debounce pour éviter trop de mises à jour
        clearTimeout(searchDebounceTimeout);
        searchDebounceTimeout = setTimeout(() => {
            const searchTerm = e.target.value.toLowerCase().trim();
            filterBatteries(searchTerm);
        }, 300);
    });
    
    // Charger l'API Google Maps
    loadGoogleMapsAPI();
    
    // Gérer les erreurs de chargement
    window.onerror = function(message, source, lineno, colno, error) {
        console.error('Erreur JavaScript:', message, error);
        showToast('Une erreur est survenue lors du chargement de la page', 'error');
        return true;
    };
});

// Fonction pour charger l'API Google Maps
function loadGoogleMapsAPI() {
    console.log('Chargement de l\'API Google Maps...');
    const script = document.createElement('script');
    script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBn88TP5X-xaRCYo5gYxvGnVy_0WYotZWo&callback=loadBatteriesData';
    script.async = true;
    script.defer = true;
    script.onerror = function() {
        showError('Impossible de charger Google Maps. Veuillez vérifier votre connexion Internet.');
    };
    document.head.appendChild(script);
}

// Fonction pour charger les données des batteries
async function loadBatteriesData() {
    try {
        console.log("Chargement initial des données...");
        showConnectionStatus(true);
        
        const apiUrl = '/batteries/api/map-data';
        console.log("URL de l'API:", apiUrl);
        
        const response = await fetch(apiUrl);
        console.log("Statut de la réponse:", response.status);
        
        if (!response.ok) {
            // Essayer d'obtenir les détails de l'erreur
            let errorText = await response.text();
            try {
                const errorJson = JSON.parse(errorText);
                console.error("Détails de l'erreur:", errorJson);
            } catch (e) {
                console.error("Réponse d'erreur brute:", errorText);
            }
            throw new Error(`Erreur de chargement (${response.status})`);
        }
        
        const data = await response.json();
        console.log("Données reçues:", data);
        
        if (!Array.isArray(data)) {
            console.error("Les données reçues ne sont pas un tableau:", data);
            throw new Error("Format de données invalide");
        }
        
        batteriesData = data;
        
        // Mise à jour du compteur
        document.getElementById('batteries-count').textContent = batteriesData.length;
        
        // Initialisation de la carte
        initMap();
        
        // Suppression du chargement
        const loadingElements = document.querySelectorAll('.loading');
        loadingElements.forEach(el => el.remove());
        
        // Afficher un toast de succès
        showToast(`${batteriesData.length} batteries chargées avec succès`, 'success');
        
        // Commencer les mises à jour en temps réel
        startRealTimeUpdates();
    } catch (error) {
        console.error('Erreur détaillée:', error);
        showError('Impossible de charger les données des batteries. ' + error.message);
        showConnectionStatus(false);
    }
}

// Initialisation de la carte Google Maps
function initMap() {
    console.log('Initialisation de la carte...');
    
    // Déterminer le centre de la carte (moyenne des coordonnées ou emplacement par défaut)
    let centerLat = CONFIG.defaultLocation.lat;
    let centerLng = CONFIG.defaultLocation.lng;
    
    if (batteriesData.length > 0) {
        // Calculer le centre basé sur toutes les batteries
        let totalLat = 0;
        let totalLng = 0;
        batteriesData.forEach(battery => {
            totalLat += parseFloat(battery.latitude);
            totalLng += parseFloat(battery.longitude);
        });
        centerLat = totalLat / batteriesData.length;
        centerLng = totalLng / batteriesData.length;
    }
    
    // Créer la carte avec des options optimisées
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: CONFIG.initialZoom,
        center: { lat: centerLat, lng: centerLng },
        mapTypeControl: true,
        fullscreenControl: true,
        streetViewControl: false,
        zoomControl: true,
        gestureHandling: 'greedy', // Amélioration de l'expérience sur mobile
        styles: [
            {
                "featureType": "poi",
                "stylers": [
                    { "visibility": "off" }
                ]
            }
        ],
        // Optimisations de performances
        maxZoom: 18,
        minZoom: 5,
        clickableIcons: false
    });
    
    // Ajouter les marqueurs de batteries
    addBatteryMarkers();
    
    // Initialiser la liste des batteries dans la sidebar
    renderBatteriesList(batteriesData);
}

// Fonction pour démarrer les mises à jour en temps réel
function startRealTimeUpdates() {
    // Arrêter l'intervalle existant si présent
    if (updateInterval) {
        clearInterval(updateInterval);
    }
    
    // Mettre à jour toutes les 10 secondes
    updateInterval = setInterval(() => {
        fetchUpdates();
    }, CONFIG.updateInterval);
    
    console.log('Mises à jour en temps réel activées');
}

// Fonction pour récupérer les mises à jour
async function fetchUpdates() {
    if (!connectionActive && failedUpdateAttempts >= CONFIG.maxFailedAttempts) {
        // Si la connexion est inactive et qu'il y a eu trop d'échecs, ne pas tenter de mise à jour
        return;
    }
    
    try {
        const apiUrl = `/batteries/api/map-updates?since=${encodeURIComponent(lastUpdateTime)}`;
        console.log("Récupération des mises à jour:", apiUrl);
        
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            let errorText = await response.text();
            console.error("Erreur de mise à jour:", response.status, errorText);
            throw new Error(`Erreur de mise à jour (${response.status})`);
        }
        
        const updates = await response.json();
        console.log(`Mises à jour reçues: ${updates.length} batteries`);
        
        // Réinitialiser les compteurs d'erreur
        failedUpdateAttempts = 0;
        showConnectionStatus(true);
        
        if (updates.length > 0) {
            // Appliquer les mises à jour
            updates.forEach(updatedBattery => {
                const index = batteriesData.findIndex(b => b.mac_id === updatedBattery.mac_id);
                
                if (index !== -1) {
                    // Déterminer si le statut a changé
                    const oldStatus = batteriesData[index].status;
                    const statusChanged = oldStatus !== updatedBattery.status;
                    
                    // Mettre à jour les données
                    batteriesData[index] = {...batteriesData[index], ...updatedBattery};
                    
                    // Mettre à jour le marqueur et l'infobulle
                    updateMarker(index, batteriesData[index], statusChanged);
                }
            });
            
            // Mettre à jour la liste (optimisé avec requestAnimationFrame)
            requestAnimationFrame(() => {
                updateBatteriesList(updates);
            });
        }
        
        lastUpdateTime = new Date().toISOString();
    } catch (error) {
        console.warn('Erreur lors de la mise à jour:', error);
        failedUpdateAttempts++;
        
        if (failedUpdateAttempts >= CONFIG.maxFailedAttempts) {
            showConnectionStatus(false);
            showToast('La connexion au serveur a été perdue', 'error');
        }
    }
}

// Ajouter des marqueurs pour chaque batterie
function addBatteryMarkers() {
    console.log('Ajout des marqueurs pour', batteriesData.length, 'batteries');
    
    batteriesData.forEach((battery, index) => {
        if (!battery.latitude || !battery.longitude) return;
        
        const position = { 
            lat: parseFloat(battery.latitude), 
            lng: parseFloat(battery.longitude) 
        };
        
        // Créer un marqueur personnalisé selon le statut
        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: `Batterie ${battery.mac_id}`,
            animation: google.maps.Animation.DROP,
            icon: getMarkerIcon(battery.status),
            batteryId: battery.mac_id,
            optimized: true // Amélioration des performances
        });
        
        // Retarder l'animation DROP pour les marqueurs suivants
        marker.setAnimation(null);
        
        // Créer une fenêtre d'info pour le marqueur
        const infoWindow = new google.maps.InfoWindow({
            content: createInfoWindowContent(battery),
            maxWidth: 320
        });
        
        // Ajouter un écouteur d'événements pour l'infobulle
        marker.addListener('click', function() {
            // Fermer toutes les infobulles ouvertes
            infoWindows.forEach(info => info.close());
            
            // Ouvrir cette infobulle
            infoWindow.open(map, marker);
            
            // Mettre en évidence la batterie dans la liste
            highlightBatteryInList(battery.mac_id);
            
            // Animation du marqueur
            toggleBounce(marker);
        });
        
        markers.push(marker);
        infoWindows.push(infoWindow);
    });
}

// Obtenir l'icône du marqueur en fonction du statut
function getMarkerIcon(status) {
    // Couleurs personnalisées pour les différents statuts
    switch(status) {
        case 'En charge':
            return 'https://maps.google.com/mapfiles/ms/icons/green-dot.png';
        case 'En décharge':
            return 'https://maps.google.com/mapfiles/ms/icons/yellow-dot.png';
        case 'Inactive':
            return 'https://maps.google.com/mapfiles/ms/icons/red-dot.png';
        default:
            return 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';
    }
}

// Créer le contenu HTML pour l'infobulle
function createInfoWindowContent(battery) {
    return `
        <div class="info-window">
            <h3>Batterie ${battery.unique_id || battery.mac_id}</h3>
            <p><strong>MAC ID:</strong> ${battery.mac_id}</p>
            <p><strong>Fabricant:</strong> ${battery.fabriquant || 'Non spécifié'}</p>
            <p><strong>État de charge:</strong> ${battery.soc || 0}%</p>
            <p><strong>Statut:</strong> ${battery.status || 'Inconnu'}</p>
            <p><strong>Dernière mise à jour:</strong> ${battery.last_update || 'Inconnue'}</p>
            <p><strong>GPS:</strong> ${battery.gps || 'Non spécifié'}</p>
            <p><strong>Coordonnées:</strong> ${battery.latitude}, ${battery.longitude}</p>
        </div>
    `;
}

// Fonction pour filtrer les batteries (recherche)
function filterBatteries(searchTerm) {
    if (!searchTerm) {
        // Si la recherche est vide, afficher toutes les batteries
        renderBatteriesList(batteriesData);
        markers.forEach(marker => marker.setVisible(true));
        document.getElementById('batteries-count').textContent = batteriesData.length;
        return;
    }
    
    // Filtrer les batteries selon le terme de recherche
    const filteredBatteries = batteriesData.filter(battery => 
        (battery.mac_id && battery.mac_id.toLowerCase().includes(searchTerm)) || 
        (battery.unique_id && battery.unique_id.toLowerCase().includes(searchTerm)) ||
        (battery.fabriquant && battery.fabriquant.toLowerCase().includes(searchTerm))
    );
    
    // Mettre à jour la liste et le compteur
    renderBatteriesList(filteredBatteries);
    document.getElementById('batteries-count').textContent = filteredBatteries.length;
    
    // Filtrer les marqueurs
    markers.forEach(marker => {
        const battery = batteriesData.find(b => b.mac_id === marker.batteryId);
        
        if (!battery) return;
        
        const shouldShow = 
            marker.batteryId.toLowerCase().includes(searchTerm) || 
            (battery.unique_id && battery.unique_id.toLowerCase().includes(searchTerm)) ||
            (battery.fabriquant && battery.fabriquant.toLowerCase().includes(searchTerm));
        
        marker.setVisible(shouldShow);
    });
}

// Fonction pour mettre à jour un marqueur existant
function updateMarker(index, battery, statusChanged) {
    if (!markers[index]) return;
    
    // Mettre à jour l'icône si le statut a changé
    if (statusChanged) {
        markers[index].setIcon(getMarkerIcon(battery.status));
    }
    
    // Mettre à jour l'infobulle
    if (infoWindows[index]) {
        infoWindows[index].setContent(createInfoWindowContent(battery));
    }
    
    // Animation brève pour indiquer la mise à jour si le statut a changé
    if (statusChanged) {
        markers[index].setAnimation(google.maps.Animation.BOUNCE);
        setTimeout(() => {
            markers[index].setAnimation(null);
        }, CONFIG.markerAnimationDuration);
    }
}

// Animation de rebond pour le marqueur actif
function toggleBounce(marker) {
    if (activeMarker && activeMarker !== marker) {
        activeMarker.setAnimation(null);
    }
    
    if (marker.getAnimation() !== null) {
        marker.setAnimation(null);
    } else {
        marker.setAnimation(google.maps.Animation.BOUNCE);
        setTimeout(() => {
            marker.setAnimation(null);
        }, CONFIG.markerAnimationDuration);
    }
    
    activeMarker = marker;
}

// Mettre en évidence la batterie dans la liste latérale
function highlightBatteryInList(mac_id) {
    const listItems = document.querySelectorAll('.battery-item');
    listItems.forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-mac-id') === mac_id) {
            item.classList.add('active');
            
            // Scroll vers l'élément avec une animation douce
            item.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }
    });
}

// Afficher la liste initiale des batteries dans la sidebar
function renderBatteriesList(batteriesToRender) {
    const listContainer = document.getElementById('batteries-list');
    
    // Vider la liste actuelle
    while (listContainer.firstChild) {
        listContainer.removeChild(listContainer.firstChild);
    }
    
    if (batteriesToRender.length === 0) {
        const noResultDiv = document.createElement('div');
        noResultDiv.className = 'no-result';
        noResultDiv.innerHTML = 'Aucune batterie trouvée';
        listContainer.appendChild(noResultDiv);
        return;
    }
    
    // Utiliser un fragment de document pour optimiser les performances
    const fragment = document.createDocumentFragment();
    
    batteriesToRender.forEach(battery => {
        const batteryItem = createBatteryListItem(battery);
        fragment.appendChild(batteryItem);
    });
    
    // Ajouter tous les éléments d'un coup pour minimiser les mises à jour du DOM
    listContainer.appendChild(fragment);
}

// Mettre à jour la liste des batteries (plus efficace que de tout redessiner)
function updateBatteriesList(updatedBatteries) {
    // Si la recherche est active, appliquer les mises à jour seulement aux éléments visibles
    const searchTerm = document.getElementById('search-battery').value.toLowerCase().trim();
    
    updatedBatteries.forEach(updatedBattery => {
        // Vérifier si l'élément doit être visible selon le terme de recherche
        const shouldBeVisible = !searchTerm || 
            updatedBattery.mac_id.toLowerCase().includes(searchTerm) || 
            (updatedBattery.unique_id && updatedBattery.unique_id.toLowerCase().includes(searchTerm)) ||
            (updatedBattery.fabriquant && updatedBattery.fabriquant.toLowerCase().includes(searchTerm));
        
        // Trouver l'élément existant dans la liste
        const existingItem = document.querySelector(`.battery-item[data-mac-id="${updatedBattery.mac_id}"]`);
        
        if (existingItem) {
            // Si l'élément existe, mettre à jour ses données
            updateBatteryListItem(existingItem, updatedBattery);
            
            // Masquer/afficher selon le terme de recherche
            existingItem.style.display = shouldBeVisible ? 'block' : 'none';
        } else if (shouldBeVisible) {
            // Si l'élément n'existe pas (rare) et devrait être visible, le créer
            const listContainer = document.getElementById('batteries-list');
            const newItem = createBatteryListItem(updatedBattery);
            listContainer.appendChild(newItem);
        }
    });
}

// Créer un élément de liste pour une batterie
function createBatteryListItem(battery) {
    const batteryItem = document.createElement('div');
    batteryItem.className = 'battery-item';
    batteryItem.setAttribute('data-mac-id', battery.mac_id);
    
    // Déterminer la classe CSS pour le statut
    let statusClass = '';
    switch(battery.status) {
        case 'En charge': statusClass = 'status-charging'; break;
        case 'En décharge': statusClass = 'status-discharging'; break;
        case 'Inactive': statusClass = 'status-inactive'; break;
        default: statusClass = 'status-idle';
    }
    
    batteryItem.innerHTML = `
        <div class="battery-info">
            <div class="battery-id">${battery.mac_id}</div>
            <div class="battery-details">
                <span>${battery.unique_id || 'ID inconnu'}</span>
                <span class="battery-status ${statusClass}">${battery.status || 'Inconnu'}</span>
            </div>
            <div class="battery-indicator">
                <div class="battery-level" style="width: ${battery.soc || 0}%"></div>
            </div>
        </div>
    `;
    
    // Événement de clic pour centrer la carte sur cette batterie
    batteryItem.addEventListener('click', function() {
        const battery = batteriesData.find(b => b.mac_id === this.getAttribute('data-mac-id'));
        if (battery) {
            centerMapOnBattery(battery);
        }
    });
    
    return batteryItem;
}

// Mettre à jour un élément de liste existant
function updateBatteryListItem(listItem, battery) {
    // Mettre à jour le statut
    const statusElement = listItem.querySelector('.battery-status');
    if (statusElement) {
        // Réinitialiser les classes
        statusElement.className = 'battery-status';
        
        // Ajouter la nouvelle classe de statut
        let statusClass = '';
        switch(battery.status) {
            case 'En charge': statusClass = 'status-charging'; break;
            case 'En décharge': statusClass = 'status-discharging'; break;
            case 'Inactive': statusClass = 'status-inactive'; break;
            default: statusClass = 'status-idle';
        }
        statusElement.classList.add(statusClass);
        
        // Mettre à jour le texte
        statusElement.textContent = battery.status || 'Inconnu';
    }
    
    // Mettre à jour la barre de progression du SOC
    const batteryLevelElement = listItem.querySelector('.battery-level');
    if (batteryLevelElement) {
        batteryLevelElement.style.width = `${battery.soc || 0}%`;
    }
    
    // Ajouter une classe pour indiquer que l'élément a été mis à jour
    listItem.classList.add('updated');
    setTimeout(() => {
        listItem.classList.remove('updated');
    }, 2000);
}

// Centrer la carte sur une batterie spécifique et afficher son infobulle
function centerMapOnBattery(battery) {
    if (!battery || !battery.latitude || !battery.longitude) return;
    
    const position = { 
        lat: parseFloat(battery.latitude), 
        lng: parseFloat(battery.longitude) 
    };
    
    // Centrer la carte avec une animation fluide
    map.panTo(position);
    map.setZoom(CONFIG.focusZoom);
    
    // Trouver le marqueur correspondant
    const markerIndex = markers.findIndex(m => m.batteryId === battery.mac_id);
    
    if (markerIndex !== -1) {
        const marker = markers[markerIndex];
        
        // Fermer toutes les infobulles
        infoWindows.forEach(info => info.close());
        
        // Ouvrir l'infobulle correspondante
        infoWindows[markerIndex].open(map, marker);
        
        // Animation du marqueur
        toggleBounce(marker);
    }
    
    // Mettre en évidence la batterie dans la liste
    highlightBatteryInList(battery.mac_id);
}

// Afficher un message d'erreur
function showError(message) {
    // Suppression du loader
    const loadingElements = document.querySelectorAll('.loading');
    loadingElements.forEach(el => el.remove());
    
    // Afficher l'erreur dans la zone de carte
    const mapArea = document.querySelector('.map-area');
    mapArea.innerHTML = `
        <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; padding: 20px; text-align: center;">
            <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
            <h3 style="margin-bottom: 10px;">Erreur</h3>
            <p>${message}</p>
            <button onclick="location.reload()" style="margin-top: 20px; padding: 8px 15px; background-color: #DCDB32; color: #101010; border: none; border-radius: 4px; cursor: pointer;">
                <i class="fas fa-redo-alt"></i> Réessayer
            </button>
        </div>
    `;
    
    // Afficher aussi un message dans la sidebar
    const batteriesList = document.getElementById('batteries-list');
    batteriesList.innerHTML = `
        <div class="no-result">
            <i class="fas fa-exclamation-triangle" style="font-size: 24px; color: #dc3545; margin-bottom: 10px;"></i>
            <p>Impossible de charger les données</p>
        </div>
    `;
    
    // Mettre à jour le compteur
    document.getElementById('batteries-count').textContent = "0";
    
    // Afficher un toast d'erreur
    showToast(message, 'error');
}

// Afficher un toast de notification
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container');
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    toastContainer.appendChild(toast);
    
    // Animation d'entrée
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 10);
    
    // Disparition après 5 secondes
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            if (toast.parentNode) {
                toastContainer.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

// Mettre à jour l'indicateur d'état de connexion
function showConnectionStatus(isActive) {
    const statusDot = document.querySelector('.status-dot');
    const statusText = document.getElementById('connection-status-text');
    
    if (statusDot && statusText) {
        connectionActive = isActive;
        
        if (isActive) {
            statusDot.className = 'status-dot online pulse';
            statusText.textContent = 'Connexion active';
            statusText.style.color = '#28a745';
        } else {
            statusDot.className = 'status-dot offline';
            statusText.textContent = 'Connexion perdue';
            statusText.style.color = '#dc3545';
        }
    }
}

// Fonction utilitaire pour formater la date
function formatDate(dateString) {
    if (!dateString) return 'Inconnue';
    
    const date = new Date(dateString);
    
    if (isNaN(date.getTime())) return dateString;
    
    return date.toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

// Gestionnaire d'événements pour la visibilité de la page
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        // L'utilisateur est revenu sur la page, effectuer une mise à jour immédiate
        console.log('Page visible, mise à jour immédiate...');
        fetchUpdates();
        
        // Redémarrer les mises à jour en temps réel
        startRealTimeUpdates();
    } else {
        // L'utilisateur a quitté la page, arrêter les mises à jour en temps réel
        console.log('Page masquée, pause des mises à jour...');
        if (updateInterval) {
            clearInterval(updateInterval);
            updateInterval = null;
        }
    }
});

// Gestionnaire d'événements pour les changements de connexion
window.addEventListener('online', function() {
    console.log('Connexion internet rétablie');
    showConnectionStatus(true);
    showToast('Connexion internet rétablie', 'success');
    
    // Effectuer une mise à jour immédiate
    fetchUpdates();
    
    // Redémarrer les mises à jour en temps réel
    startRealTimeUpdates();
});

window.addEventListener('offline', function() {
    console.log('Connexion internet perdue');
    showConnectionStatus(false);
    showToast('Connexion internet perdue', 'error');
    
    // Arrêter les mises à jour en temps réel
    if (updateInterval) {
        clearInterval(updateInterval);
        updateInterval = null;
    }
});
    </script>
</body>
</html>