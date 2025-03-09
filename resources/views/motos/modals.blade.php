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