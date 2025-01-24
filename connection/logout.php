<?php
    require '../inc/pdo.php';
    require '../inc/functions/token_function.php';
    session_start();

    // Permet d'interdire l'accès à logout si l'utilisateur ne s'est jamais connecté
    if(!isset($_SESSION['id'])){
        header('Location: ./login.php');
    }else{
        $id = $_SESSION['id'];
    }

    $logout = $website_pdo->prepare("
        UPDATE token SET token = :token WHERE token.user_id = :user
    ");

    $logout->execute([
        ":token" => 'null',
        ":user" => $id
    ]);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="5;url=./login.php">
    <link href="../assets/css/logout.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/font.css">
    <title>OIKOS | Déconnexion</title>
</head>
<body>
    <div class='logo'><h1>OIKOS</h1></div> 
    <div class="logout-container">
        <div class="logout-container-title"><p>Vous avez été déconnecté.</p></div>
        <div class="thanks"><p>Merci de votre visite et à bientôt sur Oikos.</p></div>
        <div class="redirection"><p>Vous allez être redirigé dans <span id="countdown">5</span> secondes...</p></div>
    </div>
    <div class="background-img"></div>
    <div class="copyright"><p>&copy; OIKOS - 2023</p></div>
    <script src="../assets/js/connection_js/logout.js"></script>
</body>
</html>

<?php
    session_destroy();
    exit();
?>