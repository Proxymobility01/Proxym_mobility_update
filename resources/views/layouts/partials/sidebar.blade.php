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
    <a href="{{ route('dashboard') }}">
        <div class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Tableau de bord</span>
        </div>
    </a>
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
    <a href="{{ route('chauffeurs.index') }}">
        <div class="nav-item">
            <i class="fas fa-users"></i>
            <span>Chaufeurs</span>
        </div>
    </a>
    <a href="{{ route('associations.index') }}">
        <div class="nav-item">
            <i class="fas fa-wrench"></i>
            <span>Associations</span>
        </div>
    </a>
    <a href="{{ route('swaps.index') }}">
        <div class="nav-item">
            <i class="fas fa-sync-alt"></i>
            <span>Gestion des Swaps</span>
        </div>
    </a>
    <a href="{{ route('leases.index') }}">
        <div class="nav-item">
            <i class="fas fa-money-bill-wave"></i>
            <span>Gestion des Leases</span>
        </div>
    </a>
    <a href="#">
        <div class="nav-item">
            <i class="fas fa-chart-line"></i>
            <span>Rapports</span>
        </div>
    </a>
    <a href="{{ route('agences.index') }}">
        <div class="nav-item">
            <i class="fas fa-home"></i>
            <span>Gestion des Entitées</span>
        </div>
    </a>
    <a href="{{ route('employe.index') }}">
        <div class="nav-item">
            <i class="fas fa-users"></i>
            <span>Employés</span>
        </div>
    </a>
    <div class="nav-item">
        <i class="fas fa-cog"></i>
        <span>Paramètres</span>
    </div>
    <div class="nav-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="cursor: pointer;">
    <i class="fas fa-sign-out-alt"></i>
    <span>Déconnexion</span>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</div>

   
</div>