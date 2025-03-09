document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const motosTableBody = document.getElementById('motos-table-body');
    const searchInput = document.getElementById('search-moto');
    const statusFilter = document.getElementById('status-filter');

    // Modals
    const addMotoModal = document.getElementById('add-moto-modal');
    const editMotoModal = document.getElementById('edit-moto-modal');
    const validateMotoModal = document.getElementById('validate-moto-modal');
    const deleteMotoModal = document.getElementById('delete-moto-modal');

    // Modal Triggers
    document.getElementById('add-moto-btn').addEventListener('click', openAddMotoModal);
    document.getElementById('confirm-add-moto').addEventListener('click', submitAddMoto);
    document.getElementById('confirm-edit-moto').addEventListener('click', submitEditMoto);
    document.getElementById('confirm-validate-moto').addEventListener('click', submitValidateMoto);
    document.getElementById('confirm-delete-moto').addEventListener('click', submitDeleteMoto);

    // Modal Close Buttons
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });

    // Filter and Search
    searchInput.addEventListener('input', filterMotos);
    statusFilter.addEventListener('change', filterMotos);

    // Initial Load
    loadMotos();

    function loadMotos() {
        fetch('/motos?' + new URLSearchParams({
            status: statusFilter.value,
            search: searchInput.value
        }), {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(motos => {
            renderMotos(motos);
            updateStats(motos);
        })
        .catch(error => console.error('Erreur de chargement:', error));
    }

    function renderMotos(motos) {
        motosTableBody.innerHTML = '';
        motos.forEach(moto => {
            const row = `
                <tr data-id="${moto.id}">
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
                        <button class="action-btn edit-moto">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn validate-moto">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="action-btn delete-moto">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            motosTableBody.insertAdjacentHTML('beforeend', row);
        });

        // Attach event listeners to dynamic buttons
        attachActionListeners();
    }

    function attachActionListeners() {
        document.querySelectorAll('.edit-moto').forEach(btn => {
            btn.addEventListener('click', () => openEditMotoModal(btn.closest('tr')));
        });

        document.querySelectorAll('.validate-moto').forEach(btn => {
            btn.addEventListener('click', () => openValidateMotoModal(btn.closest('tr')));
        });

        document.querySelectorAll('.delete-moto').forEach(btn => {
            btn.addEventListener('click', () => openDeleteMotoModal(btn.closest('tr')));
        });
    }

    // Modification de la fonction openAddMotoModal
function openAddMotoModal() {
    const modal = document.getElementById('add-moto-modal');
    modal.classList.add('active');  // Utilise une classe 'active' au lieu de style.display
    document.body.style.overflow = 'hidden'; // Empêche le défilement du fond
}

    function openEditMotoModal(row) {
        const id = row.dataset.id;
        const model = row.querySelector('td:nth-child(3)').textContent;
        const gpsImei = row.querySelector('td:nth-child(4)').textContent;

        document.getElementById('edit-moto-id').value = id;
        document.getElementById('edit-model').value = model;
        document.getElementById('edit-gps_imei').value = gpsImei;

        editMotoModal.style.display = 'block';
    }

    function openValidateMotoModal(row) {
        const id = row.dataset.id;
        const uniqueId = row.querySelector('td:nth-child(1)').textContent;
        const model = row.querySelector('td:nth-child(3)').textContent;

        document.getElementById('validate-moto-id').value = id;
        document.getElementById('validate-unique-id').textContent = uniqueId;
        document.getElementById('validate-model').textContent = model;

        validateMotoModal.style.display = 'block';
    }

    function openDeleteMotoModal(row) {
        const id = row.dataset.id;
        const uniqueId = row.querySelector('td:nth-child(1)').textContent;
        const model = row.querySelector('td:nth-child(3)').textContent;

        document.getElementById('delete-unique-id').textContent = uniqueId;
        document.getElementById('delete-model').textContent = model;
        document.getElementById('confirm-delete-moto').dataset.id = id;

        deleteMotoModal.style.display = 'block';
    }

    function submitEditMoto() {
        const form = document.getElementById('edit-moto-form');
        const id = document.getElementById('edit-moto-id').value;
        const formData = new FormData(form);

        fetch(`/motos/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-HTTP-METHOD-OVERRIDE': 'PUT'
            },
            body: formData
        })
        .then(response => response.json())
        .then(moto => {
            loadMotos();
            closeAllModals();
            form.reset();
        })
        .catch(error => {
            console.error('Erreur de modification:', error);
            alert('Erreur lors de la modification de la moto');
        });
    }

    function submitValidateMoto() {
        const form = document.getElementById('validate-moto-form');
        const id = document.getElementById('validate-moto-id').value;
        const formData = new FormData(form);

        fetch(`/motos/${id}/validate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(motoValidee => {
            loadMotos();
            closeAllModals();
            form.reset();
        })
        .catch(error => {
            console.error('Erreur de validation:', error);
            alert('Erreur lors de la validation de la moto');
        });
    }

    function submitDeleteMoto() {
        const id = document.getElementById('confirm-delete-moto').dataset.id;

        fetch(`/motos/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(result => {
            loadMotos();
            closeAllModals();
        })
        .catch(error => {
            console.error('Erreur de suppression:', error);
            alert('Erreur lors de la suppression de la moto');
        });
    }

    function filterMotos() {
        loadMotos();
    }

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

    // Modification de la fonction closeAllModals
function closeAllModals() {
    [addMotoModal, editMotoModal, validateMotoModal, deleteMotoModal].forEach(modal => {
        modal.classList.remove('active');  // Retire la classe 'active' au lieu de style.display
    });
    document.body.style.overflow = ''; // Rétablit le défilement
}

// Ajout de la fermeture au clic en dehors de la modale
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeAllModals();
        }
    });
});

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
});