<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Motos\MotoController;
use App\Http\Controllers\Batteries\BatterieController;
use App\Http\Controllers\Bms\BMSController;
use App\Http\Controllers\Associations\AssociationUserMotoController;
use App\Http\Controllers\Associations\BatteryMotoUserAssociationController;

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





// Routes pour les associations batterie-utilisateur
Route::prefix('associations/batteries')->name('associations.batterie.')->group(function () {
    // Page d'accueil des associations batterie-utilisateur
    Route::get('/', [BatteryMotoUserAssociationController::class, 'index'])->name('moto.user.index');
    
    // Créer une association
    Route::post('/', [BatteryMotoUserAssociationController::class, 'store'])->name('user.store');
    
    // Confirmer une association
    Route::post('/confirm', [BatteryMotoUserAssociationController::class, 'confirmAssociation'])->name('user.confirm');
    
    // Mettre à jour une association
    Route::put('/{id}', [BatteryMotoUserAssociationController::class, 'update'])->name('user.update');
    
    // Supprimer une association
    Route::delete('/{id}', [BatteryMotoUserAssociationController::class, 'destroy'])->name('user.destroy');
    
    // Obtenir les détails d'une association
    Route::get('/{id}/details', [BatteryMotoUserAssociationController::class, 'getAssociationDetails'])->name('user.details');
    
    // Liste des associations pour l'API
    Route::get('/all', [BatteryMotoUserAssociationController::class, 'getAllAssociations'])->name('user.all');
    
    // Statistiques
    Route::get('/stats', [BatteryMotoUserAssociationController::class, 'getStats'])->name('user.stats');
});