<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proxym - Suivi GPS des Motos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            overflow: hidden;
        }
        
        .navbar {
            background-color: #1a1a1a;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .logo-text {
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .sidebar {
            width: 360px;
            background-color: #f8f9fa;
            height: calc(100vh - 60px);
            overflow-y: auto;
            transition: transform 0.3s;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 100;
            position: relative;
        }
        
        .sidebar-header {
            padding: 15px;
            background-color: #e9ecef;
            border-bottom: 1px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .search-box {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .motos-count {
            padding: 10px 15px;
            color: #6c757d;
            font-size: 14px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .moto-list {
            list-style: none;
        }
        
        .moto-item {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .moto-item:hover {
            background-color: #e9ecef;
        }
        
        .moto-item.active {
            background-color: #e9ecef;
            border-left: 4px solid #007bff;
        }
        
        .moto-id {
            font-weight: bold;
            font-size: 16px;
        }
        
        .moto-vin {
            font-size: 14px;
            color: #6c757d;
            margin: 5px 0;
        }
        
        .moto-driver {
            font-size: 14px;
        }
        
        .driver-name {
            font-weight: 500;
        }
        
        .status-bar {
            display: flex;
            align-items: center;
            margin-top: 8px;
        }
        
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .in-zone {
            background-color: #28a745;
        }
        
        .out-zone {
            background-color: #dc3545;
        }
        
        .inactive {
            background-color: #6c757d;
        }
        
        .charging {
            background-color: #ffc107;
        }
        
        .status-text {
            font-size: 12px;
        }
        
        .status-progress {
            flex-grow: 1;
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            margin: 0 10px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 3px;
        }
        
        .progress-full {
            background-color: #28a745;
        }
        
        .progress-medium {
            background-color: #ffc107;
        }
        
        .progress-low {
            background-color: #dc3545;
        }
        
        .map-container {
            position: absolute;
            top: 60px;
            right: 0;
            width: calc(100% - 360px);
            height: calc(100vh - 60px);
            transition: width 0.3s;
        }
        
        #map {
            height: 100%;
            width: 100%;
        }
        
        .toggle-sidebar {
            position: absolute;
            top: 50%;
            left: 360px;
            transform: translateY(-50%);
            background-color: white;
            border: 1px solid #dee2e6;
            border-left: none;
            width: 25px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 101;
            border-radius: 0 4px 4px 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-collapsed {
            transform: translateX(-360px);
        }
        
        .map-expanded {
            width: 100%;
        }
        
        .toggle-icon {
            transition: transform 0.3s;
        }
        
        .toggle-icon.collapsed {
            transform: rotate(180deg);
        }
        
        .moto-details {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            padding: 15px;
            min-width: 300px;
            z-index: 99;
            display: none;
        }
        
        .moto-details.visible {
            display: block;
        }
        
        .detail-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .detail-row {
            display: flex;
            padding: 5px 0;
        }
        
        .detail-label {
            width: 100px;
            color: #6c757d;
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .nav-link i {
            margin-right: 5px;
        }
        
        .refresh-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .refresh-button:hover {
            background-color: #0069d9;
        }
        
        .refresh-button i {
            margin-right: 5px;
        }
        
        .refresh-status {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
            text-align: center;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 14px;
            display: none;
        }
        
        .error-message.visible {
            display: block;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 280px;
            }
            .map-container {
                width: calc(100% - 280px);
            }
            .toggle-sidebar {
                left: 280px;
            }
            .sidebar-collapsed {
                transform: translateX(-280px);
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo-container">
            <div class="logo-text">PROXYM</div>
            <h2>Gestion de Motos</h2>
        </div>
        <a href="{{ route('gps.motos.carte') }}" class="nav-link">
            <i class="fas fa-list"></i> Liste des motos
        </a>
    </div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <input type="text" class="search-box" placeholder="Rechercher une moto...">
            <button id="refresh-button" class="refresh-button">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
            <div id="refresh-status" class="refresh-status"></div>
            <div id="error-message" class="error-message"></div>
        </div>
        <div class="motos-count" id="motos-count">
            0 motos trouvées
        </div>
        <ul class="moto-list" id="moto-list">
            <!-- Les motos seront chargées ici dynamiquement -->
        </ul>
    </div>

    <div class="toggle-sidebar" id="toggle-sidebar">
        <i class="fas fa-chevron-left toggle-icon" id="toggle-icon"></i>
    </div>

    <div class="map-container" id="map-container">
        <div id="map"></div>
        <div class="moto-details" id="moto-details">
            <div class="detail-header">Détails de la Moto</div>
            <div class="detail-row">
                <div class="detail-label">MACID:</div>
                <div class="detail-value" id="detail-macid">--</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">VIN:</div>
                <div class="detail-value" id="detail-vin">--</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Chauffeur:</div>
                <div class="detail-value" id="detail-driver">--</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Position:</div>
                <div class="detail-value" id="detail-position">--</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Statut:</div>
                <div class="detail-value" id="detail-status">--</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Dernière MAJ:</div>
                <div class="detail-value" id="detail-lastupdate">--</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Batterie:</div>
                <div class="detail-value" id="detail-battery">--</div>
            </div>
        </div>
    </div>

    <!-- Correction de l'attribut 'defer' qui causait un avertissement -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBn88TP5X-xaRCYo5gYxvGnVy_0WYotZWo&callback=initMap" async></script>
    <script>
        // État global de l'application
        let state = {
            motos: [],
            selectedMoto: null,
            markers: {},
            map: null,
            zonePolygon: null,
            sidebarCollapsed: false,
            searchTerm: '',
            detailsVisible: false,
            lastRefresh: null,
            autoRefresh: false,
            autoRefreshInterval: null
        };

        // Fonction pour afficher un message d'erreur
        function showError(message) {
            const errorElement = document.getElementById('error-message');
            errorElement.textContent = message;
            errorElement.classList.add('visible');
            
            // Masquer après 5 secondes
            setTimeout(() => {
                errorElement.classList.remove('visible');
            }, 5000);
        }

        // Initialisation de la carte Google Maps
        function initMap() {
            console.log("Initialisation de la carte...");
            try {
                state.map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 12,
                    center: { lat: 4.0500, lng: 9.7000 },
                    mapTypeControl: true,
                    mapTypeControlOptions: {
                        style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                        position: google.maps.ControlPosition.TOP_RIGHT
                    }
                });

                // Charger la zone Douala avec un paramètre anti-cache
                const timestamp = new Date().getTime();
                fetch(`{{ route("gps.motos.zone") }}?_=${timestamp}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Erreur HTTP: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(text => {
                        // Nettoyer la réponse des caractères non désirés (BOM, espaces, etc)
                        const cleanText = text.replace(/^\s+|\s+$/g, '').replace(/^\uFEFF/g, '');
                        console.log("Texte reçu:", cleanText.substring(0, 100) + "...");
                        
                        // Convertir en JSON
                        const coordinates = JSON.parse(cleanText);
                        console.log("Zone Douala chargée:", coordinates.length, "points");
                        
                        state.zonePolygon = new google.maps.Polygon({
                            paths: coordinates,
                            strokeColor: '#FF0000',
                            strokeOpacity: 1.0,
                            strokeWeight: 2,
                            fillColor: '#FF0000',
                            fillOpacity: 0.05
                        });
                        state.zonePolygon.setMap(state.map);
                    })
                    .catch(error => {
                        console.error('Erreur chargement zone:', error);
                        showError("Erreur lors du chargement de la zone. Veuillez rafraîchir la page.");
                    });

                // Charger les positions des motos
                loadMotosData();
            } catch (error) {
                console.error("Erreur lors de l'initialisation de la carte:", error);
                showError("Erreur lors de l'initialisation de la carte Google Maps.");
            }
        }

        // Chargement des données des motos
        function loadMotosData() {
            console.log("Chargement des données des motos...");
            updateRefreshStatus("Chargement des données...");
            
            // Masquer les éventuels messages d'erreur précédents
            document.getElementById('error-message').classList.remove('visible');
            
            // Ajouter un paramètre anti-cache pour éviter la mise en cache du navigateur
            const timestamp = new Date().getTime();
            const url = `{{ route("gps.motos.positions") }}?_=${timestamp}`;
            
            console.log("URL de l'API:", url);
            
            fetch(url)
                .then(response => {
                    console.log("Statut de la réponse:", response.status);
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    // Nettoyer la réponse des caractères non désirés (BOM, espaces, etc)
                    const cleanText = text.replace(/^\s+|\s+$/g, '').replace(/^\uFEFF/g, '');
                    console.log("Texte reçu (début):", cleanText.substring(0, 100) + "...");
                    
                    try {
                        // Convertir en JSON
                        const data = JSON.parse(cleanText);
                        console.log("Données brutes reçues:", data);
                        console.log("Nombre de motos reçues:", data.length);
                        
                        if (data.length === 0) {
                            console.warn("Aucune moto reçue de l'API!");
                            showError("Aucune moto n'a été trouvée dans la base de données.");
                        }
                        
                        // Vérifier que les données ont le bon format
                        if (data.length > 0) {
                            console.log("Exemple de moto reçue:", data[0]);
                        }
                        
                        // Mettre à jour l'état avec les nouvelles données
                        state.motos = data.map(moto => {
                            return {
                                ...moto,
                                // Formater la date de dernière mise à jour
                                formattedLastUpdate: formatDate(moto.lastUpdate)
                            };
                        });
                        
                        // Mettre à jour la dernière actualisation
                        state.lastRefresh = new Date();
                        updateRefreshStatus("Dernière actualisation: " + formatTime(state.lastRefresh));
                        
                        // Mise à jour de l'interface
                        updateMotoList();
                        updateMarkers();
                        
                        // Si une moto était sélectionnée, garder la sélection
                        if (state.selectedMoto) {
                            const updatedMoto = state.motos.find(m => m.vin === state.selectedMoto.vin);
                            if (updatedMoto) {
                                selectMoto(updatedMoto);
                            }
                        }
                    } catch (e) {
                        console.error("Erreur de parsing JSON:", e);
                        showError("Format de réponse incorrect. Contactez l'administrateur.");
                        throw e;
                    }
                })
                .catch(error => {
                    console.error('Erreur chargement motos:', error);
                    updateRefreshStatus("Erreur de chargement. Réessayez.");
                    showError("Erreur lors du chargement des motos: " + error.message);
                });
        }

        // Formater l'heure uniquement
        function formatTime(date) {
            if (!date) return 'N/A';
            
            return date.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
        }

        // Mettre à jour le statut de rafraîchissement
        function updateRefreshStatus(message) {
            const refreshStatus = document.getElementById('refresh-status');
            refreshStatus.textContent = message;
        }

        // Formater la date pour l'affichage
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            
            return date.toLocaleString('fr-FR', { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit'
            });
        }

        // Mise à jour de la liste des motos dans la sidebar
        function updateMotoList() {
            const motoList = document.getElementById('moto-list');
            const motosCount = document.getElementById('motos-count');
            
            // Filtrer les motos selon le terme de recherche
            const filteredMotos = state.motos.filter(moto => 
                moto.vin.toLowerCase().includes(state.searchTerm.toLowerCase()) ||
                (moto.macid && moto.macid.toLowerCase().includes(state.searchTerm.toLowerCase())) ||
                (moto.driverName && moto.driverName.toLowerCase().includes(state.searchTerm.toLowerCase()))
            );
            
            // Mettre à jour le compteur
            motosCount.textContent = `${filteredMotos.length} motos trouvées`;
            
            // Vider et reconstruire la liste
            motoList.innerHTML = '';
            
            filteredMotos.forEach(moto => {
                const li = document.createElement('li');
                li.className = `moto-item ${state.selectedMoto && state.selectedMoto.vin === moto.vin ? 'active' : ''}`;
                
                // Déterminer le statut et sa couleur
                let statusClass = 'inactive';
                let statusText = 'Inactive';
                
                if (!moto.isActive) {
                    statusClass = 'inactive';
                    statusText = 'Inactive';
                } else if (moto.inZone) {
                    statusClass = 'in-zone';
                    statusText = 'Dans la zone';
                } else {
                    statusClass = 'out-zone';
                    statusText = 'Hors zone';
                }
                
                // Calculer la largeur de la barre de progression basée sur le niveau de batterie
                const batteryLevel = moto.batteryLevel || 0;
                let batteryClass = 'progress-low';
                
                if (batteryLevel >= 70) {
                    batteryClass = 'progress-full';
                } else if (batteryLevel >= 30) {
                    batteryClass = 'progress-medium';
                }
                
                // Générer le HTML pour l'élément de liste
                li.innerHTML = `
                    <div class="moto-id">${moto.macid || 'N/A'}</div>
                    <div class="moto-vin">VIN: ${moto.vin}</div>
                    <div class="moto-driver">
                        <span class="driver-name">${moto.driverName || 'Aucun chauffeur'}</span>
                    </div>
                    <div class="status-bar">
                        <div class="status-indicator ${statusClass}"></div>
                        <div class="status-text">${statusText}</div>
                        <div class="status-progress">
                            <div class="progress-bar ${batteryClass}" style="width: ${batteryLevel}%"></div>
                        </div>
                        <div class="status-text">${batteryLevel}%</div>
                    </div>
                `;
                
                // Ajouter un événement clic pour sélectionner la moto
                li.addEventListener('click', () => selectMoto(moto));
                
                motoList.appendChild(li);
            });
        }

        // Mise à jour des marqueurs sur la carte
        function updateMarkers() {
            console.log("Mise à jour des marqueurs...");
            
            if (!state.map) {
                console.error("La carte Google Maps n'est pas initialisée!");
                showError("La carte n'est pas initialisée. Veuillez rafraîchir la page.");
                return;
            }
            
            try {
                // Supprimer les marqueurs existants
                Object.values(state.markers).forEach(marker => marker.setMap(null));
                state.markers = {};
                
                // Vérifier que nous avons des motos à afficher
                if (!state.motos || state.motos.length === 0) {
                    console.warn("Aucune moto à afficher sur la carte");
                    return;
                }
                
                let validMarkerCount = 0;
                
                // Créer de nouveaux marqueurs pour chaque moto
                state.motos.forEach(moto => {
                    // Ignorer les motos avec coordonnées invalides (0,0 ou null)
                    if (!moto.latitude || !moto.longitude || 
                        (Math.abs(parseFloat(moto.latitude)) < 0.00001 && Math.abs(parseFloat(moto.longitude)) < 0.00001)) {
                        console.warn(`Moto ${moto.vin} sans coordonnées valides:`, moto.latitude, moto.longitude);
                        return;
                    }
                    
                    // Convertir en nombres flottants
                    const lat = parseFloat(moto.latitude);
                    const lng = parseFloat(moto.longitude);
                    
                    // Vérifier encore une fois que les conversions sont valides
                    if (isNaN(lat) || isNaN(lng)) {
                        console.error(`Moto ${moto.vin} a des coordonnées non numériques:`, moto.latitude, moto.longitude);
                        return;
                    }
                    
                    // Déterminer l'icône en fonction du statut de la moto
                    const icon = {
                        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                        scale: 5,
                        rotation: moto.heading || 0,
                        fillColor: moto.inZone ? '#28a745' : (moto.isActive ? '#dc3545' : '#6c757d'),
                        fillOpacity: 1,
                        strokeWeight: 1,
                        strokeColor: '#FFFFFF'
                    };
                    
                    try {
                        // Créer le marqueur
                        const marker = new google.maps.Marker({
                            position: { lat, lng },
                            map: state.map,
                            title: `${moto.vin} - ${moto.driverName || 'Non associé'}`,
                            icon: icon
                        });
                        
                        // Ajouter un événement clic pour sélectionner la moto
                        marker.addListener('click', () => selectMoto(moto));
                        
                        // Stocker le marqueur dans l'état
                        state.markers[moto.vin] = marker;
                        validMarkerCount++;
                    } catch (error) {
                        console.error(`Erreur lors de la création du marqueur pour la moto ${moto.vin}:`, error);
                    }
                });
                
                console.log(`${validMarkerCount} marqueurs ajoutés à la carte.`);
            } catch (error) {
                console.error("Erreur lors de la mise à jour des marqueurs:", error);
                showError("Erreur lors de l'affichage des motos sur la carte.");
            }
        }

        // Sélectionner une moto
        function selectMoto(moto) {
            console.log(`Moto sélectionnée: ${moto.vin}`);
            
            // Mettre à jour l'état
            state.selectedMoto = moto;
            state.detailsVisible = true;
            
            // Mettre à jour l'affichage de la liste
            updateMotoList();
            
            // Centrer la carte sur la moto sélectionnée
            if (moto.latitude && moto.longitude) {
                const lat = parseFloat(moto.latitude);
                const lng = parseFloat(moto.longitude);
                
                // Vérifier que les coordonnées sont valides
                if (!isNaN(lat) && !isNaN(lng) && 
                    !(Math.abs(lat) < 0.00001 && Math.abs(lng) < 0.00001)) {
                    state.map.setCenter({ lat, lng });
                    state.map.setZoom(15);
                }
            }
            
            // Mettre à jour les détails de la moto
            document.getElementById('detail-macid').textContent = moto.macid || 'N/A';
            document.getElementById('detail-vin').textContent = moto.vin;
            document.getElementById('detail-driver').textContent = moto.driverInfo || 'Non associé';
            
            let positionText = 'N/A';
            if (moto.latitude && moto.longitude) {
                const lat = parseFloat(moto.latitude);
                const lng = parseFloat(moto.longitude);
                
                if (!isNaN(lat) && !isNaN(lng) && 
                    !(Math.abs(lat) < 0.00001 && Math.abs(lng) < 0.00001)) {
                    positionText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                }
            }
            document.getElementById('detail-position').textContent = positionText;
            
            let statusText;
            if (!moto.isActive) {
                statusText = '⚫ Inactive';
            } else if (moto.inZone) {
                statusText = '🟢 Dans la zone';
            } else {
                statusText = '🔴 HORS ZONE';
            }
            
            document.getElementById('detail-status').textContent = statusText;
            document.getElementById('detail-lastupdate').textContent = moto.formattedLastUpdate;
            document.getElementById('detail-battery').textContent = `${moto.batteryLevel || 0}%`;
            
            // Afficher le panneau de détails
            document.getElementById('moto-details').className = 'moto-details visible';
        }

        // Basculer l'état de la sidebar
        function toggleSidebar() {
            state.sidebarCollapsed = !state.sidebarCollapsed;
            const sidebar = document.getElementById('sidebar');
            const mapContainer = document.getElementById('map-container');
            const toggleIcon = document.getElementById('toggle-icon');
            const toggleButton = document.getElementById('toggle-sidebar');
            
            if (state.sidebarCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                mapContainer.classList.add('map-expanded');
                toggleIcon.classList.add('collapsed');
                toggleButton.style.left = '0';
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                mapContainer.classList.remove('map-expanded');
                toggleIcon.classList.remove('collapsed');
                toggleButton.style.left = sidebar.offsetWidth + 'px';
            }
            
            // Recalculer la taille de la carte pour s'assurer qu'elle s'affiche correctement
            setTimeout(() => {
                google.maps.event.trigger(state.map, 'resize');
            }, 300);
        }

        // Gérer la recherche
        function handleSearch(event) {
            state.searchTerm = event.target.value.trim();
            updateMotoList();
        }

        // Basculer l'actualisation automatique
        function toggleAutoRefresh() {
            state.autoRefresh = !state.autoRefresh;
            const refreshButton = document.getElementById('refresh-button');
            
            if (state.autoRefresh) {
                refreshButton.innerHTML = '<i class="fas fa-pause"></i> Arrêter auto';
                refreshButton.style.backgroundColor = '#dc3545';
                state.autoRefreshInterval = setInterval(loadMotosData, 30000); // 30 secondes
                updateRefreshStatus("Actualisation automatique activée");
            } else {
                refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Actualiser';
                refreshButton.style.backgroundColor = '#007bff';
                clearInterval(state.autoRefreshInterval);
                updateRefreshStatus("Dernière actualisation: " + formatTime(state.lastRefresh));
            }
        }

        // Ajouter les écouteurs d'événements quand le document est chargé
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Document chargé, ajout des écouteurs d'événements...");
            
            // Écouteur pour le bouton de bascule de la sidebar
            document.getElementById('toggle-sidebar').addEventListener('click', toggleSidebar);
            
            // Écouteur pour la recherche
            document.querySelector('.search-box').addEventListener('input', handleSearch);
            
            // Écouteur pour le bouton d'actualisation
            document.getElementById('refresh-button').addEventListener('click', function() {
                if (state.autoRefresh) {
                    toggleAutoRefresh(); // Désactiver l'auto-actualisation
                } else {
                    loadMotosData(); // Actualisation manuelle
                }
            });
            
            // Écouteur pour double-clic sur le bouton d'actualisation (active l'auto-actualisation)
            document.getElementById('refresh-button').addEventListener('dblclick', function() {
                if (!state.autoRefresh) {
                    toggleAutoRefresh(); // Activer l'auto-actualisation
                }
            });
            
            // Écouteur pour fermer les détails en cliquant ailleurs sur la carte
            document.getElementById('map').addEventListener('click', function(e) {
                if (e.target === this) {
                    document.getElementById('moto-details').className = 'moto-details';
                    state.detailsVisible = false;
                }
            });
        });
    </script>
</body>
</html>