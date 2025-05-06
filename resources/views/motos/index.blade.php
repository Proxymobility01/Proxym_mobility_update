@extends('layouts.app')
<style>
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


.modal.active {
    display: block;
}


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
    background-color: #DCDB32;
    color: #101010;
}

/* Styles pour les badges de statut */
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

@section('content')
<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>Gestion des Motos</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-moto" placeholder="Rechercher une moto...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-group">
            <select id="status-filter" class="select-status">
                <option value="">Tous les statuts</option>
                <option value="en attente">En attente</option>
                <option value="validé">Validé</option>
                <option value="rejeté">Rejeté</option>
            </select>
            <button id="add-moto-btn" class="add-btn">
                <i class="fas fa-plus"></i>
                Ajouter une moto
            </button>
        </div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-motorcycle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-motos">0</div>
                <div class="stat-label">Total des motos</div>
                <div class="stat-text">Toutes les motos enregistrées</div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="pending-motos">0</div>
                <div class="stat-label">Motos à valider</div>
                <div class="stat-text">À traiter</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="validated-motos">0</div>
                <div class="stat-label">Motos validées</div>
                <div class="stat-text">Prêtes à être utilisées</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="rejected-motos">0</div>
                <div class="stat-label">Motos rejetées</div>
                <div class="stat-text">Non conformes</div>
            </div>
        </div>
    </div>

    <!-- Tableau de données -->
    <div class="table-container">
    <div class="d-flex gap-3 my-4">
    <!-- Bouton vers la page des distances parcourues -->
    <a href="{{ route('distances.index') }}" class="btn btn-primary">
        📍 Distances Journalières
    </a>

    <!-- Bouton vers la carte GPS des motos -->
    <a href="{{ route('gps.motos.carte') }}" class="btn btn-success">
        🗺️ Carte GPS des Motos
    </a>
</div>

        <table id="motos-table">
            <thead>
                <tr>
                    <th>ID Unique</th>
                    <th>VIN</th>
                    <th>Modèle</th>
                    <th>GPS IMEI</th>
                    <th>Statut</th>
                    <th>Date d'ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="motos-table-body">
                <!-- Les motos seront chargées dynamiquement ici -->
            </tbody>
        </table>
    </div>


</div>










<!-- Modale Ajouter Moto -->
<div class="modal" id="add-moto-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Ajouter une Moto</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="add-moto-form">
                @csrf
                <div class="form-group">
                    <label for="vin">VIN</label>
                    <input type="text" id="vin" name="vin" required>
                </div>
                <div class="form-group">
                    <label for="model">Modèle</label>
                    <input type="text" id="model" name="model" required>
                </div>
                <div class="form-group">
                    <label for="gps_imei">GPS IMEI</label>
                    <input type="text" id="gps_imei" name="gps_imei" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-add-moto" class="btn btn-primary">Ajouter</button>
        </div>
    </div>
</div>

<!-- Modale Modifier Moto -->
<div class="modal" id="edit-moto-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Modifier la Moto</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="edit-moto-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-moto-id" name="id">
                <div class="form-group">
                    <label for="edit-model">Modèle</label>
                    <input type="text" id="edit-model" name="model" required>
                </div>
                <div class="form-group">
                    <label for="edit-gps_imei">GPS IMEI</label>
                    <input type="text" id="edit-gps_imei" name="gps_imei" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-edit-moto" class="btn btn-primary">Modifier</button>
        </div>
    </div>
</div>

<!-- Modale Valider Moto -->
<div class="modal" id="validate-moto-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Valider la Moto</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="validate-moto-form">
                @csrf
                <input type="hidden" id="validate-moto-id" name="id">
                <div class="form-group">
                    <label for="assurance">Assurance</label>
                    <input type="text" id="assurance" name="assurance" required>
                </div>
                <div class="form-group">
                    <label for="permis">Permis</label>
                    <input type="text" id="permis" name="permis" required>
                </div>
                <div class="moto-details">
                    <p><strong>ID Unique :</strong> <span id="validate-unique-id"></span></p>
                    <p><strong>Modèle :</strong> <span id="validate-model"></span></p>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-validate-moto" class="btn btn-primary">Valider</button>
        </div>
    </div>
</div>

<!-- Modale Supprimer Moto -->
<div class="modal" id="delete-moto-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Supprimer la Moto</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette moto ?</p>
            <div class="moto-details">
                <p><strong>ID Unique :</strong> <span id="delete-unique-id"></span></p>
                <p><strong>Modèle :</strong> <span id="delete-model"></span></p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-delete-moto" class="btn btn-primary">Supprimer</button>
        </div>
    </div>
</div>
@endsection



<script>
document.addEventListener('DOMContentLoaded', function() {
    // ------------------------------------------------------------
    // Initialisation des variables et récupération des éléments du DOM
    // ------------------------------------------------------------
    // Récupération du token CSRF pour les requêtes AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Références aux éléments du tableau et aux champs de recherche/filtres
    const motosTableBody = document.getElementById('motos-table-body');
    const searchInput = document.getElementById('search-moto');
    const statusFilter = document.getElementById('status-filter');

    // Références aux modales
    const addMotoModal = document.getElementById('add-moto-modal');
    const editMotoModal = document.getElementById('edit-moto-modal');
    const validateMotoModal = document.getElementById('validate-moto-modal');
    const deleteMotoModal = document.getElementById('delete-moto-modal');

    // ------------------------------------------------------------
    // Fonctions AJAX et affichage dynamique des motos
    // ------------------------------------------------------------

    /**
     * Charge la liste des motos depuis le backend avec les filtres appliqués.
     */
    function loadMotos() {
        // Construction des paramètres d'URL pour les filtres
        const params = new URLSearchParams({
            status: statusFilter.value,
            search: searchInput.value
        });
        
        fetch('/motos?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' // Pour que Laravel détecte comme AJAX
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
                }
                return response.json();
            })
            .then(motos => {
                console.log('Motos chargées:', motos); // Debug
                renderMotos(motos); // Affiche les motos dans le tableau
                updateStats(motos); // Met à jour les statistiques affichées
            })
            .catch(error => {
                console.error('Erreur de chargement:', error);
                showToast('Erreur lors du chargement des données', 'error');
            });
    }

    /**
     * Affiche les motos dans le tableau en générant dynamiquement les lignes.
     * @param {Array} motos - Tableau d'objets moto.
     */
    function renderMotos(motos) {
        motosTableBody.innerHTML = '';
        
        if (motos.length === 0) {
            motosTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Aucune moto trouvée</td></tr>';
            return;
        }
        
        motos.forEach(moto => {
            // Vérifier si l'ID est présent, sinon utiliser un ID alternatif
            const motoId = moto.id || moto.moto_unique_id;
            
            const row = `
                <tr data-id="${motoId}">
                    <td>${moto.moto_unique_id || 'N/A'}</td>
                    <td>${moto.vin}</td>
                    <td>${moto.model}</td>
                    <td>${moto.gps_imei}</td>
                    <td>
                        <span class="status-badge ${moto.statut.toLowerCase()}">
                            ${moto.statut}
                        </span>
                    </td>
                    <td>${formatDate(moto.created_at)}</td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                        <button class="action-btn edit-moto">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn validate-moto" ${moto.statut === 'validé' ? 'disabled' : ''}>
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="action-btn delete-moto">
                            <i class="fas fa-trash"></i>
                        </button>
                        </div>
                    </td>
                </tr>
            `;
            motosTableBody.insertAdjacentHTML('beforeend', row);
        });
        attachActionListeners();
    }

    /**
     * Met à jour les statistiques (nombre total, en attente, validées, rejetées).
     * @param {Array} motos - Tableau d'objets moto.
     */
    function updateStats(motos) {
        const totalMotos = motos.length;
        const pendingMotos = motos.filter(moto => moto.statut === 'en attente').length;
        const validatedMotos = motos.filter(moto => moto.statut === 'validé').length;
        const rejectedMotos = motos.filter(moto => moto.statut === 'rejeté').length;
        
        document.getElementById('total-motos').textContent = totalMotos;
        document.getElementById('pending-motos').textContent = pendingMotos;
        document.getElementById('validated-motos').textContent = validatedMotos;
        document.getElementById('rejected-motos').textContent = rejectedMotos;
    }

    // ------------------------------------------------------------
    // Attachement des événements pour la recherche et le filtrage
    // ------------------------------------------------------------
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadMotos, 300); // Délai pour éviter trop de requêtes
    });
    
    statusFilter.addEventListener('change', loadMotos);

    // ------------------------------------------------------------
    // Attachement des événements sur les boutons des lignes générées
    // ------------------------------------------------------------
    function attachActionListeners() {
        document.querySelectorAll('.edit-moto').forEach(btn => {
            btn.addEventListener('click', () => openEditMotoModal(btn.closest('tr')));
        });
        document.querySelectorAll('.validate-moto').forEach(btn => {
            if (!btn.disabled) {
                btn.addEventListener('click', () => openValidateMotoModal(btn.closest('tr')));
            }
        });
        document.querySelectorAll('.delete-moto').forEach(btn => {
            btn.addEventListener('click', () => openDeleteMotoModal(btn.closest('tr')));
        });
    }

    // ------------------------------------------------------------
    // Gestion des modales (ouverture et fermeture)
    // ------------------------------------------------------------

    /**
     * Ouvre la modale d'ajout de moto.
     */
    function openAddMotoModal() {
        // Réinitialiser le formulaire
        document.getElementById('add-moto-form').reset();
        
        addMotoModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Ouvre la modale d'édition et pré-remplit les champs avec les données de la moto.
     * @param {HTMLElement} row - La ligne du tableau correspondant à la moto.
     */
    function openEditMotoModal(row) {
        const id = row.dataset.id;
        const model = row.querySelector('td:nth-child(3)').textContent;
        const gpsImei = row.querySelector('td:nth-child(4)').textContent;
        
        document.getElementById('edit-moto-id').value = id;
        document.getElementById('edit-model').value = model;
        document.getElementById('edit-gps_imei').value = gpsImei;
        
        editMotoModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Ouvre la modale de validation en affichant les informations de la moto.
     * @param {HTMLElement} row - La ligne du tableau correspondant à la moto.
     */
    function openValidateMotoModal(row) {
        const id = row.dataset.id;
        const uniqueId = row.querySelector('td:nth-child(1)').textContent;
        const model = row.querySelector('td:nth-child(3)').textContent;
        
        // Réinitialiser le formulaire
        document.getElementById('validate-moto-form').reset();
        
        document.getElementById('validate-moto-id').value = id;
        document.getElementById('validate-unique-id').textContent = uniqueId;
        document.getElementById('validate-model').textContent = model;
        
        validateMotoModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Ouvre la modale de suppression en affichant les détails de la moto.
     * @param {HTMLElement} row - La ligne du tableau correspondant à la moto.
     */
    function openDeleteMotoModal(row) {
        const id = row.dataset.id;
        const uniqueId = row.querySelector('td:nth-child(1)').textContent;
        const model = row.querySelector('td:nth-child(3)').textContent;
        
        document.getElementById('delete-unique-id').textContent = uniqueId;
        document.getElementById('delete-model').textContent = model;
        document.getElementById('confirm-delete-moto').dataset.id = id;
        
        deleteMotoModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Ferme toutes les modales en retirant la classe "active" et rétablit le défilement.
     */
    function closeAllModals() {
        [addMotoModal, editMotoModal, validateMotoModal, deleteMotoModal].forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    // Ferme la modale en cliquant sur l'overlay (en dehors du contenu de la modale)
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeAllModals();
            }
        });
    });

    // ------------------------------------------------------------
    // Soumission des formulaires via AJAX
    // ------------------------------------------------------------

    /**
     * Soumet le formulaire d'ajout de moto.
     * @param {Event} e - L'événement de soumission.
     */
    function submitAddMoto(e) {
        e.preventDefault();
        
        const form = document.getElementById('add-moto-form');
        const formData = new FormData(form);
        
        // Conversion en objet JSON pour l'envoi
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        fetch('/motos', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Erreur lors de l\'ajout de la moto');
                    });
                }
                return response.json();
            })
            .then(moto => {
                console.log('Moto ajoutée:', moto); // Debug
                loadMotos(); // Recharge la liste des motos
                closeAllModals();
                showToast('Moto ajoutée avec succès', 'success');
                form.reset();
            })
            .catch(error => {
                console.error('Erreur lors de l\'ajout de la moto:', error);
                showToast(error.message, 'error');
            });
    }

    /**
     * Soumet le formulaire d'édition de la moto via AJAX.
     */
    function submitEditMoto(e) {
        if (e) e.preventDefault();
        
        const form = document.getElementById('edit-moto-form');
        const id = document.getElementById('edit-moto-id').value;
        const formData = new FormData(form);
        
        // Conversion en objet JSON pour l'envoi
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        fetch(`/motos/${id}`, {
                method: 'PUT', // Utiliser PUT directement
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Erreur lors de la modification de la moto');
                    });
                }
                return response.json();
            })
            .then(moto => {
                console.log('Moto modifiée:', moto); // Debug
                loadMotos(); // Recharge la liste des motos
                closeAllModals();
                showToast('Moto modifiée avec succès', 'success');
                form.reset();
            })
            .catch(error => {
                console.error('Erreur de modification:', error);
                showToast(error.message, 'error');
            });
    }

    /**
     * Soumet le formulaire de validation de la moto via AJAX.
     */
    function submitValidateMoto(e) {
        if (e) e.preventDefault();
        
        const form = document.getElementById('validate-moto-form');
        const id = document.getElementById('validate-moto-id').value;
        const formData = new FormData(form);
        
        // Conversion en objet JSON pour l'envoi
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        fetch(`/motos/${id}/validate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Erreur lors de la validation de la moto');
                    });
                }
                return response.json();
            })
            .then(motoValidee => {
                console.log('Moto validée:', motoValidee); // Debug
                loadMotos();
                closeAllModals();
                showToast('Moto validée avec succès', 'success');
                form.reset();
            })
            .catch(error => {
                console.error('Erreur de validation:', error);
                showToast(error.message, 'error');
            });
    }

    /**
     * Soumet la suppression d'une moto via AJAX.
     */
    function submitDeleteMoto(e) {
        if (e) e.preventDefault();
        
        const id = document.getElementById('confirm-delete-moto').dataset.id;
        
        fetch(`/motos/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Erreur lors de la suppression de la moto');
                    });
                }
                return response.json();
            })
            .then(result => {
                console.log('Moto supprimée:', result); // Debug
                loadMotos();
                closeAllModals();
                showToast('Moto supprimée avec succès', 'success');
            })
            .catch(error => {
                console.error('Erreur de suppression:', error);
                showToast(error.message, 'error');
            });
    }

    // ------------------------------------------------------------
    // Utilitaires
    // ------------------------------------------------------------

    /**
     * Formate une chaîne de date ISO en format local (fr-FR).
     * @param {string} dateString - La chaîne de date ISO.
     * @returns {string} La date formatée.
     */
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        } catch (e) {
            console.error('Erreur de formatage de date:', e);
            return 'N/A';
        }
    }

    /**
     * Affiche un message toast en bas à droite de l'écran.
     * @param {string} message - Le message à afficher.
     * @param {string} [type='info'] - Le type de toast (info, success, error).
     */
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
        
        // Couleurs selon le type
        if (type === 'success') {
            toast.style.backgroundColor = '#DCDB32';
            toast.style.color = '#101010';
        } else if (type === 'error') {
            toast.style.backgroundColor = '#dc3545';
            toast.style.color = '#ffffff';
        } else {
            toast.style.backgroundColor = '#007bff';
            toast.style.color = '#ffffff';
        }
        
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '8px';
        toast.style.marginTop = '10px';
        toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        toast.style.opacity = '1';
        toast.style.transition = 'opacity 0.3s ease-out';
        toast.textContent = message;
        
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ------------------------------------------------------------
    // Attachement des événements sur les boutons de modale
    // ------------------------------------------------------------
    document.getElementById('add-moto-btn').addEventListener('click', openAddMotoModal);
    document.getElementById('confirm-add-moto').addEventListener('click', submitAddMoto);
    document.getElementById('confirm-edit-moto').addEventListener('click', submitEditMoto);
    document.getElementById('confirm-validate-moto').addEventListener('click', submitValidateMoto);
    document.getElementById('confirm-delete-moto').addEventListener('click', submitDeleteMoto);

    // Boutons de fermeture pour toutes les modales
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });

    // Chargement initial des motos
    loadMotos();
});
</script>