@extends('layouts.app')



 <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="assets/css/styles.css">
    
<style>
/* ===============================
   Styles hérités de la vue batteries principale
   =============================== */
.main-content {
    padding: 20px;
    background-color: #f8f9fa;
    min-height: 100vh;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.content-header h2 {
    color: #2c3e50;
    font-weight: 600;
    margin: 0;
}

.date {
    color: #6c757d;
    font-size: 14px;
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

/* ===============================
   Barre de recherche et filtres modernisée
   =============================== */
.search-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.search-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 300px;
}

.search-group input {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.search-group input:focus {
    outline: none;
    border-color: #DCDB32;
    box-shadow: 0 0 0 3px rgba(220, 219, 50, 0.1);
}

.search-btn {
    padding: 12px 15px;
    background-color: #DCDB32;
    color: #101010;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-btn:hover {
    background-color: #c7c62d;
    transform: translateY(-1px);
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.select-status {
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    background-color: white;
    min-width: 200px;
    transition: border-color 0.2s ease;
}

.select-status:focus {
    outline: none;
    border-color: #DCDB32;
    box-shadow: 0 0 0 3px rgba(220, 219, 50, 0.1);
}

.add-btn {
    padding: 12px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.add-btn:hover {
    background-color: #218838;
    transform: translateY(-1px);
}

/* ===============================
   Cartes de statistiques 
   =============================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border-left: 4px solid;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-card.total {
    border-left-color: #3498db;
}

.stat-card.pending {
    border-left-color: #ffc107;
}

.stat-card.success {
    border-left-color: #28a745;
}

.stat-card.danger {
    border-left-color: #dc3545;
}

.stat-card.agency {
    border-left-color: #2ecc71;
}

.stat-card.charged {
    border-left-color: #27ae60;
}

.stat-card.discharged {
    border-left-color: #e74c3c;
}

.stat-card.medium {
    border-left-color: #17a2b8;
}

.stat-card .stat-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 24px;
    opacity: 0.3;
}

.stat-card.total .stat-icon {
    color: #3498db;
}

.stat-card.pending .stat-icon {
    color: #ffc107;
}

.stat-card.success .stat-icon {
    color: #28a745;
}

.stat-card.danger .stat-icon {
    color: #dc3545;
}

.stat-card.agency .stat-icon {
    color: #2ecc71;
}

.stat-card.charged .stat-icon {
    color: #27ae60;
}

.stat-card.discharged .stat-icon {
    color: #e74c3c;
}

.stat-card.medium .stat-icon {
    color: #17a2b8;
}

.stat-details {
    position: relative;
    z-index: 2;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
    line-height: 1;
}

.stat-label {
    font-size: 16px;
    font-weight: 600;
    color: #34495e;
    margin-bottom: 5px;
}

.stat-text {
    font-size: 12px;
    color: #7f8c8d;
}

/* ===============================
   Tableau modernisé
   =============================== */
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.head-table {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    background-color: #f8f9fa;
}

.head-table h2 {
    color: #2c3e50;
    font-weight: 600;
    margin: 0;
}

.head-table a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    background-color: #DCDB32;
    color: #101010;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s ease;
    margin-left: 10px;
}

.head-table a:hover {
    background-color: #c7c62d;
    transform: translateY(-1px);
}

#batteries-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin: 0;
}

#batteries-table thead th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 18px 15px;
    border: none;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#batteries-table tbody td {
    padding: 15px;
    border-top: 1px solid #e9ecef;
    vertical-align: middle;
    font-size: 14px;
}

#batteries-table tbody tr:hover {
    background-color: #f8f9fa;
  
}
#batteries-table th,
#batteries-table td {
    text-align: center;
    vertical-align: middle;
}

/* ===============================
   Badges et éléments visuels
   =============================== */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.bg-primary {
    background-color: #007bff !important;
    color: white;
}

.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
}

.bg-info {
    background-color: #17a2b8 !important;
    color: white;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: 600;
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

/* ===============================
   Boutons d'action
   =============================== */
.action-btn {
    padding: 8px 10px;
    margin-right: 5px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.action-btn.edit-batterie {
    background-color: #17a2b8;
    color: white;
}

.action-btn.edit-batterie:hover {
    background-color: #138496;
}

.action-btn.validate-batterie {
    background-color: #28a745;
    color: white;
}

.action-btn.validate-batterie:hover {
    background-color: #218838;
}

.action-btn.delete-batterie {
    background-color: #dc3545;
    color: white;
}

.action-btn.delete-batterie:hover {
    background-color: #c82333;
}

.action-btn.bms {
    background-color: #6f42c1;
    color: white;
}

.action-btn.bms:hover {
    background-color: #5a32a3;
}

/* ===============================
   Barre de progression pour SOC
   =============================== */
.battery-level {
    width: 60px;
    height: 10px;
    background-color: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
    display: inline-block;
    margin-right: 10px;
    vertical-align: middle;
}

.battery-fill {
    height: 100%;
    background: linear-gradient(90deg, #dc3545 0%, #ffc107 30%, #28a745 70%);
    transition: width 0.3s ease;
}

/* ===============================
   Messages toast
   =============================== */
.toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background-color: #DCDB32;
    color: #101010;
    padding: 12px 20px;
    border-radius: 8px;
    margin-top: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: opacity 0.3s ease;
}

.toast.toast-error {
    background-color: #dc3545;
    color: white;
}

.toast.toast-success {
    background-color: #28a745;
    color: white;
}

/* ===============================
   Responsive
   =============================== */
@media (max-width: 768px) {
    .search-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-group {
        min-width: auto;
    }
    
    .filter-group {
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .head-table {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .head-table a {
        margin-left: 0;
        justify-content: center;
    }
}

@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
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
    width: 60px;
    height: 10px;
    background-color: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
    display: inline-block;
    margin-right: 10px;
    vertical-align: middle;
}

.battery-fill {
    height: 100%;
    background: linear-gradient(90deg, #dc3545 0%, #ffc107 30%, #28a745 70%);
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



@section('content')

<div class="main-content">

 <!-- Onglets de navigation -->
    <!-- Onglets de navigation -->
<div class="nav-tabs">
    <div class="nav-tab {{ request()->is('batteries') ? 'active' : '' }}" data-url="{{ route('batteries.index') }}">
        Batteries Validées
    </div>
    <div class="nav-tab {{ request()->is('batteries/station*') ? 'active' : '' }}" data-url="{{ route('batteries.station.index') }}">
        Gestion Batteries Stations
    </div>
   
</div>

    <!-- En-tête -->
    <div class="content-header">
        <h1>Gestion des Batteries de Station</h1>
        <div class="date" id="date"></div>
    </div>

    <!-- Cartes des statistiques -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="total-batteries">{{ $totalBatteries }}</div>
                <div class="stat-label">Batteries Totales</div>
                <div class="stat-text">Toutes les batteries enregistrées</div>
            </div>
        </div>

        <div class="stat-card agency">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="agency-batteries">{{ $batteriesEnAgence }}</div>
                <div class="stat-label">En Agence</div>
                <div class="stat-text">Batteries affectées à une agence</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="charging-batteries">{{ $batteriesEnCharge }}</div>
                <div class="stat-label">En Charge</div>
                <div class="stat-text">Batteries en cours de charge</div>
            </div>
        </div>

        <div class="stat-card charged">
            <div class="stat-icon">
                <i class="fas fa-battery-full"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="charged-batteries">{{ $batteriesChargees }}</div>
                <div class="stat-label">Chargées > 95%</div>
                <div class="stat-text">Prêtes à l'utilisation</div>
            </div>
        </div>

        <div class="stat-card discharged">
            <div class="stat-icon">
                <i class="fas fa-battery-empty"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="discharged-batteries">{{ $batteriesDechargees }}</div>
                <div class="stat-label">Déchargées < 30%</div>
                <div class="stat-text">Nécessitent une recharge</div>
            </div>
        </div>

        <div class="stat-card medium">
            <div class="stat-icon">
                <i class="fas fa-battery-half"></i>
            </div>
            <div class="stat-details">
                <div class="stat-number" id="medium-batteries">{{ $batteriesEntre50Et80 }}</div>
                <div class="stat-label">SOC 50-80%</div>
                <div class="stat-text">Charge intermédiaire</div>
            </div>
        </div>
    </div>

     <!-- Barre de recherche et filtres -->
    <div class="search-bar">
        <div class="search-group">
            <input type="text" id="search-batterie" placeholder="Rechercher MAC ID / ID batterie..." value="{{ $search }}">
            <button type="button" class="search-btn" id="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <div class="filter-group">
            <select id="agence-filter" class="select-status">
                <option value="">-- Toutes les agences --</option>
                @foreach ($agences as $agence)
                    <option value="{{ $agence->id }}" {{ $selectedAgence == $agence->id ? 'selected' : '' }}>
                        {{ $agence->nom_agence }}
                    </option>
                @endforeach
            </select>
            
            <select id="location-filter" class="select-status">
                <option value="">-- Toutes les batteries --</option>
                <option value="1" {{ $enAgence === "1" ? 'selected' : '' }}>En agence</option>
                <option value="0" {{ $enAgence === "0" ? 'selected' : '' }}>Hors agence</option>
            </select>
            
            <select id="status-filter" class="select-status">
                <option value="">-- Filtrer par état --</option>
                <option value="en_charge" {{ $filtre == 'en_charge' ? 'selected' : '' }}>En charge</option>
                <option value="decharge_faible" {{ $filtre == 'decharge_faible' ? 'selected' : '' }}>Décharge faible (&lt; 30%)</option>
                <option value="en_veille" {{ $filtre == 'en_veille' ? 'selected' : '' }}>En veille</option>
                <option value="en_ligne" {{ $filtre == 'en_ligne' ? 'selected' : '' }}>En ligne</option>
                <option value="hors_ligne" {{ $filtre == 'hors_ligne' ? 'selected' : '' }}>Hors ligne</option>
            </select>
            
            <button id="reset-filters" class="add-btn" style="background-color: #6c757d;">
                <i class="fas fa-undo"></i> Réinitialiser
            </button>
        </div>
    </div>


     <!-- Boutons d'export -->
    <div class="export-buttons">
        <button id="exportExcel" class="export-btn btn-excel">
            <i class="fas fa-file-excel"></i>
            Exporter Excel
        </button>
        <button id="exportPDF" class="export-btn btn-pdf">
            <i class="fas fa-file-pdf"></i>
            Exporter PDF
        </button>
        <button id="exportCSV" class="export-btn btn-csv">
            <i class="fas fa-file-csv"></i>
            Exporter CSV
        </button>
    </div>

    <!-- Tableau des batteries -->
    <div class="table-container">
        <div class="head-table">
            <h2>Liste des Batteries de Station</h2>
            <div>
                <a href="{{ route('bms.index') }}">
                    <i class="fas fa-chart-line"></i>
                    Voir les Details du BMS
                </a>
                <a href="{{ route('batteries.map') }}">
                    <i class="fas fa-map-marker-alt"></i>
                    Voir sur la carte
                </a>
            </div>
        </div>
        
        <table id="batteries-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Identifiant</th>
                    <th>MAC ID</th>
                    <th>Fabriquant</th>
                    <th>SOC (%)</th>
                    <th>Statut</th>
                    <th>Connexion</th>
                    <th>Agence</th>
                </tr>
            </thead>
            <tbody id="batteries-table-body">
                @forelse ($batteries as $index => $battery)
                    <tr data-id="{{ $battery['id'] }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $battery['batterie_unique_id'] }}</strong>
                        </td>
                        <td>
                            <code>{{ $battery['mac_id'] }}</code>
                        </td>
                        <td>{{ $battery['fabriquant'] }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="battery-level">
                                    <div class="battery-fill" style="width: {{ $battery['soc'] }}%;"></div>
                                </div>
                                <span style="font-weight: 600; color: 
                                    @if($battery['soc'] > 75) #28a745
                                    @elseif($battery['soc'] > 50) #856404
                                    @elseif($battery['soc'] > 25) #bd2130
                                    @else #dc3545
                                    @endif;">{{ $battery['soc'] }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge @if($battery['status'] == 'En charge') bg-primary
                                @elseif($battery['status'] == 'En décharge') bg-warning
                                @elseif($battery['status'] == 'En veille') bg-info
                                @else bg-danger
                                @endif">
                                {{ $battery['status'] }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $battery['online_status'] === 'En ligne' ? 'bg-success' : 'bg-danger' }}">
                                <i class="fas {{ $battery['online_status'] === 'En ligne' ? 'fa-wifi' : 'fa-times' }}"></i>
                                {{ $battery['online_status'] }}
                            </span>
                        </td>
                        <td>
                            @if($battery['agence'] !== 'Non affectée')
                                <i class="fas fa-building text-success"></i>
                                {{ $battery['agence'] }}
                            @else
                                <span class="text-muted">
                                    <i class="fas fa-minus-circle"></i>
                                    Non affectée
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 40px;">
                            <i class="fas fa-battery-empty text-muted" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <p class="text-muted mb-0">Aucune batterie trouvée avec les critères sélectionnés.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Affichage de la date
    document.getElementById('date').textContent = new Date().toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });

    // Références DOM
    const searchInput = document.getElementById('search-batterie');
    const agenceFilter = document.getElementById('agence-filter');
    const locationFilter = document.getElementById('location-filter');
    const statusFilter = document.getElementById('status-filter');
    const resetButton = document.getElementById('reset-filters');
    const searchButton = document.getElementById('search-btn');
    const batteriesTableBody = document.getElementById('batteries-table-body');

    // Fonction pour construire l'URL avec les paramètres
    function buildFilterURL() {
        const params = new URLSearchParams();
        
        if (searchInput.value.trim()) {
            params.append('search', searchInput.value.trim());
        }
        
        if (agenceFilter.value) {
            params.append('agence_id', agenceFilter.value);
        }
        
        if (locationFilter.value) {
            params.append('en_agence', locationFilter.value);
        }
        
        if (statusFilter.value) {
            params.append('filtre', statusFilter.value);
        }

        return `{{ route('batteries.station.index') }}${params.toString() ? '?' + params.toString() : ''}`;
    }

    // Fonction pour appliquer les filtres
    function applyFilters() {
        window.location.href = buildFilterURL();
    }

    // Fonction pour charger les batteries via AJAX (optionnel pour une mise à jour dynamique)
    function loadBatteriesAjax() {
        const url = buildFilterURL();
        
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.json();
        })
        .then(data => {
            renderBatteries(data.batteries);
            updateStats(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors du chargement des batteries', 'error');
        });
    }

    // Afficher les batteries dans le tableau (pour AJAX)
    function renderBatteries(batteries) {
        batteriesTableBody.innerHTML = '';
        
        if (batteries.length === 0) {
            batteriesTableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center" style="padding: 40px;">
                        <i class="fas fa-battery-empty text-muted" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <p class="text-muted mb-0">Aucune batterie trouvée avec les critères sélectionnés.</p>
                    </td>
                </tr>
            `;
            return;
        }

        batteries.forEach((battery, index) => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', battery.id);
            
            let socColor = '#dc3545';
            if (battery.soc > 75) socColor = '#28a745';
            else if (battery.soc > 50) socColor = '#856404';
            else if (battery.soc > 25) socColor = '#bd2130';

            let statusClass = 'bg-danger';
            if (battery.status === 'En charge') statusClass = 'bg-primary';
            else if (battery.status === 'En décharge') statusClass = 'bg-warning';
            else if (battery.status === 'En veille') statusClass = 'bg-info';

            row.innerHTML = `
                <td>${index + 1}</td>
                <td><strong>${battery.batterie_unique_id}</strong></td>
                <td><code>${battery.mac_id}</code></td>
                <td>${battery.fabriquant}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="battery-level">
                            <div class="battery-fill" style="width: ${battery.soc}%;"></div>
                        </div>
                        <span style="font-weight: 600; color: ${socColor};">${battery.soc}%</span>
                    </div>
                </td>
                <td>
                    <span class="badge ${statusClass}">
                        ${battery.status}
                    </span>
                </td>
                <td>
                    <span class="badge ${battery.online_status === 'En ligne' ? 'bg-success' : 'bg-danger'}">
                        <i class="fas ${battery.online_status === 'En ligne' ? 'fa-wifi' : 'fa-times'}"></i>
                        ${battery.online_status}
                    </span>
                </td>
                <td>
                    ${battery.agence !== 'Non affectée' ? 
                        `<i class="fas fa-building text-success"></i> ${battery.agence}` : 
                        `<span class="text-muted"><i class="fas fa-minus-circle"></i> Non affectée</span>`
                    }
                </td>
            `;
            
            batteriesTableBody.appendChild(row);
        });
    }

    // Mise à jour des statistiques (pour AJAX)
    function updateStats(data) {
        document.getElementById('total-batteries').textContent = data.totalBatteries || 0;
        document.getElementById('agency-batteries').textContent = data.batteriesEnAgence || 0;
        document.getElementById('charging-batteries').textContent = data.batteriesEnCharge || 0;
        document.getElementById('charged-batteries').textContent = data.batteriesChargees || 0;
        document.getElementById('discharged-batteries').textContent = data.batteriesDechargees || 0;
        document.getElementById('medium-batteries').textContent = data.batteriesEntre50Et80 || 0;
    }

    // Fonction pour afficher un toast
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

    // Événements pour les filtres
    searchButton.addEventListener('click', applyFilters);
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });

    agenceFilter.addEventListener('change', applyFilters);
    locationFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    // Bouton de réinitialisation
    resetButton.addEventListener('click', function() {
        window.location.href = '{{ route('batteries.station.index') }}';
    });

    // Auto-filtrage en temps réel (optionnel - commenté par défaut)
    /*
    searchInput.addEventListener('input', function() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            loadBatteriesAjax();
        }, 500);
    });
    */
});


  // Navigation par onglets
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });
    });
</script>


<!-- A insérer dans la vue Blade à la fin du fichier -->

<!-- Ajoute les bibliothèques SheetJS + jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

<script>
function getFilteredTableData() {
    const rows = document.querySelectorAll('#batteries-table tbody tr');
    const data = [];

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 1) {
            data.push([
                cells[0].textContent.trim(),
                cells[1].textContent.trim(),
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim(),
                cells[5].textContent.trim(),
                cells[6].textContent.trim(),
                cells[7].textContent.trim()
            ]);
        }
    });

    return data;
}

// CSV EXPORT

document.getElementById('exportCSV').addEventListener('click', () => {
    const data = getFilteredTableData();
    let csvContent = "\uFEFF";
    csvContent += "#\tIdentifiant\tMAC ID\tFabriquant\tSOC %\tStatut\tConnexion\tAgence\n";

    data.forEach(row => {
        csvContent += row.join("\t") + "\n";
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "batteries_station_filtrees.csv";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});

// Excel EXPORT

document.getElementById('exportExcel').addEventListener('click', () => {
    const headers = ["#", "Identifiant", "MAC ID", "Fabriquant", "SOC %", "Statut", "Connexion", "Agence"];
    const data = getFilteredTableData();
    const worksheetData = [headers, ...data];

    const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Batteries");

    XLSX.writeFile(workbook, "batteries_station_filtrees.xlsx");
});

// PDF EXPORT

document.getElementById('exportPDF').addEventListener('click', () => {
    const headers = ["#", "Identifiant", "MAC ID", "Fabriquant", "SOC %", "Statut", "Connexion", "Agence"];
    const data = getFilteredTableData();
    const doc = new jspdf.jsPDF();

    doc.setFontSize(14);
    doc.text("Batteries de Station filtrées", 14, 15);

    doc.autoTable({
        startY: 20,
        head: [headers],
        body: data,
        styles: {
            font: 'helvetica',
            fontSize: 9,
            cellPadding: 3,
        },
        headStyles: {
            fillColor: [220, 219, 50],
            textColor: 16,
            halign: 'center'
        },
        bodyStyles: {
            halign: 'left'
        }
    });

    doc.save("batteries_station_filtrees.pdf");
});
</script>

@endsection