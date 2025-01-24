<?php 
require '../../inc/pdo.php';
require '../../inc/functions/token_function.php';
session_start();

$id = $_SESSION['id'];

if(isset($_SESSION['token'])){
    $check = token_check($_SESSION["token"], $website_pdo, $_SESSION['id']);
    if($check == 'false'){
        header('Location: ../../connection/login.php');
        exit();
    } elseif($_SESSION['status'] == 0) {
        header('Location: ../../inc/tpl/inactive_user.html');
        exit();     
    }
}elseif(!isset($_SESSION['token'])){
    header('Location: ../../connection/login.php');
    exit();

}

$heart_icon = '../../assets/images/heart.svg';
$menu_icon =   '../../assets/images/menu.svg';
$account_icon = '../../assets/images/account.svg';
$link_favorite = '../../client_zone/profile/favorites.php';
$homepage_link = "../../public_zone/homepage.php";

$path = 'http://localhost/OIKOS-Fullstack-Project/uploads/';

$recup_housing = $website_pdo->prepare(
    'SELECT f.user_id, f.housing_id as housing_id, h.district, h.title, h.place, h.number_of_pieces, h.area, h.price, h.description, h.capacity, h.type
    FROM housing h
    JOIN favorite f ON h.id = f.housing_id'
);
$recup_housing->execute();
$result_recup_housing = $recup_housing->fetchAll();

$recup_fav=$website_pdo->prepare(
    'SELECT * FROM favorite WHERE user_id = :id'
);
$recup_fav->execute([
    ':id'=> $_SESSION['id']
]);
$result_recup_fav = $recup_fav->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/font.css">
    <link rel="stylesheet" href="../../assets/css/header_publiczone.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/favorites.css">

    <title>OIKOS | Mes Favoris</title>
</head>
<body>
    <?php require '../../inc/tpl/header_publiczone.php' ?>
    <div class="favorites-container">
        <div class="favorites"><h1>Mes favoris</h1></div>
        <div class="grid">

        <?php
        foreach($result_recup_fav as $fav) {
            $housing_info = $website_pdo->prepare(
                'SELECT * FROM housing WHERE id = :housing_id'
            );
            $housing_info->execute([
                ':housing_id'=>$fav['housing_id']
            ]);
            $result_housing_info = $housing_info->fetchAll();
            foreach($result_housing_info as $info) {


                                ?>
                                    <a href="../../public_zone/housing.php?id=<?= $info['id']?>">
                                        <div class="grid-item">
                                            <div class="grid-item-img">
                                                <?php 
                                                $housing_img = $website_pdo->prepare(
                                                    'SELECT * FROM housing_image WHERE housing_id = :housing_id'
                                                );
                                            $housing_img->execute([
                                                ':housing_id'=>$info['id']
                                            ]);
                                            $result_recup_housing_img = $housing_img->fetchAll();
                                                ?>
                                                <img src="<?= $path.$result_recup_housing_img[0]['image']?>" alt="">
                                            </div>
                    <div class="grid-item-content">
                        <div class="grid-item-left">
                            <div class="grid-item-title"><h4><?= $info['title']?></h4></div>
                            <div class="grid-item-capacity"><p><?= $info['number_of_pieces']?> pièces - <?= $info['area']?>m²</p></div>
                            <div class="grid-item-location"><p><?= $info['district']?></p></div>
                        </div>
                        <div class="grid-item-right">
                            <div class="grid-item-heart">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="#DD3F57" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.8401 4.60987C20.3294 4.09888 19.7229 3.69352 19.0555 3.41696C18.388 3.14039 17.6726 2.99805 16.9501 2.99805C16.2276 2.99805 15.5122 3.14039 14.8448 3.41696C14.1773 3.69352 13.5709 4.09888 13.0601 4.60987L12.0001 5.66987L10.9401 4.60987C9.90843 3.57818 8.50915 2.99858 7.05012 2.99858C5.59109 2.99858 4.19181 3.57818 3.16012 4.60987C2.12843 5.64156 1.54883 7.04084 1.54883 8.49987C1.54883 9.95891 2.12843 11.3582 3.16012 12.3899L4.22012 13.4499L12.0001 21.2299L19.7801 13.4499L20.8401 12.3899C21.3511 11.8791 21.7565 11.2727 22.033 10.6052C22.3096 9.93777 22.4519 9.22236 22.4519 8.49987C22.4519 7.77738 22.3096 7.06198 22.033 6.39452C21.7565 5.72706 21.3511 5.12063 20.8401 4.60987Z" stroke="#DD3F57" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <?php } }?>
        </div>
    </div>
    <script src="../../assets/js/header_public.js"></script>
</body>
</html>