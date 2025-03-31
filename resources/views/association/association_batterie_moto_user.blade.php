@extends('layouts.app')

@section('content')
<div class="main-content">
    <!-- En-tête de la page -->
    <div class="header-container">
        <div class="title-container">
            <h1>{{ $pageTitle }}</h1>
            <div class="date" id="current-date"></div>
        </div>
    </div>

    <!-- Grille des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-link"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-associations">{{ $associations->count() }}</div>
                <div class="stat-title">Total des associations</div>
                <div class="stat-subtitle">Batteries associées</div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-batteries">{{ $batteries->count() }}</div>
                <div class="stat-title">Batteries disponibles</div>
                <div class="stat-subtitle">Prêtes à être associées</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-users">{{ $users->count() }}</div>
                <div class="stat-title">Utilisateurs</div>
                <div class="stat-subtitle">Utilisateurs validés</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="low-batteries">{{ $lowBatteries ?? 0 }}</div>
                <div class="stat-title">Batteries faibles</div>
                <div class="stat-subtitle">Niveau < 20%</div>
                </div>
            </div>
        </div>

        <!-- Barre de recherche et filtres -->
        <div class="search-filter-container">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="search-association" placeholder="Rechercher une association...">
            </div>
            <div class="filter-container">
                <select class="filter-select" id="filter-type">
                    <option value="all">Tous les types</option>
                    <option value="moto">Par moto</option>
                    <option value="user">Par utilisateur</option>
                    <option value="battery">Par batterie</option>
                    <option value="battery_status">Par statut batterie</option>
                </select>
                <select class="filter-select" id="filter-status">
                    <option value="all">Tous les statuts</option>
                    <option value="online">En ligne</option>
                    <option value="offline">Hors ligne</option>
                    <option value="charging">En charge</option>
                    <option value="discharging">En décharge</option>
                    <option value="low">Niveau faible</option>
                </select>
                <button class="action-button" id="add-association-btn">
                    <i class="fas fa-plus"></i>
                    Créer une association
                </button>
            </div>
        </div>

        <!-- Tableau des associations -->
        <div class="table-container">
            <table class="association-table">
                <thead>
                    <tr>
                        <th>ID Utilisateur</th>
                        <th>Nom Utilisateur</th>
                        <th>ID Moto</th>
                        <th>VIN Moto</th>
                        <th>Modèle Moto</th>
                        <th>ID Batterie</th>
                        <th>MAC Batterie</th>
                        <th>Niveau Batterie</th>
                        <th>Statut</th>
                        <th>Date d'Association</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="associations-table-body">
                    @if($associations->count() > 0)
                    @foreach($associations as $association)
                    @php
                    // Récupérer les données BMS pour cette batterie si disponible
                    $macId = $association->batterie->mac_id ?? null;
                    $bmsData = null;
                    $batteryLevel = 0;
                    $batteryStatus = 'Offline';
                    $workStatus = 'Inactive';
                    $workStatusCode = '0'; // Initialiser la variable

                    if ($macId) {
                        $bmsData = App\Models\BMSData::where('mac_id', $macId)
                            ->orderBy('timestamp', 'desc')
                            ->first();

                        if ($bmsData) {
                            $stateData = json_decode($bmsData->state, true);
                            $batteryLevel = $stateData['SOC'] ?? 0;

                            // Déterminer si la batterie est en ligne (dernière mise à jour < 5 min)
                            $lastBmsTime = strtotime($bmsData->timestamp);
                            $isOnline = (time() - $lastBmsTime < 300); 
                            $batteryStatus = $isOnline ? 'Online' : 'Offline';
                            
                            // Statut de fonctionnement 
                            $workStatusCode = $stateData['WorkStatus'] ?? '0';
                            $workStatusMap = [
                                '0' => 'Inactive',
                                '1' => 'Charging',
                                '2' => 'Discharging',
                                '3' => 'Idle'
                            ];
                            $workStatus = $workStatusMap[$workStatusCode] ?? 'Unknown';
                        }
                    }

                    // Classes pour l'affichage
                    $batteryLevelClass = $batteryLevel > 70 ? 'high' : ($batteryLevel > 30 ? 'medium' : 'low');
                    $statusClass = $batteryStatus === 'Online' ? 'active' : 'inactive';
                    $workStatusClass = '';

                    switch ($workStatus) {
                        case 'Charging':
                            $workStatusClass = 'charging';
                            break;
                        case 'Discharging':
                            $workStatusClass = 'discharging';
                            break;
                        case 'Idle':
                            $workStatusClass = 'idle';
                            break;
                        default:
                            $workStatusClass = 'inactive';
                    }
                    @endphp
                    <tr data-id="{{ $association->id }}" data-status="{{ $batteryStatus }}"
                        data-work-status="{{ $workStatus }}" data-level="{{ $batteryLevel }}">
                        <td>{{ $association->association->validatedUser->user_unique_id ?? 'Non défini' }}</td>
                        <td>{{ $association->association->validatedUser->nom ?? '' }}
                            {{ $association->association->validatedUser->prenom ?? '' }}</td>
                        <td>{{ $association->association->motosValide->moto_unique_id ?? 'Non défini' }}</td>
                        <td>{{ $association->association->motosValide->vin ?? 'Non défini' }}</td>
                        <td>{{ $association->association->motosValide->model ?? 'Non défini' }}</td>
                        <td>{{ $association->batterie->batterie_unique_id ?? 'Non défini' }}</td>
                        <td>{{ $association->batterie->mac_id ?? 'Non défini' }}</td>
                        <td>
                            <div class="battery-level">
                                <div class="battery-fill {{ $batteryLevelClass }}"
                                    style="width: {{ $batteryLevel }}%"></div>
                            </div>
                            <div class="battery-percentage">{{ $batteryLevel }}%</div>
                        </td>
                        <td>
                            <span class="status-badge {{ $statusClass }}">{{ $batteryStatus }}</span>
                            @if($batteryStatus === 'Online')
                            <span class="status-badge {{ $workStatusClass }}">{{ $workStatus }}</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($association->created_at)->format('d/m/Y') }}</td>
                        <td>
                            <div class="action-btns">
                                <button class="action-btn view" data-id="{{ $association->id }}"
                                    title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn edit" data-id="{{ $association->id }}"
                                    title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" data-id="{{ $association->id }}"
                                    title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                @if($macId)
                                <button class="action-btn bms" data-mac="{{ $macId }}" title="Données BMS">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="11">
                            <div class="empty-state">
                                <i class="fas fa-link"></i>
                                <p>Aucune association trouvée</p>
                                <button class="action-button" id="empty-add-association-btn">
                                    <i class="fas fa-plus"></i>
                                    Créer une association
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" id="associations-pagination">
            <!-- La pagination sera générée dynamiquement par JavaScript -->
        </div>
    </div>
</div>

<!-- Modales existantes... -->

<!-- Nouvelle Modal pour afficher les données BMS détaillées -->
<div class="modal" id="bms-modal">
    <div class="modal-content" style="width: 800px; max-width: 90%;">
        <div class="modal-header">
            <h2>Données BMS de la Batterie</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="bms-container">
                <!-- Les données seront chargées dynamiquement -->
                <div class="loading-spinner"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal-btn">Fermer</button>
        </div>
    </div>
</div>

<!-- Modale d'ajout d'association modifiée avec checkboxes -->
<div class="modal" id="add-association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Créer une nouvelle association</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="add-association-form" method="POST" action="{{ route('associations.batterie.user.store') }}">
                @csrf
              

                <!-- Section Batteries -->
                <div class="form-group">
                    <label class="form-label">Batteries</label>
                    <div class="search-box">
                        <input type="text" class="search-input" id="battery-search"
                            placeholder="Rechercher une batterie...">
                        <i class="fas fa-search search-icon"></i>
                    </div>

                    <div class="checkbox-container" id="battery-checkbox-container">
                        <div class="checkbox-loading">
                            <div class="loading-spinner"></div>
                            <p>Chargement des batteries...</p>
                        </div>
                        <!-- Les checkboxes seront ajoutées dynamiquement ici -->
                    </div>
                </div>

                <!-- Section Motos -->
                <div class="form-group">
                    <label class="form-label">Motos (avec utilisateur)</label>
                    <div class="search-box">
                        <input type="text" class="search-input" id="moto-search" placeholder="Rechercher une moto...">
                        <i class="fas fa-search search-icon"></i>
                    </div>

                    <div class="checkbox-container" id="moto-checkbox-container">
                        <div class="checkbox-loading">
                            <div class="loading-spinner"></div>
                            <p>Chargement des motos...</p>
                        </div>
                        <!-- Les checkboxes seront ajoutées dynamiquement ici -->
                    </div>
                </div>

                <div class="form-group mt-4">
                    <p><strong>Attention:</strong> Vérifiez que les batteries et les motos sélectionnées sont bien
                        celles que vous souhaitez associer.</p>
                </div>
                
                <!-- Boutons à l'intérieur du formulaire -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal-btn">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="create-association-btn">Créer l'association</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Conteneur pour les notifications toast -->
<div id="toast-container"></div>

<!-- Modale d'édition d'association modifiée avec checkboxes -->
<div class="modal" id="edit-association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Modifier l'association</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="edit-association-form" method="POST" action="{{ route('associations.batterie.user.update', ['id' => 0]) }}">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_association_id" name="association_id" value="">
                
                <!-- Section Batterie (une seule pour l'édition) -->
                <div class="form-group">
                    <label class="form-label">Batterie</label>
                    <div class="search-box">
                        <input type="text" class="search-input" id="edit-battery-search"
                            placeholder="Rechercher une batterie...">
                        <i class="fas fa-search search-icon"></i>
                    </div>

                    <div class="checkbox-container" id="edit-battery-checkbox-container">
                        <div class="checkbox-loading">
                            <div class="loading-spinner"></div>
                            <p>Chargement des batteries...</p>
                        </div>
                        <!-- Les radio buttons seront ajoutées dynamiquement ici -->
                    </div>
                </div>

                <!-- Section Moto (une seule pour l'édition) -->
                <div class="form-group">
                    <label class="form-label">Moto (avec utilisateur)</label>
                    <div class="search-box">
                        <input type="text"  class="search-input" id="edit-moto-search"
                            placeholder="Rechercher une moto...">
                        <i class="fas fa-search search-icon"></i>
                    </div>

                    <div class="checkbox-container" id="edit-moto-checkbox-container">
                        <div class="checkbox-loading">
                            <div class="loading-spinner"></div>
                            <p>Chargement des motos...</p>
                        </div>
                        <!-- Les radio buttons seront ajoutées dynamiquement ici -->
                    </div>
                </div>
                
                <!-- Boutons à l'intérieur du formulaire -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal-btn">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modale de visualisation détaillée -->
<div class="modal" id="view-association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Détails de l'association</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="detail-sections">
                <div class="detail-section">
                    <h3><i class="fas fa-user"></i> Informations Utilisateur</h3>
                    <div id="view-user-info" class="detail-content">
                        <!-- Contenu chargé dynamiquement -->
                    </div>
                </div>

                <div class="detail-section">
                    <h3><i class="fas fa-motorcycle"></i> Informations Moto</h3>
                    <div id="view-moto-info" class="detail-content">
                        <!-- Contenu chargé dynamiquement -->
                    </div>
                </div>

                <div class="detail-section">
                    <h3><i class="fas fa-battery-full"></i> Informations Batterie</h3>
                    <div id="view-battery-info" class="detail-content">
                        <!-- Contenu chargé dynamiquement -->
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-modal-btn">Fermer</button>
        </div>
    </div>
</div>


<!-- Modal de confirmation de suppression -->
<div class="modal" id="confirm-delete-modal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h2>Confirmation de suppression</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle warning-icon"></i>
                <p class="confirm-message">Êtes-vous sûr de vouloir supprimer cette association ?</p>
                <p class="text-muted">Cette action est irréversible.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary close-modal-btn">Annuler</button>
            <button type="button" class="btn btn-danger" id="confirm-delete-btn">Supprimer</button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ------- CONFIGURATION ET INITIALISATION -------
    const DEBUG = true; // Activer le mode débogage

    // Éléments du DOM fréquemment utilisés
    const searchInput = document.getElementById('search-association');
    const filterType = document.getElementById('filter-type');
    const filterStatus = document.getElementById('filter-status');
    const addAssociationBtn = document.getElementById('add-association-btn');
    const emptyAddAssociationBtn = document.getElementById('empty-add-association-btn');

    // Variables pour les checkboxes sélectionnées
    let selectedBatteries = [];
    let selectedMotos = [];
    let associationToDeleteId = null;

    // ------- FONCTIONS DE DÉBOGAGE -------
    function debugLog(message, data = null) {
        if (!DEBUG) return;

        if (data) {
            console.log(`%c[Debug] ${message}`, 'color: #2196F3; font-weight: bold;', data);
        } else {
            console.log(`%c[Debug] ${message}`, 'color: #2196F3; font-weight: bold;');
        }
    }

    function debugError(message, error = null) {
        if (!DEBUG) return;

        console.error(`%c[Error] ${message}`, 'color: #F44336; font-weight: bold;', error);
    }

    // ------- INITIALISATION -------
    function init() {
        debugLog('Initialisation du script');

        // Initialiser les événements pour les éléments de filtrage
        if (searchInput) searchInput.addEventListener('input', filterAssociations);
        if (filterType) filterType.addEventListener('change', filterAssociations);
        if (filterStatus) filterStatus.addEventListener('change', filterAssociations);

        // Initialiser les événements pour les boutons d'ajout
        if (addAssociationBtn) addAssociationBtn.addEventListener('click', openAddModal);
        if (emptyAddAssociationBtn) emptyAddAssociationBtn.addEventListener('click', openAddModal);

        // Initialiser les boutons d'action dans le tableau
        initActionButtons();

        // Initialiser les événements des modales
        initModalEvents();

        // Initialiser les toasts
        initToasts();

        // Initialiser la date
        updateCurrentDate();

        // Filtrer les associations au chargement initial
        filterAssociations();

        debugLog('Initialisation terminée');
    }

    // ------- GESTION DES MODALES -------
    function openModal(modalId) {
        debugLog(`Ouverture de la modale: ${modalId}`);
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.classList.add('modal-open');
        } else {
            debugError(`Modale non trouvée: ${modalId}`);
        }
    }

    function closeModal(modalId) {
        debugLog(`Fermeture de la modale: ${modalId}`);
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');

            // Réinitialiser le formulaire si présent
            const form = modal.querySelector('form');
            if (form) form.reset();

            // Réinitialiser les sélections
            if (modalId === 'add-association-modal') {
                selectedBatteries = [];
                selectedMotos = [];
            }

            document.body.classList.remove('modal-open');
        }
    }

    function initModalEvents() {
        // Fermeture des modales via boutons de fermeture
        document.querySelectorAll('.close-modal, .close-modal-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    closeModal(modal.id);
                }
            });
        });

        // Fermer les modales en cliquant en dehors
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal(this.id);
                }
            });
        });

        // Bouton de confirmation de suppression
        const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                closeModal('confirm-delete-modal');

                if (associationToDeleteId) {
                    deleteAssociation(associationToDeleteId);
                    associationToDeleteId = null;
                }
            });
        }
    }

    // ------- MODALES SPÉCIFIQUES -------
    function openAddModal() {
        debugLog('Ouverture de la modal d\'ajout');

        // Réinitialiser les sélections
        selectedBatteries = [];
        selectedMotos = [];

        // Charger les données
        loadBatteriesForCheckboxes();
        loadMotosForCheckboxes();

        // Initialiser les filtres de recherche
        initSearchFilters();

        // Ouvrir la modale
        openModal('add-association-modal');
    }

    function openEditModal(id) {
        debugLog(`Ouverture de la modal d'édition pour l'ID: ${id}`);

        fetch(`/associations/batterie/user/${id}/details`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                debugLog('Données de l\'association récupérées', data);

                // Mettre à jour l'ID de l'association
                const associationIdInput = document.getElementById('edit_association_id');
                if (associationIdInput) {
                    associationIdInput.value = id;
                }

                // Charger les batteries et motos avec sélection
                loadBatteriesForEdit(data.battery_unique_id);
                loadMotosForEdit(data.moto_unique_id);

                // Initialiser les filtres de recherche
                initSearchFilters();

                // Mettre à jour l'action du formulaire
                const form = document.getElementById('edit-association-form');
                if (form) {
                    form.action = `/associations/batterie/user/${id}`;
                }

                // Ouvrir la modale
                openModal('edit-association-modal');
            })
            .catch(error => {
                debugError('Erreur lors du chargement des détails pour édition', error);
                Toast.error('Erreur', `Impossible de charger les détails: ${error.message}`);
            });
    }

    function openViewModal(id) {
        debugLog(`Ouverture de la modal de visualisation pour l'ID: ${id}`);

        fetch(`/associations/batterie/user/${id}/details`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                debugLog('Données de l\'association récupérées', data);

                // Remplir la modale avec les détails
                const userInfo = document.getElementById('view-user-info');
                const motoInfo = document.getElementById('view-moto-info');
                const batteryInfo = document.getElementById('view-battery-info');

                if (userInfo) {
                    userInfo.innerHTML = `
                        <p><strong>ID Utilisateur:</strong> ${data.user_unique_id || 'Non défini'}</p>
                        <p><strong>Nom:</strong> ${data.user_nom || ''} ${data.user_prenom || ''}</p>
                    `;
                }

                if (motoInfo) {
                    motoInfo.innerHTML = `
                        <p><strong>ID Moto:</strong> ${data.moto_unique_id || 'Non défini'}</p>
                        <p><strong>VIN:</strong> ${data.moto_vin || 'Non défini'}</p>
                        <p><strong>Modèle:</strong> ${data.moto_model || 'Non défini'}</p>
                    `;
                }

                if (batteryInfo) {
                    batteryInfo.innerHTML = `
                        <p><strong>ID Batterie:</strong> ${data.battery_unique_id || 'Non défini'}</p>
                        <p><strong>MAC ID:</strong> ${data.battery_mac_id || 'Non défini'}</p>
                        <p><strong>Date d'association:</strong> ${data.date_association || 'Non défini'}</p>
                    `;
                }

                // Ouvrir la modale
                openModal('view-association-modal');
            })
            .catch(error => {
                debugError('Erreur lors du chargement des détails pour visualisation', error);
                Toast.error('Erreur', `Impossible de charger les détails: ${error.message}`);
            });
    }

    function confirmDelete(id) {
        debugLog(`Confirmation de suppression pour l'ID: ${id}`);
        associationToDeleteId = id;
        openModal('confirm-delete-modal');
    }

    // ------- CHARGEMENT DES DONNÉES POUR LES CHECKBOXES -------
    function loadBatteriesForCheckboxes() {
        debugLog('Chargement des batteries pour checkboxes');
        const container = document.getElementById('battery-checkbox-container');
        if (!container) return;

        // Afficher le chargement
        container.innerHTML = `
            <div class="checkbox-loading">
                <div class="loading-spinner"></div>
                <p>Chargement des batteries...</p>
            </div>
        `;

        // Charger les données
        fetch('/associations/batterie/user/available-batteries')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors du chargement des batteries disponibles');
                }
                return response.json();
            })
            .then(data => {
                debugLog('Batteries récupérées', data);

                if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
                    container.innerHTML = `
                        <div class="checkbox-no-results">
                            Aucune batterie disponible
                        </div>
                    `;
                    return;
                }

                // Construire les checkboxes
                let html = '';
                data.data.forEach(battery => {
                    const batteryId = battery.batterie_unique_id;
                    const macId = battery.mac_id || 'Non défini';
                    const status = battery.status || 'offline';

                    html += `
                        <div class="checkbox-item" data-id="${batteryId}" data-search="${batteryId.toLowerCase()} ${macId.toLowerCase()}">
                            <input type="checkbox" id="battery-${batteryId}" name="battery_unique_id" value="${batteryId}" class="battery-checkbox">
                            <div class="battery-info">
                                <label for="battery-${batteryId}" class="item-id">
                                    ${batteryId}
                                </label>
                                <span class="item-details">
                                    <span class="status-dot ${status.toLowerCase()}"></span> MAC: ${macId}
                                </span>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;

                // Initialiser les événements des checkboxes
                initBatteryCheckboxes();
            })
            .catch(error => {
                debugError('Erreur lors du chargement des batteries', error);
                container.innerHTML = `
                    <div class="checkbox-no-results">
                        Erreur: Impossible de charger les batteries
                    </div>
                `;
            });
    }

    function loadMotosForCheckboxes() {
        debugLog('Chargement des motos pour checkboxes');
        const container = document.getElementById('moto-checkbox-container');
        if (!container) return;

        // Afficher le chargement
        container.innerHTML = `
            <div class="checkbox-loading">
                <div class="loading-spinner"></div>
                <p>Chargement des motos...</p>
            </div>
        `;

        // Charger les données
        fetch('/associations/batterie/user/available-motos')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors du chargement des motos disponibles');
                }
                return response.json();
            })
            .then(data => {
                debugLog('Motos récupérées', data);

                if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
                    container.innerHTML = `
                        <div class="checkbox-no-results">
                            Aucune moto disponible
                        </div>
                    `;
                    return;
                }

                // Construire les checkboxes
                let html = '';
                data.data.forEach(moto => {
                    const motoId = moto.moto_unique_id;
                    const vin = moto.vin || 'Non défini';
                    const model = moto.model || '';
                    const userInfo = moto.user ? `${moto.user.nom} ${moto.user.prenom}` :
                        'Aucun utilisateur';
                    const searchText =
                        `${motoId.toLowerCase()} ${vin.toLowerCase()} ${model.toLowerCase()} ${userInfo.toLowerCase()}`;

                    html += `
                        <div class="checkbox-item" data-id="${motoId}" data-search="${searchText}">
                            <input type="checkbox" id="moto-${motoId}" name="moto_unique_id" value="${motoId}" class="moto-checkbox">
                            <div class="moto-info">
                                <label for="moto-${motoId}" class="item-id">
                                    ${motoId} - ${model}
                                </label>
                                <span class="item-details">
                                    VIN: ${vin} | Utilisateur: ${userInfo}
                                </span>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;

                // Initialiser les événements des checkboxes
                initMotoCheckboxes();
            })
            .catch(error => {
                debugError('Erreur lors du chargement des motos', error);
                container.innerHTML = `
                    <div class="checkbox-no-results">
                        Erreur: Impossible de charger les motos
                    </div>
                `;
            });
    }

    function loadBatteriesForEdit(selectedBatteryId) {
        debugLog(`Chargement des batteries pour édition, batterie sélectionnée: ${selectedBatteryId}`);
        const container = document.getElementById('edit-battery-checkbox-container');
        if (!container) return;

        // Afficher le chargement
        container.innerHTML = `
            <div class="checkbox-loading">
                <div class="loading-spinner"></div>
                <p>Chargement des batteries...</p>
            </div>
        `;

        // Charger les données
        fetch('/associations/batterie/user/available-batteries')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors du chargement des batteries disponibles');
                }
                return response.json();
            })
            .then(data => {
                debugLog('Batteries récupérées pour édition', data);

                if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
                    container.innerHTML = `
                        <div class="checkbox-no-results">
                            Aucune batterie disponible
                        </div>
                    `;
                    return;
                }

                // Construire les radios
                let html = '';
                data.data.forEach(battery => {
                    const batteryId = battery.batterie_unique_id;
                    const macId = battery.mac_id || 'Non défini';
                    const status = battery.status || 'offline';
                    const isSelected = batteryId === selectedBatteryId;

                    html += `
                        <div class="checkbox-item ${isSelected ? 'selected' : ''}" data-id="${batteryId}" data-search="${batteryId.toLowerCase()} ${macId.toLowerCase()}">
                            <input type="radio" id="edit-battery-${batteryId}" name="battery_unique_id" value="${batteryId}" class="battery-radio" ${isSelected ? 'checked' : ''}>
                            <div class="battery-info">
                                <label for="edit-battery-${batteryId}" class="item-id">
                                    ${batteryId}
                                </label>
                                <span class="item-details">
                                    <span class="status-dot ${status.toLowerCase()}"></span> MAC: ${macId}
                                </span>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;

                // Initialiser les événements des radio buttons
                initRadioButtons('edit-battery-checkbox-container', 'battery-radio');
            })
            .catch(error => {
                debugError('Erreur lors du chargement des batteries pour édition', error);
                container.innerHTML = `
                    <div class="checkbox-no-results">
                        Erreur: Impossible de charger les batteries
                    </div>
                `;
            });
    }

    function loadMotosForEdit(selectedMotoId) {
        debugLog(`Chargement des motos pour édition, moto sélectionnée: ${selectedMotoId}`);
        const container = document.getElementById('edit-moto-checkbox-container');
        if (!container) return;

        // Afficher le chargement
        container.innerHTML = `
            <div class="checkbox-loading">
                <div class="loading-spinner"></div>
                <p>Chargement des motos...</p>
            </div>
        `;

        // Charger les données
        fetch('/associations/batterie/user/available-motos')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors du chargement des motos disponibles');
                }
                return response.json();
            })
            .then(data => {
                debugLog('Motos récupérées pour édition', data);

                if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
                    container.innerHTML = `
                        <div class="checkbox-no-results">
                            Aucune moto disponible
                        </div>
                    `;
                    return;
                }

                // Construire les radios
                let html = '';
                data.data.forEach(moto => {
                    const motoId = moto.moto_unique_id;
                    const vin = moto.vin || 'Non défini';
                    const model = moto.model || '';
                    const userInfo = moto.user ? `${moto.user.nom} ${moto.user.prenom}` :
                        'Aucun utilisateur';
                    const searchText =
                        `${motoId.toLowerCase()} ${vin.toLowerCase()} ${model.toLowerCase()} ${userInfo.toLowerCase()}`;
                    const isSelected = motoId === selectedMotoId;

                    html += `
                        <div class="checkbox-item ${isSelected ? 'selected' : ''}" data-id="${motoId}" data-search="${searchText}">
                            <input type="radio" id="edit-moto-${motoId}" name="moto_unique_id" value="${motoId}" class="moto-radio" ${isSelected ? 'checked' : ''}>
                            <div class="moto-info">
                                <label for="edit-moto-${motoId}" class="item-id">
                                    ${motoId} - ${model}
                                </label>
                                <span class="item-details">
                                    VIN: ${vin} | Utilisateur: ${userInfo}
                                </span>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;

                // Initialiser les événements des radio buttons
                initRadioButtons('edit-moto-checkbox-container', 'moto-radio');
            })
            .catch(error => {
                debugError('Erreur lors du chargement des motos pour édition', error);
                container.innerHTML = `
                    <div class="checkbox-no-results">
                        Erreur: Impossible de charger les motos
                    </div>
                `;
            });
    }

    // ------- ÉVÉNEMENTS DES CHECKBOXES -------
    function initBatteryCheckboxes() {
        const container = document.getElementById('battery-checkbox-container');
        if (!container) return;

        // Gestionnaire pour les clics sur les checkboxes
        container.querySelectorAll('.battery-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const item = this.closest('.checkbox-item');

                if (this.checked) {
                    selectedBatteries.push(this.value);
                    item.classList.add('selected');
                } else {
                    selectedBatteries = selectedBatteries.filter(id => id !== this.value);
                    item.classList.remove('selected');
                }

                debugLog('Batteries sélectionnées:', selectedBatteries);
            });
        });

        // Gestionnaire pour les clics sur les éléments (pour cocher/décocher en cliquant n'importe où)
        container.querySelectorAll('.checkbox-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Ne rien faire si on a cliqué directement sur la checkbox (déjà géré)
                if (e.target.type === 'checkbox') return;

                // Trouver la checkbox et inverser son état
                const checkbox = this.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;

                // Déclencher l'événement change
                const event = new Event('change', {
                    bubbles: true
                });
                checkbox.dispatchEvent(event);
            });
        });
    }

    function initMotoCheckboxes() {
        const container = document.getElementById('moto-checkbox-container');
        if (!container) return;

        // Gestionnaire pour les clics sur les checkboxes
        container.querySelectorAll('.moto-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const item = this.closest('.checkbox-item');

                if (this.checked) {
                    selectedMotos.push(this.value);
                    item.classList.add('selected');
                } else {
                    selectedMotos = selectedMotos.filter(id => id !== this.value);
                    item.classList.remove('selected');
                }

                debugLog('Motos sélectionnées:', selectedMotos);
            });
        });

        // Gestionnaire pour les clics sur les éléments (pour cocher/décocher en cliquant n'importe où)
        container.querySelectorAll('.checkbox-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Ne rien faire si on a cliqué directement sur la checkbox (déjà géré)
                if (e.target.type === 'checkbox') return;

                // Trouver la checkbox et inverser son état
                const checkbox = this.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;

                // Déclencher l'événement change
                const event = new Event('change', {
                    bubbles: true
                });
                checkbox.dispatchEvent(event);
            });
        });
    }

    function initRadioButtons(containerId, radioClass) {
        const container = document.getElementById(containerId);
        if (!container) return;

        // Gestionnaire pour les clics sur les radios
        container.querySelectorAll(`.${radioClass}`).forEach(radio => {
            radio.addEventListener('change', function() {
                // Retirer la classe selected de tous les items
                container.querySelectorAll('.checkbox-item').forEach(item => {
                    item.classList.remove('selected');
                });

                // Ajouter la classe selected à l'item sélectionné
                if (this.checked) {
                    const item = this.closest('.checkbox-item');
                    item.classList.add('selected');
                }
            });
        });

        // Gestionnaire pour les clics sur les éléments (pour sélectionner en cliquant n'importe où)
        container.querySelectorAll('.checkbox-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Ne rien faire si on a cliqué directement sur le radio (déjà géré)
                if (e.target.type === 'radio') return;

                // Trouver le radio et le sélectionner
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;

                // Déclencher l'événement change
                const event = new Event('change', {
                    bubbles: true
                });
                radio.dispatchEvent(event);
            });
        });
    }

    function initSearchFilters() {
        // Réinitialiser et initialiser les filtres de recherche
        const filterInputs = [
            'battery-search',
            'moto-search',
            'edit-battery-search',
            'edit-moto-search'
        ];

        filterInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.value = '';

                input.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    let containerId;

                    switch (inputId) {
                        case 'battery-search':
                            containerId = 'battery-checkbox-container';
                            break;
                        case 'moto-search':
                            containerId = 'moto-checkbox-container';
                            break;
                        case 'edit-battery-search':
                            containerId = 'edit-battery-checkbox-container';
                            break;
                        case 'edit-moto-search':
                            containerId = 'edit-moto-checkbox-container';
                            break;
                    }

                    filterCheckboxItems(containerId, searchTerm);
                });
            }
        });
    }

    function filterCheckboxItems(containerId, searchTerm) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const items = container.querySelectorAll('.checkbox-item');
        let visibleCount = 0;

        items.forEach(item => {
            const searchText = item.getAttribute('data-search') || '';

            if (searchTerm === '' || searchText.includes(searchTerm)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Afficher un message si aucun résultat
        let noResultsElement = container.querySelector('.checkbox-no-results');

        if (visibleCount === 0) {
            if (!noResultsElement) {
                noResultsElement = document.createElement('div');
                noResultsElement.className = 'checkbox-no-results';
                noResultsElement.textContent = 'Aucun résultat pour cette recherche';
                container.appendChild(noResultsElement);
            }
        } else if (noResultsElement) {
            noResultsElement.remove();
        }
    }

    // ------- SOUMISSION DES FORMULAIRES -------
    function setupFormSubmission() {
        // Retirer les gestionnaires d'événements précédents pour éviter les doublons
        const forms = document.querySelectorAll('#add-association-form, #edit-association-form');
        forms.forEach(form => {
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);
        });

        // Ajouter le nouveau gestionnaire général
        document.addEventListener('submit', handleFormSubmission);
    }

    // Remplacez la section de soumission de formulaire par cette version corrigée
    // qui vérifie l'existence du bouton de soumission avant de l'utiliser

    function handleFormSubmission(e) {
        const form = e.target;

        // Ne traiter que les formulaires d'ajout ou d'édition
        if (form.id === 'add-association-form' || form.id === 'edit-association-form') {
            e.preventDefault();
            debugLog(`Soumission du formulaire détectée: ${form.id}`);

            // Vérifications pour le formulaire d'ajout
            if (form.id === 'add-association-form') {
                const selectedBatteryCheckboxes = form.querySelectorAll(
                    'input[name="battery_unique_id"]:checked');
                const selectedMotoCheckboxes = form.querySelectorAll('input[name="moto_unique_id"]:checked');

                if (selectedBatteryCheckboxes.length === 0) {
                    Toast.warning('Attention', 'Veuillez sélectionner au moins une batterie');
                    return;
                }

                if (selectedMotoCheckboxes.length === 0) {
                    Toast.warning('Attention', 'Veuillez sélectionner au moins une moto');
                    return;
                }
            }

            // Vérifications pour le formulaire d'édition
            if (form.id === 'edit-association-form') {
                const selectedBatteryRadio = form.querySelector('input[name="battery_unique_id"]:checked');
                const selectedMotoRadio = form.querySelector('input[name="moto_unique_id"]:checked');

                if (!selectedBatteryRadio) {
                    Toast.warning('Attention', 'Veuillez sélectionner une batterie');
                    return;
                }

                if (!selectedMotoRadio) {
                    Toast.warning('Attention', 'Veuillez sélectionner une moto');
                    return;
                }
            }

            // Préparation des données et envoi
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Déterminer l'URL et la méthode selon le formulaire
            let url, method, actionType;

            if (form.id === 'add-association-form') {
                url = form.getAttribute('action');
                method = 'POST';
                actionType = 'création';

                // Afficher les données pour le débogage
                debugLog('Soumission du formulaire d\'ajout:', {
                    url: url,
                    data: Object.fromEntries(formData.entries())
                });
            } else {
                // Pour le formulaire d'édition
                url = form.getAttribute('action');
                method = 'PUT';
                actionType = 'modification';

                // Vérification de l'ID de l'association
                const associationId = document.getElementById('edit_association_id')?.value;
                if (!associationId) {
                    debugError('ID de l\'association non défini pour l\'édition');
                    Toast.error('Erreur', 'ID de l\'association non défini');
                    return;
                }

                // Afficher les données pour le débogage
                debugLog('Soumission du formulaire d\'édition:', {
                    url: url,
                    associationId: associationId,
                    data: Object.fromEntries(formData.entries())
                });
            }

            // Vérifier que l'URL est correctement définie
            if (!url) {
                debugError('URL non définie pour la soumission du formulaire');
                Toast.error('Erreur', 'URL du formulaire non définie');
                return;
            }

            // Afficher un toast de chargement
            const loadingToastId = Toast.info('En cours...', `La ${actionType} de l'association est en cours`,
                0);

            // Afficher un indicateur de chargement sur le bouton (avec vérification d'existence)
            const submitButton = form.querySelector('button[type="submit"]');
            let originalText = '';

            if (submitButton) {
                originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
                submitButton.disabled = true;
            } else {
                debugLog('Bouton de soumission non trouvé dans le formulaire');
            }

            // Envoyer la requête
            fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    // Vérifier d'abord le statut HTTP
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Restaurer le bouton si existant
                    if (submitButton) {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    }

                    // Fermer le toast de chargement
                    Toast.close(loadingToastId);

                    debugLog(`Réponse reçue pour ${actionType}:`, data);

                    if (data.success) {
                        // Fermer la modale
                        const modal = form.closest('.modal');
                        if (modal) {
                            closeModal(modal.id);
                        }

                        // Afficher un message de succès
                        Toast.success('Succès', data.message ||
                            `L'${actionType} a été réalisée avec succès`);

                        // Recharger la page pour afficher les mises à jour
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        Toast.error('Erreur', data.message ||
                            `Une erreur est survenue lors de l'${actionType}`);
                    }
                })
                .catch(error => {
                    // Restaurer le bouton si existant
                    if (submitButton) {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    }

                    // Fermer le toast de chargement
                    Toast.close(loadingToastId);

                    debugError(`Erreur lors de la ${actionType}:`, error);
                    Toast.error('Erreur', `Une erreur est survenue: ${error.message}`);
                });
        }
    }

    // ------- ACTIONS SUR LES ASSOCIATIONS -------
    function initActionButtons() {
        // Boutons de visualisation
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                openViewModal(id);
            });
        });

        // Boutons d'édition
        document.querySelectorAll('.action-btn.edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                openEditModal(id);
            });
        });

        // Boutons de suppression
        document.querySelectorAll('.action-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                confirmDelete(id);
            });
        });

        // Boutons BMS
        document.querySelectorAll('.action-btn.bms').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const macId = this.dataset.mac;
                window.location.href = `/bms-details?battery=${macId}`;
            });
        });
    }

    function deleteAssociation(id) {
        debugLog(`Suppression de l'association ID: ${id}`);

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Afficher une notification de chargement
        const loadingToastId = Toast.info('En cours...', 'Suppression de l\'association en cours', 0);

        fetch(`/associations/batterie/user/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                // Fermer la notification de chargement
                Toast.close(loadingToastId);

                debugLog('Réponse de suppression reçue:', data);

                if (data.success) {
                    // Supprimer la ligne du tableau
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        row.remove();
                    }

                    // Mettre à jour les compteurs
                    updateCounters();

                    // Mettre à jour l'état vide si nécessaire
                    const rows = document.querySelectorAll('#associations-table-body tr[data-id]');
                    if (rows.length === 0) {
                        updateEmptyState(0);
                    }

                    // Afficher une notification de succès
                    Toast.success('Succès', data.message || 'Association supprimée avec succès');
                } else {
                    // Afficher une notification d'erreur
                    Toast.error('Erreur', data.message || 'Erreur lors de la suppression');
                }
            })
            .catch(error => {
                // Fermer la notification de chargement
                Toast.close(loadingToastId);

                debugError('Erreur lors de la suppression:', error);

                // Afficher une notification d'erreur
                Toast.error('Erreur', 'Impossible de supprimer l\'association: ' + error.message);
            });
    }

    // ------- FILTRES D'ASSOCIATION -------
    function filterAssociations() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const typeFilter = filterType ? filterType.value : 'all';
        const statusFilter = filterStatus ? filterStatus.value : 'all';
        const rows = document.querySelectorAll('#associations-table-body tr[data-id]');

        debugLog('Filtrage des associations:', {
            searchTerm: searchTerm,
            typeFilter: typeFilter,
            statusFilter: statusFilter
        });

        let visibleCount = 0;

        rows.forEach(row => {
            let match = true;

            // Filtre de recherche
            if (searchTerm) {
                const textContent = row.textContent.toLowerCase();
                match = textContent.includes(searchTerm);
            }

            // Filtre par type
            if (match && typeFilter !== 'all') {
                switch (typeFilter) {
                    case 'moto':
                        match = row.querySelector('td:nth-child(3)').textContent.trim() !==
                            'Non défini';
                        break;
                    case 'user':
                        match = row.querySelector('td:nth-child(1)').textContent.trim() !==
                            'Non défini';
                        break;
                    case 'battery':
                        match = row.querySelector('td:nth-child(6)').textContent.trim() !==
                            'Non défini';
                        break;
                    case 'battery_status':
                        // Ce filtre active le filtre par statut
                        break;
                }
            }

            // Filtre par statut
            if (match && statusFilter !== 'all') {
                const rowStatus = row.dataset.status?.toLowerCase() || '';
                const workStatus = row.dataset.workStatus?.toLowerCase() || '';
                const batteryLevel = parseInt(row.dataset.level || 0);

                switch (statusFilter) {
                    case 'online':
                        match = rowStatus === 'online';
                        break;
                    case 'offline':
                        match = rowStatus === 'offline';
                        break;
                    case 'charging':
                        match = workStatus === 'charging';
                        break;
                    case 'discharging':
                        match = workStatus === 'discharging';
                        break;
                    case 'low':
                        match = batteryLevel < 20;
                        break;
                }
            }

            // Afficher ou masquer la ligne
            row.style.display = match ? '' : 'none';

            if (match) {
                visibleCount++;
            }
        });

        // Afficher un message si aucun résultat
        updateEmptyState(visibleCount);

        // Mettre à jour le compteur
        const totalAssociationsElement = document.getElementById('total-associations');
        if (totalAssociationsElement) {
            totalAssociationsElement.textContent = visibleCount;
        }

        debugLog(`Filtrage terminé: ${visibleCount} associations visibles`);
    }

    function updateEmptyState(visibleCount) {
        const tableBody = document.getElementById('associations-table-body');
        if (!tableBody) return;

        let emptyRow = tableBody.querySelector('.empty-row');

        if (visibleCount === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.className = 'empty-row';
                emptyRow.innerHTML = `
                    <td colspan="11">
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <p>Aucune association ne correspond à votre recherche</p>
                        </div>
                    </td>
                `;
                tableBody.appendChild(emptyRow);
            }
        } else if (emptyRow) {
            emptyRow.remove();
        }
    }

    // ------- UTILITAIRES -------
    function updateCounters() {
        debugLog('Mise à jour des compteurs');

        fetch('/associations/batterie/user/stats')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors du chargement des statistiques');
                }
                return response.json();
            })
            .then(data => {
                debugLog('Statistiques reçues:', data);

                const totalAssociationsElement = document.getElementById('total-associations');
                const totalBatteriesElement = document.getElementById('total-batteries');
                const totalUsersElement = document.getElementById('total-users');
                const lowBatteriesElement = document.getElementById('low-batteries');

                if (totalAssociationsElement) totalAssociationsElement.textContent = data
                    .total_associations;
                if (totalBatteriesElement) totalBatteriesElement.textContent = data.total_batteries;
                if (totalUsersElement) totalUsersElement.textContent = data.total_users;
                if (lowBatteriesElement && data.low_batteries !== undefined) {
                    lowBatteriesElement.textContent = data.low_batteries;
                }
            })
            .catch(error => {
                debugError('Erreur lors de la mise à jour des compteurs:', error);
            });
    }

    function updateCurrentDate() {
        const currentDateElement = document.getElementById('current-date');
        if (currentDateElement) {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            currentDateElement.textContent = now.toLocaleDateString('fr-FR', options);
        }
    }

    // ------- SYSTÈME DE NOTIFICATIONS TOAST -------
    function initToasts() {
        // S'assurer que le conteneur existe
        const container = document.getElementById('toast-container');
        if (!container) {
            const newContainer = document.createElement('div');
            newContainer.id = 'toast-container';
            document.body.appendChild(newContainer);
        }
    }

    // Objet Toast (système de notifications)
    const Toast = {
        container: null,

        // Initialisation du système de toast
        init: function() {
            this.container = document.getElementById('toast-container');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                document.body.appendChild(this.container);
            }
        },

        // Créer une notification toast
        create: function(type, title, message, duration = 5000) {
            // S'assurer que le conteneur existe
            if (!this.container) {
                this.init();
            }

            // Créer un ID unique
            const id = 'toast-' + Date.now();

            // Déterminer l'icône en fonction du type
            let icon;
            switch (type) {
                case 'success':
                    icon = 'fas fa-check-circle';
                    break;
                case 'error':
                    icon = 'fas fa-exclamation-circle';
                    break;
                case 'warning':
                    icon = 'fas fa-exclamation-triangle';
                    break;
                case 'info':
                default:
                    icon = 'fas fa-info-circle';
                    break;
            }

            // Créer l'élément toast
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.id = id;
            toast.innerHTML = `
                <i class="${icon} toast-icon"></i>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close">&times;</button>
                <div class="toast-progress">
                    <div class="toast-progress-bar"></div>
                </div>
            `;

            // Ajouter au conteneur
            this.container.appendChild(toast);

            // Animer l'entrée (attendre le prochain cycle de rendu)
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            // Gérer la fermeture
            const closeButton = toast.querySelector('.toast-close');
            closeButton.addEventListener('click', () => {
                this.close(id);
            });

            // Animer la barre de progression
            const progressBar = toast.querySelector('.toast-progress-bar');
            progressBar.style.transition = `width ${duration}ms linear`;

            // Déclencher une reflow pour démarrer l'animation après
            progressBar.getBoundingClientRect();
            progressBar.style.width = '0%';

            // Fermer automatiquement après la durée spécifiée
            if (duration > 0) {
                setTimeout(() => {
                    this.close(id);
                }, duration);
            }

            return id;
        },

        // Afficher une notification de succès
        success: function(title, message, duration = 5000) {
            return this.create('success', title, message, duration);
        },

        // Afficher une notification d'erreur
        error: function(title, message, duration = 5000) {
            return this.create('error', title, message, duration);
        },

        // Afficher une notification d'avertissement
        warning: function(title, message, duration = 5000) {
            return this.create('warning', title, message, duration);
        },

        // Afficher une notification d'information
        info: function(title, message, duration = 5000) {
            return this.create('info', title, message, duration);
        },

        // Fermer une notification
        close: function(id) {
            const toast = document.getElementById(id);
            if (toast) {
                toast.classList.add('hide');
                toast.classList.remove('show');

                // Supprimer l'élément après l'animation
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        },

        // Fermer toutes les notifications
        closeAll: function() {
            const toasts = this.container.querySelectorAll('.toast');
            toasts.forEach(toast => {
                this.close(toast.id);
            });
        }
    };

    // ------- INITIALISATION -------
    // Initialiser le système de toast
    Toast.init();

    // Initialiser le gestionnaire de soumission de formulaire
    setupFormSubmission();

    // Initialiser l'application
    init();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Bouton ou icône pour ouvrir la modale d'édition (classe à adapter si besoin)
    document.querySelectorAll('.edit-association-btn').forEach(button => {
        button.addEventListener('click', function () {
            const associationId = this.dataset.id; // id de l'association
            const url = `/associations/batterie/user/${associationId}/details`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // Remplir l'ID de l'association caché
                    document.getElementById('edit_association_id').value = associationId;

                    // Mise à jour dynamique de l'action du formulaire
                    const form = document.getElementById('edit-association-form');
                    form.action = `/associations/batterie/user/${associationId}`;

                    // Charger les batteries et sélectionner celle de l'association
                    fetch('/associations/batterie/user/available-batteries')
                        .then(response => response.json())
                        .then(batteryData => {
                            const container = document.getElementById('edit-battery-checkbox-container');
                            container.innerHTML = ''; // Nettoyer
                            batteryData.data.forEach(battery => {
                                const checked = battery.batterie_unique_id === data.battery_unique_id ? 'checked' : '';
                                container.innerHTML += `
                                    <label class="checkbox-item">
                                        <input type="radio" name="battery_unique_id" value="${battery.batterie_unique_id}" ${checked}>
                                        ${battery.batterie_unique_id} (${battery.pourcentage}% - ${battery.status})
                                    </label>
                                `;
                            });
                        });

                    // Charger les motos et sélectionner celle de l'association
                    fetch('/associations/batterie/user/available-motos')
                        .then(response => response.json())
                        .then(motoData => {
                            const container = document.getElementById('edit-moto-checkbox-container');
                            container.innerHTML = ''; // Nettoyer
                            motoData.data.forEach(moto => {
                                const checked = moto.moto_unique_id === data.moto_unique_id ? 'checked' : '';
                                const user = moto.user ? `${moto.user.nom} ${moto.user.prenom}` : 'Utilisateur inconnu';
                                container.innerHTML += `
                                    <label class="checkbox-item">
                                        <input type="radio" name="moto_unique_id" value="${moto.moto_unique_id}" ${checked}>
                                        ${moto.moto_unique_id} (${moto.vin}) - ${user}
                                    </label>
                                `;
                            });
                        });

                    // Afficher la modale
                    document.getElementById('edit-association-modal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des données :', error);
                    alert("Impossible de charger les données de l'association.");
                });
        });
    });

    // Bouton pour fermer la modale
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('.modal').style.display = 'none';
        });
    });
    document.querySelectorAll('.close-modal-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('form').closest('.modal').style.display = 'none';
        });
    });

});
</script>

@endpush



<!-- CSS supplémentaire pour les notifications -->
<style>
/* 
 * SYSTÈME DE DESIGN UNIFIÉ
 * Styles optimisés et consolidés pour l'interface d'administration
 */

/* ======================================================
   1. VARIABLES ET CONFIGURATION
   ====================================================== */
   :root {
  /* Couleurs primaires */
  --primary: #2196F3;
  --primary-dark: #0b7dda;
  --primary-light: rgba(33, 150, 243, 0.1);
  
  /* Couleurs sémantiques */
  --success: #4CAF50;
  --success-light: rgba(76, 175, 80, 0.1);
  --warning: #FF9800;
  --warning-light: rgba(255, 152, 0, 0.1);
  --danger: #F44336;
  --danger-light: rgba(244, 67, 54, 0.1);
  --info: #17a2b8;
  
  /* Couleurs neutres */
  --dark: #343a40;
  --gray: #6c757d;
  --gray-light: #adb5bd;
  --light: #f8f9fa;
  --white: #ffffff;
  --border-color: #e9ecef;
  
  /* Typographie */
  --font-size-base: 1rem;
  --font-size-sm: 0.875rem;
  --font-size-xs: 0.75rem;
  --font-size-lg: 1.25rem;
  --font-size-xl: 1.5rem;
  
  /* Espacements - système à base de 4px */
  --space-1: 0.25rem;  /* 4px */
  --space-2: 0.5rem;   /* 8px */
  --space-3: 0.75rem;  /* 12px */
  --space-4: 1rem;     /* 16px */
  --space-5: 1.25rem;  /* 20px */
  --space-6: 1.5rem;   /* 24px */
  --space-8: 2rem;     /* 32px */
  --space-10: 2.5rem;  /* 40px */
  
  /* Bordures et ombres */
  --border-radius-sm: 4px;
  --border-radius: 6px;
  --border-radius-lg: 8px;
  --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
  --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 5px 20px rgba(0, 0, 0, 0.2);
  
  /* Transitions */
  --transition-base: all 0.2s ease;
  --transition-slow: all 0.3s ease;
  
  /* Z-index */
  --z-index-modal: 1000;
  --z-index-notification: 1100;
  --z-index-toast: 9999;
  
  /* Breakpoints */
  --breakpoint-xs: 480px;
  --breakpoint-sm: 768px;
  --breakpoint-md: 1024px;
  --breakpoint-lg: 1200px;
}

/* ======================================================
   2. RESET ET BASE
   ====================================================== */
*, *::before, *::after {
  box-sizing: border-box;
}

body.modal-open {
  overflow: hidden;
}

/* ======================================================
   3. COMPOSANTS
   ====================================================== */

/* 3.1 Boutons
   ------------------------------------------------------ */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 40px;
  padding: 0 var(--space-4);
  font-size: var(--font-size-sm);
  font-weight: 500;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition-base);
  border: none;
  outline: none;
}

.btn i {
  margin-right: var(--space-2);
}

.btn-primary {
  background-color: var(--primary);
  color: var(--white);
}

.btn-primary:hover {
  background-color: var(--primary-dark);
}

.btn-secondary {
  background-color: var(--gray);
  color: var(--white);
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.btn-danger {
  background-color: var(--danger);
  color: var(--white);
}

.btn-danger:hover {
  background-color: #c0392b;
}

.btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Action buttons pour tableaux */
.action-btn {
  width: 30px;
  height: 30px;
  border-radius: var(--border-radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  font-size: 0.85rem;
  transition: var(--transition-base);
}

.action-btn.view {
  background-color: var(--primary-light);
  color: var(--primary);
}

.action-btn.edit {
  background-color: var(--warning-light);
  color: var(--warning);
}

.action-btn.delete {
  background-color: var(--danger-light);
  color: var(--danger);
}

.action-btn:hover {
  opacity: 0.8;
}

.action-btns {
  display: flex;
  gap: var(--space-1);
}

/* 3.2 Modales
   ------------------------------------------------------ */
.modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: var(--z-index-modal);
  overflow-y: auto;
  padding: var(--space-5) 0;
}

.modal.active {
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.modal-content {
  background-color: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-lg);
  width: 700px;
  max-width: 90%;
  margin: var(--space-8) auto;
  position: relative;
  animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

.modal-header {
  padding: var(--space-4) var(--space-5);
  background-color: var(--light);
  border-bottom: 1px solid var(--border-color);
  border-top-left-radius: var(--border-radius-lg);
  border-top-right-radius: var(--border-radius-lg);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: var(--font-size-lg);
  color: var(--dark);
}

.close-modal {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--gray);
  transition: color 0.2s;
}

.close-modal:hover {
  color: var(--dark);
}

.modal-body {
  padding: var(--space-5);
  max-height: 70vh;
  overflow-y: auto;
}

.modal-footer {
  padding: var(--space-4) var(--space-5);
  border-top: 1px solid var(--border-color);
  background-color: var(--light);
  border-bottom-left-radius: var(--border-radius-lg);
  border-bottom-right-radius: var(--border-radius-lg);
  display: flex;
  justify-content: flex-end;
  gap: var(--space-2);
}

/* 3.3 Toasts et Notifications
   ------------------------------------------------------ */
#toast-container {
  position: fixed;
  bottom: var(--space-5);
  right: var(--space-5);
  z-index: var(--z-index-toast);
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  max-width: 350px;
  width: 100%;
}

.toast {
  background-color: var(--white);
  border-radius: var(--border-radius);
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  margin-bottom: var(--space-2);
  display: flex;
  align-items: flex-start;
  padding: var(--space-3) var(--space-4);
  transform: translateX(150%);
  animation: slideIn 0.3s forwards;
  position: relative;
  border-left: 4px solid var(--primary);
}

.toast.success { border-left-color: var(--success); }
.toast.error { border-left-color: var(--danger); }
.toast.warning { border-left-color: var(--warning); }
.toast.info { border-left-color: var(--info); }

.toast-icon {
  font-size: 18px;
  margin-right: var(--space-3);
  flex-shrink: 0;
}

.toast.success .toast-icon { color: var(--success); }
.toast.error .toast-icon { color: var(--danger); }
.toast.warning .toast-icon { color: var(--warning); }
.toast.info .toast-icon { color: var(--info); }

.toast-content {
  flex: 1;
}

.toast-title {
  font-weight: 600;
  margin-bottom: var(--space-1);
  color: var(--dark);
}

.toast-message {
  color: var(--gray);
  font-size: var(--font-size-xs);
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  color: var(--gray-light);
  font-size: 16px;
  cursor: pointer;
  padding: 0;
}

.toast-close:hover {
  color: var(--gray);
}

.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background-color: rgba(0, 0, 0, 0.1);
}

.toast-progress-bar {
  height: 100%;
  background-color: rgba(0, 0, 0, 0.2);
  width: 100%;
  transition: width linear;
}

.toast.show {
  transform: translateX(0);
  opacity: 1;
}

.toast.hide {
  transform: translateX(100%);
  opacity: 0;
}

@keyframes slideIn {
  to {
    transform: translateX(0);
  }
}

/* 3.4 Formulaires et Champs
   ------------------------------------------------------ */
.form-group {
  margin-bottom: var(--space-5);
}

.form-label {
  display: block;
  margin-bottom: var(--space-2);
  font-weight: 600;
  color: var(--dark);
}

.search-box {
  position: relative;
  margin-bottom: var(--space-3);
}

.search-input {
  display: block;
  width: 100%;
  padding: var(--space-2) var(--space-4) var(--space-2) var(--space-10);
  border: 1px solid #ced4da;
  border-radius: var(--border-radius);
  font-size: var(--font-size-sm);
  transition: var(--transition-base);
  background-color: var(--light);
}

.search-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.25);
  outline: none;
  background-color: var(--white);
}

.search-icon {
  position: absolute;
  left: var(--space-3);
  top: 50%;
  transform: translateY(-50%);
  color: var(--gray);
  font-size: 16px;
}

/* 3.5 Checkbox Containers
   ------------------------------------------------------ */
.checkbox-container {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #ddd;
  border-radius: var(--border-radius);
  background-color: var(--white);
  padding: 0;
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
}

.checkbox-container::-webkit-scrollbar {
  width: 8px;
}

.checkbox-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.checkbox-container::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.checkbox-container::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}

.checkbox-item {
  padding: var(--space-3) var(--space-4);
  border-bottom: 1px solid #eee;
  display: flex;
  align-items: flex-start;
  cursor: pointer;
  transition: var(--transition-base);
  position: relative;
}

.checkbox-item:last-child {
  border-bottom: none;
}

.checkbox-item:hover {
  background-color: #f5f9ff;
}

.checkbox-item.selected {
  background-color: #e8f4ff;
  border-left: 4px solid var(--primary);
}

.checkbox-item input[type="radio"] {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  margin-right: var(--space-3);
  border: 2px solid #ced4da;
  border-radius: 50%;
  background-color: var(--white);
  flex-shrink: 0;
  cursor: pointer;
  transition: var(--transition-base);
  outline: none;
  position: relative;
  top: 2px;
}

.checkbox-item input[type="radio"]:checked {
  border-color: var(--primary);
  border-width: 6px;
}

.checkbox-item input[type="radio"]:focus {
  box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.25);
}

.checkbox-item .battery-info,
.checkbox-item .moto-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.checkbox-item .item-id {
  font-weight: 600;
  font-size: var(--font-size-sm);
  color: var(--dark);
  margin-bottom: var(--space-1);
  display: block;
  cursor: pointer;
}

.checkbox-item .item-details {
  font-size: var(--font-size-xs);
  color: var(--gray);
  display: block;
}

.checkbox-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--space-8);
  text-align: center;
}

.loading-spinner {
  width: 30px;
  height: 30px;
  border: 3px solid rgba(52, 152, 219, 0.2);
  border-radius: 50%;
  border-top-color: var(--primary);
  animation: spin 1s linear infinite;
  margin-bottom: var(--space-4);
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.checkbox-no-results {
  padding: var(--space-5);
  text-align: center;
  color: var(--gray);
  font-style: italic;
  background-color: var(--light);
  border-radius: var(--border-radius-sm);
  margin: var(--space-2);
}

/* 3.6 Tables
   ------------------------------------------------------ */
.table-container {
  background-color: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  margin-bottom: var(--space-6);
}

.association-table {
  width: 100%;
  border-collapse: collapse;
}

.association-table thead th {
  background-color: var(--light);
  padding: var(--space-3) var(--space-4);
  text-align: left;
  font-weight: 600;
  color: var(--dark);
  border-bottom: 1px solid #eee;
}

.association-table tbody td {
  padding: var(--space-3) var(--space-4);
  border-bottom: 1px solid #eee;
  color: #333;
}

.association-table tbody tr:hover {
  background-color: var(--light);
}

/* 3.7 Cards et Stats
   ------------------------------------------------------ */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--space-4);
  margin-bottom: var(--space-6);
}

.stat-card {
  display: flex;
  align-items: center;
  padding: var(--space-5);
  border-radius: var(--border-radius-lg);
  background-color: var(--white);
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow);
}

.stat-card.total {
  border-left: 4px solid var(--primary);
}

.stat-card.pending {
  border-left: 4px solid var(--warning);
}

.stat-card.success {
  border-left: 4px solid var(--success);
}

.stat-card.danger {
  border-left: 4px solid var(--danger);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: var(--space-4);
  flex-shrink: 0;
}

.stat-card.total .stat-icon {
  background-color: var(--primary-light);
  color: var(--primary);
}

.stat-card.pending .stat-icon {
  background-color: var(--warning-light);
  color: var(--warning);
}

.stat-card.success .stat-icon {
  background-color: var(--success-light);
  color: var(--success);
}

.stat-card.danger .stat-icon {
  background-color: var(--danger-light);
  color: var(--danger);
}

.stat-icon i {
  font-size: 1.5rem;
}

.stat-details {
  flex-grow: 1;
}

.stat-number {
  font-size: var(--font-size-xl);
  font-weight: 600;
  color: var(--dark);
  margin-bottom: var(--space-1);
}

.stat-title {
  font-size: var(--font-size-base);
  color: var(--dark);
  margin-bottom: var(--space-1);
}

.stat-subtitle {
  font-size: var(--font-size-xs);
  color: var(--gray);
}

/* 3.8 Indicators
   ------------------------------------------------------ */
.battery-level {
  width: 100%;
  height: 8px;
  background-color: #eee;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: var(--space-1);
}

.battery-fill {
  height: 100%;
  border-radius: 4px;
}

.battery-fill.high {
  background-color: var(--success);
}

.battery-fill.medium {
  background-color: var(--warning);
}

.battery-fill.low {
  background-color: var(--danger);
}

.battery-percentage {
  font-size: var(--font-size-xs);
  color: var(--gray);
}

/* 3.9 Status Badges
   ------------------------------------------------------ */
.status-badge {
  display: inline-block;
  padding: 0.25em 0.6em;
  font-size: 75%;
  font-weight: 600;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 0.25rem;
  margin-right: 0.25rem;
  margin-bottom: 0.25rem;
}

.status-badge.active {
  background-color: var(--success-light);
  color: var(--success);
}

.status-badge.inactive {
  background-color: var(--gray-light);
  color: var(--gray);
}

.status-badge.charging {
  background-color: rgba(52, 152, 219, 0.1);
  color: #3498db;
}

.status-badge.discharging {
  background-color: rgba(255, 193, 7, 0.1);
  color: #ffc107;
}

.status-badge.idle {
  background-color: rgba(108, 117, 125, 0.1);
  color: #6c757d;
}

/* 3.10 Details and Information Sections
   ------------------------------------------------------ */
.detail-sections {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-5);
}

.detail-section {
  border: 1px solid #eee;
  border-radius: var(--border-radius);
  overflow: hidden;
}

.detail-section h3 {
  margin: 0;
  padding: var(--space-3) var(--space-4);
  background-color: var(--light);
  font-size: 1.1rem;
  border-bottom: 1px solid #eee;
}

.detail-section h3 i {
  margin-right: var(--space-2);
  color: var(--primary);
}

.detail-content {
  padding: var(--space-4);
}

.detail-content p {
  margin: 0 0 var(--space-2) 0;
  color: #333;
}

.detail-content p:last-child {
  margin-bottom: 0;
}

/* ======================================================
   4. LAYOUT
   ====================================================== */
.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-5);
}

.title-container {
  display: flex;
  flex-direction: column;
}

.title-container h1 {
  margin: 0;
  color: var(--dark);
}

.date {
  font-size: 0.9rem;
  color: var(--gray);
  margin-top: var(--space-1);
}

.search-filter-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-5);
  flex-wrap: wrap;
  gap: var(--space-4);
}

.search-container {
  position: relative;
  flex-grow: 1;
  max-width: 400px;
}

.search-container i {
  position: absolute;
  left: var(--space-3);
  top: 50%;
  transform: translateY(-50%);
  color: var(--gray);
}

#search-association {
  width: 100%;
  padding: var(--space-2) var(--space-2) var(--space-2) var(--space-10);
  border: 1px solid #ddd;
  border-radius: var(--border-radius-sm);
  font-size: var(--font-size-base);
}

#search-association:focus {
  border-color: var(--primary);
  outline: none;
}

.filter-container {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: var(--space-2);
}

.filter-select {
  padding: var(--space-2) var(--space-3);
  border: 1px solid #ddd;
  border-radius: var(--border-radius-sm);
  background-color: var(--white);
  font-size: 0.9rem;
}

.filter-select:focus {
  border-color: var(--primary);
  outline: none;
}

/* ======================================================
   5. UTILITAIRES & ÉTATS
   ====================================================== */
.highlight {
  animation: highlight 1s ease-in-out;
}

@keyframes highlight {
  0% { background-color: transparent; }
  50% { background-color: rgba(52, 152, 219, 0.2); }
  100% { background-color: transparent; }
}

.loading {
  animation: pulse 1.5s infinite ease-in-out;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.empty-state {
  padding: var(--space-10);
  text-align: center;
  color: var(--gray);
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: var(--space-4);
  opacity: 0.5;
}

.empty-state p {
  margin-bottom: var(--space-5);
}

/* ======================================================
   6. RESPONSIVE
   ====================================================== */
@media (max-width: var(--breakpoint-md)) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .table-container {
    overflow-x: auto;
  }

  .association-table {
    min-width: 1000px;
  }
}

@media (max-width: var(--breakpoint-sm)) {
  .header-container {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-2);
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .search-filter-container {
    flex-direction: column;
    align-items: stretch;
  }

  .search-container {
    max-width: none;
  }

  .filter-container {
    justify-content: space-between;
  }

  .modal-content {
    width: 95%;
    margin: var(--space-2) auto;
  }

  .modal-body {
    max-height: 60vh;
  }

  .checkbox-container {
    max-height: 200px;
  }

  .checkbox-item {
    padding: var(--space-2);
  }

  .checkbox-item .item-id {
    font-size: 13px;
  }

  .checkbox-item .item-details {
    font-size: 11px;
  }
}

@media (max-width: var(--breakpoint-xs)) {
  #toast-container {
    left: var(--space-2);
    right: var(--space-2);
    max-width: none;
  }

  .toast {
    width: 100%;
  }
}
</style>