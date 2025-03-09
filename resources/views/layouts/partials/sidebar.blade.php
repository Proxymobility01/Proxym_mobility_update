<button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
    <i class="fas fa-bars"></i>
</button>
<div class="overlay" onclick="toggleMobileMenu()"></div>

<div class="sidebar">
    <button class="toggle-sidebar" onclick="toggleSidebar()">
        <i class="fas fa-chevron-left"></i>
    </button>

    <div class="logo-container">
        <i class="fas fa-bolt logo-icon"></i>
        <span class="logo-text">Proxym Mobility</span>
    </div>

    <div class="nav-item active">
        <i class="fas fa-home"></i>
        <span>Tableau de bord</span>
    </div>
    <a href="{{ route('motos.index') }}">
    <div class="nav-item">
        <i class="fas fa-motorcycle"></i>
        <span>Gestion Motos</span>
    </div>
    </a>
    <a href="{{ route('batteries.index') }}">
        <div class="nav-item">
            <i class="fas fa-battery-three-quarters"></i>
            <span>Gestion Batteries</span>
        </div>
    </a>
    <div class="nav-item">
        <i class="fas fa-users"></i>
        <span>Utilisateurs</span>
    </div>
    <div class="nav-item">
        <i class="fas fa-wrench"></i>
        <span>Maintenance</span>
    </div>
    <div class="nav-item">
        <i class="fas fa-chart-line"></i>
        <span>Rapports</span>
    </div>
    <div class="nav-item">
        <i class="fas fa-cog"></i>
        <span>Paramètres</span>
    </div>
</div>