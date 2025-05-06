<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proxym Mobility - Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #d4de23;
            --primary-dark: #bbc720;
            --text-dark: #333;
            --text-light: #777;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--bg-color);
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: linear-gradient(135deg, rgba(212, 222, 35, 0.1) 0%, rgba(255, 255, 255, 0.8) 100%);
        }
        
        .container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-text {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 28px;
            color: var(--text-dark);
            letter-spacing: 1px;
        }
        
        .subtitle {
            font-family: Arial, sans-serif;
            color: var(--text-light);
            margin-top: 5px;
            font-size: 14px;
        }
        
        .login-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }
        
        .login-header {
            margin-bottom: 25px;
            text-align: center;
        }
        
        .login-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 20px;
            font-weight: 600;
            color: var(--text-dark);
            letter-spacing: 0.5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-family: Arial, sans-serif;
            font-size: 13px;
            font-weight: bold;
            color: var(--text-light);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(212, 222, 35, 0.2);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: var(--text-dark);
            border: none;
            border-radius: 8px;
            font-family: 'Orbitron', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 6px;
            font-family: Arial, sans-serif;
        }
        
        .footer {
            text-align: center;
            margin-top: 25px;
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: var(--text-light);
        }
        
        .battery-icon {
            width: 50px;
            height: 24px;
            background-color: var(--primary-color);
            border-radius: 3px;
            position: relative;
            margin: 0 auto 15px;
        }
        
        .battery-icon:before {
            content: '';
            position: absolute;
            width: 6px;
            height: 10px;
            background-color: var(--primary-color);
            right: -6px;
            top: 7px;
            border-radius: 0 2px 2px 0;
        }
        
        .stats-bar {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-family: 'Orbitron', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: var(--text-light);
            text-transform: uppercase;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <div class="battery-icon"></div>
            <div class="logo-text">PROXYM MOBILITY</div>
            <div class="subtitle">Solutions de mobilité électrique</div>
        </div>
        
        <div class="login-card">
            <div class="login-header">
                <h1>ACCÈS AU TABLEAU DE BORD</h1>
            </div>
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required autofocus>
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <button type="submit" class="btn-login">Connexion</button>
            </form>
            
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-number">156</div>
                    <div class="stat-label">Motos actives</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">47</div>
                    <div class="stat-label">Batteries en charge</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">23</div>
                    <div class="stat-label">Stations</div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>© 2025 Proxym Mobility · Tous droits réservés</p>
        </div>
    </div>
</body>
</html>