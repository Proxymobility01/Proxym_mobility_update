@extends('layouts.app')


@section('content')
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
    width: 900px;
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

.action-btn.edit-association i {
    color: #ffc107;
}

.action-btn.delete-association i {
    color: #dc3545;
}

.action-btn.view-association i {
    color: #17a2b8;
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

/* Badges pour le statut de batterie */
.battery-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.battery-status.online {
    background-color: rgba(40, 167, 69, 0.2);
    color: #28a745;
}

.battery-status.offline {
    background-color: rgba(108, 117, 125, 0.2);
    color: #6c757d;
}

.battery-level {
    display: inline-block;
    width: 50px;
    height: 16px;
    background-color: #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}

.battery-level-inner {
    height: 100%;
    border-radius: 8px;
    transition: width 0.3s ease;
}

.battery-level-text {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    color: #212529;
}

.battery-high .battery-level-inner {
    background-color: #28a745;
}

.battery-medium .battery-level-inner {
    background-color: #ffc107;
}

.battery-low .battery-level-inner {
    background-color: #dc3545;
}

/* Styles pour les tabs */
.nav-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.nav-tab {
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-right: 10px;
}

.nav-tab:hover {
    background-color: var(--tertiary);
}

.nav-tab.active {
    border-bottom-color: var(--primary);
    font-weight: bold;
}

/* Styles pour la modale d'ajout/édition d'association */
.association-form-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.selection-column {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
}

.selection-column h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 16px;
    border-bottom: 2px solid var(--primary);
    padding-bottom: 8px;
    display: inline-block;
}

.selection-search {
    margin-bottom: 15px;
}

.selection-search input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.selection-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.selection-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.selection-item:last-child {
    border-bottom: none;
}

.selection-item:hover {
    background-color: #f8f9fa;
}

.selection-item.selected {
    background-color: rgba(220, 219, 50, 0.1);
}

.selection-item input[type="radio"] {
    margin-right: 10px;
}

.selection-item-details {
    flex-grow: 1;
}

.selection-item-title {
    font-weight: bold;
    margin-bottom: 2px;
}

.selection-item-subtitle {
    font-size: 0.8em;
    color: #6c757d;
}

.battery-info {
    display: flex;
    align-items: center;
    margin-top: 4px;
}

.selection-footer {
    margin-top: 15px;
    color: #6c757d;
    font-size: 0.9em;
}





















































* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Metropolis', sans-serif;
}

:root {
    --primary: #DCDB32;
    --secondary: #101010;
    --tertiary: #F3F3F3;
    --background: #ffffff;
    --text: #101010;
    --sidebar: #F8F8F8;
}

body {
    background-color: var(--background);
    color: var(--text);
    overflow-x: hidden;
}


h1, h2, h3, h4, h5, h6 {
    font-family: 'Orbitron', sans-serif;
  }

  
/* Styles pour les tabs  des navbar*/
.nav-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.nav-tab {
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-right: 10px;
    text-decoration: none;
    font-size: 1.5rem;
    color: var(--secondary);
}

.nav-tab:hover {
    background-color: var(--tertiary);
}

.nav-tab.active {
    border-bottom-color: var(--primary);
    font-weight: bold;
    color: var(--primary);
}


/* Export buttons */
.export-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.export-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-excel {
    background-color: #1D6F42;
    color: white;
}

.btn-pdf {
    background-color: #F40F02;
    color: white;
}

.btn-csv {
    background-color: #ffcc00;
    color: #333;
}

/* Fichier: public/assets/css/logo.css */

/* ===========================================
   STYLES LOGO PROXYM - CSS SÉPARÉ
   =========================================== */
/* Fichier: public/assets/css/logo.css */

.logo-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-icon a {
    display: block;
    text-decoration: none;
    line-height: 0;
}

.logo-image {
    width: 45px;
    height: 45px;
    object-fit: contain;
    border-radius: 4px;
    transition: opacity 0.3s ease;
}

.logo-image:hover {
    opacity: 0.8;
}

/* Logo dans le container avec texte - CLASSES EXISTANTES */
.logo-container {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

/* Remplacer l'icône fas fa-bolt par une image */
.logo-container .logo-icon {
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-container .logo-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 4px;
    transition: opacity 0.3s ease;
    display: block;
    margin: auto;
}

.logo-container:hover .logo-icon img {
    opacity: 0.8;
}

.logo-container .logo-text {
    font-family: 'Orbitron', monospace;
    font-weight: 700;
}

.logo-container span{
    font-size: 30px;
    font-family: 'Orbitron', monospace;
    text-transform: uppercase;
    letter-spacing: 0.5;
    font-weight: bold;
    color: #DCDB32;
}

/* Responsive Design */
@media (max-width: 768px) {
    .logo-image {
        width: 35px;
        height: 35px;
    }
    
    .logo-container .logo-icon {
        width: 30px;
        height: 30px;
    }
}

@media (max-width: 480px) {
    .logo-image {
        width: 30px;
        height: 30px;
    }
    
    .logo-container .logo-icon {
        width: 25px;
        height: 25px;
    }
}
/* Styles pour le dasshboard*/
.dashboard {
    display: flex;
    min-height: 100vh;
    position: relative;
}

.sidebar {
    background-color: var(--sidebar);
    padding: 2rem 1.5rem;
    border-right: 1px solid rgba(16, 16, 16, 0.1);
    position: fixed;
    height: 100vh;
    width: 280px;
    transition: width 0.3s ease;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar a{
    text-decoration: none;
}

.sidebar.collapsed {
    width: 80px;
    padding: 2rem 0.8rem;
}

.toggle-sidebar {
    position: absolute;
    right: -15px;
    top: 20px;
    width: 30px;
    height: 30px;
    background-color: var(--primary);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--secondary);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    z-index: 100;
    transition: transform 0.3s ease;
}

.toggle-sidebar:hover {
    transform: scale(1.1);
}

.nav-item {
    padding: 1rem;
    margin: 0.5rem 0;
    border-radius: 0.5rem;
    cursor: pointer;
    color: var(--secondary);
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 1rem;
    white-space: nowrap;
}

.nav-item i {
    font-size: 1.2rem;
    min-width: 24px;
    text-align: center;
}

.sidebar.collapsed .nav-item {
    justify-content: center;
    padding: 1rem 0;
}

.sidebar.collapsed .nav-item span {
    display: none;
}

.sidebar.collapsed .logo-text {
    display: none;
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 3rem;
    transition: justify-content 0.3s ease;
}

.sidebar.collapsed .logo-container {
    justify-content: center;
}

.logo-icon {
    min-width: 24px;
    height: 24px;
    color: var(--primary);
}

.logo-text {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary);
    transition: opacity 0.3s ease;
}

.nav-item.active {
    background-color: var(--primary);
    color: var(--secondary);
}

.main-content {
    flex: 1;
    padding: 2rem 3rem;
    margin-left: 280px;
    transition: margin-left 0.3s ease;
}

.main-content.expanded {
    margin-left: 80px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3rem;
}

.title {
    font-size: 2rem;
    color: var(--secondary);
    font-weight: 600;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.stat-card {
    background-color: var(--background);
    padding: 2rem;
    border-radius: 1rem;
    border: 1px solid rgba(16, 16, 16, 0.1);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(16, 16, 16, 0.1);
}

.stat-title {
    color: var(--secondary);
    font-size: 1rem;
    margin-bottom: 1rem;
    opacity: 0.7;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 600;
    color: var(--primary);
}

.stat-change {
    font-size: 0.9rem;
    color: var(--secondary);
    margin-top: 0.5rem;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.chart-container {
    background-color: var(--background);
    padding: 2rem;
    border-radius: 1rem;
    border: 1px solid rgba(16, 16, 16, 0.1);
    min-height: 300px;
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 2rem;
    background-color: var(--background);
    border-radius: 1rem;
    overflow: hidden;
    border: 1px solid rgba(16, 16, 16, 0.1);
}

.head-table {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    margin-bottom: 1rem;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 6px;
  }
  
  .head-table h2 {
    font-size: 1.5rem;
    color: #333;
    margin: 0;
  }
  
  .head-table a {
    background-color: #DCDB32;
    color: #101010;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
    text-decoration: none;
  }
  
  .head-table a:hover {
    background-color: #c0bd26;
  }
  


th, td {
    padding: 1.5rem;
    text-align: left;
    border-bottom: 1px solid rgba(16, 16, 16, 0.1);
}

th {
    background-color: rgba(220, 219, 50, 0.1);
    font-weight: 600;
    color: var(--secondary);
}

.status {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-active {
    background-color: rgba(220, 219, 50, 0.2);
    color: var(--secondary);
}

.status-charging {
    background-color: rgba(16, 16, 16, 0.1);
    color: var(--secondary);
}

.status-inactive {
    background-color: rgba(243, 243, 243, 0.5);
    color: var(--secondary);
}

.battery-level {
    width: 100%;
    height: 8px;
    background-color: rgba(16, 16, 16, 0.1);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 4px;
}

.battery-fill {
    height: 100%;
    background-color: var(--primary);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.date {
    color: var(--secondary);
    font-size: 1rem;
    opacity: 0.7;
}

/* Responsive designs */
@media (max-width: 1200px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 992px) {
    .sidebar {
        width: 80px;
        padding: 2rem 0.8rem;
    }
    
    .sidebar .nav-item span,
    .sidebar .logo-text {
        display: none;
    }
    
    .sidebar .logo-container,
    .sidebar .nav-item {
        justify-content: center;
    }
    
    .main-content {
        margin-left: 80px;
    }
    
    .toggle-sidebar {
        display: none;
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 2rem 1.5rem;
    }
    
    .title {
        font-size: 1.5rem;
    }
    
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    table {
        display: block;
        overflow-x: auto;
    }
}

@media (max-width: 576px) {
    .main-content {
        margin-left: 0;
        padding: 1rem;
    }
    
    .sidebar {
        transform: translateX(-100%);
        width: 250px;
    }
    
    .sidebar.mobile-visible {
        transform: translateX(0);
    }
    
    .mobile-menu-toggle {
        display: flex !important;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1001;
        background-color: var(--primary);
        color: var(--secondary);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }
    
    .overlay.active {
        display: block;
    }
    
    .chart-container {
        min-height: 250px;
        padding: 1rem;
    }
}






/* gestion des motos */

/* Styles supplémentaires pour le module de gestion des motos */

/* Statuts avec indicateurs visuels améliorés */
.status-indicator {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.status-indicator i {
    margin-right: 0.5rem;
}

.status-indicator.en-attente {
    background-color: rgba(255, 193, 7, 0.15);
    color: #f39c12;
    border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-indicator.valide {
    background-color: rgba(220, 219, 50, 0.15);
    color: #DCDB32;
    border: 1px solid rgba(220, 219, 50, 0.3);
}

.status-indicator.rejete {
    background-color: rgba(231, 76, 60, 0.15);
    color: #e74c3c;
    border: 1px solid rgba(231, 76, 60, 0.3);
}

/* Cartes motos avec design moderne */
.moto-card {
    background-color: var(--background);
    border-radius: 1rem;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid rgba(16, 16, 16, 0.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.moto-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.moto-card-header {
    background-color: var(--sidebar);
    padding: 1.2rem;
    position: relative;
}

.moto-card-body {
    padding: 1.5rem;
    flex: 1;
}

.moto-card-footer {
    padding: 1rem 1.5rem;
    background-color: var(--tertiary);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.moto-model {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--secondary);
    margin-bottom: 0.5rem;
}

.moto-id {
    color: var(--secondary);
    opacity: 0.6;
    font-size: 0.85rem;
}

.moto-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1.2rem;
}

.moto-info-item {
    display: flex;
    flex-direction: column;
}

.moto-info-label {
    font-size: 0.75rem;
    color: var(--secondary);
    opacity: 0.6;
    margin-bottom: 0.3rem;
}

.moto-info-value {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--secondary);
}

.moto-corner-status {
    position: absolute;
    top: 0;
    right: 0;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 50px 50px 0;
    z-index: 1;
}

.moto-corner-status.en-attente {
    border-color: transparent #f39c12 transparent transparent;
}

.moto-corner-status.valide {
    border-color: transparent #DCDB32 transparent transparent;
}

.moto-corner-status.rejete {
    border-color: transparent #e74c3c transparent transparent;
}

.moto-corner-icon {
    position: absolute;
    top: 5px;
    right: 5px;
    color: white;
    z-index: 2;
    font-size: 0.9rem;
}

/* Processus de validation visuel */
.validation-process {
    display: flex;
    justify-content: space-between;
    margin: 2rem 0;
    position: relative;
}

.validation-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    flex: 1;
}

.validation-step-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: var(--tertiary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    color: var(--secondary);
    font-size: 1.2rem;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.validation-step.active .validation-step-icon {
    background-color: var(--primary);
    color: var(--secondary);
    box-shadow: 0 0 0 5px rgba(220, 219, 50, 0.2);
}

.validation-step.completed .validation-step-icon {
    background-color: var(--primary);
    color: var(--secondary);
}

.validation-step-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--secondary);
    margin-bottom: 0.5rem;
    text-align: center;
}

.validation-step-description {
    font-size: 0.8rem;
    color: var(--secondary);
    opacity: 0.6;
    text-align: center;
    max-width: 150px;
}

.validation-progress-bar {
    position: absolute;
    top: 25px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: var(--tertiary);
    z-index: 1;
}

.validation-progress-fill {
    height: 100%;
    background-color: var(--primary);
    transition: width 0.5s ease;
    width: 0;
}

/* Formulaire de validation amélioré */
.validation-form {
    background-color: var(--background);
    border-radius: 1rem;
    padding: 2rem;
    margin-top: 2rem;
    border: 1px solid rgba(16, 16, 16, 0.1);
}

.validation-form-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--secondary);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(16, 16, 16, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.validation-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(16, 16, 16, 0.1);
}

/* Animations pour le processus de validation */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 219, 50, 0.5);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(220, 219, 50, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 219, 50, 0);
    }
}

.validation-step.active .validation-step-icon {
    animation: pulse 1.5s infinite;
}

/* Modal personnalisé pour la validation */
.modal-validation {
    max-width: 700px;
}

.modal-validation .modal-body {
    padding: 0;
}

.validation-tabs {
    display: flex;
    border-bottom: 1px solid rgba(16, 16, 16, 0.1);
}

.validation-tab {
    padding: 1rem 1.5rem;
    cursor: pointer;
    font-weight: 500;
    color: var(--secondary);
    opacity: 0.7;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
}

.validation-tab.active {
    color: var(--primary);
    opacity: 1;
    border-bottom: 2px solid var(--primary);
}

.validation-tab-content {
    padding: 2rem;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

/* Zone de prévisualisation des documents */
.document-preview {
    border: 2px dashed rgba(16, 16, 16, 0.1);
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
    margin-bottom: 1.5rem;
}

.document-preview-icon {
    font-size: 2rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.document-preview-text {
    font-size: 0.9rem;
    color: var(--secondary);
    opacity: 0.7;
}

.document-preview-button {
    margin-top: 1rem;
}

/* Affichage des données de vérification */
.verification-data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
}

.verification-data-table tr {
    border-bottom: 1px solid rgba(16, 16, 16, 0.05);
}

.verification-data-table tr:last-child {
    border-bottom: none;
}

.verification-data-table th {
    text-align: left;
    padding: 0.8rem;
    font-weight: 500;
    color: var(--secondary);
    opacity: 0.7;
    font-size: 0.85rem;
    width: 40%;
}

.verification-data-table td {
    padding: 0.8rem;
    color: var(--secondary);
    font-size: 0.9rem;
}

/* Loading spinner */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}

.loading-overlay.active {
    opacity: 1;
    visibility: visible;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 3px solid var(--tertiary);
    border-radius: 50%;
    border-top-color: var(--primary);
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Message de succès */
.success-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
}

.success-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: rgba(220, 219, 50, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.success-icon i {
    font-size: 2.5rem;
    color: var(--primary);
}

.success-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--secondary);
    margin-bottom: 1rem;
}

.success-description {
    font-size: 1rem;
    color: var(--secondary);
    opacity: 0.7;
    margin-bottom: 2rem;
    max-width: 400px;
}



























/* Conteneur principal */
.content {
    padding: 20px;
    background: #fff;
}

.content-header {
    margin-bottom: 24px;
}

.content-header h2 {
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

/* Barre de recherche et filtres */
.search-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding: 16px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
}

.search-group {
    position: relative;
    width: 300px;
}

.search-group input {
    width: 100%;
    padding: 10px 40px 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.search-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: none;
    color: #666;
    cursor: pointer;
}

.filter-group {
    display: flex;
    gap: 16px;
    align-items: center;
}

.select-status {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: #fff;
    min-width: 150px;
}

.add-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #DCDB32;
    border: none;
    border-radius: 6px;
    color: #333;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.add-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Cartes statistiques */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.stat-card {
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    position: relative;
    overflow: hidden;
    min-height: 120px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
}

.stat-card.total::before { background-color: #DCDB32; }
.stat-card.pending::before { background-color: #FFA500; }
.stat-card.success::before { background-color: #4CAF50; }
.stat-card.danger::before { background-color: #F44336; }

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.stat-details {
    flex: 1;
}

.stat-number {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 16px;
    color: #666;
    margin-bottom: 4px;
}

.stat-text {
    font-size: 14px;
    color: #999;
}

/* Tableau */
.table-container {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e0e0e0;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #fbfbf2;
}

th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 1px solid #e0e0e0;
}

td {
    padding: 15px;
    border-bottom: 1px solid #e0e0e0;
}

/* Badges de statut */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.status-badge.en_attente {
    background: #FFF3E0;
    color: #E65100;
}

.status-badge.valide {
    background: #E8F5E9;
    color: #2E7D32;
}

.status-badge.rejete {
    background: #FFEBEE;
    color: #C62828;
}

/* Boutons d'action */
.action-group {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.action-btn:hover {
    transform: translateY(-2px);
}

.action-btn.edit {
    background: #E3F2FD;
    color: #1976D2;
}

.action-btn.validate {
    background: #E8F5E9;
    color: #2E7D32;
}

.action-btn.delete {
    background: #FFEBEE;
    color: #C62828;
}

/* Message "Aucune donnée" */
.no-data {
    text-align: center;
    padding: 30px;
    color: #666;
    font-style: italic;
}

/* Responsive */
@media (max-width: 992px) {
    .search-bar {
        flex-direction: column;
        gap: 16px;
    }

    .search-group {
        width: 100%;
    }

    .filter-group {
        width: 100%;
        justify-content: space-between;
    }

    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .stats-container {
        grid-template-columns: 1fr;
    }

    .table-container {
        overflow-x: auto;
    }

    .action-group {
        flex-wrap: wrap;
    }
}

@media (max-width: 576px) {
    .filter-group {
        flex-direction: column;
    }

    .select-status {
        width: 100%;
    }

    .add-btn {
        width: 100%;
        justify-content: center;
    }
}





.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    /* On retire le padding ici pour que le header puisse occuper toute la largeur */
    padding: 0;
    border: 1px solid #888;
    width: 700px;
    border-radius: 8px;
    overflow: hidden; /* Pour éviter tout débordement */
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    /* On donne un padding interne pour le contenu */
    padding: 20px;
    border-bottom: 1px solid #ddd;
    background: var(--primary);
    color: var(--secondary);
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #ddd;
    padding: 10px 20px;
}


.modal.active {
    display: block !important; /* Force l'affichage */
}

/* Boutons et autres styles restent inchangés */
.close-modal {
    color: var(--secondary);
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: #000;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
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
    background-color: #DCDB32;
    color: #101010;
}

/* Styles pour les badges de statut */
.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
}

.status-badge.en_attente {
    background-color: #ffc107;
    color: #212529;
}

.status-badge.validé {
    background-color: #28a745;
    color: white;
}

.status-badge.rejeté {
    background-color: #dc3545;
    color: white;
}










  /* Styles spécifiques pour la modale des batteries avec !important pour forcer l'affichage */
  .modal {
    display: none !important;
    position: fixed !important;
    z-index: 1000 !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    height: 100% !important;
    overflow: auto !important;
    background-color: rgba(0, 0, 0, 0.4) !important;
}

.modal.active {
    display: block !important;
    opacity: 1 !important;
}

.modal-content {
    background-color: #fefefe !important;
    margin: 15% auto !important;
    padding: 0 !important;
    border: 1px solid #888 !important;
    width: 700px !important;
    border-radius: 8px !important;
    overflow: hidden !important;
}

.modal-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    padding: 20px !important;
    border-bottom: 1px solid #ddd !important;
    background: var(--primary) !important;
    color: var(--secondary) !important;
}

.modal-body {
    padding: 20px !important;
}

.modal-footer {
    display: flex !important;
    justify-content: flex-end !important;
    border-top: 1px solid #ddd !important;
    padding: 10px 20px !important;
}

.close-modal {
    color: var(--secondary) !important;
    font-size: 28px !important;
    font-weight: bold !important;
    cursor: pointer !important;
}

.close-modal:hover {
    color: #000 !important;
}
</style>

<div class="main-content">
    <!-- En-tête -->
    <div class="content-header">
        <h2>{{ $pageTitle }}</h2>
        <div id="date" class="date"></div>
    </div>

    <!-- Onglets de navigation -->
    
<!-- Onglets de navigation -->
<div class="nav-tabs">
    <div class="nav-tab {{ Request::is('associations') || (Request::is('associations/*') && !Request::is('associations/batteries*')) ? 'active' : '' }}"
         data-tab="moto-user"
         data-url="{{ route('associations.index') }}">
        Associations Moto-Utilisateur
    </div>

    <div class="nav-tab {{ Request::is('associations/batteries*') ? 'active' : '' }}"
         data-tab="battery-user"
         data-url="{{ route('associations.batteries.index') }}">
        Associations Batterie-Utilisateur
    </div>

    <div class="nav-tab {{ Request::is('ravitaillements') || Request::is('ravitaillements/*') ? 'active' : '' }}"
         data-tab="ravitaillement"
         data-url="{{ route('ravitailler.batteries.index') }}">
        Ravitailler Une Station
    </div>
</div>


    <!-- Barre de recherche et ajout -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-association" placeholder="Rechercher une association...">
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-group">
            <button id="add-association-btn" class="add-btn">
                <i class="fas fa-plus"></i>
                Associer Batterie & Moto
            </button>
        </div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-link"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-associations">{{ $associations->count() }}</div>
                <div class="stat-label">Total des associations</div>
                <div class="stat-text">Batteries associées</div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-motorcycle"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-motos">{{ $motos->count() }}</div>
                <div class="stat-label">Motos disponibles</div>
                <div class="stat-text">Avec utilisateur associé</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-batteries">{{ $batteries->count() }}</div>
                <div class="stat-label">Batteries</div>
                <div class="stat-text">Disponibles pour l'association</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-battery-quarter"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="low-batteries">{{ $lowBatteries }}</div>
                <div class="stat-label">Batteries faibles</div>
                <div class="stat-text">Niveau < 20%</div>
            </div>
        </div>
    </div>

    <!-- Tableau des associations -->
   <!-- Dans votre fichier Blade, modifiez la structure du tableau comme suit -->

<!-- Tableau des associations -->
<div class="table-container">
    <table id="associations-table">
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Moto</th>
                <th>VIN</th> <!-- Nouvelle colonne pour le VIN -->
                <th>Batterie</th>
                <th>MAC ID</th> <!-- Nouvelle colonne pour le MAC ID -->
                <th>État</th>
                <th>Niveau</th>
                <th>Date d'association</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="associations-table-body">
            @foreach($associations as $association)
            <tr data-id="{{ $association->id }}">
                <td>{{ $association->association->validatedUser->nom ?? 'N/A' }} {{ $association->association->validatedUser->prenom ?? '' }}</td>
                <td>{{ $association->association->motosValide->model ?? 'N/A' }} ({{ $association->association->motosValide->moto_unique_id ?? 'N/A' }})</td>
                <td>{{ $association->association->motosValide->vin ?? 'N/A' }}</td> <!-- VIN de la moto -->
                <td>{{ $association->batterie->batterie_unique_id ?? 'N/A' }}</td>
                <td>{{ $association->batterie->mac_id ?? 'N/A' }}</td> <!-- MAC ID de la batterie -->
                <td>
                    <span class="battery-status offline" data-mac-id="{{ $association->batterie->mac_id ?? '' }}">Offline</span>
                </td>
                <td>
                    <div class="battery-level battery-medium" data-mac-id="{{ $association->batterie->mac_id ?? '' }}">
                        <div class="battery-level-inner" style="width: 0%"></div>
                        <div class="battery-level-text">0%</div>
                    </div>
                </td>
                <td>{{ \Carbon\Carbon::parse($association->created_at)->format('d/m/Y') }}</td>
                <td style="display: flex;">
                    <button class="action-btn view-association" title="Voir les détails" data-mac-id="{{ $association->batterie->mac_id ?? '' }}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit-association" title="Modifier l'association">
                        <i class="fas fa-edit"></i>
                    </button>
                 
                    <button class="action-btn delete-association" title="Supprimer l'association">
                        <i class="fas fa-trash"></i>
                    </button>
                   
                     <form action="{{ route('associations.batterie.moto.user.desassociate', $association->id) }}" method="POST" onsubmit="return confirm('Confirmer la désassociation ?');">
    @csrf
    @method('PATCH')
    <button type="submit" class="action-btn" title="Désassocier">
        <i class="fas fa-unlink" style="color: #dcdb32;"></i>
    </button>
</form>


                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modale d'ajout/édition d'association -->
<div class="modal" id="association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Associer Batterie & Moto</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="association-form">
                @csrf
                <input type="hidden" id="association-id" name="id">
                <input type="hidden" id="form-method" name="_method" value="POST">

                <div class="association-form-container">
                    <!-- Sélection de la moto avec utilisateur -->
                    <div class="selection-column">
                        <h3>Sélectionner une Moto</h3>
                        <div class="selection-search">
                            <input type="text" id="search-moto" placeholder="Rechercher une moto...">
                        </div>
                        <div class="selection-list" id="motos-list">
                            <!-- Les motos seront chargées dynamiquement ici -->
                        </div>
                        <div class="selection-footer">
                            <small>Seules les motos déjà associées à un utilisateur sont affichées.</small>
                        </div>
                    </div>

                    <!-- Sélection de la batterie -->
                    <div class="selection-column">
                        <h3>Sélectionner une Batterie</h3>
                        <div class="selection-search">
                            <input type="text" id="search-battery" placeholder="Rechercher une batterie...">
                        </div>
                        <div class="selection-list" id="batteries-list">
                            <!-- Les batteries seront chargées dynamiquement ici -->
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="save-association" class="btn btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

<!-- Modale de détails de la batterie -->
<div class="modal" id="battery-details-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Détails de la Batterie</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div id="battery-info-container">
                <!-- Les détails de la batterie seront chargés dynamiquement ici -->
                <div class="loading">Chargement des données...</div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Fermer</button>
        </div>
    </div>
</div>

<!-- Modale de suppression d'association -->
<div class="modal" id="delete-association-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Supprimer l'association</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette association ?</p>
            <div class="association-details">
                <p><strong>Utilisateur :</strong> <span id="delete-user-name"></span></p>
                <p><strong>Moto :</strong> <span id="delete-moto-id"></span></p>
                <p><strong>Batterie :</strong> <span id="delete-battery-id"></span></p>
            </div>
            <form id="delete-association-form" method="POST">
                @csrf
                @method('DELETE')
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button id="confirm-delete-association" class="btn btn-primary">Supprimer</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ------------------------------------------------------------
    // Initialisation et variables
    // ------------------------------------------------------------
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const searchInput = document.getElementById('search-association');
    const addAssociationBtn = document.getElementById('add-association-btn');
    const associationModal = document.getElementById('association-modal');
    const batteryDetailsModal = document.getElementById('battery-details-modal');
    const deleteAssociationModal = document.getElementById('delete-association-modal');
    const associationsTableBody = document.getElementById('associations-table-body');
    
    // Élements de recherche
    const searchMotoInput = document.getElementById('search-moto');
    const searchBatteryInput = document.getElementById('search-battery');
    
    // Listes des éléments sélectionnables
    const motosList = document.getElementById('motos-list');
    const batteriesList = document.getElementById('batteries-list');
    
    // Données en cache
    let cachedMotos = [];
    let cachedBatteries = [];
    let selectedMotoId = null;
    let selectedBatteryId = null;

    // Afficher la date actuelle
    const dateElement = document.getElementById('date');
    const today = new Date();
    dateElement.textContent = today.toLocaleDateString('fr-FR');

    // ------------------------------------------------------------
    // Fonctions de chargement des données
    // ------------------------------------------------------------
    
    // Charger les motos disponibles
    function loadAvailableMotos() {
        fetch('/associations/batteries/motos/available')
            .then(response => response.json())
            .then(result => {
                if (result.data) {
                    cachedMotos = result.data;
                    renderMotosList(result.data);
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des motos:', error);
                showToast('Erreur lors du chargement des motos disponibles.', 'error');
            });
    }
    
    // Charger les batteries disponibles
function loadAvailableBatteries(includeBatteryId = null) {
    let url = '/associations/batteries/available';
    
    // Si nous sommes en mode édition, inclure la batterie actuellement associée
    if (includeBatteryId) {
        url += `?include_battery_id=${includeBatteryId}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(result => {
            if (result.data) {
                cachedBatteries = result.data;
                renderBatteriesList(result.data);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des batteries:', error);
            showToast('Erreur lors du chargement des batteries disponibles.', 'error');
        });
}
    // Charger les données BMS pour toutes les batteries en une seule fois
    function loadAllBatteriesStatus() {
        const batteryElements = document.querySelectorAll('[data-mac-id]');
        const macIds = new Set();
        
        batteryElements.forEach(element => {
            const macId = element.getAttribute('data-mac-id');
            if (macId && macId.trim() !== '') {
                macIds.add(macId);
            }
        });
        
        if (macIds.size === 0) return;
        
        const macIdArray = Array.from(macIds);
        
        // Appel API en lot pour toutes les batteries
        fetch('/associations/batteries/bms/bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ mac_ids: macIdArray })
        })
        .then(response => response.json())
        .then(result => {
            if (result.data) {
                // Mettre à jour tous les états de batterie en une seule fois
                updateBatteriesStatus(result.data);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des statuts de batteries:', error);
        });
    }
    
    // Mettre à jour les états des batteries dans l'interface
    function updateBatteriesStatus(batteriesData) {
        for (const macId in batteriesData) {
            const batteryData = batteriesData[macId];
            
            // Mettre à jour le statut (online/offline)
            const statusElements = document.querySelectorAll(`.battery-status[data-mac-id="${macId}"]`);
            statusElements.forEach(element => {
                element.textContent = batteryData.status;
                element.className = `battery-status ${batteryData.status.toLowerCase()}`;
            });
            
            // Mettre à jour le niveau de batterie
            const levelElements = document.querySelectorAll(`.battery-level[data-mac-id="${macId}"]`);
            levelElements.forEach(element => {
                const levelInner = element.querySelector('.battery-level-inner');
                const levelText = element.querySelector('.battery-level-text');
                
                if (levelInner && levelText) {
                    const socValue = batteryData.soc || 0;
                    levelInner.style.width = `${socValue}%`;
                    levelText.textContent = `${Math.round(socValue)}%`;
                    
                    // Appliquer la classe en fonction du niveau
                    element.className = 'battery-level';
                    if (socValue < 20) {
                        element.classList.add('battery-low');
                    } else if (socValue < 50) {
                        element.classList.add('battery-medium');
                    } else {
                        element.classList.add('battery-high');
                    }
                }
            });
        }
    }

    // ------------------------------------------------------------
    // Fonctions de rendu
    // ------------------------------------------------------------
    
    // Afficher la liste des motos
    function renderMotosList(motos) {
        motosList.innerHTML = '';
        
        if (motos.length === 0) {
            motosList.innerHTML = '<div class="selection-item">Aucune moto disponible</div>';
            return;
        }
        
        motos.forEach(moto => {
            const motoItem = document.createElement('div');
            motoItem.className = 'selection-item';
            if (selectedMotoId === moto.moto_unique_id) {
                motoItem.classList.add('selected');
            }
            
            motoItem.innerHTML = `
                <input type="radio" name="moto_unique_id" id="moto-${moto.moto_unique_id}" 
                    value="${moto.moto_unique_id}" ${selectedMotoId === moto.moto_unique_id ? 'checked' : ''}>
                <div class="selection-item-details">
                    <div class="selection-item-title">${moto.model} (${moto.moto_unique_id})</div>
                    <div class="selection-item-subtitle">
                        VIN: ${moto.vin || 'N/A'}
                        ${moto.user ? `| Utilisateur: ${moto.user.nom} ${moto.user.prenom}` : ''}
                    </div>
                </div>
            `;
            
            motoItem.addEventListener('click', function() {
                document.querySelectorAll('#motos-list .selection-item').forEach(item => {
                    item.classList.remove('selected');
                });
                
                motoItem.classList.add('selected');
                const radio = motoItem.querySelector('input[type="radio"]');
                radio.checked = true;
                selectedMotoId = moto.moto_unique_id;
            });
            
            motosList.appendChild(motoItem);
        });
    }
    
    // Afficher la liste des batteries
    function renderBatteriesList(batteries) {
        batteriesList.innerHTML = '';
        
        if (batteries.length === 0) {
            batteriesList.innerHTML = '<div class="selection-item">Aucune batterie disponible</div>';
            return;
        }
        
        batteries.forEach(battery => {
            const batteryItem = document.createElement('div');
            batteryItem.className = 'selection-item';
            if (selectedBatteryId === battery.batterie_unique_id) {
                batteryItem.classList.add('selected');
            }
            
            // Déterminer la classe pour le niveau de batterie
            let batteryLevelClass = 'battery-high';
            if (battery.pourcentage < 20) {
                batteryLevelClass = 'battery-low';
            } else if (battery.pourcentage < 50) {
                batteryLevelClass = 'battery-medium';
            }
            
            batteryItem.innerHTML = `
                <input type="radio" name="battery_unique_id" id="battery-${battery.batterie_unique_id}" 
                    value="${battery.batterie_unique_id}" ${selectedBatteryId === battery.batterie_unique_id ? 'checked' : ''}>
                <div class="selection-item-details">
                    <div class="selection-item-title">Batterie ${battery.batterie_unique_id}</div>
                    <div class="selection-item-subtitle">MAC: ${battery.mac_id || 'N/A'}</div>
                    <div class="battery-info">
                        <span class="battery-status ${battery.status}">${battery.status}</span>
                        <div class="battery-level ${batteryLevelClass}" style="margin-left: 10px;">
                            <div class="battery-level-inner" style="width: ${battery.pourcentage}%"></div>
                            <div class="battery-level-text">${Math.round(battery.pourcentage)}%</div>
                        </div>
                    </div>
                </div>
            `;
            
            batteryItem.addEventListener('click', function() {
                document.querySelectorAll('#batteries-list .selection-item').forEach(item => {
                    item.classList.remove('selected');
                });
                
                batteryItem.classList.add('selected');
                const radio = batteryItem.querySelector('input[type="radio"]');
                radio.checked = true;
                selectedBatteryId = battery.batterie_unique_id;
            });
            
            batteriesList.appendChild(batteryItem);
        });
    }
    
    // Filtrer les motos dans la liste
    function filterMotosList(searchTerm) {
        searchTerm = searchTerm.toLowerCase();
        
        if (!cachedMotos.length) return;
        
        const filteredMotos = cachedMotos.filter(moto => {
            return moto.moto_unique_id.toLowerCase().includes(searchTerm) || 
                   moto.model.toLowerCase().includes(searchTerm) ||
                   (moto.vin && moto.vin.toLowerCase().includes(searchTerm)) ||
                   (moto.user && (`${moto.user.nom} ${moto.user.prenom}`).toLowerCase().includes(searchTerm));
        });
        
        renderMotosList(filteredMotos);
    }
    
    // Filtrer les batteries dans la liste
    function filterBatteriesList(searchTerm) {
        searchTerm = searchTerm.toLowerCase();
        
        if (!cachedBatteries.length) return;
        
        const filteredBatteries = cachedBatteries.filter(battery => {
            return battery.batterie_unique_id.toLowerCase().includes(searchTerm) || 
                   (battery.mac_id && battery.mac_id.toLowerCase().includes(searchTerm));
        });
        
        renderBatteriesList(filteredBatteries);
    }
    
    // Filtrer le tableau principal
    function filterAssociationsTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = associationsTableBody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            row.style.display = textContent.includes(searchTerm) ? '' : 'none';
        });
    }

    // ------------------------------------------------------------
    // Fonctions de gestion des modales
    // ------------------------------------------------------------
    
    // Ouvrir la modale d'ajout d'association
    function openAddAssociationModal() {
        selectedMotoId = null;
        selectedBatteryId = null;
        
        document.getElementById('modal-title').textContent = 'Associer Batterie & Moto';
        document.getElementById('association-id').value = '';
        document.getElementById('form-method').value = 'POST';
        
        // Charger les données fraîches
        loadAvailableMotos();
        loadAvailableBatteries();
        
        associationModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
   // Ouvrir la modale d'édition d'association
function openEditAssociationModal(row) {
    const id = row.dataset.id;
    
    // Récupérer les détails de l'association
    fetch(`/associations/batteries/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modal-title').textContent = 'Modifier l\'association';
            document.getElementById('association-id').value = id;
            document.getElementById('form-method').value = 'PUT';
            
            selectedMotoId = data.moto_unique_id;
            selectedBatteryId = data.battery_unique_id;
            

            
            // Charger les motos disponibles
            console.log("Battery ID reçu:", data.battery_id);
            loadAvailableMotos();
            
            // Charger les batteries disponibles, y compris la batterie actuellement associée
           // Corrigé
if (data.battery_id) {
    loadAvailableBatteries(data.battery_id);
} else {
    loadAvailableBatteries(); // sans paramètre
}


            
            associationModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Erreur lors du chargement des détails de l\'association:', error);
            showToast('Erreur lors du chargement des détails de l\'association.', 'error');
        });
}
    
    // Ouvrir la modale de détails de la batterie
    function openBatteryDetailsModal(macId) {
        if (!macId) {
            showToast('Cette batterie n\'a pas d\'identifiant MAC.', 'error');
            return;
        }
        
        const container = document.getElementById('battery-info-container');
        container.innerHTML = '<div class="loading">Chargement des données...</div>';
        
        batteryDetailsModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Charger les détails BMS de la batterie
        fetch(`/associations/batteries/bms/${macId}`)
            .then(response => response.json())
            .then(data => {
                renderBatteryDetails(container, data);
            })
            .catch(error => {
                console.error('Erreur lors du chargement des détails de la batterie:', error);
                container.innerHTML = '<div class="error">Erreur lors du chargement des données. La batterie est peut-être hors ligne.</div>';
            });
    }
    
    // Ouvrir la modale de suppression d'association
    function openDeleteAssociationModal(row) {
        const id = row.dataset.id;
        const user = row.cells[0].textContent;
        const moto = row.cells[1].textContent;
        const battery = row.cells[2].textContent;
        
        document.getElementById('delete-user-name').textContent = user;
        document.getElementById('delete-moto-id').textContent = moto;
        document.getElementById('delete-battery-id').textContent = battery;
        
        const form = document.getElementById('delete-association-form');
        form.action = `/associations/batteries/${id}`;
        
        deleteAssociationModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Fermer toutes les modales
    function closeAllModals() {
        [associationModal, batteryDetailsModal, deleteAssociationModal].forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
    
    // Rendre les détails de la batterie
    function renderBatteryDetails(container, data) {
        // Formater les données pour l'affichage
        const batteryLevelClass = data.soc < 20 ? 'battery-low' : (data.soc < 50 ? 'battery-medium' : 'battery-high');
        
        container.innerHTML = `
            <div class="battery-details">
                <div class="battery-details-header">
                    <h3>Batterie ${data.id}</h3>
                    <div class="battery-status ${data.status.toLowerCase()}">${data.status}</div>
                </div>
                
                <div class="battery-details-grid">
                    <div class="battery-details-card">
                        <h4>Informations Générales</h4>
                        <table class="battery-info-table">
                            <tr>
                                <td>MAC ID:</td>
                                <td>${data.mac_id}</td>
                            </tr>
                            <tr>
                                <td>État de charge:</td>
                                <td>
                                    <div class="battery-level ${batteryLevelClass}">
                                        <div class="battery-level-inner" style="width: ${data.soc}%"></div>
                                        <div class="battery-level-text">${Math.round(data.soc)}%</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Tension:</td>
                                <td>${data.voltage} V</td>
                            </tr>
                            <tr>
                                <td>Courant:</td>
                                <td>${data.current} A</td>
                            </tr>
                            <tr>
                                <td>Cycles:</td>
                                <td>${data.cycles}</td>
                            </tr>
                            <tr>
                                <td>État de fonctionnement:</td>
                                <td>${data.batteryStatus}</td>
                            </tr>
                            <tr>
                                <td>Dernière mise à jour:</td>
                                <td>${data.updatedAt}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="battery-details-card">
                        <h4>Graphiques</h4>
                        <div class="battery-chart" id="battery-soc-chart"></div>
                    </div>
                </div>
                
                <div class="battery-cell-voltages">
                    <h4>Tensions des cellules</h4>
                    <div class="cell-voltages-grid">
                        ${data.cellVoltages.map(cell => `
                            <div class="cell-voltage-item cell-${cell.status}">
                                <div class="cell-number">Cellule ${cell.number}</div>
                                <div class="cell-value">${cell.voltage} V</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
        
        // Initialiser les graphiques si nécessaire
        if (data.chartData && data.chartData.timeLabels.length > 0) {
            setTimeout(() => {
                initBatteryChart(data.chartData);
            }, 100);
        }
    }
    
    // Initialiser le graphique de la batterie
    function initBatteryChart(chartData) {
        // Cette fonction pourrait être implémentée avec Chart.js ou une autre bibliothèque
        // Pour simplifier, on laisse cette partie en commentaire
        console.log('Données pour le graphique:', chartData);
    }

    // ------------------------------------------------------------
    // Fonctions de soumission de formulaires
    // ------------------------------------------------------------
    
    // Enregistrer ou mettre à jour une association
    function saveAssociation() {
        const isEdit = document.getElementById('form-method').value === 'PUT';
        const associationId = document.getElementById('association-id').value;
        
        if (!selectedMotoId) {
            showToast('Veuillez sélectionner une moto.', 'error');
            return;
        }
        
        if (!selectedBatteryId) {
            showToast('Veuillez sélectionner une batterie.', 'error');
            return;
        }
        
        const data = {
            moto_unique_id: selectedMotoId,
            battery_unique_id: selectedBatteryId,
            _token: csrfToken
        };
        
        const url = isEdit ? `/associations/batteries/${associationId}` : '/associations/batteries';
        const method = isEdit ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                closeAllModals();
                
                // Recharger la page pour afficher les changements
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur lors de l\'enregistrement de l\'association:', error);
            showToast('Erreur lors de l\'enregistrement de l\'association.', 'error');
        });
    }
    
    // Supprimer une association
    function deleteAssociation() {
        const form = document.getElementById('delete-association-form');
        const url = form.action;
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                closeAllModals();
                
                // Recharger la page pour afficher les changements
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur lors de la suppression de l\'association:', error);
            showToast('Erreur lors de la suppression de l\'association.', 'error');
        });
    }

    // ------------------------------------------------------------
    // Fonctions utilitaires
    // ------------------------------------------------------------
    
    // Afficher un message toast
    function showToast(message, type = 'info') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ------------------------------------------------------------
    // Gestion des messages de session Laravel
    // ------------------------------------------------------------
    @if(session('success'))
    showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
    showToast("{{ session('error') }}", 'error');
    @endif

    // ------------------------------------------------------------
    // Attacher les événements
    // ------------------------------------------------------------
    
    // Événements pour la barre de recherche principale
    searchInput.addEventListener('input', filterAssociationsTable);
    
    // Événements pour les recherches dans les modales
    searchMotoInput.addEventListener('input', function() {
        filterMotosList(this.value);
    });
    
    searchBatteryInput.addEventListener('input', function() {
        filterBatteriesList(this.value);
    });
    
    // Événements pour les boutons d'action
    addAssociationBtn.addEventListener('click', openAddAssociationModal);
    
    document.getElementById('save-association').addEventListener('click', saveAssociation);
    document.getElementById('confirm-delete-association').addEventListener('click', deleteAssociation);
    
    // Événements pour les actions du tableau
    document.querySelectorAll('.edit-association').forEach(btn => {
        btn.addEventListener('click', function() {
            openEditAssociationModal(this.closest('tr'));
        });
    });
    
    document.querySelectorAll('.delete-association').forEach(btn => {
        btn.addEventListener('click', function() {
            openDeleteAssociationModal(this.closest('tr'));
        });
    });
    
    document.querySelectorAll('.view-association').forEach(btn => {
        btn.addEventListener('click', function() {
            const macId = this.getAttribute('data-mac-id');
            openBatteryDetailsModal(macId);
        });
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
    
    // Événements pour les onglets
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });
    });
    // ------------------------------------------------------------
    // Initialisation
    // ------------------------------------------------------------
    
    // Charger les statuts de batterie pour les éléments du tableau
    loadAllBatteriesStatus();
    
    // Rafraîchir les statuts toutes les 30 secondes
    setInterval(loadAllBatteriesStatus, 30000);
});
</script>
@endsection