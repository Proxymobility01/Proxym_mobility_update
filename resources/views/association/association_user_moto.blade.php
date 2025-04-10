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

th,
td {
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

.action-btn.edit-association i {
    color: #ffc107;
}

.action-btn.delete-association i {
    color: #dc3545;
}

.action-btn.view-association i {
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




/* Styles pour les tabs */
.nav-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.nav-tab {
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-right: 10px;
    
}

.nav-tab:hover {
    background-color: var(--tertiary);
}

.nav-tab.active {
    border-bottom-color: var(--primary);
    font-weight: bold;
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
    <div class="nav-tab {{ Request::is('associations') || (Request::is('associations/*') && !Request::is('associations/batteries*')) ? 'active' : '' }}" data-tab="moto-user">Associations Moto-Utilisateur</div>
    <div class="nav-tab {{ Request::is('associations/batteries*') ? 'active' : '' }}" data-tab="battery-user">Associations Batterie-Utilisateur</div>
</div>

    <!-- Barre de recherche et ajout -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-association" placeholder="Rechercher une association...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-group">
            <button id="add-association-btn" class="add-btn">
                <i class="fas fa-plus"></i>
                Associer Moto & Utilisateur
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
                <div class="stat-number" id="total-associations">{{ $associations->count() }}</div>
                <div class="stat-label">Total des associations</div>
                <div class="stat-text">Motos associées aux utilisateurs</div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-motorcycle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-motos">{{ $motos->count() }}</div>
                <div class="stat-label">Motos disponibles</div>
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
        <table id="associations-table">
            <thead>
                <tr>
                    <th>ID Utilisateur</th>
                    <th>Nom Utilisateur</th>
                    <th>ID Moto</th>
                    <th>Modèle Moto</th>
                    <th>Date d'association</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="associations-table-body">
                @foreach($associations as $association)
                <tr data-id="{{ $association->id }}">
                    <td>{{ $association->validatedUser->user_unique_id }}</td>
                    <td>{{ $association->validatedUser->nom }} {{ $association->validatedUser->prenom }}</td>
                    <td>{{ $association->motosValide->moto_unique_id }}</td>
                    <td>{{ $association->motosValide->model }}</td>
                    <td>{{ \Carbon\Carbon::parse($association->created_at)->format('d/m/Y') }}</td>
                    <td style="display: flex;">
                        <button class="action-btn edit-association" title="Modifier l'association">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete-association" title="Supprimer l'association">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modale d'association avec checkboxes -->
<div class="modal" id="association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Associer Moto & Utilisateur</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="association-form" method="POST" action="{{ route('associations.associerMotoAUtilisateurs') }}">
                @csrf
                <input type="hidden" id="edit-association-id" name="id">
                <input type="hidden" id="form-method" name="_method" value="POST">

                <div class="association-container">
                    <!-- Section Utilisateurs -->
                    <div class="association-column">
                        <h3>Liste des Utilisateurs</h3>
                        <div class="search-box">
                            <input type="text" id="search-user" placeholder="Rechercher un utilisateur">
                        </div>
                        <div class="checkbox-list" id="users-list">
                            @foreach ($users as $user)
                            <div class="checkbox-item">
                                <input type="checkbox" name="user_unique_id[]" id="user-{{ $user->user_unique_id }}"
                                    value="{{ $user->user_unique_id }}" class="user-checkbox">
                                <label for="user-{{ $user->user_unique_id }}">
                                    {{ $user->nom }} {{ $user->prenom }} ({{ $user->user_unique_id }})
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Section Motos -->
                    <div class="association-column">
                        <h3>Liste des Motos</h3>
                        <div class="search-box">
                            <input type="text" id="search-moto" placeholder="Rechercher une moto par VIN ou ID">
                        </div>
                        <div class="checkbox-list" id="motos-list">
                            @foreach ($motos as $moto)
                            <div class="checkbox-item">
                                <input type="checkbox" name="moto_unique_id[]" id="moto-{{ $moto->moto_unique_id }}"
                                    value="{{ $moto->moto_unique_id }}" class="moto-checkbox">
                                <label for="moto-{{ $moto->moto_unique_id }}">
                                    {{ $moto->model }} ({{ $moto->moto_unique_id }})
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
            <button id="confirm-association" class="btn btn-primary">Associer</button>
        </div>
    </div>
</div>

<!-- Modale de confirmation d'association multiple -->
<div class="modal" id="confirm-association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirmation d'association</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Cette moto est déjà associée à un ou plusieurs utilisateurs. Voulez-vous l'associer à cet utilisateur
                supplémentaire?</p>
            <form id="confirm-association-form" method="POST" action="{{ route('associations.confirm') }}">
                @csrf
                <input type="hidden" id="confirm-moto-id" name="moto_id">
                <input type="hidden" id="confirm-user-ids" name="user_ids">
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-association-btn" class="btn btn-primary">Confirmer</button>
        </div>
    </div>
</div>

<!-- Modale de suppression d'association -->
<div class="modal" id="delete-association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Supprimer l'association</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette association ?</p>
            <div class="association-details">
                <p><strong>Utilisateur :</strong> <span id="delete-user-name"></span></p>
                <p><strong>Moto :</strong> <span id="delete-moto-id"></span></p>
            </div>
            <form id="delete-association-form" method="POST">
                @csrf
                @method('DELETE')
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-delete-association" class="btn btn-primary">Supprimer</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ------------------------------------------------------------
    // Initialisation et variables
    // ------------------------------------------------------------
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const searchInput = document.getElementById('search-association');
    const addAssociationBtn = document.getElementById('add-association-btn');
    const associationModal = document.getElementById('association-modal');
    const confirmAssociationModal = document.getElementById('confirm-association-modal');
    const deleteAssociationModal = document.getElementById('delete-association-modal');
    const associationsTableBody = document.getElementById('associations-table-body');

    // Recherche dans les listes de checkboxes
    const searchUserInput = document.getElementById('search-user');
    const searchMotoInput = document.getElementById('search-moto');

    // Afficher la date actuelle
    const dateElement = document.getElementById('date');
    const today = new Date();
    dateElement.textContent = today.toLocaleDateString('fr-FR');

    // ------------------------------------------------------------
    // Fonction pour initialiser les événements des checkboxes
    // ------------------------------------------------------------
    function initCheckboxEvents() {
        // Gérer le clic sur les checkboxes des utilisateurs
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        userCheckboxes.forEach(checkbox => {
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
                    if (document.getElementById('form-method').value === 'PUT') {
                        userCheckboxes.forEach(cb => {
                            if (cb !== checkbox && cb.checked) {
                                cb.checked = false;
                                cb.closest('.checkbox-item').classList.remove(
                                    'selected');
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
                    if (document.getElementById('form-method').value === 'PUT') {
                        motoCheckboxes.forEach(cb => {
                            if (cb !== checkbox && cb.checked) {
                                cb.checked = false;
                                cb.closest('.checkbox-item').classList.remove(
                                    'selected');
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
    // Fonctions de gestion des modales
    // ------------------------------------------------------------
    function openAddAssociationModal() {
        // Réinitialiser les checkboxes
        document.querySelectorAll('.checkbox-item').forEach(item => {
            item.classList.remove('selected');
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
        });

        // Réinitialiser le formulaire
        document.getElementById('association-form').reset();
        document.getElementById('form-method').value = 'POST';
        document.getElementById('edit-association-id').value = '';

        // Mettre à jour le titre
        document.getElementById('modal-title').textContent = 'Associer Moto & Utilisateur';
        document.getElementById('association-form').action =
            "{{ route('associations.associerMotoAUtilisateurs') }}";

        // Permettre la sélection multiple
        enableMultipleSelection(true);

        // Réinitialiser les champs de recherche
        if (searchUserInput) searchUserInput.value = '';
        if (searchMotoInput) searchMotoInput.value = '';

        // Afficher tous les éléments qui pourraient avoir été masqués
        document.querySelectorAll('#users-list .checkbox-item, #motos-list .checkbox-item').forEach(item => {
            item.style.display = '';
        });

        // Afficher la modale
        associationModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function openEditAssociationModal(row) {
        // Réinitialiser les checkboxes
        document.querySelectorAll('.checkbox-item').forEach(item => {
            item.classList.remove('selected');
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
        });

        const id = row.dataset.id;
        const userId = row.querySelector('td:nth-child(1)').textContent;
        const motoId = row.querySelector('td:nth-child(3)').textContent;

        // Réinitialiser le formulaire
        document.getElementById('association-form').reset();
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('edit-association-id').value = id;

        // Mettre à jour le titre et l'action du formulaire
        document.getElementById('modal-title').textContent = 'Modifier l\'association';
        document.getElementById('association-form').action = `/associations/${id}`;

        // Limiter à une seule sélection
        enableMultipleSelection(false);

        // Réinitialiser les champs de recherche
        if (searchUserInput) searchUserInput.value = '';
        if (searchMotoInput) searchMotoInput.value = '';

        // Afficher tous les éléments qui pourraient avoir été masqués
        document.querySelectorAll('#users-list .checkbox-item, #motos-list .checkbox-item').forEach(item => {
            item.style.display = '';
        });

        // Après avoir coché les checkboxes, mettre à jour les styles
        if (document.getElementById(`user-${userId}`)) {
            document.getElementById(`user-${userId}`).checked = true;
            document.getElementById(`user-${userId}`).closest('.checkbox-item').classList.add('selected');
        }

        if (document.getElementById(`moto-${motoId}`)) {
            document.getElementById(`moto-${motoId}`).checked = true;
            document.getElementById(`moto-${motoId}`).closest('.checkbox-item').classList.add('selected');
        }

        // Afficher la modale
        associationModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function openConfirmAssociationModal(motoId, userId) {
        document.getElementById('confirm-moto-id').value = motoId;
        document.getElementById('confirm-user-ids').value = userId;
        confirmAssociationModal.classList.add('active');
    }

    function openDeleteAssociationModal(row) {
        const id = row.dataset.id;
        const userName = row.querySelector('td:nth-child(2)').textContent;
        const motoId = row.querySelector('td:nth-child(3)').textContent;
        const motoModel = row.querySelector('td:nth-child(4)').textContent;

        // Configurer la modale
        document.getElementById('delete-user-name').textContent = userName;
        document.getElementById('delete-moto-id').textContent = `${motoModel} (${motoId})`;

        // Modifier l'action du formulaire
        const form = document.getElementById('delete-association-form');
        form.action = `/associations/${id}`;

        deleteAssociationModal.classList.add('active');
    }

    function closeAllModals() {
        [associationModal, confirmAssociationModal, deleteAssociationModal].forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    // Activer/désactiver la sélection multiple
    function enableMultipleSelection(enable) {
        // Nettoyer tous les événements pour éviter les doublons
        document.querySelectorAll('.checkbox-item').forEach(item => {
            const newItem = item.cloneNode(true);
            item.parentNode.replaceChild(newItem, item);
        });

        // Réinitialiser les événements
        initCheckboxEvents();
    }

    // ------------------------------------------------------------
    // Fonctions de recherche dans les listes de checkboxes
    // ------------------------------------------------------------
    searchUserInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const userItems = document.querySelectorAll('#users-list .checkbox-item');

        userItems.forEach(item => {
            const label = item.querySelector('label').textContent.toLowerCase();
            if (label.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

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

    // ------------------------------------------------------------
    // Fonctions d'envoi de formulaires
    // ------------------------------------------------------------
    function submitAssociationForm() {
        // Vérifier si au moins un utilisateur et une moto sont sélectionnés
        const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
        const selectedMotos = document.querySelectorAll('.moto-checkbox:checked');

        if (selectedUsers.length === 0 || selectedMotos.length === 0) {
            showToast('Veuillez sélectionner au moins un utilisateur et une moto.', 'error');
            return;
        }

        // Soumettre le formulaire
        document.getElementById('association-form').submit();
    }

    function submitConfirmAssociation() {
        document.getElementById('confirm-association-form').submit();
    }

    function submitDeleteAssociation() {
        document.getElementById('delete-association-form').submit();
    }

    // ------------------------------------------------------------
    // Fonction de filtrage du tableau
    // ------------------------------------------------------------
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = associationsTableBody.querySelectorAll('tr');

        rows.forEach(row => {
            let found = false;
            const cells = row.querySelectorAll('td');

            for (let i = 0; i < 4; i++) { // Recherche dans les 4 premières colonnes uniquement
                if (cells[i].textContent.toLowerCase().includes(searchTerm)) {
                    found = true;
                    break;
                }
            }

            row.style.display = found ? '' : 'none';
        });

        // Mettre à jour le compteur de statistiques
        updateStats();
    }

    function updateStats() {
        const visibleRows = Array.from(associationsTableBody.querySelectorAll('tr')).filter(row => {
            return row.style.display !== 'none';
        });

        document.getElementById('total-associations').textContent = visibleRows.length;
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
    // Gestion des messages de session Laravel
    // ------------------------------------------------------------
    @if(session('success'))
    showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
    showToast("{{ session('error') }}", 'error');
    @endif

    @if(session('warning'))
    openConfirmAssociationModal("{{ session('moto_id') }}", "{{ session('user_id') }}");
    @endif

    // ------------------------------------------------------------
    // Attachement des événements
    // ------------------------------------------------------------
    // Boutons d'ouverture des modales
    addAssociationBtn.addEventListener('click', openAddAssociationModal);

    // Boutons de confirmation
    document.getElementById('confirm-association').addEventListener('click', submitAssociationForm);
    document.getElementById('confirm-association-btn').addEventListener('click', submitConfirmAssociation);
    document.getElementById('confirm-delete-association').addEventListener('click', submitDeleteAssociation);

    // Boutons de fermeture des modales
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });

    // Fermeture des modales en cliquant en dehors
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeAllModals();
            }
        });
    });

    // Boutons d'action dans le tableau
    document.querySelectorAll('.edit-association').forEach(btn => {
        btn.addEventListener('click', () => openEditAssociationModal(btn.closest('tr')));
    });

    document.querySelectorAll('.delete-association').forEach(btn => {
        btn.addEventListener('click', () => openDeleteAssociationModal(btn.closest('tr')));
    });

    // Filtre de recherche
    searchInput.addEventListener('input', filterTable);

    // Initialiser les événements des checkboxes
    initCheckboxEvents();

    // Ajoutez ce code dans la section "Attachement des événements" de votre premier fichier (paste.txt)
// À placer juste après l'initialisation des événements des checkboxes

// Événements pour les onglets
document.querySelectorAll('.nav-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabName = this.getAttribute('data-tab');
        
        // Gestion de la navigation par onglets
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        if (tabName === 'battery-user') {
            window.location.href = '/associations/batteries'; // Rediriger vers la page des associations batterie-utilisateur
        }
    });
});



});




   
</script>
@endsection