<?php

    session_start();
    require '../../inc/pdo.php';
    require '../../inc/functions/token_function.php';
    require '../../inc/functions/check_existing_user.php';
    require '../../inc/functions/booking_function.php';

    if(isset($_SESSION['token'])){
        $check = token_check($_SESSION["token"], $website_pdo, $_SESSION['id']);
        if($check == 'false'){
            header('Location: ../../connection/login.php');
            exit();
        }else {
            if ($_SESSION['status'] == 0) {
                header ('Location: ../../inc/tpl/inactive_user.html');
                exit(); 
            }
            if ($_SESSION['management_role'] == 0 && $_SESSION['admin_role'] == 0){
                header ('Location: ../../public_zone/homepage.php');
                exit();
            }
        }   
    }elseif(!isset($_SESSION['token'])){
        header('Location: ../../connection/login.php');
        exit();
    }


    if (!isset($_GET['housing_id'])) {
        header('Location: ../housing_list.php');
    }

    $housing_id = $_GET['housing_id'];
    $housing_reviews_request = $website_pdo->prepare('
        SELECT housing_review.id as housing_review_id, housing_id, user_id, review, review_date_time, lastname, firstname, title from housing_review
        LEFT JOIN user ON user_id = user.id
        LEFT JOIN housing ON housing_id = housing.id
        WHERE housing_id = :housing_id
        ORDER BY review_date_time DESC
    ');
    $housing_reviews_request->execute([
        'housing_id' => $housing_id
    ]);
    $housing_reviews_request_result = $housing_reviews_request->fetchAll(PDO::FETCH_ASSOC);

    $heart_icon = '../../assets/images/heart.svg';
    $menu_icon =   '../../assets/images/menu.svg';
    $account_icon = '../../assets/images/account.svg';
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/font.css">
    <link rel="stylesheet" href="../../assets/css/header_gestion.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/reviews_moderation.css">
    <title><?= $housing_reviews_request_result[0]['title'] ?> - Avis OIKOS Gestion</title>
</head>
<body>
    <?php require '../../inc/tpl/header_gestion.php' ?>
    <div id="page-content" class="page-content">
        <h1 id="page-title" class="main-title page-title"><?= $housing_reviews_request_result[0]['title'] ?></h1>
        <section id="opinion-section" class="opinion-section">
            <h2 id="page-subtitle" class="page-title">Gerer les avis</h2>
            <table class="housing-review-moderation-table">
                <thead>
                    <tr class="table-title-row">
                        <th class="table-title">Nom</th>
                        <th class="table-title">Prenom</th>
                        <th class="table-title">Avis</th>
                        <th class="table-title">Date</th>
                        <th class="table-title">Interaction</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($housing_reviews_request_result as $review_info): ?>
                        <tr id="<?= $review_info['housing_review_id'] ?>" class="table-text-row">
                            <td class="table-text"><?= $review_info['lastname'] ?></td>
                            <td class="table-text"><?= $review_info['firstname'] ?></td>
                            <td class="table-text"><?= $review_info['review'] ?></td>
                            <td class="table-text"><?= $review_info['review_date_time'] ?></td>
                            <td class="cross"><img id="deleting<?= $review_info['housing_review_id'] ?>" class="deletion-cross" src="../../assets/images/close_cross.svg" alt="Croix de suppression"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
    
    <script src="../../assets/js/management_zone/reviews_moderation.js"></script>
    <script src="../../assets/js/header_public.js"></script>
</body>
</html>