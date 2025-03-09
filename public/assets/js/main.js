 // Mise à jour de la date
 const dateElement = document.getElementById('date');
 const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
 dateElement.textContent = new Date().toLocaleDateString('fr-FR', options);

 // Configuration des couleurs pour les graphiques
 Chart.defaults.color = '#101010';
 Chart.defaults.font.family = 'Metropolis';

 // Fonction pour rendre les graphiques responsifs
 function createResponsiveCharts() {
     // Nettoyer les anciens graphiques s'ils existent
     Chart.helpers.each(Chart.instances, function(instance) {
         instance.destroy();
     });
     
     // Graphique des batteries
     const batteryCtx = document.getElementById('batteryChart').getContext('2d');
     new Chart(batteryCtx, {
         type: 'doughnut',
         data: {
             labels: ['Pleine charge (>80%)', 'Charge moyenne (30-80%)', 'Faible charge (<30%)', 'En charge'],
             datasets: [{
                 data: [125, 84, 32, 47],
                 backgroundColor: [
                     '#DCDB32',
                     '#101010',
                     '#F3F3F3',
                     'rgba(220, 219, 50, 0.5)'
                 ]
             }]
         },
         options: {
             responsive: true,
             maintainAspectRatio: false,
             plugins: {
                 title: {
                     display: true,
                     text: 'État des batteries de la flotte',
                     padding: window.innerWidth < 768 ? 10 : 20,
                     font: {
                         size: window.innerWidth < 768 ? 14 : 16
                     }
                 },
                 legend: {
                     position: window.innerWidth < 500 ? 'right' : 'bottom',
                     labels: {
                         boxWidth: window.innerWidth < 768 ? 10 : 15,
                         padding: window.innerWidth < 768 ? 8 : 10,
                         font: {
                             size: window.innerWidth < 768 ? 10 : 12
                         }
                     }
                 }
             }
         }
     });

     // Graphique d'utilisation
     const usageCtx = document.getElementById('usageChart').getContext('2d');
     new Chart(usageCtx, {
         type: 'line',
         data: {
             labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
             datasets: [{
                 label: 'Motos actives',
                 data: [142, 156, 168, 155, 162, 148, 139],
                 borderColor: '#DCDB32',
                 backgroundColor: 'rgba(220, 219, 50, 0.1)',
                 fill: true,
                 tension: 0.4
             }]
         },
         options: {
             responsive: true,
             maintainAspectRatio: false,
             plugins: {
                 title: {
                     display: true,
                     text: 'Utilisation hebdomadaire',
                     padding: window.innerWidth < 768 ? 10 : 20,
                     font: {
                         size: window.innerWidth < 768 ? 14 : 16
                     }
                 },
                 legend: {
                     labels: {
                         font: {
                             size: window.innerWidth < 768 ? 10 : 12
                         }
                     }
                 }
             },
             scales: {
                 y: {
                     beginAtZero: true,
                     grid: {
                         color: 'rgba(16, 16, 16, 0.1)'
                     },
                     ticks: {
                         font: {
                             size: window.innerWidth < 768 ? 10 : 12
                         }
                     }
                 },
                 x: {
                     grid: {
                         display: false
                     },
                     ticks: {
                         font: {
                             size: window.innerWidth < 768 ? 10 : 12
                         }
                     }
                 }
             }
         }
     });
 }

 // Toggle sidebar
 function toggleSidebar() {
     const sidebar = document.querySelector('.sidebar');
     const mainContent = document.querySelector('.main-content');
     const toggleBtn = document.querySelector('.toggle-sidebar i');
     
     sidebar.classList.toggle('collapsed');
     mainContent.classList.toggle('expanded');
     
     if (sidebar.classList.contains('collapsed')) {
         toggleBtn.classList.remove('fa-chevron-left');
         toggleBtn.classList.add('fa-chevron-right');
     } else {
         toggleBtn.classList.remove('fa-chevron-right');
         toggleBtn.classList.add('fa-chevron-left');
     }
 }

 // Toggle mobile menu
 function toggleMobileMenu() {
     const sidebar = document.querySelector('.sidebar');
     const overlay = document.querySelector('.overlay');
     
     sidebar.classList.toggle('mobile-visible');
     overlay.classList.toggle('active');
 }

 // Check window width and adjust UI accordingly
 function checkWindowSize() {
     const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
     
     if (window.innerWidth <= 576) {
         mobileMenuToggle.style.display = 'flex';
     } else {
         mobileMenuToggle.style.display = 'none';
         document.querySelector('.sidebar').classList.remove('mobile-visible');
         document.querySelector('.overlay').classList.remove('active');
     }
 }

 // Initialize responsive behavior
 window.addEventListener('load', function() {
     checkWindowSize();
     createResponsiveCharts();
 });
 
 window.addEventListener('resize', function() {
     checkWindowSize();
     createResponsiveCharts();
 });













 

 /*    gestion des motos */
 // JavaScript pour le processus de validation des motos
document.addEventListener('DOMContentLoaded', function() {
    // Fonctions pour les modales
    setupModalHandlers();
    
    // Initialiser le process de validation
    setupValidationProcess();
    
    // Initialiser les onglets de validation
    setupValidationTabs();
    
    // Gestion des formulaires
    setupForms();
    
    // Initialiser les filtres et recherche
    setupFiltersAndSearch();
});

/**
 * Configuration des modales
 */
function setupModalHandlers() {
    // Ouvrir une modale
    window.openModal = function(modalId) {
        const modalOverlay = document.getElementById(modalId);
        if (modalOverlay) {
            modalOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };
    
    // Fermer une modale
    window.closeModal = function(modalId) {
        const modalOverlay = document.getElementById(modalId);
        if (modalOverlay) {
            modalOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    };
    
    // Fermer la modale lorsqu'on clique en dehors
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Empêcher la propagation du clic depuis la modale
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Boutons de fermeture
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.closest('.modal-overlay').id;
            closeModal(modalId);
        });
    });
}

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
 * Configuration des formulaires
 */
function setupForms() {
    // Formulaire d'ajout de moto
    const addMotoForm = document.getElementById('addMotoForm');
    if (addMotoForm) {
        addMotoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Afficher le spinner de chargement
            document.getElementById('loadingOverlay').classList.add('active');
            
            // Simuler l'envoi du formulaire (remplacer par un vrai appel AJAX)
            setTimeout(function() {
                document.getElementById('loadingOverlay').classList.remove('active');
                closeModal('addMotoModal');
                
                // Afficher un message de succès
                showToast('Moto ajoutée avec succès', 'success');
                
                // Réinitialiser le formulaire
                addMotoForm.reset();
            }, 1500);
        });
    }
    
    // Formulaire de validation
    const validationForm = document.getElementById('validationForm');
    if (validationForm) {
        validationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Afficher le spinner de chargement
            document.getElementById('loadingOverlay').classList.add('active');
            
            // Simuler l'envoi du formulaire (remplacer par un vrai appel AJAX)
            setTimeout(function() {
                document.getElementById('loadingOverlay').classList.remove('active');
                
                // Passer à l'étape de confirmation
                document.getElementById('validationStepContent').style.display = 'none';
                document.getElementById('validationSuccessContent').style.display = 'block';
            }, 1500);
        });
    }
    
    // Téléchargement de documents
    setupDocumentUpload();
}

/**
 * Configuration du téléchargement de documents
 */
function setupDocumentUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileLabel = this.nextElementSibling;
            if (fileLabel && this.files.length > 0) {
                fileLabel.textContent = this.files[0].name;
                
                // Afficher la prévisualisation si possible
                const previewContainer = this.closest('.form-group').querySelector('.document-preview');
                if (previewContainer && this.files[0].type.startsWith('image/')) {
                    const previewImg = document.createElement('img');
                    previewImg.classList.add('preview-image');
                    previewImg.style.maxWidth = '100%';
                    previewImg.style.maxHeight = '200px';
                    previewImg.style.objectFit = 'contain';
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                    };
                    reader.readAsDataURL(this.files[0]);
                    
                    // Vider le conteneur et ajouter l'image
                    previewContainer.innerHTML = '';
                    previewContainer.appendChild(previewImg);
                }
            }
        });
    });
}

/**
 * Configuration des filtres et de la recherche
 */
function setupFiltersAndSearch() {
    // Recherche dans le tableau
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            
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
    
    // Filtre par statut
    const statusFilter = document.querySelector('.dropdown-filter select');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const selectedStatus = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            
            if (selectedStatus === '') {
                // Afficher toutes les lignes si "Tous les statuts" est sélectionné
                tableRows.forEach(row => {
                    row.style.display = '';
                });
                return;
            }
            
            tableRows.forEach(row => {
                const statusCell = row.querySelector('td:nth-child(5)');
                if (statusCell) {
                    const statusText = statusCell.textContent.toLowerCase();
                    if (statusText.includes(selectedStatus)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
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
function openValidationModal(motoId) {
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
 * Avancer dans le processus de validation
 */
function nextValidationStep(currentStep, totalSteps) {
    if (currentStep < totalSteps) {
        updateValidationProgress(currentStep + 1);
        
        // Passer au prochain onglet si nécessaire
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
        
        // Revenir à l'onglet précédent si nécessaire
        const prevTab = document.querySelector(`.validation-tab[data-step="${currentStep - 1}"]`);
        if (prevTab) {
            prevTab.click();
        }
    }
}

/**
 * Finaliser la validation de la moto
 */
function completeValidation() {
    // Afficher l'overlay de chargement
    document.getElementById('loadingOverlay').classList.add('active');
    
    // Simuler le traitement (remplacer par un vrai appel AJAX)
    setTimeout(function() {
        // Cacher l'overlay de chargement
        document.getElementById('loadingOverlay').classList.remove('active');
        
        // Afficher le message de succès dans la modale
        document.getElementById('validationStepContent').style.display = 'none';
        document.getElementById('validationSuccessContent').style.display = 'block';
    }, 1500);
}

/**
 * Fermer la modale de validation après succès et rediriger vers les motos validées
 */
function finishValidation() {
    closeModal('validationModal');
    
    // Redirection vers la liste des motos validées (à implémenter selon votre routage)
    // window.location.href = '/motos/valides';
    
    // Pour la démo, nous affichons juste un message
    showToast('Moto validée avec succès et transférée dans les motos validées', 'success');
}

/**
 * Afficher un message toast
 */
function showToast(message, type = 'info') {
    // Créer l'élément toast s'il n'existe pas déjà
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
    
    // Créer le toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.backgroundColor = type === 'success' ? '#DCDB32' : '#3498db';
    toast.style.color = '#101010';
    toast.style.padding = '12px 20px';
    toast.style.borderRadius = '8px';
    toast.style.marginTop = '10px';
    toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.minWidth = '300px';
    toast.style.transform = 'translateY(100px)';
    toast.style.opacity = '0';
    toast.style.transition = 'all 0.3s ease';
    
    // Icône
    const icon = document.createElement('i');
    icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-info-circle';
    icon.style.marginRight = '10px';
    icon.style.fontSize = '18px';
    
    // Texte
    const text = document.createElement('span');
    text.textContent = message;
    
    // Ajouter les éléments au toast
    toast.appendChild(icon);
    toast.appendChild(text);
    
    // Ajouter le toast au conteneur
    toastContainer.appendChild(toast);
    
    // Animation d'entrée
    setTimeout(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
    }, 10);
    
    // Supprimer le toast après un délai
    setTimeout(() => {
        toast.style.transform = 'translateY(-20px)';
        toast.style.opacity = '0';
        
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 5000);
}

/**
 * Fonction pour rejeter une moto
 */
function rejectMoto(motoId) {
    // Afficher une confirmation
    if (confirm('Êtes-vous sûr de vouloir rejeter cette moto ?')) {
        // Afficher l'overlay de chargement
        document.getElementById('loadingOverlay').classList.add('active');
        
        // Simuler le traitement (remplacer par un vrai appel AJAX)
        setTimeout(function() {
            // Cacher l'overlay de chargement
            document.getElementById('loadingOverlay').classList.remove('active');
            
            // Afficher un message de succès
            showToast('La moto a été rejetée', 'info');
            
            // Mettre à jour le statut dans le tableau (pour la démo)
            const row = document.querySelector(`tr[data-moto-id="${motoId}"]`);
            if (row) {
                const statusCell = row.querySelector('td:nth-child(5)');
                if (statusCell) {
                    statusCell.innerHTML = '<div class="table-status rejected">Rejeté</div>';
                }
            }
        }, 1500);
    }
}