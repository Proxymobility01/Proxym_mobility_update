<!-- Navbar -->
<nav style="background-color: #fff; padding: 10px 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: flex-end; align-items: center; font-family: Arial, sans-serif;">
  
  <!-- Icône de notification -->
  <div style="margin-right: 20px; position: relative; cursor: pointer;">
    <a href="{{ route('notifications.index') }}">
        <span style="font-size: 20px; color: #555;">🔔</span>
        <!-- Badge rouge pour indiquer une notification -->
        <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; font-size: 10px; padding: 2px 5px; border-radius: 50%;">3</span>
    </a>
  </div>

  <!-- Nom de l'utilisateur -->
  <div style="display: flex; align-items: center;">
    <img src="https://ui-avatars.com/api/?name=User&background=ffc107&color=fff&rounded=true&size=32" alt="Avatar" style="border-radius: 50%; margin-right: 8px;">
    <span style="color: #333; font-weight: bold;">Patrick Blondy BIKA SAM</span>
  </div>

</nav>
