<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Motos\MotoController;
use App\Http\Controllers\Batteries\BatterieController;
use App\Http\Controllers\Bms\BMSController;
use App\Http\Controllers\Associations\AssociationUserMotoController;
use App\Http\Controllers\Associations\BatteryMotoUserAssociationController;
use App\Http\Controllers\Chauffeurs\ChauffeurController;
use App\Http\Controllers\Agences\AgenceController;
use App\Http\Controllers\Entrepots\EntrepotController;
use App\Http\Controllers\Distributeurs\DistributeurController;
use App\Http\Controllers\Employes\EmployeController;
use App\Http\Controllers\Associations\RavitaillementController;
use App\Http\Controllers\BmsSetupController;



Route::get('/', function () {
    return view('welcome');
});




Route::get('/', [DashboardController::class, 'index'])->name('dashboard');



Route::prefix('motos')->name('motos.')->group(function () {
    Route::get('/', [MotoController::class, 'index'])->name('index');
    Route::post('/', [MotoController::class, 'store'])->name('store');
    Route::put('/{id}', [MotoController::class, 'update'])->name('update');
    Route::post('/{id}/validate', [MotoController::class, 'validate'])->name('validate');
    Route::post('/{id}/reject', [MotoController::class, 'reject'])->name('reject');
    Route::delete('/{id}', [MotoController::class, 'destroy'])->name('destroy');
});




// Routes pour les batteries
Route::prefix('batteries')->name('batteries.')->group(function () {
    Route::get('/', [BatterieController::class, 'index'])->name('index');
    Route::post('/', [BatterieController::class, 'store'])->name('store');
    Route::put('/{id}', [BatterieController::class, 'update'])->name('update');
    Route::post('/{id}/validate', [BatterieController::class, 'validate'])->name('validate');
    Route::post('/{id}/reject', [BatterieController::class, 'reject'])->name('reject');
    Route::delete('/{id}', [BatterieController::class, 'destroy'])->name('destroy');
    Route::get('/valides', [BatterieController::class, 'indexValide'])->name('valide');
});


// Route principale pour afficher la vue BMS
Route::get('bms-details', [BMSController::class, 'index'])->name('bms.index');

// Routes API pour les données BMS
Route::prefix('api/bms')->group(function () {
    // Récupérer la liste de toutes les batteries
    Route::get('batteries', [BMSController::class, 'getBatteries'])->name('api.bms.batteries');

    // Récupérer les détails d'une batterie spécifique
    Route::get('battery/{mac_id}', [BMSController::class, 'getBatteryDetails'])->name('api.bms.battery.details');

    // Rafraîchir les données d'une batterie (pour les mises à jour en temps réel)
    Route::get('battery/{mac_id}/refresh', [BMSController::class, 'refreshBatteryData'])->name('api.bms.battery.refresh');
});











// Routes pour les associations moto-utilisateur
Route::prefix('associations')->name('associations.')->group(function () {
    // Page principale des associations
    Route::get('/', [AssociationUserMotoController::class, 'showAssociationForm'])->name('index');

    // API et actions spécifiques (doivent être avant les routes avec paramètres génériques)
    Route::get('/all', [AssociationUserMotoController::class, 'getAllAssociations'])->name('all');
    Route::get('/filter', [AssociationUserMotoController::class, 'filterAssociations'])->name('filter');
    Route::get('/stats', [AssociationUserMotoController::class, 'getStats'])->name('stats');

    // Routes avec vérifications spécifiques
    Route::get('/check-moto/{motoUniqueId}', [AssociationUserMotoController::class, 'checkMotoAssociation'])->name('checkMoto');
    Route::get('/check-user-moto/{userUniqueId}/{motoUniqueId}', [AssociationUserMotoController::class, 'checkUserMotoAssociation'])->name('checkUserMoto');

    // Routes pour les associations d'utilisateurs et de motos spécifiques
    Route::get('/user/{userUniqueId}', [AssociationUserMotoController::class, 'getUserAssociations'])->name('userAssociations');
    Route::get('/moto/{motoUniqueId}', [AssociationUserMotoController::class, 'getMotoAssociations'])->name('motoAssociations');

    // Routes pour créer et confirmer les associations
    Route::post('/associer', [AssociationUserMotoController::class, 'associerMotoAUtilisateurs'])->name('associerMotoAUtilisateurs');
    Route::post('/confirm', [AssociationUserMotoController::class, 'confirmAssociation'])->name('confirm');

    // Routes pour les actions spécifiques sur une association individuelle (détails, modification, suppression)
    Route::get('/{id}/details', [AssociationUserMotoController::class, 'getAssociationDetails'])->name('details');
    Route::put('/{id}', [AssociationUserMotoController::class, 'updateAssociation'])->name('updateAssociation');
    Route::delete('/{id}', [AssociationUserMotoController::class, 'deleteAssociation'])->name('deleteAssociation');
});


/*
|--------------------------------------------------------------------------
| Routes pour les associations batterie-moto-utilisateur
|--------------------------------------------------------------------------
|
| Routes web et API pour gérer les associations entre batteries, motos et utilisateurs
|
*/

// Routes web
// Ajoutez ces routes dans votre fichier de routes (routes/web.php)

// Dans routes/web.php
Route::group(['prefix' => 'associations'], function () {
    // Mettre les routes spécifiques AVANT les routes paramétrées
    Route::get('/batteries/motos/available', 'App\Http\Controllers\Associations\BatteryMotoUserAssociationController@getAvailableMotos');
    Route::get('/batteries/available', 'App\Http\Controllers\Associations\BatteryMotoUserAssociationController@getAvailableBatteries');
    Route::get('/batteries/bms/{macId}', 'App\Http\Controllers\Associations\BatteryMotoUserAssociationController@getBmsData');
    Route::post('/batteries/bms/bulk', 'App\Http\Controllers\Associations\BatteryMotoUserAssociationController@getBulkBmsData');
    Route::get('/batteries/stats', 'App\Http\Controllers\Associations\BatteryMotoUserAssociationController@getStats');

    // Routes génériques avec ID APRÈS les routes spécifiques
    Route::get('/batteries', 'App\Http\Controllers\Associations\BatteryMotoUserAssociationController@index')->name('associations.batteries.index');
    Route::get('/batteries/{id}', 'App\Http\Controllers\Associations\BatteryMotoUserAssociationController@getAssociationDetails');
    Route::post('/batteries', 'App\Http\Controllers\Associations\BatteryMotoUserAssociationController@store');
    Route::put('/batteries/{id}', 'App\Http\Controllers\Associations\BatteryMotoUserAssociationController@update');
    Route::delete('/batteries/{id}', 'App\Http\Controllers\Associations\BatteryMotoUserAssociationController@destroy');
});
// Route pour afficher la page avec la carte
Route::get('/batteries/map', [App\Http\Controllers\Batteries\BatterieController::class, 'showBatteriesOnMap'])
    ->name('batteries.map');

// Routes API pour les données de la carte (mais dans web.php)
Route::get('/batteries/api/map-data', [App\Http\Controllers\Batteries\BatterieController::class, 'getBatteriesMapData']);
Route::get('/batteries/api/map-updates', [App\Http\Controllers\Batteries\BatterieController::class, 'getBatteriesUpdates']);


// Routes pour la gestion des chauffeurs
    // Routes d'affichage et de manipulation des chauffeurs
    Route::get('/chauffeurs', [ChauffeurController::class, 'index'])->name('chauffeurs.index');
    Route::post('/chauffeurs', [ChauffeurController::class, 'store'])->name('chauffeurs.store');
    Route::get('/chauffeurs/{id}/edit', [ChauffeurController::class, 'edit'])->name('chauffeurs.edit');
    Route::put('/chauffeurs/{id}', [ChauffeurController::class, 'update'])->name('chauffeurs.update');
    Route::delete('/chauffeurs/{id}', [ChauffeurController::class, 'destroy'])->name('chauffeurs.destroy');

    // Routes pour la validation et le rejet
    Route::post('/chauffeurs/{id}/validate', [ChauffeurController::class, 'validate'])->name('chauffeurs.validate');
    Route::post('/chauffeurs/{id}/reject', [ChauffeurController::class, 'reject'])->name('chauffeurs.reject');



    // gestion des agences

Route::prefix('agences')->name('agences.')->group(function () {
    // Routes principales
    Route::get('/', [AgenceController::class, 'index'])->name('index');
    Route::post('/', [AgenceController::class, 'store'])->name('store');
    Route::put('/{agence}', [AgenceController::class, 'update'])->name('update');
    Route::delete('/{agence}', [AgenceController::class, 'destroy'])->name('destroy');

    // Routes AJAX
    Route::get('/list', [AgenceController::class, 'getAgencesList'])->name('list');
    Route::get('/stats', [AgenceController::class, 'getStats'])->name('stats');
    Route::get('/{agence}/edit', [AgenceController::class, 'edit'])->name('edit');
});



// gestion des entrepôts
Route::prefix('entrepots')->name('entrepots.')->group(function () {
    // Routes principales
    Route::get('/', [EntrepotController::class, 'index'])->name('index');
    Route::post('/', [EntrepotController::class, 'store'])->name('store');
    Route::put('/{entrepot}', [EntrepotController::class, 'update'])->name('update');
    Route::delete('/{entrepot}', [EntrepotController::class, 'destroy'])->name('destroy');

    // Routes AJAX
    Route::get('/list', [EntrepotController::class, 'getEntrepotsList'])->name('list');
    Route::get('/stats', [EntrepotController::class, 'getStats'])->name('stats');
    Route::get('/{entrepot}/edit', [EntrepotController::class, 'edit'])->name('edit');
});

// Routes pour la gestion des distributeurs
Route::prefix('distributeurs')->name('distributeurs.')->group(function () {
    // Route pour afficher la liste des distributeurs et le formulaire d'ajout/modification
    Route::get('/', [DistributeurController::class, 'index'])->name('index');

    // Route pour récupérer la liste des distributeurs (pour AJAX)
    Route::get('/list', [DistributeurController::class, 'getDistributeursList'])->name('list');

    // Route pour récupérer les statistiques actualisées
    Route::get('/stats', [DistributeurController::class, 'getStatistics'])->name('stats');

    // Route pour récupérer les détails d'un distributeur spécifique pour l'édition (pour AJAX)
    Route::get('/{id}/edit', [DistributeurController::class, 'edit'])->name('edit');

    // Route pour enregistrer un nouveau distributeur
    Route::post('/', [DistributeurController::class, 'store'])->name('store');

    // Route pour mettre à jour un distributeur existant
    Route::put('/{id}', [DistributeurController::class, 'update'])->name('update');

    // Route pour supprimer un distributeur
    Route::delete('/{id}', [DistributeurController::class, 'destroy'])->name('destroy');
});








// Route principale pour la page des leases
Route::get('/leases', [App\Http\Controllers\Leases\LeaseController::class, 'index'])->name('leases.index');

// Routes API pour le filtrage et les statistiques
Route::post('/api/leases/filter', [App\Http\Controllers\Leases\LeaseController::class, 'filterLeases'])->name('api.leases.filter');
Route::post('/api/leases/stats', [App\Http\Controllers\Leases\LeaseController::class, 'getStats'])->name('api.leases.stats');






// Route pour afficher la vue principale des swaps
Route::get('/swaps', [App\Http\Controllers\Associations\SwapController::class, 'index'])->name('swaps.index');
// Route pour traiter un swap
Route::post('/swaps/handle', [App\Http\Controllers\Associations\SwapController::class, 'handleSwap'])->name('swaps.handle');
// Routes API pour les statistiques et le filtrage
Route::post('/api/swaps/filter', [App\Http\Controllers\Associations\SwapController::class, 'filterSwaps'])->name('api.swaps.filter');
Route::post('/api/swaps/stats', [App\Http\Controllers\Associations\SwapController::class, 'getStats'])->name('api.swaps.stats');




// Routes for Employee Management
// Add these routes to your web.php file

// Employee Management
Route::prefix('employe')->name('employe.')->group(function () {

    Route::post('/login', [EmployeController::class, 'login'])->name('login.submit');

    // Protected routes (require authentication)
        // Main dashboard/index
        Route::get('/', [EmployeController::class, 'index'])->name('index');

        // API-like routes for AJAX
        Route::get('/list', [EmployeController::class, 'list'])->name('list');
        Route::post('/store', [EmployeController::class, 'store'])->name('store');
        Route::get('/{id}', [EmployeController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [EmployeController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [EmployeController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [EmployeController::class, 'destroy'])->name('destroy');
        Route::patch('/restore/{id}', [EmployeController::class, 'restore'])->name('restore');
        Route::get('/export', [EmployeController::class, 'export'])->name('export');

        // Logout
        Route::post('/logout', [EmployeController::class, 'logout'])->name('logout');

});
    // Public routes (login)
    Route::get('/login', [EmployeController::class, 'showLoginForm'])->name('login');







// Routes pour la page de ravitaillement
Route::prefix('ravitaillements')->group(function () {
    Route::get('/', [RavitaillementController::class, 'index'])->name('ravitailler.batteries.index');
    Route::post('/store', [RavitaillementController::class, 'store'])->name('ravitailler.batteries.store');
});

// Routes API pour les filtres et statistiques
Route::prefix('api')->group(function () {
    Route::post('/ravitaillements/filter', [RavitaillementController::class, 'filter']);
    Route::post('/ravitaillements/stats', [RavitaillementController::class, 'getStats']);
});

//bms configurations
Route::get('/bms/setup', [BmsSetupController::class, 'index'])->name('bms.setup');
Route::post('/bms/send', [BmsSetupController::class, 'send'])->name('bms.send');


//distances
Route::get('/distances', [App\Http\Controllers\DailyDistanceController::class, 'index'])->name('distances.index');
