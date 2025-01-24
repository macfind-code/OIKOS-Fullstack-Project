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


    if (!isset($_GET['housing_id'])) {
        header('Location: ./housing_list.php');
    } else {
        $housing_id = $_GET['housing_id'];
        $housing_info_request = $website_pdo->prepare('
            SELECT id, title, place, district, number_of_pieces, area, price, description, capacity, type FROM housing
            WHERE id = :id
        ');
        $housing_info_request->execute([
            ':id' => $housing_id
        ]);
        $housing_info_request_result = $housing_info_request->fetch(PDO::FETCH_ASSOC);

        if (!$housing_info_request_result) {
            header('Location: ./housing_list.php');
        } else {
            $housing_id = $housing_info_request_result['id'];
            $housing_title = $housing_info_request_result['title'];
            $housing_place = $housing_info_request_result['place'];
            $housing_district = $housing_info_request_result['district'];
            $housing_number_of_pieces = $housing_info_request_result['number_of_pieces'];
            $housing_area = $housing_info_request_result['area'];
            $housing_price = $housing_info_request_result['price'];
            $housing_description = $housing_info_request_result['description'];
            $housing_capacity = $housing_info_request_result['capacity'];
            $housing_type = $housing_info_request_result['type'];
            
            $housing_img_request = $website_pdo->prepare('
                SELECT image, id from housing_image
                WHERE housing_id = :housing_id
            ');
            $housing_img_request->execute([
                ':housing_id' => $housing_id
            ]);
            $housing_img_request_result = $housing_img_request->fetchAll(PDO::FETCH_ASSOC);

            $housing_service_request = $website_pdo->prepare('
                SELECT concierge, driver, chef, babysitter, guide FROM housing_service
                WHERE housing_id = :housing_id
            ');
            $housing_service_request->execute([
                ':housing_id' => $housing_id
            ]);
            $housing_service_request_result = $housing_service_request->fetch(PDO::FETCH_ASSOC);

            $housing_concierge = $housing_service_request_result['concierge'];
            $housing_driver = $housing_service_request_result['driver'];
            $housing_chef = $housing_service_request_result['chef'];
            $housing_babysitter = $housing_service_request_result['babysitter'];
            $housing_guide = $housing_service_request_result['guide'];

            $housing_booking_request = $website_pdo->prepare('
                SELECT lastname, firstname, booking.id, user_id, start_date_time, end_date_time, booking_date_time, price, concierge, driver, chef, babysitter, guide FROM booking
                INNER JOIN booking_service ON booking.id =  booking_service.booking_id
                INNER JOIN user ON booking.user_id = user.id 
                WHERE housing_id = :housing_id
                ORDER BY start_date_time DESC
            ');
            $housing_booking_request->execute([
                ':housing_id' => $housing_id
            ]);
            $housing_booking_request_result = $housing_booking_request->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    $heart_icon = '../assets/images/heart.svg';
    $menu_icon =   '../assets/images/menu.svg';
    $account_icon = '../assets/images/account.svg'; 

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/font.css">
    <link rel="stylesheet" href="../assets/css/header_gestion.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/housing_management.css">
    <title><?= $housing_title ?> - OIKOS Gestion</title>
</head>
<body>
    <?php require '../inc/tpl/header_gestion.php' ?>
    <figure id="img-container" class="img-container">
        <img id="housing-img" class="housing-img" src="../uploads/<?= $housing_img_request_result[0]['image'] ?>" alt="Photo de l'appartement" width="100%">

        <div id="caption-block" class="caption-block">
            <figcaption id="housing-title-caption" class="housing-title-caption"><h1 id="housing-title" class="housing-title"><?= $housing_title ?></h1></figcaption>

            <figcaption id="housing-district" class="housing-district"><h2><?= $housing_district ?> - Paris</h2></figcaption>

            <figcaption id="housing-capacity" class="housing-capacity">Capacités <?= $housing_capacity ?> personnes, <?= $housing_number_of_pieces ?> pièces.</figcaption>
        </div>

        <div id="redirection-btn-block" class="redirection-btn-block">
            <button id="opinion-btn" class="redirection-btn <?= $housing_id ?>">Avis</button>
            <button id="modify-btn" class="redirection-btn <?= $housing_id ?>">Modifier</button>
        </div>
    </figure>
    <script src="../assets/js/management_zone/housing.js"></script>
    <script src="../assets/js/header_public.js"></script>
</body>
</html>