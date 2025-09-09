<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeAuthController;
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
use App\Http\Controllers\Gps\LocalisationController;
use App\Http\Controllers\Gps\LeaseController;
use App\Http\Controllers\Batteries\BatteryStationController;
use App\Http\Controllers\Compteur\CompteurController;
use App\Http\Controllers\DailyDistanceController;
use App\Http\Controllers\Dashboard\DisplayDashboardController;
use App\Http\Controllers\Swaps\SwapParHeureController;
use App\Http\Controllers\Batteries\BatterieSOEController;
use App\Http\Controllers\Batteries\EtatBatterieAssociation5MinController;

use App\Http\Controllers\Swaps\EtatProprietaireBatterieController;
use App\Http\Controllers\Notifications\NotificationController;




Route::get('/', function () {
    return view('welcome');
});



Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');


//Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');




// Routes du Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/dashboard/filter', [DashboardController::class, 'filter'])->name('dashboard.filter');
Route::get('/dashboard/inactive-batteries', [DashboardController::class, 'getInactiveBatteries'])->name('dashboard.inactive-batteries');
   Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


// compteur
Route::get('/compteur/test', [CompteurController::class, 'index'])->name('compteur.index');
Route::post('/compteur/envoyer', [CompteurController::class, 'envoyerMessage'])->name('compteur.envoyer');

// display dashpoard
Route::get('/dashboard/display', [DisplayDashboardController::class, 'index'])->name('display.index');
Route::get('/dashboard/full-data', [DisplayDashboardController::class, 'getFullDashboardData']);

// Routes API pour les batteries
Route::prefix('api')->group(function () {
    Route::get('/inactive-batteries', [DashboardController::class, 'getInactiveBatteries'])->name('api.inactive_batteries');
    Route::get('/batteries/{id}', [DashboardController::class, 'getBatteryDetails'])->name('api.battery_details');
});





    // Motos
    Route::prefix('motos')->name('motos.')->group(function () {
        Route::get('/', [MotoController::class, 'index'])->name('index');
        Route::post('/', [MotoController::class, 'store'])->name('store');
        Route::put('/{id}', [MotoController::class, 'update'])->name('update');
        Route::post('/{id}/validate', [MotoController::class, 'validateMoto'])->name('validate');
        Route::post('/{id}/reject', [MotoController::class, 'reject'])->name('reject');
        Route::delete('/{id}', [MotoController::class, 'destroy'])->name('destroy');
    });

    // Batteries
    Route::prefix('batteries')->name('batteries.')->group(function () {
        Route::get('/', [BatterieController::class, 'index'])->name('index');
        Route::post('/', [BatterieController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [BatterieController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BatterieController::class, 'update'])->name('update');
        Route::post('/{id}/validate', [BatterieController::class, 'validate'])->name('validate');
        Route::post('/{id}/reject', [BatterieController::class, 'reject'])->name('reject');
        Route::delete('/{id}', [BatterieController::class, 'destroy'])->name('destroy');
        Route::get('/valides', [BatterieController::class, 'indexValide'])->name('valide');
        Route::get('/map', [BatterieController::class, 'showBatteriesOnMap'])->name('map');
        Route::get('/api/map-data', [BatterieController::class, 'getBatteriesMapData']);
        Route::get('/api/map-updates', [BatterieController::class, 'getBatteriesUpdates']);

         Route::get('/{id}/validate-form', [BatterieController::class, 'getValidationForm'])->name('getValidationForm');
    });

    // BMS
    Route::get('bms-details', [BmsSetupController::class, 'index'])->name('bms.index');
    Route::prefix('api/bms')->group(function () {
        Route::get('batteries', [BmsSetupController::class, 'getBatteries']);
        Route::get('battery/{mac_id}', [BmsSetupController::class, 'getBatteryDetails']);
        Route::get('battery/{mac_id}/refresh', [BmsSetupController::class, 'refreshBatteryData']);
    });

    // Associations moto-utilisateur
    Route::prefix('associations')->name('associations.')->group(function () {
        Route::get('/', [AssociationUserMotoController::class, 'showAssociationForm'])->name('index');
        Route::get('/all', [AssociationUserMotoController::class, 'getAllAssociations'])->name('all');
        Route::get('/filter', [AssociationUserMotoController::class, 'filterAssociations'])->name('filter');
        Route::get('/stats', [AssociationUserMotoController::class, 'getStats'])->name('stats');
        Route::get('/check-moto/{motoUniqueId}', [AssociationUserMotoController::class, 'checkMotoAssociation'])->name('checkMoto');
        Route::get('/check-user-moto/{userUniqueId}/{motoUniqueId}', [AssociationUserMotoController::class, 'checkUserMotoAssociation'])->name('checkUserMoto');
        Route::get('/user/{userUniqueId}', [AssociationUserMotoController::class, 'getUserAssociations'])->name('userAssociations');
        Route::get('/moto/{motoUniqueId}', [AssociationUserMotoController::class, 'getMotoAssociations'])->name('motoAssociations');
        Route::post('/associer', [AssociationUserMotoController::class, 'associerMotoAUtilisateurs'])->name('associerMotoAUtilisateurs');
        Route::post('/confirm', [AssociationUserMotoController::class, 'confirmAssociation'])->name('confirm');
        Route::get('/{id}/details', [AssociationUserMotoController::class, 'getAssociationDetails'])->name('details');
        Route::put('/{id}', [AssociationUserMotoController::class, 'updateAssociation'])->name('updateAssociation');
        Route::delete('/{id}', [AssociationUserMotoController::class, 'deleteAssociation'])->name('deleteAssociation');

        // Associations batteries-motos-utilisateurs
        Route::get('/batteries/motos/available', [BatteryMotoUserAssociationController::class, 'getAvailableMotos']);
        Route::get('/batteries/available', [BatteryMotoUserAssociationController::class, 'getAvailableBatteries']);
        Route::get('/batteries/bms/{macId}', [BatteryMotoUserAssociationController::class, 'getBmsData']);
        Route::post('/batteries/bms/bulk', [BatteryMotoUserAssociationController::class, 'getBulkBmsData']);
        Route::get('/batteries/stats', [BatteryMotoUserAssociationController::class, 'getStats']);
        Route::get('/batteries', [BatteryMotoUserAssociationController::class, 'index'])->name('batteries.index');
        Route::get('/batteries/{id}', [BatteryMotoUserAssociationController::class, 'getAssociationDetails']);
        Route::post('/batteries', [BatteryMotoUserAssociationController::class, 'store']);
        Route::put('/batteries/{id}', [BatteryMotoUserAssociationController::class, 'update']);
        Route::delete('/batteries/{id}', [BatteryMotoUserAssociationController::class, 'destroy']);
    });
    Route::patch('/associations/batteries/{id}/desassociate', [BatteryMotoUserAssociationController::class, 'desassociate'])->name('associations.batterie.moto.user.desassociate');


    // Chauffeurs
    Route::resource('chauffeurs', ChauffeurController::class)->except(['create', 'show']);
    Route::post('/chauffeurs/{id}/validate', [ChauffeurController::class, 'validate'])->name('chauffeurs.validate');
    Route::post('/chauffeurs/{id}/reject', [ChauffeurController::class, 'reject'])->name('chauffeurs.reject');

    // Agences
    Route::resource('agences', AgenceController::class);
    Route::get('/agences/list', [AgenceController::class, 'getAgencesList'])->name('agences.list');
    Route::get('/agences/stats', [AgenceController::class, 'getStats'])->name('agences.stats');

    // Entrepôts
    Route::resource('entrepots', EntrepotController::class);
    Route::get('/entrepots/list', [EntrepotController::class, 'getEntrepotsList'])->name('entrepots.list');
    Route::get('/entrepots/stats', [EntrepotController::class, 'getStats'])->name('entrepots.stats');

    // Distributeurs
    Route::resource('distributeurs', DistributeurController::class)->except(['show']);
    Route::get('/distributeurs/list', [DistributeurController::class, 'getDistributeursList'])->name('distributeurs.list');
    Route::get('/distributeurs/stats', [DistributeurController::class, 'getStatistics'])->name('distributeurs.stats');

    // Employés
    Route::prefix('employe')->name('employe.')->group(function () {
        Route::get('/', [EmployeController::class, 'index'])->name('index');
        Route::get('/list', [EmployeController::class, 'list'])->name('list');
        Route::post('/store', [EmployeController::class, 'store'])->name('store');
        Route::get('/{id}', [EmployeController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [EmployeController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [EmployeController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [EmployeController::class, 'destroy'])->name('destroy');
        Route::patch('/restore/{id}', [EmployeController::class, 'restore'])->name('restore');
        Route::get('/export', [EmployeController::class, 'export'])->name('export');
    });

    // Swaps
    Route::get('/swaps', [App\Http\Controllers\Associations\SwapController::class, 'index'])->name('swaps.index');
    Route::post('/swaps/handle', [App\Http\Controllers\Associations\SwapController::class, 'handleSwap'])->name('swaps.handle');
    Route::post('/api/swaps/filter', [App\Http\Controllers\Associations\SwapController::class, 'filterSwaps']);
    Route::post('/api/swaps/stats', [App\Http\Controllers\Associations\SwapController::class, 'getStats']);




      // Route principale pour afficher la page des swaps par chauffeur
      Route::get('/swaps-chauffeur', [App\Http\Controllers\Associations\SwapChauffeurController::class, 'index'])->name('swaps.chauffeur.index');

      // Routes API pour le filtrage et les statistiques des swaps par chauffeur
      Route::post('/api/swaps-chauffeur/filter', [App\Http\Controllers\Associations\SwapChauffeurController::class, 'filterSwaps'])->name('api.swaps.chauffeur.filter');
      Route::post('/api/swaps-chauffeur/stats', [App\Http\Controllers\Associations\SwapChauffeurController::class, 'getStats'])->name('api.swaps.chauffeur.stats');


    // Ravitaillements
    Route::prefix('ravitaillements')->group(function () {
        Route::get('/', [RavitaillementController::class, 'index'])->name('ravitailler.batteries.index');
        Route::post('/store', [RavitaillementController::class, 'store'])->name('ravitailler.batteries.store');
    });
    Route::prefix('api')->group(function () {
        Route::post('/ravitaillements/filter', [RavitaillementController::class, 'filter']);
        Route::post('/ravitaillements/stats', [RavitaillementController::class, 'getStats']);
    });

//bms confirgurations


    Route::get('/bms/configure', [BmsSetupController::class, 'configure'])->name('bms.configure');
    Route::post('/bms/preview', [BmsSetupController::class, 'preview'])->name('bms.preview');
    Route::get('/bms/preview', fn() => redirect()->route('bms.configure'));
    Route::post('/bms/send', [BmsSetupController::class, 'send'])->name('bms.send');
    Route::get('/bms/configs', [BmsSetupController::class, 'configs'])->name('bms.configs');

    // GPS
    Route::prefix('gps')->group(function () {
        Route::get('/motos/carte', [LocalisationController::class, 'carteDouala'])->name('gps.motos.carte');
        Route::get('/motos/zone', [LocalisationController::class, 'apiZoneDouala'])->name('gps.motos.zone');
        Route::get('/motos/positions', [LocalisationController::class, 'apiMotosPositions'])->name('gps.motos.positions');
    });

    // Distances
    Route::get('/distances', [App\Http\Controllers\DailyDistanceController::class, 'index'])->name('distances.index');
    Route::post('/distances/filter', [App\Http\Controllers\DailyDistanceController::class, 'filter']);
    Route::post('/distances/calculate', [App\Http\Controllers\DailyDistanceController::class, 'calculateDailyDistances']);

    // route pour recalculer les distance sur une plage de date
    Route::get('/recalculer-distances', [DailyDistanceController::class, 'recalculerDistanceParPlage'])
    ->name('distances.recalculer');


// Route principale pour la page des leases
Route::get('/leases', [App\Http\Controllers\Leases\LeaseController::class, 'index'])->name('leases.index');

// Routes API pour le filtrage et les statistiques
Route::post('/api/leases/filter', [App\Http\Controllers\Leases\LeaseController::class, 'filterLeases'])->name('api.leases.filter');
Route::post('/api/leases/stats', [App\Http\Controllers\Leases\LeaseController::class, 'getStats'])->name('api.leases.stats');





Route::get('/batteries/station', [BatteryStationController::class, 'index'])->name('batteries.station.index');
// Dans web.php
Route::get('/batteries/station/export', [BatteryStationController::class, 'export'])->name('batteries.station.export');
Route::get('/batteries/station/stats', [BatteryStationController::class, 'getStats'])->name('batteries.station.stats');



// SOE des batteries à 100%
Route::get('/batteries/soe', [BatterieSOEController::class, 'showSoeAtFullCharge'])->name('batterie.soe');



// route pour afficher les notifications
Route::get('/notifications', [NotificationController::class, 'index'])
    ->name('notifications.index');

// Raccourci permanent vers "Aujourd'hui"
Route::get('/notifications/today', function () {
    return redirect()->route('notifications.index', ['scope' => 'today']);
})->name('notifications.today');

// routes etat des batteries toutes les 5 minutes avec leurs associations
Route::get('/etat-batterie/enregistrer', [\App\Http\Controllers\Batteries\EtatBatterieAssociation5MinController::class, 'enregistrer']);
Route::get('/etat-batteries', [EtatBatterieAssociation5MinController::class, 'index'])->name('etat-batteries.index');


// routes pour le nombre de swap chaque heure
Route::get('/swaps/statistiques-par-heure', [SwapParHeureController::class, 'index'])->name('swaps.par.heure');
Route::get('/api/swaps/statistiques-par-heure', [SwapParHeureController::class, 'api'])->name('swaps.par.heure.api');


// propiretaire  batterie  taleaux logistique
Route::get('/swaps/etat-batteries-proprietaire', [EtatProprietaireBatterieController::class, 'index'])
    ->name('swaps.etat_batteries_proprietaire');

});

require __DIR__.'/auth.php';


// route pour afficher les notifications
// route pour afficher les notifications
Route::get('/notifications', [NotificationController::class, 'index'])
    ->name('notifications.index');

// Raccourci permanent vers "Aujourd'hui"
Route::get('/notifications/today', function () {
    return redirect()->route('notifications.index', ['scope' => 'today']);
})->name('notifications.today');
