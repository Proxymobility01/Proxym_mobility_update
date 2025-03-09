<!-- resources/views/motos/motos-attente.blade.php -->
@extends('layouts.app')

@section('content')
<div class="main-content">
    <div class="header">
        <h1 class="title">{{ $pageTitle ?? 'Motos en attente de validation' }}</h1>
        <div class="tabs">
            <a href="{{ route('motos.index') }}" class="action-button secondary">
                <i class="fas fa-list-ul"></i>
                Toutes les motos
            </a>
            <a href="{{ route('motos.attente') }}" class="action-button active">
                <i class="fas fa-clock"></i>
                En attente
            </a>
            <a href="{{ route('motos.valide') }}" class="action-button secondary">
                <i class="fas fa-check-circle"></i>
                Validées
            </a>
        </div>
    </div>

    <div class="action-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Rechercher une moto...">
        </div>

        <div class="filter-options">
            <button class="action-button" onclick="openAddMotoModal()">
                <i class="fas fa-plus"></i>
                Ajouter une moto
            </button>
        </div>
    </div>

    <div class="card-container">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-title">Motos en attente</div>
                <div class="stat-icon" style="background-color: rgba(243, 156, 18, 0.2); color: var(--warning);">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-value">{{ count($motos) }}</div>
            <div class="stat-description">Motos à valider</div>
            <div class="status-badge pending">
                <i class="fas fa-exclamation-circle"></i>
                À traiter
            </div>
        </div>
    </div>

    <div class="data-table">
        <table id="motosTable">
            <thead>
                <tr>
                    <th>ID Unique</th>
                    <th>VIN</th>
                    <th>Modèle</th>
                    <th>GPS IMEI</th>
                    <th>Date d'ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($motos as $moto)
                @if($moto->statut == 'en attente')
                <tr data-moto-id="{{ $moto->id }}">
                    <td>{{ $moto->moto_unique_id }}</td>
                    <td>{{ $moto->vin }}</td>
                    <td>{{ $moto->model }}</td>
                    <td>{{ $moto->gps_imei }}</td>
                    <td>{{ \Carbon\Carbon::parse($moto->created_at)->format('d/m/Y') }}</td>
                    <td>
                        <div class="table-actions">
                            <a href="{{ route('motos.edit', $moto->id) }}" class="table-action-btn edit" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="table-action-btn validate" title="Valider" 
                                    onclick="openValidationModal('{{ $moto->id }}', '{{ $moto->moto_unique_id }}', '{{ $moto->vin }}', '{{ $moto->model }}', '{{ $moto->gps_imei }}', '{{ \Carbon\Carbon::parse($moto->created_at)->format('d/m/Y') }}')">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="table-action-btn reject" title="Rejeter" 
                                    onclick="openRejectModal('{{ $moto->id }}')">
                                <i class="fas fa-times"></i>
                            </button>
                            <form action="{{ route('motos.destroy', $moto->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="table-action-btn delete" title="Supprimer" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette moto ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="6" class="text-center">Aucune moto en attente trouvée</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal pour ajouter une moto -->
<div id="addMotoModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Ajouter une nouvelle moto</h3>
            <button class="modal-close" onclick="closeModal('addMotoModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addMotoForm" action="{{ route('motos.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">VIN (Numéro d'identification) *</label>
                    <input type="text" class="form-control" name="vin" required placeholder="Ex: VF1KMS40A12345678">
                    <small class="form-text">Le VIN doit être unique et comporter 17 caractères.</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Modèle *</label>
                    <input type="text" class="form-control" name="model" required placeholder="Ex: MX-2000">
                </div>
                
                <div class="form-group">
                    <label class="form-label">IMEI du GPS *</label>
                    <input type="text" class="form-control" name="gps_imei" required placeholder="Ex: 861234567890123">
                    <small class="form-text">L'IMEI est généralement composé de 15 chiffres.</small>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="action-button secondary" onclick="closeModal('addMotoModal')">Annuler</button>
                    <button type="submit" class="action-button">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour confirmer le rejet -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Rejeter la moto</h3>
            <button class="modal-close" onclick="closeModal('rejectModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="rejectForm" action="" method="POST">
                @csrf
                @method('PUT')
                <p>Vous êtes sur le point de rejeter cette moto. Cette action est irréversible.</p>
                
                <div class="form-group">
                    <label class="form-label">Raison du rejet *</label>
                    <textarea class="form-control" name="raison_rejet" id="rejectReason" rows="4" 
                             placeholder="Veuillez préciser la raison du rejet..." required></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="action-button secondary" onclick="closeModal('rejectModal')">Annuler</button>
                    <button type="submit" class="action-button" style="background-color: #e74c3c;">Confirmer le rejet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour la validation des motos -->
<div id="validationModal" class="modal-overlay">
    <div class="modal modal-validation">
        <div class="modal-header">
            <h3 class="modal-title">Validation de la moto <span id="motoIdToValidate"></span></h3>
            <button class="modal-close" onclick="closeModal('validationModal')">&times;</button>
        </div>
        
        <!-- Processus de validation visuel -->
        <div class="validation-process">
            <div class="validation-progress-bar">
                <div class="validation-progress-fill"></div>
            </div>
            
            <div class="validation-step" data-step="1">
                <div class="validation-step-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="validation-step-title">Vérification</div>
                <div class="validation-step-description">Vérification des informations de la moto</div>
            </div>
            
            <div class="validation-step" data-step="2">
                <div class="validation-step-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="validation-step-title">Documents</div>
                <div class="validation-step-description">Ajout des documents requis</div>
            </div>
            
            <div class="validation-step" data-step="3">
                <div class="validation-step-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="validation-step-title">Confirmation</div>
                <div class="validation-step-description">Validation finale</div>
            </div>
        </div>
        
        <!-- Contenu de la validation -->
        <div id="validationStepContent">
            <div class="validation-tabs">
                <div class="validation-tab active" data-target="stepVerification" data-step="1">Vérification</div>
                <div class="validation-tab" data-target="stepDocuments" data-step="2">Documents</div>
                <div class="validation-tab" data-target="stepConfirmation" data-step="3">Confirmation</div>
            </div>
            
            <form id="validationForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="moto_id" id="validationMotoId">
                
                <div class="tab-pane active" id="stepVerification">
                    <div class="validation-tab-content">
                        <h4>Informations de la moto</h4>
                        <table class="verification-data-table">
                            <tr>
                                <th>ID Unique</th>
                                <td id="motoUniqueId"></td>
                            </tr>
                            <tr>
                                <th>VIN</th>
                                <td id="motoVin"></td>
                            </tr>
                            <tr>
                                <th>Modèle</th>
                                <td id="motoModel"></td>
                            </tr>
                            <tr>
                                <th>GPS IMEI</th>
                                <td id="motoGpsImei"></td>
                            </tr>
                            <tr>
                                <th>Statut actuel</th>
                                <td><div class="table-status pending">En attente</div></td>
                            </tr>
                            <tr>
                                <th>Date d'ajout</th>
                                <td id="motoCreatedAt"></td>
                            </tr>
                        </table>
                        
                        <div class="form-group">
                            <label class="form-label">Observations (facultatif)</label>
                            <textarea class="form-control" name="observations" rows="3" placeholder="Ajoutez vos observations ici..."></textarea>
                        </div>
                        
                        <div class="validation-actions">
                            <button type="button" class="action-button secondary" onclick="closeModal('validationModal')">Annuler</button>
                            <button type="button" class="action-button" onclick="nextValidationStep(1, 3)">Continuer</button>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane" id="stepDocuments">
                    <div class="validation-tab-content">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Document d'assurance *</label>
                                <div class="document-preview">
                                    <div class="document-preview-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <div class="document-preview-text">
                                        Téléchargez le document d'assurance (PDF, JPG)
                                    </div>
                                    <div class="document-preview-button">
                                        <input type="file" id="assuranceFile" class="form-control-file" hidden accept=".pdf,.jpg,.jpeg,.png">
                                        <label for="assuranceFile" class="action-button secondary">
                                            <i class="fas fa-upload"></i> Sélectionner
                                        </label>
                                    </div>
                                </div>
                                <input type="text" class="form-control" name="assurance" placeholder="Numéro d'assurance" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Document de permis *</label>
                                <div class="document-preview">
                                    <div class="document-preview-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <div class="document-preview-text">
                                        Téléchargez le document de permis (PDF, JPG)
                                    </div>
                                    <div class="document-preview-button">
                                        <input type="file" id="permisFile" class="form-control-file" hidden accept=".pdf,.jpg,.jpeg,.png">
                                        <label for="permisFile" class="action-button secondary">
                                            <i class="fas fa-upload"></i> Sélectionner
                                        </label>
                                    </div>
                                </div>
                                <input type="text" class="form-control" name="permis" placeholder="Numéro de permis" required>
                            </div>
                        </div>
                        
                        <div class="validation-actions">
                            <button type="button" class="action-button secondary" onclick="prevValidationStep(2)">Retour</button>
                            <button type="button" class="action-button" onclick="nextValidationStep(2, 3)">Continuer</button>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane" id="stepConfirmation">
                    <div class="validation-tab-content">
                        <div class="confirmation-message">
                            <h4>Confirmation de validation</h4>
                            <p>Vous êtes sur le point de valider cette moto. Une fois validée, elle sera transférée dans la liste des motos validées et sera disponible pour assignation.</p>
                            
                            <div class="confirmation-summary">
                                <h5>Récapitulatif</h5>
                                <ul>
                                    <li>ID Unique: <strong id="confirmMotoId"></strong></li>
                                    <li>VIN: <strong id="confirmVin"></strong></li>
                                    <li>Modèle: <strong id="confirmModel"></strong></li>
                                    <li>Numéro d'assurance: <strong id="confirmAssurance"></strong></li>
                                    <li>Numéro de permis: <strong id="confirmPermis"></strong></li>
                                </ul>
                            </div>
                            
                            <div class="validation-actions">
                                <button type="button" class="action-button secondary" onclick="prevValidationStep(3)">Retour</button>
                                <button type="submit" class="action-button">Valider la moto</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Message de succès après validation -->
        <div id="validationSuccessContent" style="display: none;">
            <div class="success-message">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="success-title">Validation réussie !</h3>
                <p class="success-description">
                    La moto a été validée avec succès et transférée dans la liste des motos validées.
                    Elle est maintenant prête à être utilisée.
                </p>
                <button class="action-button" onclick="finishValidation()">
                    Terminer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Overlay de chargement -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-spinner"></div>
</div>
@endsection

@section('scripts')
<script>
// JavaScript pour le processus de validation des motos
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le process de validation
    setupValidationProcess();
    
    // Initialiser les onglets de validation
    setupValidationTabs();
    
    // Initialiser les filtres et recherche
    setupFiltersAndSearch();
});

/**
 * Configuration du processus de validation visuel
 */
function setupValidationProcess() {
    window.updateValidationProgress = function(step) {
        const progressFill = document.querySelector('.validation-progress-fill');
        const validationSteps = document.querySelectorAll('.validation-step');
        
        // Calculer le pourcentage de progression
        const totalSteps = validationSteps.length - 1; // -1 car on compte les espaces entre les étapes
        const percentage = ((step - 1) / totalSteps) * 100;
        
        // Mettre à jour la barre de progression
        if (progressFill) {
            progressFill.style.width = `${percentage}%`;
        }
        
        // Mettre à jour les étapes
        validationSteps.forEach((stepElement, index) => {
            if (index + 1 < step) {
                stepElement.classList.add('completed');
                stepElement.classList.remove('active');
            } else if (index + 1 === step) {
                stepElement.classList.add('active');
                stepElement.classList.remove('completed');
            } else {
                stepElement.classList.remove('active', 'completed');
            }
        });
    };
    
    // Initialisation de la progression
    if (document.querySelector('.validation-process')) {
        updateValidationProgress(1);
    }
}

/**
 * Configuration des onglets dans la modale de validation
 */
function setupValidationTabs() {
    const tabs = document.querySelectorAll('.validation-tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            
            // Désactiver tous les onglets et contenu
            document.querySelectorAll('.validation-tab').forEach(t => {
                t.classList.remove('active');
            });
            
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            
            // Activer l'onglet cliqué et son contenu
            this.classList.add('active');
            document.getElementById(targetId).classList.add('active');
        });
    });
}

/**
 * Configuration des filtres et de la recherche
 */
function setupFiltersAndSearch() {
    // Recherche dans le tableau
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#motosTable tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}

/**
 * Ouvrir/fermer les modales
 */
function openModal(modalId) {
    const modalOverlay = document.getElementById(modalId);
    if (modalOverlay) {
        modalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modalOverlay = document.getElementById(modalId);
    if (modalOverlay) {
        modalOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Ouvrir la modale d'ajout de moto
 */
function openAddMotoModal() {
    openModal('addMotoModal');
}

/**
 * Ouvrir la modale de validation
 */
function openValidationModal(motoId, motoUniqueId, vin, model, gpsImei, createdAt) {
    // Remplir les informations de la moto
    document.getElementById('motoIdToValidate').textContent = motoUniqueId;
    document.getElementById('validationMotoId').value = motoId;
    document.getElementById('motoUniqueId').textContent = motoUniqueId;
    document.getElementById('motoVin').textContent = vin;
    document.getElementById('motoModel').textContent = model;
    document.getElementById('motoGpsImei').textContent = gpsImei;
    document.getElementById('motoCreatedAt').textContent = createdAt;
    
    // Configurer le formulaire
    const validationForm = document.getElementById('validationForm');
    validationForm.action = "{{ url('/motos/valider') }}/" + motoId;
    
    // Réinitialiser l'état de la modale
    document.getElementById('validationStepContent').style.display = 'block';
    document.getElementById('validationSuccessContent').style.display = 'none';
    
    // Afficher la modale
    openModal('validationModal');
    
    // Initialiser le processus à l'étape 1
    updateValidationProgress(1);
    
    // Activer le premier onglet
    document.querySelector('.validation-tab').click();
}

/**
 * Ouvrir la modale de rejet
 */
function openRejectModal(motoId) {
    const rejectForm = document.getElementById('rejectForm');
    rejectForm.action = "{{ url('/motos/rejeter') }}/" + motoId;
    openModal('rejectModal');
}

/**
 * Avancer dans le processus de validation
 */
function nextValidationStep(currentStep, totalSteps) {
    // Si nous sommes à l'étape des documents, mettre à jour le récapitulatif
    if (currentStep === 2) {
        document.getElementById('confirmMotoId').textContent = document.getElementById('motoUniqueId').textContent;
        document.getElementById('confirmVin').textContent = document.getElementById('motoVin').textContent;
        document.getElementById('confirmModel').textContent = document.getElementById('motoModel').textContent;
        document.getElementById('confirmAssurance').textContent = document.querySelector('input[name="assurance"]').value;
        document.getElementById('confirmPermis').textContent = document.querySelector('input[name="permis"]').value;
    }
    
    if (currentStep < totalSteps) {
        updateValidationProgress(currentStep + 1);
        
        // Passer au prochain onglet
        const nextTab = document.querySelector(`.validation-tab[data-step="${currentStep + 1}"]`);
        if (nextTab) {
            nextTab.click();
        }
    }
}

/**
 * Revenir à l'étape précédente du processus de validation
 */
function prevValidationStep(currentStep) {
    if (currentStep > 1) {
        updateValidationProgress(currentStep - 1);
        
        // Revenir à l'onglet précédent
        const prevTab = document.querySelector(`.validation-tab[data-step="${currentStep - 1}"]`);
        if (prevTab) {
            prevTab.click();
        }
    }
}

/**
 * Fermer la modale de validation après succès et rediriger vers les motos validées
 */
function finishValidation() {
    closeModal('validationModal');
    window.location.href = '{{ route("motos.valide") }}';
}

// Gérer la soumission du formulaire de validation
document.getElementById('validationForm').addEventListener('submit', function(e) {
    // Permettre au formulaire de se soumettre normalement mais afficher l'overlay
    document.getElementById('loadingOverlay').classList.add('active');
});
</script>
@endsection