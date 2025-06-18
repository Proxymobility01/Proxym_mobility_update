<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Proxym Mobility</title>
    <link href="https://fonts.cdnfonts.com/css/metropolis-2" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">


    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="assets/css/styles.css">
    
    

</head>

<body>

    <!-- ========================= Main ==================== -->
    <div class="main">
        <!-- Inclure l'en-tête -->
        @include('layouts.partials.sidebar')
        
        <!-- Le contenu spécifique de chaque page sera injecté ici -->
        @yield('content')
        
        @yield('modals')

    </div>
    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>
    

</body>

</html>