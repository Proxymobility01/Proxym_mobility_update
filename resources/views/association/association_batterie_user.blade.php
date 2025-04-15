@extends('layouts.app')

<style>
/* Couleurs d'application */
:root {
    --primary: #DCDB32;
    --secondary: #101010;
    --tertiary: #F3F3F3;
    --background: #ffffff;
    --text: #101010;
    --sidebar: #F8F8F8;
}

/* Styles pour les modales */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: var(--background);
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 800px;
    max-width: 90%;
    border-radius: 8px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}

.close-modal {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: var(--secondary);
}

.modal-body {
    padding: 20px 0;
}

.modal.active {
    display: block;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: var(--secondary);
}

.form-group select,
.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #ddd;
    padding-top: 10px;
}

.btn {
    padding: 8px 15px;
    margin-left: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-primary {
    background-color: var(--primary);
    color: var(--secondary);
}

/* Styles pour les cartes de statistiques */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    display: flex;
    align-items: center;
    background-color: var(--background);
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background-color: var(--tertiary);
    border-radius: 50%;
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.total .stat-icon {
    color: var(--primary);
}

.pending .stat-icon {
    color: #ffc107;
}

.success .stat-icon {
    color: #28a745;
}

.stat-details {
    flex-grow: 1;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
}

.stat-label {
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-text {
    color: #6c757d;
    font-size: 0.9em;
}

/* Barre de recherche et filtres */
.search-bar {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.search-group {
    display: flex;
    align-items: center;
}

.search-group input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 300px;
}

.search-btn {
    background-color: var(--tertiary);
    border: 1px solid #ddd;
    border-left: none;
    padding: 8px 12px;
    cursor: pointer;
}

.filter-group {
    display: flex;
    align-items: center;
}

.add-btn {
    background-color: var(--primary);
    color: var(--secondary);
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.add-btn i {
    margin-right: 5px;
}

/* Table */
.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background-color: var(--tertiary);
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

tr:hover {
    background-color: var(--tertiary);
}

.action-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    margin-right: 5px;
}

.action-btn i {
    font-size: 16px;
}

.action-btn.edit-battery-association i {
    color: #ffc107;
}

.action-btn.delete-battery-association i {
    color: #dc3545;
}

.action-btn.view-battery-association i {
    color: #17a2b8;
}

/* Toast */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    padding: 12px 20px;
    border-radius: 4px;
    margin-top: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: opacity 0.3s ease;
}

.toast-success {
    background-color: var(--primary);
    color: var(--secondary);
}

.toast-error {
    background-color: #dc3545;
    color: white;
}

.toast-warning {
    background-color: #ffc107;
    color: var(--secondary);
}

/* Content header */
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.date {
    color: #6c757d;
    font-size: 0.9em;
}

/* Styles pour la modale d'association avec checkboxes */
.association-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.association-column {
    width: 100%;
    margin-bottom: 20px;
}

.association-column h3 {
    margin-top: 0;
    margin-bottom: 12px;
    font-size: 16px;
    color: var(--secondary);
    padding-bottom: 6px;
    border-bottom: 2px solid var(--primary);
    display: inline-block;
}

.search-box {
    margin-bottom: 12px;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 8px 12px 8px 32px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(220, 219, 50, 0.2);
}

.search-box::before {
    content: "🔍";
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    opacity: 0.5;
}

.checkbox-list {
    border: 1px solid #ddd;
    border-radius: 8px;
    max-height: 200px;
    overflow-y: auto;
    padding: 5px;
    background-color: #f8f8f8;
}

.checkbox-item {
    margin-bottom: 5px;
    padding: 8px 10px;
    border-radius: 6px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    border: 1px solid transparent;
    cursor: pointer;
}

.checkbox-item:hover {
    background-color: #f0f0f0;
    border-color: #ddd;
}

.checkbox-item.selected {
    background-color: rgba(220, 219, 50, 0.15);
    border-color: var(--primary);
}

.checkbox-item input[type="checkbox"] {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid #ccc;
    border-radius: 4px;
    outline: none;
    transition: all 0.2s;
    position: relative;
    cursor: pointer;
    margin-right: 10px;
}

.checkbox-item input[type="checkbox"]:checked {
    border-color: var(--primary);
    background-color: var(--primary);
}

.checkbox-item input[type="checkbox"]:checked::before {
    content: "✓";
    display: block;
    color: var(--secondary);
    font-size: 14px;
    font-weight: bold;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.checkbox-item label {
    display: inline-block;
    cursor: pointer;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

/* Nav tabs pour la navigation entre motos et batteries */
.nav-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.nav-tab {
    padding: 10px 20px;
    margin-right: 5px;
    border: 1px solid transparent;
    border-bottom: none;
    border-radius: 5px 5px 0 0;
    cursor: pointer;
}

.nav-tab.active {
    background-color: var(--primary);
    color: var(--secondary);
    border-color: #ddd;
    font-weight: bold;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
</style>

@section('content')
<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>{{ $pageTitle }}</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Onglets de navigation -->
    <div class="nav-tabs">
        <div class="nav-tab" data-tab="moto-user">Associations Moto-Utilisateur</div>
        <div class="nav-tab active" data-tab="battery-user">Associations Batterie-Utilisateur</div>
    </div>

    <!-- Contenu de l'onglet Batterie-Utilisateur -->
    <div id="battery-user-tab" class="tab-content active">
        <!-- Barre de recherche et ajout -->
        <div class="search-bar">
            <div class="search-group">
                <input type="text" id="search-battery-association" placeholder="Rechercher une association...">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <div class="filter-group">
                <button id="add-battery-association-btn" class="add-btn">
                    <i class="fas fa-plus"></i>
                    Associer Batterie & Utilisateur
                </button>
            </div>
        </div>

        <!-- Cartes des statistiques -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="fas fa-link"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-number" id="total-battery-associations">{{ $associations->count() }}</div>
                    <div class="stat-label">Total des associations</div>
                    <div class="stat-text">Batteries associées aux utilisateurs</div>
                </div>
            </div>

            <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-battery-full"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-number" id="total-batteries">{{ $batteries->count() }}</div>
                    <div class="stat-label">Batteries disponibles</div>
                    <div class="stat-text">Prêtes à être associées</div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <div class="stat-number" id="total-users">{{ $users->count() }}</div>
                    <div class="stat-label">Utilisateurs</div>
                    <div class="stat-text">Utilisateurs validés</div>
                </div>
            </div>
        </div>

        <!-- Tableau des associations -->
        <div class="table-container">
            <table id="battery-associations-table">
                <thead>
                    <tr>
                        <th>ID Unique Utilisateur</th>
                        <th>Nom Utilisateur</th>
                        <th>ID Unique Moto</th>
                        <th>Modèle Moto</th>
                        <th>ID Batterie</th>
                        <th>MAC Batterie</th>
                        <th>Date d'Association</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="battery-associations-table-body">
                    @foreach($associations as $association)
                    <tr data-id="{{ $association->id }}">
                        <td>{{ $association->association->validatedUser->user_unique_id ?? 'Non défini' }}</td>
                        <td>{{ $association->association->validatedUser->nom ?? '' }}
                            {{ $association->association->validatedUser->prenom ?? '' }}</td>
                        <td>{{ $association->association->motosValide->moto_unique_id ?? 'Non défini' }}</td>
                        <td>{{ $association->association->motosValide->model ?? 'Non défini' }}</td>
                        <td>{{ $association->batterie->batterie_unique_id ?? 'Non défini' }}</td>
                        <td>{{ $association->batterie->mac_id ?? 'Non défini' }}</td>
                        <td>{{ \Carbon\Carbon::parse($association->created_at)->format('d/m/Y') }}</td>
                        <td>
                            <button class="action-btn edit-battery-association" title="Modifier l'association">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('associations.batterie.user.destroy', $association->id) }}"
                                method="POST" style="display:inline;"
                                onsubmit="return confirm('Voulez-vous vraiment supprimer cette association?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete-battery-association"
                                    title="Supprimer l'association">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modale d'association Batterie-Utilisateur avec checkboxes -->
<div class="modal" id="battery-association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="battery-modal-title">Associer Batterie & Utilisateur</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="battery-association-form" method="POST" action="{{ route('associations.batterie.user.store') }}">
            @csrf
            @method('PUT')
                <input type="hidden" id="edit-battery-association-id" name="id">
                <input type="hidden" id="battery-form-method" name="_method" value="POST">
                
                <div class="association-container">
                    <!-- Section Motos (déjà associées à des utilisateurs) -->
                    <div class="association-column">
                        <h3>Liste des Motos associées à des utilisateurs</h3>
                        <div class="search-box">
                            <input type="text" id="search-moto" placeholder="Rechercher une moto par VIN ou ID">
                        </div>
                        <div class="checkbox-list" id="motos-list">
                            @foreach ($motos as $moto)
                            <div class="checkbox-item">
                                <input type="checkbox" name="moto_unique_id[]" id="moto-{{ $moto->moto_unique_id }}" value="{{ $moto->moto_unique_id }}" class="moto-checkbox">
                                <label for="moto-{{ $moto->moto_unique_id }}">
                                    {{ $moto->model }} ({{ $moto->moto_unique_id }}) - {{ $moto->users->first() ? $moto->users->first()->nom . ' ' . $moto->users->first()->prenom : 'Non associé' }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Section Batteries -->
                    <div class="association-column">
                        <h3>Liste des Batteries</h3>
                        <div class="search-box">
                            <input type="text" id="search-battery" placeholder="Rechercher une batterie par ID">
                        </div>
                        <div class="checkbox-list" id="batteries-list">
                            @foreach ($batteries as $battery)
                            <div class="checkbox-item">
                                <input type="checkbox" name="battery_unique_id[]" id="battery-{{ $battery->batterie_unique_id }}" value="{{ $battery->batterie_unique_id }}" class="battery-checkbox">
                                <label for="battery-{{ $battery->batterie_unique_id }}">
                                    {{ $battery->batterie_unique_id }} (MAC: {{ $battery->mac_id ?? 'Non défini' }})
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-battery-association" class="btn btn-primary">Associer</button>
        </div>
    </div>
</div>

<!-- Modale de confirmation d'association multiple -->
<div class="modal" id="confirm-battery-association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmation d'association</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Cette batterie est déjà associée à un ou plusieurs utilisateurs. Voulez-vous l'associer à cette moto supplémentaire?</p>
            <form id="confirm-battery-association-form" method="POST"
                action="{{ route('associations.batterie.user.confirm') }}">
                @csrf
                <input type="hidden" id="confirm-battery-id" name="battery_id">
                <input type="hidden" id="confirm-moto-id" name="moto_id">
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-battery-association-btn" class="btn btn-primary">Confirmer</button>
        </div>
    </div>
</div>

<!-- Modale de suppression d'association -->
<div class="modal" id="delete-battery-association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Supprimer l'association</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette association ?</p>
            <div class="association-details">
                <p><strong>Utilisateur :</strong> <span id="delete-battery-user-name"></span></p>
                <p><strong>Moto :</strong> <span id="delete-battery-moto-id"></span></p>
                <p><strong>Batterie :</strong> <span id="delete-battery-id"></span></p>
            </div>
            <form id="delete-battery-association-form" method="POST">
                @csrf
                @method('DELETE')
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-delete-battery-association" class="btn btn-primary">Supprimer</button>
        </div>
    </div>
</div>

<script>
 document.addEventListener('DOMContentLoaded', function() {
    // ------------------------------------------------------------
    // Initialisation et variables
    // ------------------------------------------------------------
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const searchBatteryInput = document.getElementById('search-battery-association');
    const addBatteryAssociationBtn = document.getElementById('add-battery-association-btn');
    const batteryAssociationModal = document.getElementById('battery-association-modal');
    const confirmBatteryAssociationModal = document.getElementById('confirm-battery-association-modal');
    const deleteBatteryAssociationModal = document.getElementById('delete-battery-association-modal');
    const batteryAssociationsTableBody = document.getElementById('battery-associations-table-body');

    // Recherche dans les listes de checkboxes
    const searchMotoInput = document.getElementById('search-moto');
    const searchBatteryListInput = document.getElementById('search-battery');

    // Navigation par onglets
    const navTabs = document.querySelectorAll('.nav-tab');

    // Afficher la date actuelle
    const dateElement = document.getElementById('date');
    const today = new Date();
    dateElement.textContent = today.toLocaleDateString('fr-FR');

    // ------------------------------------------------------------
    // Fonction pour initialiser les événements des checkboxes
    // ------------------------------------------------------------
    function initBatteryCheckboxEvents() {
        // Gérer le clic sur les checkboxes des motos
        const motoCheckboxes = document.querySelectorAll('.moto-checkbox');
        motoCheckboxes.forEach(checkbox => {
            const checkboxItem = checkbox.closest('.checkbox-item');

            // Appliquer la classe 'selected' si déjà sélectionné au chargement
            if (checkbox.checked) {
                checkboxItem.classList.add('selected');
            }

            // Gérer le changement d'état du checkbox
            checkbox.addEventListener('change', function() {
                // Ajouter/Supprimer la classe 'selected' en fonction de l'état
                if (this.checked) {
                    checkboxItem.classList.add('selected');

                    // Si en mode édition (sélection unique), désélectionner les autres
                    if (document.getElementById('battery-form-method').value === 'PUT') {
                        motoCheckboxes.forEach(cb => {
                            if (cb !== checkbox && cb.checked) {
                                cb.checked = false;
                                cb.closest('.checkbox-item').classList.remove('selected');
                            }
                        });
                    }
                } else {
                    checkboxItem.classList.remove('selected');
                }
            });

            // Permettre de cliquer sur l'élément entier pour cocher/décocher
            checkboxItem.addEventListener('click', function(e) {
                // Vérifier que le clic n'est pas sur le checkbox lui-même
                if (e.target !== checkbox && e.target !== checkbox.nextElementSibling) {
                    checkbox.checked = !checkbox.checked;

                    // Déclencher l'événement 'change' pour appliquer les styles
                    const changeEvent = new Event('change');
                    checkbox.dispatchEvent(changeEvent);
                }
            });
        });

        // Gérer le clic sur les checkboxes des batteries
        const batteryCheckboxes = document.querySelectorAll('.battery-checkbox');
        batteryCheckboxes.forEach(checkbox => {
            const checkboxItem = checkbox.closest('.checkbox-item');

            // Appliquer la classe 'selected' si déjà sélectionné au chargement
            if (checkbox.checked) {
                checkboxItem.classList.add('selected');
            }

            // Gérer le changement d'état du checkbox
            checkbox.addEventListener('change', function() {
                // Ajouter/Supprimer la classe 'selected' en fonction de l'état
                if (this.checked) {
                    checkboxItem.classList.add('selected');

                    // Si en mode édition (sélection unique), désélectionner les autres
                    if (document.getElementById('battery-form-method').value === 'PUT') {
                        batteryCheckboxes.forEach(cb => {
                            if (cb !== checkbox && cb.checked) {
                                cb.checked = false;
                                cb.closest('.checkbox-item').classList.remove('selected');
                            }
                        });
                    }
                } else {
                    checkboxItem.classList.remove('selected');
                }
            });

            // Permettre de cliquer sur l'élément entier pour cocher/décocher
            checkboxItem.addEventListener('click', function(e) {
                // Vérifier que le clic n'est pas sur le checkbox lui-même
                if (e.target !== checkbox && e.target !== checkbox.nextElementSibling) {
                    checkbox.checked = !checkbox.checked;

                    // Déclencher l'événement 'change' pour appliquer les styles
                    const changeEvent = new Event('change');
                    checkbox.dispatchEvent(changeEvent);
                }
            });
        });
    }

    // ------------------------------------------------------------
    // Gestion de la navigation par onglets
    // ------------------------------------------------------------
    navTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Enlever la classe active de tous les onglets
            navTabs.forEach(t => t.classList.remove('active'));

            // Ajouter la classe active à l'onglet cliqué
            this.classList.add('active');

            // Gérer la redirection selon l'onglet
            if (this.dataset.tab === 'moto-user') {
                window.location.href = "/associations";
            } else {
                // Déjà sur l'onglet batterie-user, pas besoin de redirection
            }
        });
    });

    // ------------------------------------------------------------
    // Fonctions de gestion des modales
    // ------------------------------------------------------------
    function openAddBatteryAssociationModal() {
        // Réinitialiser les checkboxes
        document.querySelectorAll('.checkbox-item').forEach(item => {
            item.classList.remove('selected');
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
        });

        // Réinitialiser le formulaire
        document.getElementById('battery-association-form').reset();
        document.getElementById('battery-form-method').value = 'POST';
        document.getElementById('edit-battery-association-id').value = '';

        // Mettre à jour le titre
        document.getElementById('battery-modal-title').textContent = 'Associer Batterie & Utilisateur';
        document.getElementById('battery-association-form').action = "/associations/batterie/store";

        // Permettre la sélection multiple
        enableBatteryMultipleSelection(true);

        // Réinitialiser les champs de recherche
        if (searchMotoInput) searchMotoInput.value = '';
        if (searchBatteryListInput) searchBatteryListInput.value = '';

        // Afficher tous les éléments qui pourraient avoir été masqués
        document.querySelectorAll('#motos-list .checkbox-item, #batteries-list .checkbox-item').forEach(item => {
            item.style.display = '';
        });

        // Afficher la modale
        batteryAssociationModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function openEditBatteryAssociationModal(row) {
        const id = row.dataset.id;
        
        // Charger les détails de l'association depuis l'API
        fetch(`/associations/batterie/${id}/details`)
            .then(response => response.json())
            .then(data => {
                // Réinitialiser les checkboxes
                document.querySelectorAll('.checkbox-item').forEach(item => {
                    item.classList.remove('selected');
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = false;
                });

                // Réinitialiser le formulaire
                document.getElementById('battery-association-form').reset();
                document.getElementById('battery-form-method').value = 'PUT';
                document.getElementById('edit-battery-association-id').value = id;

                // Mettre à jour le titre et l'action du formulaire
                document.getElementById('battery-modal-title').textContent = 'Modifier l\'association';
                document.getElementById('battery-association-form').action = `/associations/batterie/${id}`;

                // Cocher les bonnes cases
                if (document.getElementById(`moto-${data.moto_unique_id}`)) {
                    document.getElementById(`moto-${data.moto_unique_id}`).checked = true;
                    document.getElementById(`moto-${data.moto_unique_id}`).closest('.checkbox-item').classList.add('selected');
                }

                if (document.getElementById(`battery-${data.battery_unique_id}`)) {
                    document.getElementById(`battery-${data.battery_unique_id}`).checked = true;
                    document.getElementById(`battery-${data.battery_unique_id}`).closest('.checkbox-item').classList.add('selected');
                }

                // Limiter à une seule sélection
                enableBatteryMultipleSelection(false);

                // Réinitialiser les champs de recherche
                if (searchMotoInput) searchMotoInput.value = '';
                if (searchBatteryListInput) searchBatteryListInput.value = '';

                // Afficher tous les éléments qui pourraient avoir été masqués
                document.querySelectorAll('#motos-list .checkbox-item, #batteries-list .checkbox-item').forEach(item => {
                    item.style.display = '';
                });

                // Afficher la modale
                batteryAssociationModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            })
            .catch(error => {
                showToast('Erreur lors du chargement des détails de l\'association', 'error');
                console.error('Erreur:', error);
            });
    }

    function openConfirmBatteryAssociationModal(batteryId, motoId) {
        document.getElementById('confirm-battery-id').value = batteryId;
        document.getElementById('confirm-moto-id').value = motoId;
        confirmBatteryAssociationModal.classList.add('active');
    }

    function openDeleteBatteryAssociationModal(row) {
        const id = row.dataset.id;
        const userName = row.querySelector('td:nth-child(2)').textContent.trim();
        const motoId = row.querySelector('td:nth-child(3)').textContent;
        const motoModel = row.querySelector('td:nth-child(4)').textContent;
        const batteryId = row.querySelector('td:nth-child(5)').textContent;
        const batteryMac = row.querySelector('td:nth-child(6)').textContent;

        // Configurer la modale
        document.getElementById('delete-battery-user-name').textContent = userName;
        document.getElementById('delete-battery-moto-id').textContent = `${motoModel} (${motoId})`;
        document.getElementById('delete-battery-id').textContent = `${batteryId} (MAC: ${batteryMac})`;

        // Modifier l'action du formulaire
        const form = document.getElementById('delete-battery-association-form');
        form.action = `/associations/batterie/${id}`;

        deleteBatteryAssociationModal.classList.add('active');
    }

    function closeAllBatteryModals() {
        [batteryAssociationModal, confirmBatteryAssociationModal, deleteBatteryAssociationModal].forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    // Activer/désactiver la sélection multiple
    function enableBatteryMultipleSelection(enable) {
        // Nettoyer tous les événements pour éviter les doublons
        document.querySelectorAll('.checkbox-item').forEach(item => {
            const newItem = item.cloneNode(true);
            item.parentNode.replaceChild(newItem, item);
        });

        // Réinitialiser les événements
        initBatteryCheckboxEvents();
    }

    // ------------------------------------------------------------
    // Fonctions de recherche dans les listes de checkboxes
    // ------------------------------------------------------------
    searchMotoInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const motoItems = document.querySelectorAll('#motos-list .checkbox-item');

        motoItems.forEach(item => {
            const label = item.querySelector('label').textContent.toLowerCase();
            if (label.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    searchBatteryListInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const batteryItems = document.querySelectorAll('#batteries-list .checkbox-item');

        batteryItems.forEach(item => {
            const label = item.querySelector('label').textContent.toLowerCase();
            if (label.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // ------------------------------------------------------------
    // Fonctions d'envoi de formulaires
    // ------------------------------------------------------------
    function submitBatteryAssociationForm() {
        // Vérifier si au moins une moto et une batterie sont sélectionnées
        const selectedMotos = document.querySelectorAll('.moto-checkbox:checked');
        const selectedBatteries = document.querySelectorAll('.battery-checkbox:checked');

        if (selectedMotos.length === 0 || selectedBatteries.length === 0) {
            showToast('Veuillez sélectionner au moins une moto et une batterie.', 'error');
            return;
        }

        // Soumettre le formulaire
        document.getElementById('battery-association-form').submit();
    }

    function submitConfirmBatteryAssociation() {
        document.getElementById('confirm-battery-association-form').submit();
    }

    function submitDeleteBatteryAssociation() {
        document.getElementById('delete-battery-association-form').submit();
    }

    // ------------------------------------------------------------
    // Fonction de filtrage du tableau
    // ------------------------------------------------------------
    function filterBatteryTable() {
        const searchTerm = searchBatteryInput.value.toLowerCase();
        const rows = batteryAssociationsTableBody.querySelectorAll('tr');

        rows.forEach(row => {
            let found = false;
            const cells = row.querySelectorAll('td');

            for (let i = 0; i < 6; i++) { // Recherche dans les 6 premières colonnes
                if (cells[i] && cells[i].textContent.toLowerCase().includes(searchTerm)) {
                    found = true;
                    break;
                }
            }

            row.style.display = found ? '' : 'none';
        });

        // Mettre à jour le compteur de statistiques
        updateBatteryStats();
    }

    function updateBatteryStats() {
        const visibleRows = Array.from(batteryAssociationsTableBody.querySelectorAll('tr')).filter(row => {
            return row.style.display !== 'none';
        });

        document.getElementById('total-battery-associations').textContent = visibleRows.length;
    }

    // ------------------------------------------------------------
    // Fonction d'affichage des messages toast
    // ------------------------------------------------------------
    function showToast(message, type = 'info') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ------------------------------------------------------------
    // Attachement des événements
    // ------------------------------------------------------------
    // Boutons d'ouverture des modales
    addBatteryAssociationBtn.addEventListener('click', openAddBatteryAssociationModal);

    // Boutons de confirmation
    document.getElementById('confirm-battery-association').addEventListener('click', submitBatteryAssociationForm);
    document.getElementById('confirm-battery-association-btn').addEventListener('click', submitConfirmBatteryAssociation);
    document.getElementById('confirm-delete-battery-association').addEventListener('click', submitDeleteBatteryAssociation);

    // Boutons de fermeture des modales
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeAllBatteryModals);
    });

    // Fermeture des modales en cliquant en dehors
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeAllBatteryModals();
            }
        });
    });

    // Boutons d'action dans le tableau
    document.querySelectorAll('.edit-battery-association').forEach(btn => {
        btn.addEventListener('click', () => openEditBatteryAssociationModal(btn.closest('tr')));
    });

    // Filtre de recherche
    searchBatteryInput.addEventListener('input', filterBatteryTable);

    // Initialiser les événements des checkboxes
    initBatteryCheckboxEvents();
});
</script>
@endsection