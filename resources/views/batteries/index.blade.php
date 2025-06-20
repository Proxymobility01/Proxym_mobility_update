@extends('layouts.app')

@section('styles')
<!-- Vous pouvez inclure ici votre fichier global CSS si nécessaire -->
<style>
/* ===============================
   Styles pour les modales (similaires à la partie Moto)
   =============================== */
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

.modal.active {
    display: block;
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 500px;
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
    color: #000;
}

.modal-body {
    padding: 20px 0;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #ddd;
    padding-top: 10px;
}

/* Formulaire */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Boutons */
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
    background-color: #DCDB32;
    color: #101010;
}

/* Badges de statut */
.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
}

.status-badge.en_attente {
    background-color: #ffc107;
    color: #212529;
}

.status-badge.validé {
    background-color: #28a745;
    color: white;
}

.status-badge.rejeté {
    background-color: #dc3545;
    color: white;
}
</style>
@endsection

@section('content')
<div class="main-content">


    <!-- Onglets de navigation -->
<div class="nav-tabs">
    <div class="nav-tab {{ request()->is('batteries') ? 'active' : '' }}" data-url="{{ route('batteries.index') }}">
        Batteries Validées
    </div>
    <div class="nav-tab {{ request()->is('batteries/station*') ? 'active' : '' }}" data-url="{{ route('batteries.station.index') }}">
        Gestion Batteries Stations
    </div>
   
</div>

    <!-- En-tête -->
    <div class="content-header">
        <h2>Gestion des Batteries</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-batterie" placeholder="Rechercher une batterie...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <div class="filter-group">
            <select id="status-filter" class="select-status">
                <option value="">Tous les statuts</option>
                <option value="en attente">En attente</option>
                <option value="en service">En service</option>
                <option value="en charge">En charge</option>
                <option value="maintenance">Maintenance</option>
                <option value="validé">Validé</option>
                <option value="rejeté">Rejeté</option>
            </select>
            <button id="add-batterie-btn" class="add-btn">
                <i class="fas fa-plus"></i> Ajouter une batterie
            </button>
        </div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-batteries">0</div>
                <div class="stat-label">Total des batteries</div>
                <div class="stat-text">Toutes les batteries enregistrées</div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="pending-batteries">0</div>
                <div class="stat-label">Batteries à traiter</div>
                <div class="stat-text">En attente de validation</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="validated-batteries">0</div>
                <div class="stat-label">Batteries validées</div>
                <div class="stat-text">Prêtes à l'utilisation</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="rejected-batteries">0</div>
                <div class="stat-label">Batteries rejetées</div>
                <div class="stat-text">Non conformes</div>
            </div>
        </div>
    </div>

    <!-- Tableau des batteries -->
    <div class="table-container">
        <div class="head-table">
            <h2>Liste des Batteries</h2>
            <a href="{{ route('bms.index') }}">
                <i class="fas fa-eye"></i>
                Voir les Details du BMS
            </a>
            <a href="{{ route('batteries.map') }}">
                <i class="fas fa-map-marker-alt"></i>
                Voir toutes les batteries sur la carte
            </a>
        </div>
        <table id="batteries-table">
            <thead>
                <tr>
                    <th>ID Batterie</th>
                    <th>MAC ID</th>
                    <th>Fabriquant</th>
                    <th>Pourcentage %</th>
                    <th>Statut</th>
                    <th>Date d'ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="batteries-table-body">
                <!-- Les batteries seront chargées dynamiquement -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modale Ajouter Batterie -->
<div class="modal" id="add-batterie-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Ajouter une Batterie</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="add-batterie-form">
                @csrf
                <div class="form-group">
                    <label for="mac_id">MAC ID</label>
                    <input type="text" id="mac_id" name="mac_id" required>
                </div>
                <div class="form-group">
                    <label for="fabriquant">Fabriquant</label>
                    <input type="text" id="fabriquant" name="fabriquant" required>
                </div>
                <div class="form-group">
                    <label for="gps">GPS</label>
                    <input type="text" id="gps" name="gps" required>
                </div>
                <div class="form-group">
                    <label for="date_production">Date de production</label>
                    <input type="date" id="date_production" name="date_production">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-add-batterie" class="btn btn-primary">Ajouter</button>
        </div>
    </div>
</div>

<!-- Modale Modifier Batterie -->
<!-- Modale Modifier Batterie -->
<div class="modal" id="edit-batterie-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Modifier la Batterie</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="edit-batterie-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-batterie-id" name="id">

                <div class="form-group">
                    <label for="edit-mac_id">MAC ID</label>
                    <input type="text" id="edit-mac_id" name="mac_id" required>
                </div>
                <div class="form-group">
                    <label for="edit-fabriquant">Fabriquant</label>
                    <input type="text" id="edit-fabriquant" name="fabriquant" required>
                </div>
                <div class="form-group">
                    <label for="edit-gps">GPS</label>
                    <input type="text" id="edit-gps" name="gps" required>
                </div>
                <div class="form-group">
                    <label for="edit-date_production">Date de production</label>
                    <input type="date" id="edit-date_production" name="date_production">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-edit-batterie" class="btn btn-primary">Modifier</button>
        </div>
    </div>
</div>

<!-- Modale Valider Batterie -->
<div class="modal" id="validate-batterie-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Valider la Batterie</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">

    <form id="validate-batterie-form">
    @csrf
    <input type="hidden" id="validate-batterie-id" name="id">

    <div class="form-group">
        <label for="validate-mac_id">MAC ID</label>
        <input type="text" id="validate-mac_id" name="mac_id" required>
    </div>
    <div class="form-group">
        <label for="validate-fabriquant">Fabriquant</label>
        <input type="text" id="validate-fabriquant" name="fabriquant" required>
    </div>
    <div class="form-group">
        <label for="validate-gps">GPS</label>
        <input type="text" id="validate-gps" name="gps" required>
    </div>
    <div class="form-group">
        <label for="validate-date_production">Date de production</label>
        <input type="date" id="validate-date_production" name="date_production">
    </div>
</form>

        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-validate-batterie" class="btn btn-primary">Valider</button>
        </div>
    </div>
</div>

<!-- Modale Supprimer Batterie -->
<div class="modal" id="delete-batterie-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Supprimer la Batterie</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette batterie ?</p>
            <div class="batterie-details">
                <p><strong>ID Unique :</strong> <span id="delete-unique-id"></span></p>
                <p><strong>MAC ID :</strong> <span id="delete-mac-id"></span></p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-delete-batterie" class="btn btn-primary">Supprimer</button>
        </div>
    </div>
</div>




<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Références DOM pour le tableau et les filtres
    const batteriesTableBody = document.getElementById('batteries-table-body');
    const searchInput = document.getElementById('search-batterie');
    const statusFilter = document.getElementById('status-filter');

    // Références aux modales
    const addBatterieModal = document.getElementById('add-batterie-modal');
    const editBatterieModal = document.getElementById('edit-batterie-modal');
    const validateBatterieModal = document.getElementById('validate-batterie-modal');
    const deleteBatterieModal = document.getElementById('delete-batterie-modal');

    // Boutons d'action
    const addBatterieBtn = document.getElementById('add-batterie-btn');
    const confirmAddBatterieBtn = document.getElementById('confirm-add-batterie');
    const confirmEditBatterieBtn = document.getElementById('confirm-edit-batterie');
    const confirmValidateBatterieBtn = document.getElementById('confirm-validate-batterie');
    const confirmDeleteBatterieBtn = document.getElementById('confirm-delete-batterie');

    // Affichage de la date
    document.getElementById('date').textContent = new Date().toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });

    // Fonction pour charger les batteries via AJAX
    function loadBatteries() {
        let url = '/batteries';
        const params = new URLSearchParams({
            search: searchInput.value,
            status: statusFilter.value
        });
        if (params.toString()) url += '?' + params.toString();

        fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            })
            .then(batteries => {
                renderBatteries(batteries);
                updateStats(batteries);
            })
            .catch(error => {
                console.error('Erreur:', error);
                showToast('Erreur lors du chargement des batteries', 'error');
            });
    }

    // Afficher les batteries dans le tableau
    function renderBatteries(batteries) {
        batteriesTableBody.innerHTML = '';
        if (batteries.length === 0) {
            batteriesTableBody.innerHTML =
                `<tr><td colspan="8" class="text-center">Aucune batterie trouvée</td></tr>`;
            return;
        }
        batteries.forEach(batterie => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', batterie.id);
            const dateProduction = batterie.date_production ? new Date(batterie.date_production)
                .toLocaleDateString('fr-FR') : 'N/A';
            const createdAt = new Date(batterie.created_at).toLocaleDateString('fr-FR');
            row.innerHTML = `
                <td>${batterie.batterie_unique_id || 'N/A'}</td>
                <td>${batterie.mac_id}</td>
                <td>${batterie.fabriquant}</td>
                 <td>
        <div class="battery-level">
            <div class="battery-fill" style="width: ${batterie.soc || 0}%"></div>
        </div>
        ${batterie.soc || 0}%
    </td>
                <td>
                    <span class="status-badge ${batterie.statut.replace(' ', '_').toLowerCase()}">
                        ${batterie.statut}
                    </span>
                </td>
                <td>${createdAt}</td>
                <td style="display: flex; gap: 5px;">
                    <button class="action-btn edit-batterie" title="Modifier" >
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn validate-batterie" title="Valider" >
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="action-btn delete-batterie" title="Supprimer" >
                        <i class="fas fa-trash"></i>
                    </button>
                     <button class="action-btn bms" title="Données BMS">
                                    <i class="fas fa-chart-line"></i>
                    </button>

                </td>
            `;
            batteriesTableBody.appendChild(row);
        });
        attachActionListeners();
    }

    // Mise à jour des statistiques
    function updateStats(batteries) {
        const total = batteries.length;
        const pending = batteries.filter(b => b.statut === 'en attente').length;
        const validated = batteries.filter(b => b.statut === 'validé').length;
        const rejected = batteries.filter(b => b.statut === 'rejeté').length;
        document.getElementById('total-batteries').textContent = total;
        document.getElementById('pending-batteries').textContent = pending;
        document.getElementById('validated-batteries').textContent = validated;
        document.getElementById('rejected-batteries').textContent = rejected;
    }

    // Attacher les événements sur les boutons des lignes du tableau
    function attachActionListeners() {
        document.querySelectorAll('.edit-batterie').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.closest('tr').getAttribute('data-id');
                openEditModal(id);
            });
        });
        document.querySelectorAll('.validate-batterie').forEach(btn => {
            btn.addEventListener('click', () => {
                const row = btn.closest('tr');
                openValidateModal(row);
            });
        });
        document.querySelectorAll('.delete-batterie').forEach(btn => {
            btn.addEventListener('click', () => {
                const row = btn.closest('tr');
                openDeleteModal(row);
            });
        });
    }

    // Ouvrir la modale d'ajout
    function openAddModal() {
        // Vérifiez dans la console si la fonction est bien appelée
        console.log('Ouverture de la modale d\'ajout de batterie');
        addBatterieModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Ouvrir la modale d'édition
 function openEditModal(id) {
    fetch(`/batteries/${id}/edit`)
        .then(response => {
            if (!response.ok) throw new Error("Erreur de chargement des données.");
            return response.json();
        })
        .then(data => {
            document.getElementById('edit-batterie-id').value = data.id;
            document.getElementById('edit-mac_id').value = data.mac_id;
            document.getElementById('edit-fabriquant').value = data.fabriquant;
            document.getElementById('edit-gps').value = data.gps;
            document.getElementById('edit-date_production').value = data.date_production ? data.date_production.split('T')[0] : '';

            editBatterieModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(err => {
            console.error("Erreur :", err);
            alert("Impossible de charger les données de la batterie.");
        });
}


    // Ouvrir la modale de validation
   function openValidateModal(row) {
    const id = row.getAttribute('data-id');
    fetch(`/batteries/${id}/validate-form`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => {
        if (!response.ok) throw new Error('Erreur lors du chargement');
        return response.json();
    })
    .then(batterie => {
        document.getElementById('validate-batterie-id').value = batterie.id;
        document.getElementById('validate-mac_id').value = batterie.mac_id;
        document.getElementById('validate-fabriquant').value = batterie.fabriquant;
        document.getElementById('validate-gps').value = batterie.gps;
        document.getElementById('validate-date_production').value = batterie.date_production ? batterie.date_production.split('T')[0] : '';

        validateBatterieModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors du chargement des données pour validation', 'error');
    });
}


    // Ouvrir la modale de suppression
    function openDeleteModal(row) {
        const id = row.getAttribute('data-id');
        const cells = row.querySelectorAll('td');
        document.getElementById('delete-unique-id').textContent = cells[0].textContent;
        document.getElementById('delete-mac-id').textContent = cells[1].textContent;
        confirmDeleteBatterieBtn.setAttribute('data-id', id);
        deleteBatterieModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Fermer toutes les modales
    function closeAllModals() {
        [addBatterieModal, editBatterieModal, validateBatterieModal, deleteBatterieModal].forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    // Soumission du formulaire d'ajout de batterie
    function submitAddBatterie() {
        const form = document.getElementById('add-batterie-form');
        const formData = new FormData(form);
        console.log("Contenu formulaire :");
for (let [key, value] of formData.entries()) {
    console.log(`${key}: ${value}`);
}

        fetch('/batteries', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(batterie => {
                loadBatteries();
                closeAllModals();
                showToast('Batterie ajoutée avec succès', 'success');
                form.reset();
            })
            .catch(error => {
                console.error('Erreur lors de l\'ajout:', error);
                showToast('Erreur lors de l\'ajout de la batterie', 'error');
            });
    }

    // Soumission du formulaire d'édition de batterie
    function submitEditBatterie() {
        const id = document.getElementById('edit-batterie-id').value;
        const form = document.getElementById('edit-batterie-form');
        const formData = new FormData(form);
        fetch(`/batteries/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-HTTP-Method-Override': 'PUT',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(batterie => {
                loadBatteries();
                closeAllModals();
                showToast('Batterie modifiée avec succès', 'success');
                form.reset();
            })
            .catch(error => {
                console.error('Erreur de modification:', error);
                showToast('Erreur lors de la modification de la batterie', 'error');
            });
    }

    // Soumission du formulaire de validation de batterie
   function submitValidateBatterie() {
    const form = document.getElementById('validate-batterie-form');
    const formData = new FormData(form);
    const id = document.getElementById('validate-batterie-id').value;

    fetch(`/batteries/${id}/validate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        loadBatteries();
        closeAllModals();
        showToast('Batterie validée avec succès', 'success');
    })
    .catch(error => {
        console.error('Erreur de validation:', error);
        showToast('Erreur lors de la validation de la batterie', 'error');
    });
}

    // Soumission de la suppression de batterie
    function submitDeleteBatterie() {
        const id = confirmDeleteBatterieBtn.getAttribute('data-id');
        fetch(`/batteries/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(result => {
                loadBatteries();
                closeAllModals();
                showToast('Batterie supprimée avec succès', 'success');
            })
            .catch(error => {
                console.error('Erreur de suppression:', error);
                showToast('Erreur lors de la suppression de la batterie', 'error');
            });
    }

    // Fonction pour afficher un toast
    function showToast(message, type = 'info') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            toastContainer.style.position = 'fixed';
            toastContainer.style.bottom = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.backgroundColor = type === 'success' ? '#DCDB32' : '#007bff';
        toast.style.color = '#101010';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '8px';
        toast.style.marginTop = '10px';
        toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        toast.textContent = message;
        toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Événements de fermeture : boutons & clic sur l'overlay
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeAllModals();
        });
    });

    // Attacher événements aux boutons des modales
    addBatterieBtn.addEventListener('click', openAddModal);
    confirmAddBatterieBtn.addEventListener('click', submitAddBatterie);
    confirmEditBatterieBtn.addEventListener('click', submitEditBatterie);
    confirmValidateBatterieBtn.addEventListener('click', submitValidateBatterie);
    confirmDeleteBatterieBtn.addEventListener('click', submitDeleteBatterie);

    // Recherche et filtrage
    searchInput.addEventListener('input', loadBatteries);
    statusFilter.addEventListener('change', loadBatteries);

    // Chargement initial
    loadBatteries();
});



  // Navigation par onglets
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });
    });
</script>


@endsection
