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
    width: 600px;
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

.password-hint {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 0.85em;
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
    grid-template-columns: repeat(4, 1fr);
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

.danger .stat-icon {
    color: #dc3545;
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

.action-btn.edit-distributeur i {
    color: #ffc107;
}

.action-btn.delete-distributeur i {
    color: #dc3545;
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
</style>

@section('title', 'Gestion des Distributeurs')

@section('content')

<div class="main-content">
    <div class="content-header">
        <h2>Gestion des Distributeurs</h2>
        <div id="date" class="date">{{ date('d/m/Y') }}</div>
    </div>

    <!-- Barre de recherche et bouton d'ajout -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-distributeur" placeholder="Rechercher un distributeur...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-group">
            <button id="add-distributeur-btn" class="add-btn">
                <i class="fas fa-plus"></i>
                Ajouter un Distributeur
            </button>
        </div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-distributeurs">{{ $distributeurs->count() }}</div>
                <div class="stat-label">Total des distributeurs</div>
                <div class="stat-text">Tous types confondus</div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-douala">{{ $distributeurs->where('ville', 'Douala')->count() }}</div>
                <div class="stat-label">Distributeurs à Douala</div>
                <div class="stat-text">Stations de swap</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-yaounde">{{ $distributeurs->where('ville', 'Yaoundé')->count() }}</div>
                <div class="stat-label">Distributeurs à Yaoundé</div>
                <div class="stat-text">Stations de swap</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-users">{{ $distributeurs->count() }}</div>
                <div class="stat-label">Utilisateurs</div>
                <div class="stat-text">Comptes actifs</div>
            </div>
        </div>
    </div>

    <!-- Tableau des distributeurs -->
    <div class="table-container">
        <table id="distributeurs-table">
            <thead>
                <tr>
                    <th>Identifiant</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Ville</th>
                    <th>Quartier</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="distributeurs-table-body">
                @foreach($distributeurs as $distributeur)
                <tr data-id="{{ $distributeur->id }}">
                    <td>{{ $distributeur->distributeur_unique_id }}</td>
                    <td>{{ $distributeur->nom }}</td>
                    <td>{{ $distributeur->prenom }}</td>
                    <td>{{ $distributeur->ville }}</td>
                    <td>{{ $distributeur->quartier }}</td>
                    <td>{{ $distributeur->phone }}</td>
                    <td>{{ $distributeur->email }}</td>
                    <td style="display: flex;">
                        <button class="action-btn edit-distributeur" title="Modifier le distributeur" data-id="{{ $distributeur->id }}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete-distributeur" title="Supprimer le distributeur" data-id="{{ $distributeur->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modale d'ajout/édition de distributeur -->
    <div class="modal" id="distributeur-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Ajouter un Distributeur</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="distributeur-form">
                    @csrf
                    <input type="hidden" id="distributeur-id" name="id">
                    <input type="hidden" id="form-method" name="_method" value="POST">

                    <div class="form-group">
                        <label for="nom">Nom du Distributeur</label>
                        <input type="text" name="nom" id="nom" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom du Distributeur</label>
                        <input type="text" name="prenom" id="prenom" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <select name="ville" id="ville" class="form-control" required>
                            <option value="">Sélectionner une ville</option>
                            <option value="Douala">Douala</option>
                            <option value="Yaoundé">Yaoundé</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quartier">Quartier</label>
                        <select name="quartier" id="quartier" class="form-control" required>
                            <option value="">Sélectionner un quartier</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="text" name="phone" id="telephone" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                        <small class="password-hint" id="password-hint">Le mot de passe doit contenir au moins 6 caractères.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Annuler</button>
                <button id="save-distributeur" class="btn btn-primary">Enregistrer</button>
            </div>
        </div>
    </div>

    <!-- Modale de suppression de distributeur -->
    <div class="modal" id="delete-distributeur-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Supprimer le distributeur</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce distributeur ?</p>
                <div class="distributeur-details">
                    <p><strong>Nom :</strong> <span id="delete-distributeur-name"></span></p>
                    <p><strong>Identifiant :</strong> <span id="delete-distributeur-id"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Annuler</button>
                <button id="confirm-delete-distributeur" class="btn btn-primary">Supprimer</button>
            </div>
        </div>
    </div>

    <!-- Toast pour les notifications -->
    <div class="toast-container"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const searchInput = document.getElementById('search-distributeur');
    const addDistributeurBtn = document.getElementById('add-distributeur-btn');
    const distributeurModal = document.getElementById('distributeur-modal');
    const deleteDistributeurModal = document.getElementById('delete-distributeur-modal');
    const distributeursTableBody = document.getElementById('distributeurs-table-body');
    const modalTitle = document.getElementById('modal-title');
    
    // Formulaire et éléments pour ajouter/éditer un distributeur
    const distributeurForm = document.getElementById('distributeur-form');
    const distributeurIdInput = document.getElementById('distributeur-id');
    const formMethodInput = document.getElementById('form-method');
    const villeSelect = document.getElementById('ville');
    const quartierSelect = document.getElementById('quartier');
    const saveDistributeurBtn = document.getElementById('save-distributeur');
    const confirmDeleteDistributeurBtn = document.getElementById('confirm-delete-distributeur');
    const deleteDistributeurName = document.getElementById('delete-distributeur-name');
    const deleteDistributeurIdSpan = document.getElementById('delete-distributeur-id');

    // Liste des quartiers par ville
    const quartiers = {
        "Douala": [
            "Akwa", "Bali", "Bekoko", "Bepanda", "Bilonguè", "Bonabéri", "Bonadibong", "Bonagang", "Bonamikengue",
            "Bonamouang", "Bonamoussadi", "Bonamoussadi Cité", "Bonanjo", "Bonapriso", "Bonapriso Plateau",
            "Bonassama", "Bwang", "Bwang Bakoko", "Bépanda", "Cité Sic", "Cité des Palmiers", "Deido",
            "Dibamba", "Japoma", "Kake", "Kotto", "Kotto Bass", "Koumassi", "Lendi", "Logbaba", "Logpom","Logbessou",
            "Mabanda", "Makepe", "Makèpè Missokè", "Mbanya", "Mboppi", "Ndogbong", "Ndogpassi", "Ndokbon",
            "Ndokoti", "New-Bell", "Ngodi", "Ngodi Bakoko", "Nkongmondo", "Nyalla", "PK10", "PK12", "PK21",
            "PK8", "PK9", "Pékin", "Petit Paris", "Soboum","Village", "Yassa", "Youpwe"
        ],
        "Yaoundé": [
            "Ahala", "Anguissa", "Bastos", "Biyem-Assi", "Briqueterie", "Camp SIC Hippodrome",
            "Carrefour MEEC", "Ekoumdoum", "Ekounou", "Elig-Effa", "Elig-Edzoa", "Emana", "Essos",
            "Etoa-Meki", "Ewondo", "Ewoue", "Kondengui", "Koweit City", "Mbankolo", "Melen", "Messa",
            "Messamendongo", "Messassi", "Mendong", "Mimboman", "Mokolo", "Mvan", "Mvan Carrefour",
            "Mvog Ebanda", "Mvog-Ada", "Mvog-Atangana Mballa", "Mvog-Betsi", "Mvog-Mbi", "Ndamvout",
            "Ngousso", "Nkoabang", "Nkol-Afeme", "Nkol-Eton", "Nkol-Foulou", "Nkol-Nyada", "Nkoldongo",
            "Nkolbisson", "Nkolmesseng", "Nsam", "Nsimalen", "Nsimeyong", "Nlongkak", "Obili", "Odza",
            "Olembe", "Omnisports", "Oyomabang", "Santa Barbara", "Tsinga"
        ]
    };

    // Fonction pour afficher les toast
    function showToast(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        toastContainer.appendChild(toast);
        
        // Faire disparaître le toast après 3 secondes
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                toastContainer.removeChild(toast);
            }, 300);
        }, 3000);
    }

    // Mettre à jour la liste des quartiers en fonction de la ville
    function updateQuartiers() {
        quartierSelect.innerHTML = '<option value="">Sélectionner un quartier</option>';
        const selectedVille = villeSelect.value;
        
        if (quartiers[selectedVille]) {
            quartiers[selectedVille].forEach(q => {
                const option = new Option(q, q);
                quartierSelect.add(option);
            });
        }
    }

    // Filtrer le tableau des distributeurs
    function filterDistributeursTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = distributeursTableBody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            row.style.display = textContent.includes(searchTerm) ? '' : 'none';
        });
    }

    // Rafraîchir la table des distributeurs
    function refreshDistributeursTable() {
        fetch('/distributeurs/list', {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderDistributeursTable(data.distributeurs);
                    // Mettre à jour les statistiques
                    updateStats();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des distributeurs:', error);
                showToast('Erreur lors du chargement des distributeurs.', 'error');
            });
    }

    // Mettre à jour les statistiques
    function updateStats() {
        fetch('/distributeurs/stats', {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                document.getElementById('total-distributeurs').textContent = data.total_distributeurs;
                document.getElementById('total-douala').textContent = data.total_douala;
                document.getElementById('total-yaounde').textContent = data.total_yaounde;
                document.getElementById('total-users').textContent = data.total_users;
            })
            .catch(error => {
                console.error('Erreur lors du chargement des statistiques:', error);
            });
    }

    // Rendu du tableau des distributeurs
    function renderDistributeursTable(distributeurs) {
        distributeursTableBody.innerHTML = '';
        
        distributeurs.forEach(distributeur => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', distributeur.id);
            
            row.innerHTML = `
                <td>${distributeur.distributeur_unique_id}</td>
                <td>${distributeur.nom}</td>
                <td>${distributeur.prenom}</td>
                <td>${distributeur.ville}</td>
                <td>${distributeur.quartier}</td>
                <td>${distributeur.phone}</td>
                <td>${distributeur.email}</td>
                <td style="display: flex;">
                    <button class="action-btn edit-distributeur" title="Modifier le distributeur" data-id="${distributeur.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-distributeur" title="Supprimer le distributeur" data-id="${distributeur.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            distributeursTableBody.appendChild(row);
        });
        
        // Réattacher les événements pour les nouveaux boutons
        attachEventListeners();
    }

    // Ouvrir la modale d'ajout de distributeur
    function openAddDistributeurModal() {
        distributeurForm.reset();
        distributeurIdInput.value = '';
        formMethodInput.value = 'POST';
        modalTitle.textContent = 'Ajouter un Distributeur';
        
        // Réinitialiser la liste des quartiers
        villeSelect.value = '';
        quartierSelect.innerHTML = '<option value="">Sélectionner un quartier</option>';
        
        // Afficher le champ de mot de passe comme requis
        document.getElementById('password').setAttribute('required', 'required');
        document.getElementById('password-hint').style.display = 'block';
        
        distributeurModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Ouvrir la modale de suppression de distributeur
    function openDeleteDistributeurModal(id) {
        fetch(`/distributeurs/${id}/edit`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const distributeur = data.distributeur;
                    deleteDistributeurName.textContent = `${distributeur.nom} ${distributeur.prenom}`;
                    deleteDistributeurIdSpan.textContent = distributeur.distributeur_unique_id;
                    
                    // Stocker l'ID pour la confirmation
                    confirmDeleteDistributeurBtn.setAttribute('data-id', distributeur.id);
                    
                    deleteDistributeurModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails du distributeur:', error);
                showToast('Erreur lors du chargement des détails du distributeur.', 'error');
            });
    }

    // Enregistrer un distributeur (ajout ou édition)
    function saveDistributeur() {
        const formData = new FormData(distributeurForm);
        const id = distributeurIdInput.value;
        const isEdit = formMethodInput.value === 'PUT';
        
        const url = isEdit ? `/distributeurs/${id}` : '/distributeurs';
        const method = isEdit ? 'PUT' : 'POST';
        
        const data = {};
        formData.forEach((value, key) => {
            // Corriger l'incohérence entre telephone et phone
            if (key === 'telephone') {
                data['phone'] = value;
            } else {
                data[key] = value;
            }
        });

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                closeAllModals();
                refreshDistributeursTable();
            } else {
                if (result.errors) {
                    // Afficher les erreurs de validation
                    const errorMessages = Object.values(result.errors).flat();
                    errorMessages.forEach(errorMessage => {
                        showToast(errorMessage, 'error');
                    });
                } else {
                    showToast(result.message, 'error');
                }
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'enregistrement du distributeur:', error);
            showToast('Erreur lors de l\'enregistrement du distributeur.', 'error');
        });
    }

    // Ouvrir la modale d'édition de distributeur
    function openEditDistributeurModal(id) {
        fetch(`/distributeurs/${id}/edit`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const distributeur = data.distributeur;
                    
                    // Mettre à jour le titre de la modale
                    modalTitle.textContent = 'Modifier un Distributeur';
                    
                    // Remplir les champs du formulaire
                    distributeurIdInput.value = distributeur.id;
                    formMethodInput.value = 'PUT';
                    
                    document.getElementById('nom').value = distributeur.nom;
                    document.getElementById('prenom').value = distributeur.prenom;
                    document.getElementById('ville').value = distributeur.ville;
                    updateQuartiers();
                    document.getElementById('quartier').value = distributeur.quartier;
                    document.getElementById('telephone').value = distributeur.phone;
                    document.getElementById('email').value = distributeur.email;
                    
                    // Le mot de passe n'est pas obligatoire en édition
                    document.getElementById('password').removeAttribute('required');
                    document.getElementById('password').value = '';
                    document.getElementById('password-hint').style.display = 'block';
                    
                    distributeurModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails du distributeur:', error);
                showToast('Erreur lors du chargement des détails du distributeur.', 'error');
            });
    }

    // Supprimer un distributeur
    function deleteDistributeur(id) {
        fetch(`/distributeurs/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                closeAllModals();
                refreshDistributeursTable();
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la suppression du distributeur:', error);
            showToast('Erreur lors de la suppression du distributeur.', 'error');
        });
    }

    // Fermer toutes les modales
    function closeAllModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
    
    // Attacher les écouteurs d'événements aux boutons d'action
    function attachEventListeners() {
        // Attacher les écouteurs pour les boutons d'édition
        document.querySelectorAll('.edit-distributeur').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                openEditDistributeurModal(id);
            });
        });
        
        // Attacher les écouteurs pour les boutons de suppression
        document.querySelectorAll('.delete-distributeur').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                openDeleteDistributeurModal(id);
            });
        });
    }

    // Initialisation des événements
    function initEvents() {
        // Bouton pour ajouter un distributeur
        addDistributeurBtn.addEventListener('click', openAddDistributeurModal);
        
        // Bouton pour enregistrer un distributeur
        saveDistributeurBtn.addEventListener('click', saveDistributeur);
        
        // Bouton pour confirmer la suppression d'un distributeur
        confirmDeleteDistributeurBtn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            deleteDistributeur(id);
        });
        
        // Fermer les modales
        document.querySelectorAll('.close-modal, .btn-secondary').forEach(btn => {
            btn.addEventListener('click', closeAllModals);
        });
        
        // Recherche
        searchInput.addEventListener('input', filterDistributeursTable);
        
        // Mise à jour des quartiers lors du changement de ville
        villeSelect.addEventListener('change', updateQuartiers);
        
        // Attacher les écouteurs d'événements initiaux
        attachEventListeners();
    }

    // Initialisation
    initEvents();
    refreshDistributeursTable();
});
</script>

@endsection
