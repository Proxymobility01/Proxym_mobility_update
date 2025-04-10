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

/* Employee specific styles */
.status-badge.actif {
    background-color: #E8F5E9;
    color: #2E7D32;
}

.status-badge.inactif {
    background-color: #FFEBEE;
    color: #C62828;
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
</style>

@section('content')
<div class="main-content">
    <!-- Header -->
    <div class="content-header">
        <h2>Gestion des Employés</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Search bar and filters -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-employe" placeholder="Rechercher un employé...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <div class="filter-group">
            <select id="status-filter" class="select-status">
                <option value="">Tous les statuts</option>
                <option value="actif">Actif</option>
                <option value="inactif">Inactif</option>
            </select>
            <button id="add-employe-btn" class="add-btn">
                <i class="fas fa-plus"></i> Ajouter un employé
            </button>
        </div>
    </div>

    <!-- Stats cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Total des employés</div>
            <div class="stat-value" id="total-employes">0</div>
            <div class="stat-change">Tous les employés enregistrés</div>
        </div>

        <div class="stat-card">
            <div class="stat-title">Employés actifs</div>
            <div class="stat-value" id="active-employes">0</div>
            <div class="stat-change">Actuellement en fonction</div>
        </div>

        <div class="stat-card">
            <div class="stat-title">Employés inactifs</div>
            <div class="stat-value" id="inactive-employes">0</div>
            <div class="stat-change">Comptes désactivés</div>
        </div>

        <div class="stat-card">
            <div class="stat-title">Derniers ajouts</div>
            <div class="stat-value" id="recent-employes">0</div>
            <div class="stat-change">Ajoutés ce mois-ci</div>
        </div>
    </div>

    <!-- Employees table -->
    <div class="table-container">
        <div class="head-table">
            <h2>Liste des Employés</h2>
            <a href="#" id="export-employes">
                <i class="fas fa-download"></i>
                Exporter la liste
            </a>
        </div>
        <table id="employes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom complet</th>
                    <th>Contact</th>
                    <th>Statut</th>
                    <th>Date d'ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="employes-table-body">
                <!-- Employees will be loaded dynamically here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal" id="add-employe-modal">
    <div class="modal-content" style="width: 700px;">
        <div class="modal-header">
            <h2>Ajouter un Employé</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="add-employe-form">
                @csrf
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
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirmer le mot de passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-add-employe" class="btn btn-primary">Ajouter</button>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal" id="edit-employe-modal">
    <div class="modal-content" style="width: 700px;">
        <div class="modal-header">
            <h2>Modifier l'Employé</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="edit-employe-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-employe-id" name="id">
                
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
                        <label for="edit-password">Nouveau mot de passe (laisser vide si inchangé)</label>
                        <input type="password" id="edit-password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="edit-password_confirmation">Confirmer le mot de passe</label>
                        <input type="password" id="edit-password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-edit-employe" class="btn btn-primary">Modifier</button>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div class="modal" id="view-employe-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Détails de l'Employé</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="profile-info">
                <div>
                    <div id="view-nom-prenom" class="profile-name">John Doe</div>
                    <div id="view-email" class="profile-details">johndoe@example.com</div>
                    <div id="view-phone" class="profile-details">+123 456 789</div>
                </div>
            </div>
            
            <table class="verification-data-table" style="margin-top: 20px; width: 100%;">
                <tr>
                    <th>ID Unique</th>
                    <td id="view-id">EMP001</td>
                </tr>
                <tr>
                    <th>Date d'inscription</th>
                    <td id="view-created-at">01/01/2023</td>
                </tr>
                <tr>
                    <th>Statut actuel</th>
                    <td id="view-status">
                        <span class="status-badge actif">Actif</span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Fermer</button>
        </div>
    </div>
</div>

<!-- Delete Employee Modal -->
<div class="modal" id="delete-employe-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Supprimer l'Employé</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cet employé ?</p>
            <div class="employe-details">
                <p><strong>ID :</strong> <span id="delete-id"></span></p>
                <p><strong>Nom :</strong> <span id="delete-nom-prenom"></span></p>
            </div>
            <p class="text-danger">Cette action est irréversible.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-delete-employe" class="btn btn-primary">Supprimer</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM References
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const employesTableBody = document.getElementById('employes-table-body');
    const searchInput = document.getElementById('search-employe');
    const statusFilter = document.getElementById('status-filter');
    
    // Modals
    const addEmployeModal = document.getElementById('add-employe-modal');
    const editEmployeModal = document.getElementById('edit-employe-modal');
    const viewEmployeModal = document.getElementById('view-employe-modal');
    const deleteEmployeModal = document.getElementById('delete-employe-modal');
    
    // Buttons
    const addEmployeBtn = document.getElementById('add-employe-btn');
    const confirmAddEmployeBtn = document.getElementById('confirm-add-employe');
    const confirmEditEmployeBtn = document.getElementById('confirm-edit-employe');
    const confirmDeleteEmployeBtn = document.getElementById('confirm-delete-employe');
    
    // Display current date
    document.getElementById('date').textContent = new Date().toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    
    // Load employees from server
    function loadEmployes() {
        let url = '/employe/list';
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
        .then(employes => {
            renderEmployes(employes);
            updateStats(employes);
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement des employés', 'error');
        });
    }
    
    // Render employees in the table
    function renderEmployes(employes) {
        employesTableBody.innerHTML = '';
        if (employes.length === 0) {
            employesTableBody.innerHTML = 
                `<tr><td colspan="6" class="text-center">Aucun employé trouvé</td></tr>`;
            return;
        }
        
        employes.forEach(employe => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', employe.id);
            
            const createdAt = new Date(employe.created_at).toLocaleDateString('fr-FR');
            const statusClass = employe.deleted_at ? 'inactif' : 'actif';
            const statusText = employe.deleted_at ? 'Inactif' : 'Actif';
            
            row.innerHTML = `
                <td>${employe.id}</td>
                <td>${employe.nom} ${employe.prenom}</td>
                <td>
                    <div>${employe.email}</div>
                    <div>${employe.phone}</div>
                </td>
                <td>
                    <span class="status-badge ${statusClass}">
                        ${statusText}
                    </span>
                </td>
                <td>${createdAt}</td>
                <td>
                    <div class="action-group">
                        <button class="action-btn view view-employe" title="Voir">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn edit edit-employe" title="Modifier" ${employe.deleted_at ? 'disabled' : ''}>
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete delete-employe" title="Supprimer" ${employe.deleted_at ? 'disabled' : ''}>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            employesTableBody.appendChild(row);
        });
        
        attachActionListeners();
    }
    
    // Update statistics
    function updateStats(employes) {
        const total = employes.length;
        const active = employes.filter(e => !e.deleted_at).length;
        const inactive = employes.filter(e => e.deleted_at).length;
        
        // Calculate recent additions (this month)
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const recent = employes.filter(e => new Date(e.created_at) >= firstDayOfMonth).length;
        
        document.getElementById('total-employes').textContent = total;
        document.getElementById('active-employes').textContent = active;
        document.getElementById('inactive-employes').textContent = inactive;
        document.getElementById('recent-employes').textContent = recent;
    }
    
    // Attach event listeners to action buttons
    function attachActionListeners() {
        document.querySelectorAll('.view-employe').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.closest('tr').getAttribute('data-id');
                openViewModal(id);
            });
        });
        
        document.querySelectorAll('.edit-employe').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.closest('tr').getAttribute('data-id');
                openEditModal(id);
            });
        });
        
        document.querySelectorAll('.delete-employe').forEach(btn => {
            btn.addEventListener('click', () => {
                const row = btn.closest('tr');
                const id = row.getAttribute('data-id');
                const cells = row.querySelectorAll('td');
                openDeleteModal(id, cells[0].textContent, cells[1].textContent);
            });
        });
    }
    
    // Open add modal
    function openAddModal() {
        // Reset form
        document.getElementById('add-employe-form').reset();
        
        // Show modal
        addEmployeModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Open edit modal
    function openEditModal(id) {
        fetch(`/employe/${id}/edit`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(employe => {
            // Fill form with employee data
            document.getElementById('edit-employe-id').value = employe.id;
            document.getElementById('edit-nom').value = employe.nom;
            document.getElementById('edit-prenom').value = employe.prenom;
            document.getElementById('edit-email').value = employe.email;
            document.getElementById('edit-phone').value = employe.phone;
            
            // Reset password field (optional during edit)
            document.getElementById('edit-password').value = '';
            document.getElementById('edit-password_confirmation').value = '';
            
            // Show modal
            editEmployeModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la récupération des données de l\'employé', 'error');
        });
    }
    
    // Open view modal
    function openViewModal(id) {
        fetch(`/employe/${id}`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(employe => {
            // Fill employee details in modal
            document.getElementById('view-nom-prenom').textContent = `${employe.nom} ${employe.prenom}`;
            document.getElementById('view-email').textContent = employe.email;
            document.getElementById('view-phone').textContent = employe.phone;
            document.getElementById('view-id').textContent = employe.id;
            document.getElementById('view-created-at').textContent = new Date(employe.created_at).toLocaleDateString('fr-FR');
            
            // Status with badge
            const statusClass = employe.deleted_at ? 'inactif' : 'actif';
            const statusText = employe.deleted_at ? 'Inactif' : 'Actif';
            const statusBadge = `<span class="status-badge ${statusClass}">${statusText}</span>`;
            document.getElementById('view-status').innerHTML = statusBadge;
            
            // Show modal
            viewEmployeModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la récupération des données de l\'employé', 'error');
        });
    }
    
    // Open delete modal
    function openDeleteModal(id, uniqueId, nomPrenom) {
        document.getElementById('delete-id').textContent = uniqueId;
        document.getElementById('delete-nom-prenom').textContent = nomPrenom;
        confirmDeleteEmployeBtn.setAttribute('data-id', id);
        deleteEmployeModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Close all modals
    function closeAllModals() {
        const modals = [
            addEmployeModal, 
            editEmployeModal, 
            viewEmployeModal, 
            deleteEmployeModal
        ];
        
        modals.forEach(modal => {
            if (modal) modal.classList.remove('active');
        });
        
        document.body.style.overflow = '';
    }
    
    // Submit add employee form
    function submitAddEmploye() {
        const form = document.getElementById('add-employe-form');
        const formData = new FormData(form);
        
        fetch('/employe/store', {
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
                    throw new Error(data.message || 'Erreur lors de l\'ajout de l\'employé');
                });
            }
            return response.json();
        })
        .then(employe => {
            loadEmployes();
            closeAllModals();
            showToast('Employé ajouté avec succès', 'success');
            form.reset();
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast(error.message || 'Erreur lors de l\'ajout de l\'employé', 'error');
        });
    }
    
    // Submit edit employee form
    function submitEditEmploye() {
        const id = document.getElementById('edit-employe-id').value;
        const form = document.getElementById('edit-employe-form');
        const formData = new FormData(form);
        
        fetch(`/employe/update/${id}`, {
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
                    throw new Error(data.message || 'Erreur lors de la modification de l\'employé');
                });
            }
            return response.json();
        })
        .then(employe => {
            loadEmployes();
            closeAllModals();
            showToast('Employé modifié avec succès', 'success');
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast(error.message || 'Erreur lors de la modification de l\'employé', 'error');
        });
    }
    
    // Submit delete employee
    function submitDeleteEmploye() {
        const id = confirmDeleteEmployeBtn.getAttribute('data-id');
        
        fetch(`/employe/destroy/${id}`, {
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
            loadEmployes();
            closeAllModals();
            showToast('Employé supprimé avec succès', 'success');
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la suppression de l\'employé', 'error');
        });
    }
    
    // Show toast notification
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
    
    // Setup filters and search
    function setupFilters() {
        searchInput.addEventListener('input', loadEmployes);
        statusFilter.addEventListener('change', loadEmployes);
    }
    
    // Attach close events to all modal close buttons
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });
    
    // Close modal when clicking on the overlay (background)
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeAllModals();
        });
    });
    
    // Action buttons in modals
    addEmployeBtn.addEventListener('click', openAddModal);
    confirmAddEmployeBtn.addEventListener('click', submitAddEmploye);
    confirmEditEmployeBtn.addEventListener('click', submitEditEmploye);
    confirmDeleteEmployeBtn.addEventListener('click', submitDeleteEmploye);
    
    // Export employees list
    document.getElementById('export-employes').addEventListener('click', function(e) {
        e.preventDefault();
        
        fetch('/employe/export', {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'liste_employes.xlsx';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de l\'exportation des employés', 'error');
        });
    });
    
    // Initialize
    setupFilters();
    loadEmployes();
});
</script>

@endsection