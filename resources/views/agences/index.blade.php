@extends('layouts.app')

@section('title', 'Gestion des Agences')

@section('content')

<div class="main-content">
<div class="content-header">
    <h2>Gestion des Agences</h2>
    <div id="date" class="date">{{ date('d/m/Y') }}</div>
</div>

<!-- Barre de recherche et bouton d'ajout -->
<div class="search-bar">
    <div class="search-group">
        <input type="text" id="search-agence" placeholder="Rechercher une agence...">
        <button type="submit" class="search-btn">
            <i class="fas fa-search"></i>
        </button>
    </div>

    <div class="filter-group">
        <button id="add-agence-btn" class="add-btn">
            <i class="fas fa-plus"></i>
            Ajouter une Agence
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
            <div class="stat-number" id="total-agences">{{ $agences->count() }}</div>
            <div class="stat-label">Total des agences</div>
            <div class="stat-text">Tous types confondus</div>
        </div>
    </div>

    <div class="stat-card pending">
        <div class="stat-icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="stat-details">
            <div class="stat-number" id="total-douala">{{ $agences->where('ville', 'Douala')->count() }}</div>
            <div class="stat-label">Agences à Douala</div>
            <div class="stat-text">Stations de swap</div>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="stat-details">
            <div class="stat-number" id="total-yaounde">{{ $agences->where('ville', 'Yaoundé')->count() }}</div>
            <div class="stat-label">Agences à Yaoundé</div>
            <div class="stat-text">Stations de swap</div>
        </div>
    </div>

    <div class="stat-card danger">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-details">
            <div class="stat-number" id="total-users">{{ $agences->count() }}</div>
            <div class="stat-label">Utilisateurs</div>
            <div class="stat-text">Comptes actifs</div>
        </div>
    </div>
</div>

<!-- Tableau des agences -->
<div class="table-container">
    <table id="agences-table">
        <thead>
            <tr>
                <th>Identifiant</th>
                <th>Nom de l'Agence</th>
                <th>Propriétaire</th>
                <th>Ville</th>
                <th>Quartier</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="agences-table-body">
            @foreach($agences as $agence)
            <tr data-id="{{ $agence->id }}">
                <td>{{ $agence->agence_unique_id }}</td>
                <td>{{ $agence->nom_agence }}</td>
                <td>{{ $agence->nom_proprietaire }}</td>
                <td>{{ $agence->ville }}</td>
                <td>{{ $agence->quartier }}</td>
                <td>{{ $agence->telephone }}</td>
                <td>{{ $agence->email }}</td>
                <td style="display: flex;">
                    <button class="action-btn edit-agence" title="Modifier l'agence" data-id="{{ $agence->id }}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-agence" title="Supprimer l'agence" data-id="{{ $agence->id }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modale d'ajout/édition d'agence -->
<div class="modal" id="agence-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Ajouter une Agence</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="agence-form">
                @csrf
                <input type="hidden" id="agence-id" name="id">
                <input type="hidden" id="form-method" name="_method" value="POST">

                <div class="form-group">
                    <label for="nom_agence">Nom de l'Agence</label>
                    <input type="text" name="nom_agence" id="nom_agence" class="form-control" required>
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
            <button id="save-agence" class="btn btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

<!-- Modale de suppression d'agence -->
<div class="modal" id="delete-agence-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Supprimer l'agence</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette agence ?</p>
            <div class="agence-details">
                <p><strong>Nom :</strong> <span id="delete-agence-name"></span></p>
                <p><strong>Identifiant :</strong> <span id="delete-agence-id"></span></p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-delete-agence" class="btn btn-primary">Supprimer</button>
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
    const searchInput = document.getElementById('search-agence');
    const addAgenceBtn = document.getElementById('add-agence-btn');
    const agenceModal = document.getElementById('agence-modal');
    const deleteAgenceModal = document.getElementById('delete-agence-modal');
    const agencesTableBody = document.getElementById('agences-table-body');
    
    // Éléments du formulaire
    const agenceForm = document.getElementById('agence-form');
    const agenceIdInput = document.getElementById('agence-id');
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
    
    // Filtrer le tableau des agences
    function filterAgencesTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = agencesTableBody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            row.style.display = textContent.includes(searchTerm) ? '' : 'none';
        });
    }
    
    // Réinitialiser le formulaire
    function resetForm() {
        agenceForm.reset();
        agenceIdInput.value = '';
        formMethodInput.value = 'POST';
        document.getElementById('modal-title').textContent = 'Ajouter une Agence';
        
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
        fetch('/agences/stats')
            .then(response => response.json())
            .then(data => {
                document.getElementById('total-agences').textContent = data.total_agences;
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
    
    // Ouvrir la modale d'ajout d'agence
    function openAddAgenceModal() {
        resetForm();
        agenceModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Ouvrir la modale d'édition d'agence
    function openEditAgenceModal(id) {
        resetForm();
        formMethodInput.value = 'PUT';
        document.getElementById('modal-title').textContent = 'Modifier l\'Agence';
        
        // Récupérer les détails de l'agence
        fetch(`/agences/${id}/edit`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const agence = data.agence;
                    agenceIdInput.value = agence.id;
                    
                    // Remplir les champs du formulaire
                    document.getElementById('nom_agence').value = agence.nom_agence;
                    document.getElementById('nom_proprietaire').value = agence.nom_proprietaire;
                    document.getElementById('ville').value = agence.ville;
                    
                    // Mettre à jour les quartiers et sélectionner celui de l'agence
                    updateQuartiers();
                    document.getElementById('quartier').value = agence.quartier;
                    
                    document.getElementById('telephone').value = agence.telephone;
                    document.getElementById('email').value = agence.email;
                    
                    // Masquer le champ de mot de passe pour une modification
                    const passwordGroup = document.querySelector('.form-group:has(#password)');
                    passwordGroup.style.display = 'none';
                    document.getElementById('password-hint').style.display = 'none';
                    
                    agenceModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails de l\'agence:', error);
                showToast('Erreur lors du chargement des détails de l\'agence.', 'error');
            });
    }
    
    // Ouvrir la modale de suppression d'agence
    function openDeleteAgenceModal(id) {
        // Récupérer les détails de l'agence
        const row = document.querySelector(`tr[data-id="${id}"]`);
        const agenceName = row.cells[1].textContent;
        const agenceUniqueId = row.cells[0].textContent;
        
        document.getElementById('delete-agence-name').textContent = agenceName;
        document.getElementById('delete-agence-id').textContent = agenceUniqueId;
        
        // Stocker l'ID de l'agence pour la suppression
        document.getElementById('confirm-delete-agence').dataset.id = id;
        
        deleteAgenceModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Fermer toutes les modales
    function closeAllModals() {
        [agenceModal, deleteAgenceModal].forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }

    // ------------------------------------------------------------
    // Fonctions AJAX
    // ------------------------------------------------------------
    
    // Enregistrer ou mettre à jour une agence
    function saveAgence() {
        // Récupérer les données du formulaire
        const formData = new FormData(agenceForm);
        const isEdit = formMethodInput.value === 'PUT';
        const id = agenceIdInput.value;
        
        // Construire l'URL et la méthode
        const url = isEdit ? `/agences/${id}` : '/agences';
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
                
                // Recharger le tableau des agences
                refreshAgencesTable();
                
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
            console.error('Erreur lors de l\'enregistrement de l\'agence:', error);
            showToast('Erreur lors de l\'enregistrement de l\'agence.', 'error');
        });
    }
    
    // Supprimer une agence
    function deleteAgence(id) {
        fetch(`/agences/${id}`, {
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
            console.error('Erreur lors de la suppression de l\'agence:', error);
            showToast('Erreur lors de la suppression de l\'agence.', 'error');
        });
    }
    
    // Rafraîchir le tableau des agences
    function refreshAgencesTable() {
        fetch('/agences/list')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderAgencesTable(data.agences);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des agences:', error);
                showToast('Erreur lors du chargement des agences.', 'error');
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
    
    // Render le tableau des agences avec les nouvelles données
    function renderAgencesTable(agences) {
        agencesTableBody.innerHTML = '';
        
        agences.forEach(agence => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', agence.id);
            
            row.innerHTML = `
                <td>${agence.agence_unique_id}</td>
                <td>${agence.nom_agence}</td>
                <td>${agence.nom_proprietaire}</td>
                <td>${agence.ville}</td>
                <td>${agence.quartier}</td>
                <td>${agence.telephone}</td>
                <td>${agence.email}</td>
                <td style="display: flex;">
                    <button class="action-btn edit-agence" title="Modifier l'agence" data-id="${agence.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-agence" title="Supprimer l'agence" data-id="${agence.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            agencesTableBody.appendChild(row);
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
        document.querySelectorAll('.edit-agence').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                openEditAgenceModal(id);
            });
        });
        
        // Événements pour les boutons de suppression
        document.querySelectorAll('.delete-agence').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                openDeleteAgenceModal(id);
            });
        });
    }

    // ------------------------------------------------------------
    // Événements
    // ------------------------------------------------------------
    
    // Événement pour la barre de recherche
    searchInput.addEventListener('input', filterAgencesTable);
    
    // Événement pour le changement de ville
    villeSelect.addEventListener('change', updateQuartiers);
    
    // Événement pour le bouton d'ajout
    addAgenceBtn.addEventListener('click', openAddAgenceModal);
    
    // Événement pour le bouton de sauvegarde
    document.getElementById('save-agence').addEventListener('click', saveAgence);
    
    // Événement pour le bouton de confirmation de suppression
    document.getElementById('confirm-delete-agence').addEventListener('click', function() {
        const id = this.dataset.id;
        deleteAgence(id);
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



