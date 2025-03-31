@extends('layouts.app')


<style>

    

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

/* Styles supplémentaires spécifiques à la page des chauffeurs */
.photo-preview {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary);
}

.status-badge.validé {
    background-color: #E8F5E9;
    color: #2E7D32;
}

.status-badge.en_attente {
    background-color: #FFF3E0;
    color: #E65100;
}

.status-badge.rejeté {
    background-color: #FFEBEE;
    color: #C62828;
}

.cni-preview-container {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.cni-preview {
    width: 100%;
    height: 150px;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.cni-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cni-label {
    text-align: center;
    font-size: 0.8rem;
    margin-top: 5px;
    color: #666;
}

.photo-upload-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 20px;
}

.photo-upload-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-color: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 10px;
    border: 3px solid var(--primary);
}

.photo-upload-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-upload-btn {
    cursor: pointer;
    padding: 8px 12px;
    background-color: var(--primary);
    color: var(--secondary);
    border: none;
    border-radius: 4px;
    font-size: 0.8rem;
}

.profile-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-name {
    font-weight: 600;
}

.profile-details {
    font-size: 0.8rem;
    color: #666;
}

.modal-validation-tabs {
    display: flex;
    border-bottom: 1px solid rgba(16, 16, 16, 0.1);
    margin-bottom: 15px;
}

.modal-validation-tab {
    padding: 10px 15px;
    cursor: pointer;
    font-weight: 500;
    border-bottom: 2px solid transparent;
}

.modal-validation-tab.active {
    color: var(--primary);
    border-bottom: 2px solid var(--primary);
}

.modal-validation-content > div {
    display: none;
}

.modal-validation-content > div.active {
    display: block;
}
</style>


@section('content')
<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>Gestion des Chauffeurs</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-chauffeur" placeholder="Rechercher un chauffeur...">
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
            <button id="add-chauffeur-btn" class="add-btn">
                <i class="fas fa-plus"></i> Ajouter un chauffeur
            </button>
        </div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Total des chauffeurs</div>
            <div class="stat-value" id="total-chauffeurs">0</div>
            <div class="stat-change">Tous les chauffeurs enregistrés</div>
        </div>

        <div class="stat-card">
            <div class="stat-title">Chauffeurs en attente</div>
            <div class="stat-value" id="pending-chauffeurs">0</div>
            <div class="stat-change">En attente de validation</div>
        </div>

        <div class="stat-card">
            <div class="stat-title">Chauffeurs validés</div>
            <div class="stat-value" id="validated-chauffeurs">0</div>
            <div class="stat-change">Autorisés à conduire</div>
        </div>

        <div class="stat-card">
            <div class="stat-title">Chauffeurs rejetés</div>
            <div class="stat-value" id="rejected-chauffeurs">0</div>
            <div class="stat-change">Demandes refusées</div>
        </div>
    </div>

    <!-- Tableau des chauffeurs -->
    <div class="table-container">
        <div class="head-table">
            <h2>Liste des Chauffeurs</h2>
            <a href="#" id="export-chauffeurs">
                <i class="fas fa-download"></i>
                Exporter la liste
            </a>
        </div>
        <table id="chauffeurs-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>ID Chauffeur</th>
                    <th>Nom complet</th>
                    <th>Contact</th>
                    <th>N° CNI</th>
                    <th>Statut</th>
                    <th>Date d'ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="chauffeurs-table-body">
                <!-- Les chauffeurs seront chargés dynamiquement -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modale Ajouter Chauffeur -->
<div class="modal" id="add-chauffeur-modal">
    <div class="modal-content" style="width: 900px;">
        <div class="modal-header">
            <h2>Ajouter un Chauffeur</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="add-chauffeur-form" enctype="multipart/form-data">
                @csrf
                <div class="photo-upload-container">
                    <div class="photo-upload-preview">
                        <img id="photo-preview" src="/storage/chauffeurs/default-avatar.png" alt="Photo du chauffeur">
                    </div>
                    <label for="photo" class="photo-upload-btn">
                        <i class="fas fa-camera"></i> Ajouter une photo
                    </label>
                    <input type="file" id="photo" name="photo" style="display: none;" accept="image/*">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="text" id="phone" name="phone" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="numero_cni">Numéro CNI</label>
                        <input type="text" id="numero_cni" name="numero_cni" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Photos de la CNI</label>
                    <div class="cni-preview-container">
                        <div>
                            <div class="cni-preview">
                                <img id="cni-recto-preview" src="/storage/chauffeurs/cni-placeholder.png" alt="CNI Recto">
                            </div>
                            <div class="cni-label">
                                <label for="photo_cni_recto" class="btn btn-primary" style="cursor: pointer;">
                                    <i class="fas fa-upload"></i> Recto
                                </label>
                                <input type="file" id="photo_cni_recto" name="photo_cni_recto" style="display: none;" accept="image/*">
                            </div>
                        </div>
                        <div>
                            <div class="cni-preview">
                                <img id="cni-verso-preview" src="/storage/chauffeurs/cni-placeholder.png" alt="CNI Verso">
                            </div>
                            <div class="cni-label">
                                <label for="photo_cni_verso" class="btn btn-primary" style="cursor: pointer;">
                                    <i class="fas fa-upload"></i> Verso
                                </label>
                                <input type="file" id="photo_cni_verso" name="photo_cni_verso" style="display: none;" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-add-chauffeur" class="btn btn-primary">Ajouter</button>
        </div>
    </div>
</div>

<!-- Modale Modifier Chauffeur -->
<div class="modal" id="edit-chauffeur-modal">
    <div class="modal-content" style="width: 900px;">
        <div class="modal-header">
            <h2>Modifier le Chauffeur</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="edit-chauffeur-form" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-chauffeur-id" name="id">
                
                <div class="photo-upload-container">
                    <div class="photo-upload-preview">
                        <img id="edit-photo-preview" src="/storage/chauffeurs/default-avatar.png" alt="Photo du chauffeur">
                    </div>
                    <label for="edit-photo" class="photo-upload-btn">
                        <i class="fas fa-camera"></i> Modifier la photo
                    </label>
                    <input type="file" id="edit-photo" name="photo" style="display: none;" accept="image/*">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-nom">Nom</label>
                        <input type="text" id="edit-nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-prenom">Prénom</label>
                        <input type="text" id="edit-prenom" name="prenom" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-email">Email</label>
                        <input type="email" id="edit-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-phone">Téléphone</label>
                        <input type="text" id="edit-phone" name="phone" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-numero_cni">Numéro CNI</label>
                        <input type="text" id="edit-numero_cni" name="numero_cni" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-password">Nouveau mot de passe (laisser vide si inchangé)</label>
                        <input type="password" id="edit-password" name="password">
                    </div>
                </div>

                <div class="form-group">
                    <label>Photos de la CNI</label>
                    <div class="cni-preview-container">
                        <div>
                            <div class="cni-preview">
                                <img id="edit-cni-recto-preview" src="/storage/chauffeurs/cni-placeholder.png" alt="CNI Recto">
                            </div>
                            <div class="cni-label">
                                <label for="edit-photo_cni_recto" class="btn btn-primary" style="cursor: pointer;">
                                    <i class="fas fa-upload"></i> Recto
                                </label>
                                <input type="file" id="edit-photo_cni_recto" name="photo_cni_recto" style="display: none;" accept="image/*">
                            </div>
                        </div>
                        <div>
                            <div class="cni-preview">
                                <img id="edit-cni-verso-preview" src="/storage/chauffeurs/cni-placeholder.png" alt="CNI Verso">
                            </div>
                            <div class="cni-label">
                                <label for="edit-photo_cni_verso" class="btn btn-primary" style="cursor: pointer;">
                                    <i class="fas fa-upload"></i> Verso
                                </label>
                                <input type="file" id="edit-photo_cni_verso" name="photo_cni_verso" style="display: none;" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-edit-chauffeur" class="btn btn-primary">Modifier</button>
        </div>
    </div>
</div>

<!-- Modale Valider Chauffeur -->
<div class="modal" id="validate-chauffeur-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Valider le Chauffeur</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="modal-validation-tabs">
                <div class="modal-validation-tab active" data-tab="info">Informations</div>
                <div class="modal-validation-tab" data-tab="documents">Documents</div>
            </div>
            
            <div class="modal-validation-content">
                <div id="info-tab" class="active">
                    <div class="profile-info">
                        <img id="validate-photo" src="/storage/chauffeurs/default-avatar.png" alt="Photo" class="photo-preview" style="width: 80px; height: 80px;">
                        <div>
                            <div id="validate-nom-prenom" class="profile-name">John Doe</div>
                            <div id="validate-email" class="profile-details">johndoe@example.com</div>
                            <div id="validate-phone" class="profile-details">+123 456 789</div>
                        </div>
                    </div>
                    
                    <table class="verification-data-table" style="margin-top: 20px;">
                        <tr>
                            <th>ID Unique</th>
                            <td id="validate-unique-id">CH12345678</td>
                        </tr>
                        <tr>
                            <th>Numéro CNI</th>
                            <td id="validate-numero-cni">1234567890123</td>
                        </tr>
                        <tr>
                            <th>Date d'inscription</th>
                            <td id="validate-created-at">01/01/2023</td>
                        </tr>
                        <tr>
                            <th>Statut actuel</th>
                            <td id="validate-status">
                                <span class="status-badge en_attente">En attente</span>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div id="documents-tab">
                    <h3 style="margin-bottom: 15px;">Pièces d'identité</h3>
                    <div class="cni-preview-container" style="margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <h4 style="margin-bottom: 10px;">CNI Recto</h4>
                            <div class="cni-preview" style="height: 200px;">
                                <img id="validate-cni-recto" src="/storage/chauffeurs/cni-placeholder.png" alt="CNI Recto">
                            </div>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="margin-bottom: 10px;">CNI Verso</h4>
                            <div class="cni-preview" style="height: 200px;">
                                <img id="validate-cni-verso" src="/storage/chauffeurs/cni-placeholder.png" alt="CNI Verso">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <input type="hidden" id="validate-chauffeur-id">
        </div>
        <div class="modal-footer">
            <button id="reject-chauffeur-btn" class="btn" style="background-color: #ffcdd2; color: #c62828;">
                <i class="fas fa-times"></i> Rejeter
            </button>
            <button id="confirm-validate-chauffeur" class="btn btn-primary">
                <i class="fas fa-check"></i> Valider le chauffeur
            </button>
        </div>
    </div>
</div>

<!-- Modale Rejeter Chauffeur -->
<div class="modal" id="reject-chauffeur-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Rejeter le Chauffeur</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Veuillez indiquer le motif du rejet :</p>
            <form id="reject-chauffeur-form">
                @csrf
                <input type="hidden" id="reject-chauffeur-id" name="id">
                <div class="form-group">
                    <label for="motif_rejet">Motif du rejet</label>
                    <textarea id="motif_rejet" name="motif_rejet" rows="4" required></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-reject-chauffeur" class="btn btn-primary">Confirmer le rejet</button>
        </div>
    </div>
</div>

<!-- Modale Supprimer Chauffeur -->
<div class="modal" id="delete-chauffeur-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Supprimer le Chauffeur</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer ce chauffeur ?</p>
            <div class="chauffeur-details">
                <p><strong>ID :</strong> <span id="delete-unique-id"></span></p>
                <p><strong>Nom :</strong> <span id="delete-nom-prenom"></span></p>
            </div>
            <p class="text-danger">Cette action est irréversible.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-delete-chauffeur" class="btn btn-primary">Supprimer</button>
        </div>
    </div>
</div>





<script>
document.addEventListener('DOMContentLoaded', function() {
    // Références aux éléments DOM
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const chauffeursTableBody = document.getElementById('chauffeurs-table-body');
    const searchInput = document.getElementById('search-chauffeur');
    const statusFilter = document.getElementById('status-filter');
    
    // Modales
    const addChauffeurModal = document.getElementById('add-chauffeur-modal');
    const editChauffeurModal = document.getElementById('edit-chauffeur-modal');
    const validateChauffeurModal = document.getElementById('validate-chauffeur-modal');
    const rejectChauffeurModal = document.getElementById('reject-chauffeur-modal');
    const deleteChauffeurModal = document.getElementById('delete-chauffeur-modal');
    
    // Boutons
    const addChauffeurBtn = document.getElementById('add-chauffeur-btn');
    const confirmAddChauffeurBtn = document.getElementById('confirm-add-chauffeur');
    const confirmEditChauffeurBtn = document.getElementById('confirm-edit-chauffeur');
    const confirmValidateChauffeurBtn = document.getElementById('confirm-validate-chauffeur');
    const rejectChauffeurBtn = document.getElementById('reject-chauffeur-btn');
    const confirmRejectChauffeurBtn = document.getElementById('confirm-reject-chauffeur');
    const confirmDeleteChauffeurBtn = document.getElementById('confirm-delete-chauffeur');
    
    // Affichage de la date actuelle
    document.getElementById('date').textContent = new Date().toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    
    // Chargement des chauffeurs
    function loadChauffeurs() {
        let url = '/chauffeurs';
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
        .then(chauffeurs => {
            renderChauffeurs(chauffeurs);
            updateStats(chauffeurs);
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement des chauffeurs', 'error');
        });
    }
    
    // Affichage des chauffeurs dans le tableau
    function renderChauffeurs(chauffeurs) {
        chauffeursTableBody.innerHTML = '';
        if (chauffeurs.length === 0) {
            chauffeursTableBody.innerHTML = 
                `<tr><td colspan="8" class="text-center">Aucun chauffeur trouvé</td></tr>`;
            return;
        }
        
        chauffeurs.forEach(chauffeur => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', chauffeur.id);
            
            const photoUrl = chauffeur.photo 
                ? `/storage/chauffeurs/photos/${chauffeur.photo}` 
                : '/storage/chauffeurs/default-avatar.png';
            
            const createdAt = new Date(chauffeur.created_at).toLocaleDateString('fr-FR');
            const statusClass = chauffeur.status.replace(' ', '_').toLowerCase();
            
            row.innerHTML = `
                <td>
                    <img src="${photoUrl}" alt="Photo" class="photo-preview">
                </td>
                <td>${chauffeur.user_unique_id || 'N/A'}</td>
                <td>${chauffeur.nom} ${chauffeur.prenom}</td>
                <td>
                    <div>${chauffeur.email}</div>
                    <div>${chauffeur.phone}</div>
                </td>
                <td>${chauffeur.numero_cni}</td>
                <td>
                    <span class="status-badge ${statusClass}">
                        ${chauffeur.status}
                    </span>
                </td>
                <td>${createdAt}</td>
                <td>
                    <div class="action-group">
                        <button class="action-btn edit edit-chauffeur" title="Modifier" ${chauffeur.status === 'validé' ? 'disabled' : ''}>
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn validate validate-chauffeur" title="Valider" ${chauffeur.status !== 'en attente' ? 'disabled' : ''}>
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="action-btn delete delete-chauffeur" title="Supprimer" ${chauffeur.status === 'validé' ? 'disabled' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            chauffeursTableBody.appendChild(row);
        });
        
        attachActionListeners();
    }
    
    // Mise à jour des statistiques
    function updateStats(chauffeurs) {
        const total = chauffeurs.length;
        const pending = chauffeurs.filter(c => c.status === 'en attente').length;
        const validated = chauffeurs.filter(c => c.status === 'validé').length;
        const rejected = chauffeurs.filter(c => c.status === 'rejeté').length;
        
        document.getElementById('total-chauffeurs').textContent = total;
        document.getElementById('pending-chauffeurs').textContent = pending;
        document.getElementById('validated-chauffeurs').textContent = validated;
        document.getElementById('rejected-chauffeurs').textContent = rejected;
    }
    
    // Attacher les écouteurs d'événements aux boutons d'action
    function attachActionListeners() {
        document.querySelectorAll('.edit-chauffeur').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.closest('tr').getAttribute('data-id');
                openEditModal(id);
            });
        });
        
        document.querySelectorAll('.validate-chauffeur').forEach(btn => {
            btn.addEventListener('click', () => {
                const row = btn.closest('tr');
                const id = row.getAttribute('data-id');
                openValidateModal(id);
            });
        });
        
        document.querySelectorAll('.delete-chauffeur').forEach(btn => {
            btn.addEventListener('click', () => {
                const row = btn.closest('tr');
                const id = row.getAttribute('data-id');
                const cells = row.querySelectorAll('td');
                openDeleteModal(id, cells[1].textContent, cells[2].textContent);
            });
        });
    }
    
    // Ouvrir la modale d'ajout
    function openAddModal() {
        // Réinitialiser le formulaire
        document.getElementById('add-chauffeur-form').reset();
        
        // Réinitialiser les prévisualisations
        document.getElementById('photo-preview').src = '/storage/chauffeurs/default-avatar.png';
        document.getElementById('cni-recto-preview').src = '/storage/chauffeurs/cni-placeholder.png';
        document.getElementById('cni-verso-preview').src = '/storage/chauffeurs/cni-placeholder.png';
        
        // Afficher la modale
        addChauffeurModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Ouvrir la modale d'édition
    function openEditModal(id) {
        fetch(`/chauffeurs/${id}/edit`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(chauffeur => {
            // Remplir le formulaire avec les données du chauffeur
            document.getElementById('edit-chauffeur-id').value = chauffeur.id;
            document.getElementById('edit-nom').value = chauffeur.nom;
            document.getElementById('edit-prenom').value = chauffeur.prenom;
            document.getElementById('edit-email').value = chauffeur.email;
            document.getElementById('edit-phone').value = chauffeur.phone;
            document.getElementById('edit-numero_cni').value = chauffeur.numero_cni;
            
            // Réinitialiser le champ de mot de passe (optionnel lors de la modification)
            document.getElementById('edit-password').value = '';
            
            // Mettre à jour les prévisualisations des images
            if (chauffeur.photo) {
                document.getElementById('edit-photo-preview').src = `/storage/chauffeurs/photos/${chauffeur.photo}`;
            } else {
                document.getElementById('edit-photo-preview').src = '/storage/chauffeurs/default-avatar.png';
            }
            
            if (chauffeur.photo_cni_recto) {
                document.getElementById('edit-cni-recto-preview').src = `/storage/chauffeurs/cni/${chauffeur.photo_cni_recto}`;
            } else {
                document.getElementById('edit-cni-recto-preview').src = '/storage/chauffeurs/cni-placeholder.png';
            }
            
            if (chauffeur.photo_cni_verso) {
                document.getElementById('edit-cni-verso-preview').src = `/storage/chauffeurs/cni/${chauffeur.photo_cni_verso}`;
            } else {
                document.getElementById('edit-cni-verso-preview').src = '/storage/chauffeurs/cni-placeholder.png';
            }
            
            // Afficher la modale
            editChauffeurModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la récupération des données du chauffeur', 'error');
        });
    }
    
    // Ouvrir la modale de validation
    function openValidateModal(id) {
        fetch(`/chauffeurs/${id}/edit`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(chauffeur => {
            // Remplir les détails du chauffeur dans la modale
            document.getElementById('validate-chauffeur-id').value = chauffeur.id;
            document.getElementById('validate-nom-prenom').textContent = `${chauffeur.nom} ${chauffeur.prenom}`;
            document.getElementById('validate-email').textContent = chauffeur.email;
            document.getElementById('validate-phone').textContent = chauffeur.phone;
            document.getElementById('validate-unique-id').textContent = chauffeur.user_unique_id;
            document.getElementById('validate-numero-cni').textContent = chauffeur.numero_cni;
            document.getElementById('validate-created-at').textContent = new Date(chauffeur.created_at).toLocaleDateString('fr-FR');
            
            // Statut avec badge
            const statusBadge = `<span class="status-badge ${chauffeur.status.replace(' ', '_').toLowerCase()}">${chauffeur.status}</span>`;
            document.getElementById('validate-status').innerHTML = statusBadge;
            
            // Images
            if (chauffeur.photo) {
                document.getElementById('validate-photo').src = `/storage/chauffeurs/photos/${chauffeur.photo}`;
            } else {
                document.getElementById('validate-photo').src = '/storage/chauffeurs/default-avatar.png';
            }
            
            if (chauffeur.photo_cni_recto) {
                document.getElementById('validate-cni-recto').src = `/storage/chauffeurs/cni/${chauffeur.photo_cni_recto}`;
            } else {
                document.getElementById('validate-cni-recto').src = '/storage/chauffeurs/cni-placeholder.png';
            }
            
            if (chauffeur.photo_cni_verso) {
                document.getElementById('validate-cni-verso').src = `/storage/chauffeurs/cni/${chauffeur.photo_cni_verso}`;
            } else {
                document.getElementById('validate-cni-verso').src = '/storage/chauffeurs/cni-placeholder.png';
            }
            
            // Afficher la modale
            validateChauffeurModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Activer le premier onglet par défaut
            document.querySelector('.modal-validation-tab[data-tab="info"]').click();
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la récupération des données du chauffeur', 'error');
        });
    }
    
    // Ouvrir la modale de rejet
    function openRejectModal(id) {
        document.getElementById('reject-chauffeur-id').value = id;
        rejectChauffeurModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Ouvrir la modale de suppression
    function openDeleteModal(id, uniqueId, nomPrenom) {
        document.getElementById('delete-unique-id').textContent = uniqueId;
        document.getElementById('delete-nom-prenom').textContent = nomPrenom;
        confirmDeleteChauffeurBtn.setAttribute('data-id', id);
        deleteChauffeurModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Fermer toutes les modales
    function closeAllModals() {
        const modals = [
            addChauffeurModal, 
            editChauffeurModal, 
            validateChauffeurModal, 
            rejectChauffeurModal, 
            deleteChauffeurModal
        ];
        
        modals.forEach(modal => {
            if (modal) modal.classList.remove('active');
        });
        
        document.body.style.overflow = '';
    }
    
    // Soumission du formulaire d'ajout
    function submitAddChauffeur() {
        const form = document.getElementById('add-chauffeur-form');
        const formData = new FormData(form);
        
        fetch('/chauffeurs', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Erreur lors de l\'ajout du chauffeur');
                });
            }
            return response.json();
        })
        .then(chauffeur => {
            loadChauffeurs();
            closeAllModals();
            showToast('Chauffeur ajouté avec succès', 'success');
            form.reset();
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast(error.message || 'Erreur lors de l\'ajout du chauffeur', 'error');
        });
    }
    
    // Soumission du formulaire d'édition
    function submitEditChauffeur() {
        const id = document.getElementById('edit-chauffeur-id').value;
        const form = document.getElementById('edit-chauffeur-form');
        const formData = new FormData(form);
        
        fetch(`/chauffeurs/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-HTTP-Method-Override': 'PUT',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Erreur lors de la modification du chauffeur');
                });
            }
            return response.json();
        })
        .then(chauffeur => {
            loadChauffeurs();
            closeAllModals();
            showToast('Chauffeur modifié avec succès', 'success');
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast(error.message || 'Erreur lors de la modification du chauffeur', 'error');
        });
    }
    
    // Soumission de la validation du chauffeur
    function submitValidateChauffeur() {
        const id = document.getElementById('validate-chauffeur-id').value;
        
        fetch(`/chauffeurs/${id}/validate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur lors de la validation');
            return response.json();
        })
        .then(chauffeur => {
            loadChauffeurs();
            closeAllModals();
            showToast('Chauffeur validé avec succès', 'success');
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la validation du chauffeur', 'error');
        });
    }
    
    // Soumission du rejet du chauffeur
    function submitRejectChauffeur() {
        const id = document.getElementById('reject-chauffeur-id').value;
        const motif = document.getElementById('motif_rejet').value;
        
        fetch(`/chauffeurs/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                motif_rejet: motif
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur lors du rejet');
            return response.json();
        })
        .then(chauffeur => {
            loadChauffeurs();
            closeAllModals();
            showToast('Chauffeur rejeté avec succès', 'success');
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors du rejet du chauffeur', 'error');
        });
    }
    
    // Soumission de la suppression du chauffeur
    function submitDeleteChauffeur() {
        const id = confirmDeleteChauffeurBtn.getAttribute('data-id');
        
        fetch(`/chauffeurs/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur lors de la suppression');
            return response.json();
        })
        .then(result => {
            loadChauffeurs();
            closeAllModals();
            showToast('Chauffeur supprimé avec succès', 'success');
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la suppression du chauffeur', 'error');
        });
    }
    
    // Fonction pour afficher un toast de notification
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
        toast.style.backgroundColor = type === 'success' ? '#DCDB32' : type === 'error' ? '#ff6b6b' : '#007bff';
        toast.style.color = type === 'success' ? '#101010' : '#ffffff';
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
    
    // Gestion des événements de prévisualisation des images
    function setupImagePreviews() {
        // Photo principale
        document.getElementById('photo').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photo-preview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // CNI Recto
        document.getElementById('photo_cni_recto').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('cni-recto-preview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // CNI Verso
        document.getElementById('photo_cni_verso').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('cni-verso-preview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Pour la modale d'édition
        document.getElementById('edit-photo').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit-photo-preview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        document.getElementById('edit-photo_cni_recto').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit-cni-recto-preview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        document.getElementById('edit-photo_cni_verso').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit-cni-verso-preview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Gestion des onglets dans la modale de validation
    function setupValidationTabs() {
        document.querySelectorAll('.modal-validation-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Retirer la classe active de tous les onglets
                document.querySelectorAll('.modal-validation-tab').forEach(t => {
                    t.classList.remove('active');
                });
                
                // Retirer la classe active de tous les contenus
                document.querySelectorAll('.modal-validation-content > div').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Ajouter la classe active à l'onglet cliqué
                this.classList.add('active');
                
                // Afficher le contenu correspondant à l'onglet
                const tabId = this.getAttribute('data-tab');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });
    }
    
    // Initialiser les événements pour les filtres et la recherche
    function setupFilters() {
        searchInput.addEventListener('input', loadChauffeurs);
        statusFilter.addEventListener('change', loadChauffeurs);
    }
    
    // Attacher les événements à tous les boutons de fermeture des modales
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });
    
    // Fermer la modale si on clique sur l'arrière-plan (overlay)
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeAllModals();
        });
    });
    
    // Boutons d'action dans les modales
    addChauffeurBtn.addEventListener('click', openAddModal);
    confirmAddChauffeurBtn.addEventListener('click', submitAddChauffeur);
    confirmEditChauffeurBtn.addEventListener('click', submitEditChauffeur);
    confirmValidateChauffeurBtn.addEventListener('click', submitValidateChauffeur);
    rejectChauffeurBtn.addEventListener('click', () => {
        const id = document.getElementById('validate-chauffeur-id').value;
        closeAllModals();
        openRejectModal(id);
    });
    confirmRejectChauffeurBtn.addEventListener('click', submitRejectChauffeur);
    confirmDeleteChauffeurBtn.addEventListener('click', submitDeleteChauffeur);
    
    // Initialisation
    setupImagePreviews();
    setupValidationTabs();
    setupFilters();
    loadChauffeurs();
});
</script>

@endsection

