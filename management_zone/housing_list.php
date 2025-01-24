<?php

    session_start();
    require '../inc/pdo.php';
    require '../inc/functions/token_function.php';
    require '../inc/functions/check_existing_user.php';
    require '../inc/functions/booking_function.php';

    if(isset($_SESSION['token'])){
        $check = token_check($_SESSION["token"], $website_pdo, $_SESSION['id']);
        if($check == 'false'){
            header('Location: ../connection/login.php');
            exit();
        }else {
            if ($_SESSION['status'] == 0) {
                header ('Location: ../inc/tpl/inactive_user.html');
                exit(); 
            }
            if ($_SESSION['management_role'] == 0 && $_SESSION['admin_role'] == 0){
                header ('Location: ../public_zone/homepage.php');
                exit();
            }
        }   
    }elseif(!isset($_SESSION['token'])){
        header('Location: ../connection/login.php');
        exit();
    }

    $heart_icon = '../assets/images/heart.svg';
    $menu_icon =   '../assets/images/menu.svg';
    $account_icon = '../assets/images/account.svg';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/font.css">
    <link rel="stylesheet" href="../assets/css/header_gestion.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/housing_list.css">
    <title>Document</title>
</head>
<body>
    <?php require '../inc/tpl/header_gestion.php' ?>

    <div>
        <button id="booking-msg-btn" class="booking-msg-btn">Messagerie</button>
    </div>
    
    <div>
        <button id="housing-create-btn" class="housing-create-btn">Créer un Logement</button>
    </div>

    <div class="container">
        <div class="input-container">
            <input type="text" placeholder="Recherchez un logement par nom ou id" id="input">
            <img src="../assets/images/search.svg" alt="">
        </div>

        <div class="grid">

        </div>
    </div>
    <script src="../assets/js/management_zone/housing_list.js"></script>
    <script src="../assets/js/header_public.js"></script>
</body>
</html>
