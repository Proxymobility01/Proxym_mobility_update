<!DOCTYPE html>
<html>
<head>
    <title>Connexion Employé</title>
</head>
<body>
    <h2>Connexion Employé</h2>

    <form method="POST" action="{{ route('employe.authenticate') }}">
        @csrf
        <label>Email :</label><br>
        <input type="email" name="email" required><br><br>

        <label>Mot de passe :</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Se connecter</button>
    </form>

    @if ($errors->any())
        <div style="color: red;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
</body>
</html>
