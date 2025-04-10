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

.action-btn.edit-agence i {
    color: #ffc107;
}

.action-btn.delete-agence i {
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

@section('title', 'Gestion des Entrepôts')

@section('content')

<div class="main-content">
<div class="content-header">
    <h2>Gestion des Entrepôts</h2>
    <div id="date" class="date">{{ date('d/m/Y') }}</div>
</div>

<!-- Barre de recherche et bouton d'ajout -->
<div class="search-bar">
    <div class="search-group">
        <input type="text" id="search-entrepot" placeholder="Rechercher un entrepôt...">
        <button type="submit" class="search-btn">
            <i class="fas fa-search"></i>
        </button>
    </div>

    <div class="filter-group">
        <button id="add-entrepot-btn" class="add-btn">
            <i class="fas fa-plus"></i>
            Ajouter un Entrepôt
        </button>
    </div>
</div>

<!-- Cartes des statistiques -->
<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-icon">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-details">
            <div class="stat-number" id="total-entrepots">{{ $entrepots->count() }}</div>
            <div class="stat-label">Total des entrepôts</div>
            <div class="stat-text">Tous types confondus</div>
        </div>
    </div>

    <div class="stat-card pending">
        <div class="stat-icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="stat-details">
            <div class="stat-number" id="total-douala">{{ $entrepots->where('ville', 'Douala')->count() }}</div>
            <div class="stat-label">Entrepôts à Douala</div>
            <div class="stat-text">Stations de swap</div>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="stat-details">
            <div class="stat-number" id="total-yaounde">{{ $entrepots->where('ville', 'Yaoundé')->count() }}</div>
            <div class="stat-label">Entrepôts à Yaoundé</div>
            <div class="stat-text">Stations de swap</div>
        </div>
    </div>

    <div class="stat-card danger">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-details">
            <div class="stat-number" id="total-users">{{ $entrepots->count() }}</div>
            <div class="stat-label">Utilisateurs</div>
            <div class="stat-text">Comptes actifs</div>
        </div>
    </div>
</div>

<!-- Tableau des entrepôts -->
<div class="table-container">
    <table id="entrepots-table">
        <thead>
            <tr>
                <th>Identifiant</th>
                <th>Nom de l'Entrepôt</th>
                <th>Propriétaire</th>
                <th>Ville</th>
                <th>Quartier</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="entrepots-table-body">
            @foreach($entrepots as $entrepot)
            <tr data-id="{{ $entrepot->id }}">
                <td>{{ $entrepot->entrepot_unique_id }}</td>
                <td>{{ $entrepot->nom_entrepot }}</td>
                <td>{{ $entrepot->nom_proprietaire }}</td>
                <td>{{ $entrepot->ville }}</td>
                <td>{{ $entrepot->quartier }}</td>
                <td>{{ $entrepot->telephone }}</td>
                <td>{{ $entrepot->email }}</td>
                <td style="display: flex;">
                    <button class="action-btn edit-entrepot" title="Modifier l'entrepôt" data-id="{{ $entrepot->id }}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-entrepot" title="Supprimer l'entrepôt" data-id="{{ $entrepot->id }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modale d'ajout/édition d'entrepôt -->
<div class="modal" id="entrepot-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Ajouter un Entrepôt</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="entrepot-form">
                @csrf
                <input type="hidden" id="entrepot-id" name="id">
                <input type="hidden" id="form-method" name="_method" value="POST">

                <div class="form-group">
                    <label for="nom_entrepot">Nom de l'Entrepôt</label>
                    <input type="text" name="nom_entrepot" id="nom_entrepot" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="nom_proprietaire">Nom du Propriétaire</label>
                    <input type="text" name="nom_proprietaire" id="nom_proprietaire" class="form-control" required>
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
                    <label for="telephone">Téléphone</label>
                    <input type="text" name="telephone" id="telephone" class="form-control" required>
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
            <button id="save-entrepot" class="btn btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

<!-- Modale de suppression d'entrepôt -->
<div class="modal" id="delete-entrepot-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Supprimer l'entrepôt</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cet entrepôt ?</p>
            <div class="entrepot-details">
                <p><strong>Nom :</strong> <span id="delete-entrepot-name"></span></p>
                <p><strong>Identifiant :</strong> <span id="delete-entrepot-id"></span></p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-delete-entrepot" class="btn btn-primary">Supprimer</button>
        </div>
    </div>
</div>

<!-- Toast pour les notifications -->
<div class="toast-container"></div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ------------------------------------------------------------
    // Initialisation et variables
    // ------------------------------------------------------------
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const searchInput = document.getElementById('search-entrepot');
    const addEntrepotBtn = document.getElementById('add-entrepot-btn');
    const entrepotModal = document.getElementById('entrepot-modal');
    const deleteEntrepotModal = document.getElementById('delete-entrepot-modal');
    const entrepotsTableBody = document.getElementById('entrepots-table-body');
    
    // Éléments du formulaire
    const entrepotForm = document.getElementById('entrepot-form');
    const entrepotIdInput = document.getElementById('entrepot-id');
    const formMethodInput = document.getElementById('form-method');
    const villeSelect = document.getElementById('ville');
    const quartierSelect = document.getElementById('quartier');
    
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

    // ------------------------------------------------------------
    // Fonctions de mise à jour de l'interface
    // ------------------------------------------------------------
    
    // Mettre à jour la liste des quartiers en fonction de la ville sélectionnée
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
    
    // Filtrer le tableau des entrepôts
    function filterEntrepotsTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = entrepotsTableBody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            row.style.display = textContent.includes(searchTerm) ? '' : 'none';
        });
    }
    
    // Réinitialiser le formulaire
    function resetForm() {
        entrepotForm.reset();
        entrepotIdInput.value = '';
        formMethodInput.value = 'POST';
        document.getElementById('modal-title').textContent = 'Ajouter un Entrepôt';
        
        // Réinitialiser les messages d'erreur
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        // Rendre visible le champ de mot de passe pour un ajout
        const passwordGroup = document.querySelector('.form-group:has(#password)');
        passwordGroup.style.display = 'block';
        document.getElementById('password-hint').style.display = 'block';
    }
    
    // Mettre à jour les statistiques
    function updateStats() {
        fetch('/entrepots/stats')
            .then(response => response.json())
            .then(data => {
                document.getElementById('total-entrepots').textContent = data.total_entrepots;
                document.getElementById('total-douala').textContent = data.total_douala;
                document.getElementById('total-yaounde').textContent = data.total_yaounde;
                document.getElementById('total-users').textContent = data.total_users;
            })
            .catch(error => {
                console.error('Erreur lors du chargement des statistiques:', error);
            });
    }

    // ------------------------------------------------------------
    // Fonctions de gestion des modales
    // ------------------------------------------------------------
    
    // Ouvrir la modale d'ajout d'entrepôt
    function openAddEntrepotModal() {
        resetForm();
        entrepotModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Ouvrir la modale d'édition d'entrepôt
    function openEditEntrepotModal(id) {
        resetForm();
        formMethodInput.value = 'PUT';
        document.getElementById('modal-title').textContent = 'Modifier l\'Entrepôt';
        
        // Récupérer les détails de l'entrepôt
        fetch(`/entrepots/${id}/edit`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const entrepot = data.entrepot;
                    entrepotIdInput.value = entrepot.id;
                    
                    // Remplir les champs du formulaire
                    document.getElementById('nom_entrepot').value = entrepot.nom_entrepot;
                    document.getElementById('nom_proprietaire').value = entrepot.nom_proprietaire;
                    document.getElementById('ville').value = entrepot.ville;
                    
                    // Mettre à jour les quartiers et sélectionner celui de l'entrepôt
                    updateQuartiers();
                    document.getElementById('quartier').value = entrepot.quartier;
                    
                    document.getElementById('telephone').value = entrepot.telephone;
                    document.getElementById('email').value = entrepot.email;
                    
                    // Masquer le champ de mot de passe pour une modification
                    const passwordGroup = document.querySelector('.form-group:has(#password)');
                    passwordGroup.style.display = 'none';
                    document.getElementById('password-hint').style.display = 'none';
                    
                    entrepotModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails de l\'entrepôt:', error);
                showToast('Erreur lors du chargement des détails de l\'entrepôt.', 'error');
            });
    }
    
    // Ouvrir la modale de suppression d'entrepôt
    function openDeleteEntrepotModal(id) {
        // Récupérer les détails de l'entrepôt
        const row = document.querySelector(`tr[data-id="${id}"]`);
        const entrepotName = row.cells[1].textContent;
        const entrepotUniqueId = row.cells[0].textContent;
        
        document.getElementById('delete-entrepot-name').textContent = entrepotName;
        document.getElementById('delete-entrepot-id').textContent = entrepotUniqueId;
        
        // Stocker l'ID de l'entrepôt pour la suppression
        document.getElementById('confirm-delete-entrepot').dataset.id = id;
        
        deleteEntrepotModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Fermer toutes les modales
    function closeAllModals() {
        [entrepotModal, deleteEntrepotModal].forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    // ------------------------------------------------------------
    // Fonctions AJAX
    // ------------------------------------------------------------
    
    // Enregistrer ou mettre à jour un entrepôt
    function saveEntrepot() {
        // Récupérer les données du formulaire
        const formData = new FormData(entrepotForm);
        const isEdit = formMethodInput.value === 'PUT';
        const id = entrepotIdInput.value;
        
        // Construire l'URL et la méthode
        const url = isEdit ? `/entrepots/${id}` : '/entrepots';
        const method = isEdit ? 'PUT' : 'POST';
        
        // Créer un objet avec les données du formulaire
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        // Envoyer la requête AJAX
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
                
                // Recharger le tableau des entrepôts
                refreshEntrepotsTable();
                
                // Mettre à jour les statistiques
                updateStats();
            } else {
                // Afficher les erreurs de validation
                if (result.errors) {
                    displayValidationErrors(result.errors);
                } else {
                    showToast(result.message, 'error');
                }
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'enregistrement de l\'entrepôt:', error);
            showToast('Erreur lors de l\'enregistrement de l\'entrepôt.', 'error');
        });
    }
    
    // Supprimer un entrepôt
    function deleteEntrepot(id) {
        fetch(`/entrepots/${id}`, {
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
                
                // Retirer la ligne du tableau
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) row.remove();
                
                // Mettre à jour les statistiques
                updateStats();
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la suppression de l\'entrepôt:', error);
            showToast('Erreur lors de la suppression de l\'entrepôt.', 'error');
        });
    }
    
    // Rafraîchir le tableau des entrepôts
    function refreshEntrepotsTable() {
        fetch('/entrepots/list')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderEntrepotsTable(data.entrepots);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des entrepôts:', error);
                showToast('Erreur lors du chargement des entrepôts.', 'error');
            });
    }
    
    // Afficher les erreurs de validation
    function displayValidationErrors(errors) {
        // Supprimer les anciens messages d'erreur
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        // Afficher les nouvelles erreurs
        for (const field in errors) {
            const input = document.getElementById(field);
            if (input) {
                input.classList.add('is-invalid');
                
                const errorMessage = document.createElement('div');
                errorMessage.className = 'error-message';
                errorMessage.textContent = errors[field][0];
                errorMessage.style.color = '#dc3545';
                errorMessage.style.fontSize = '0.85em';
                errorMessage.style.marginTop = '5px';
                
                input.parentNode.appendChild(errorMessage);
            }
        }
    }
    
    // Render le tableau des entrepôts avec les nouvelles données
    function renderEntrepotsTable(entrepots) {
        entrepotsTableBody.innerHTML = '';
        
        entrepots.forEach(entrepot => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', entrepot.id);
            
            row.innerHTML = `
                <td>${entrepot.entrepot_unique_id}</td>
                <td>${entrepot.nom_entrepot}</td>
                <td>${entrepot.nom_proprietaire}</td>
                <td>${entrepot.ville}</td>
                <td>${entrepot.quartier}</td>
                <td>${entrepot.telephone}</td>
                <td>${entrepot.email}</td>
                <td style="display: flex;">
                    <button class="action-btn edit-entrepot" title="Modifier l'entrepôt" data-id="${entrepot.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-entrepot" title="Supprimer l'entrepôt" data-id="${entrepot.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            entrepotsTableBody.appendChild(row);
        });
        
        // Rattacher les événements aux nouveaux boutons
        attachEventListeners();
    }

    // ------------------------------------------------------------
    // Fonctions utilitaires
    // ------------------------------------------------------------
    
    // Afficher un message toast
    function showToast(message, type = 'info') {
        let toastContainer = document.querySelector('.toast-container');
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Attacher les événements aux boutons
    function attachEventListeners() {
        // Événements pour les boutons d'édition
        document.querySelectorAll('.edit-entrepot').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                openEditEntrepotModal(id);
            });
        });
        
        // Événements pour les boutons de suppression
        document.querySelectorAll('.delete-entrepot').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                openDeleteEntrepotModal(id);
            });
        });
    }

    // ------------------------------------------------------------
    // Événements
    // ------------------------------------------------------------
    
    // Événement pour la barre de recherche
    searchInput.addEventListener('input', filterEntrepotsTable);
    
    // Événement pour le changement de ville
    villeSelect.addEventListener('change', updateQuartiers);
    
    // Événement pour le bouton d'ajout
    addEntrepotBtn.addEventListener('click', openAddEntrepotModal);
    
    // Événement pour le bouton de sauvegarde
    document.getElementById('save-entrepot').addEventListener('click', saveEntrepot);
    
    // Événement pour le bouton de confirmation de suppression
    document.getElementById('confirm-delete-entrepot').addEventListener('click', function() {
        const id = this.dataset.id;
        deleteEntrepot(id);
    });
    
    // Événements pour fermer les modales
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });
    
    // Fermer les modales en cliquant en dehors
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeAllModals();
            }
        });
    });
    
    // Gestion des touches clavier (Echap pour fermer les modales)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
    
    // ------------------------------------------------------------
    // Initialisation
    // ------------------------------------------------------------
    
    // Attacher les événements aux boutons au chargement
    attachEventListeners();
    
    // Si des messages flash sont présents, les afficher
    @if(session('success'))
    showToast("{{ session('success') }}", 'success');
    @endif
    
    @if(session('error'))
    showToast("{{ session('error') }}", 'error');
    @endif
});

</script>

@endsection
