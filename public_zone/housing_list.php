<?php
require '../inc/pdo.php';
require '../inc/functions/token_function.php';
session_start();

$district = $_GET['district'];
$first_day_search = ($_GET['first_day_search']);
$end_day_search = ($_GET['end_day_search']);
$capacity = ($_GET['capacity']);
$method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

$heart_icon = '../assets/images/heart.svg';
$menu_icon =   '../assets/images/menu.svg';
$account_icon = '../assets/images/account.svg';
$link_favorite = '../client_zone/profile/favorites.php';
$homepage_link = "./homepage.php";


$path = 'http://localhost/OIKOS-Fullstack-Project/uploads/';

if(isset($_SESSION['id'])) {
    $connected = true;
}else{
    $connected = false;
};

$search_housing = $website_pdo->prepare(
    'SELECT * FROM housing ORDER BY id DESC'
);
$new_search_housing = $website_pdo->prepare(
    'SELECT * FROM housing WHERE district = :district_name AND capacity >= :capacity'
);

$new_search_housing->execute([
    ':district_name' => $district ,
    ':capacity' => $capacity
]);

$search_housing->execute();
$data_search_housing = $new_search_housing->fetchAll();

if($method == "POST") {
    $district = filter_input(INPUT_POST, "district_name");
    $first_day_search = filter_input(INPUT_POST, "first_day_search");
    $end_day_search = filter_input(INPUT_POST, "end_day_search");
    $capacity = filter_input(INPUT_POST, "capacity");
    header('Location: ./housing_list.php?district='.$district.'&first_day_search='.$first_day_search.'&end_day_search='.$end_day_search.'&capacity='.$capacity);
}



if(isset($_SESSION['id'])) {
    $user_info = $website_pdo->prepare(
        'SELECT * FROM user WHERE id = :id;'
    );
    $user_info->execute([
        ':id' => $_SESSION['id']
    ]);
    $result_user_info = $user_info->fetch(PDO::FETCH_ASSOC);
    if($result_user_info){
    $mail = $result_user_info['mail'];
    $lastname = $result_user_info['lastname'];
    $pp_image = $result_user_info['pp_image'];
    }

}

if(isset($_SESSION['id'])) {
    $housing_info = $website_pdo->prepare(
        'SELECT * FROM housing WHERE id = :id;'
    );
    $housing_info->execute([
        ':id' => $_SESSION['id']
    ]);
    $result_housing_info = $housing_info->fetch(PDO::FETCH_ASSOC);
    if($result_housing_info){
    $id_housing = $result_housing_info['id'];
    $title = $result_housing_info['title'];
    $place = $result_housing_info['place'];
    $number_of_pieces = $result_housing_info['number_of_pieces'];
    }
}

if(isset($_SESSION['id'])) {
    $housing_img = $website_pdo->prepare(
        "SELECT hi.image, h.id as housing_id, h.title, h.place, h.number_of_pieces, h.area, h.price, h.description, h.capacity, h.type
        FROM housing h
        JOIN housing_image hi ON h.id = hi.housing_id
        ORDER BY hi.housing_id"

    );
    $housing_img->execute();
    $result_housing_img = $housing_img->fetchAll();

}

$recup_district = $website_pdo->prepare(
    "SELECT district from housing ORDER BY id DESC;"
);
$recup_district->execute();
$result_recup_district = $recup_district->fetchAll();

$search_housing = $website_pdo->prepare(
    'SELECT * FROM housing ORDER BY id DESC'
);

if (isset($_POST['submit_booking'])) {
    $search_district_name = $_POST['district_name'];
    $search_capacity = $_POST['capacity_search'];

    $search_housing = $website_pdo->prepare(
        'SELECT * FROM housing WHERE district LIKE :district_name AND capacity >= :capacity ORDER BY id DESC'
    );

    $search_housing->execute([
        ':district_name' => '%' . $search_district_name . '%',
        ':capacity' => $search_capacity
    ]);
}
$search_housing->execute();
$result_search_housing = $search_housing->fetchAll();


$array_district = ['Tour Eiffel', 'Le Marais', 'Panthéon', 'Montmartre', 'Champs-Elysées', 'Opéra'];

$housing = $website_pdo->prepare("
SELECT * FROM housing");

$housing->execute();
$result_housing = $housing->fetchAll();

//         $dislikes = $app_pdo->prepare("SELECT id FROM dislikes WHERE test_publications_id = ?");
//         $dislikes->execute(array($id_pub));
//         $dislikes = $dislikes->rowCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/font.css">
    <link rel="stylesheet" href="../assets/css/header_publiczone.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/housing_list_publiczone.css">
    <title>OIKOS</title>
</head>
<body>
    <?php require '../inc/tpl/header_publiczone.php' ?>
    <div class="container-housinglist">
        <div class='input'>
        <form method="POST">
            <div class="container-label-input">
                <label for="">Quartier</label>
                <select name="district_name">
                <?php foreach($array_district as $district) :?>
                <option value="<?= $district ?>"><?= $district?></option>
                <?php endforeach ?>
                </select>
            </div>
            <div class="separator"></div>
            <div class="container-label-input">

                <label for="">Arrivée</label>
                <input type="date" name="first_day_search">
            </div>
            <div class="separator">
            </div>
            <div class="container-label-input">
                <label for="">Départ</label>
                <input type="date" name="end_day_search">
            </div>
            <div class="separator">
            </div>
            <div class="container-label-input">
                <label for="">Voyageurs</label>
                <input type="number" name="capacity_search" min="1" max="20">
            </div>
            <div class="container-label">
            <input type="submit" value="Rechercher" name="submit_booking">
            </div>
        </form>
        </div>
        <div class="house-list">
            <?php
        $i = 0;
        foreach ($data_search_housing as $row) {
            $housing_img = $website_pdo ->prepare(
                'SELECT image FROM housing_image WHERE housing_id = :id'
            );
            $housing_img->execute([
                ':id'=>$row['id']
            ]);
            $result_housing_img = $housing_img->fetchAll();

            if($connected == true) {
            $check_fav = $website_pdo->prepare(
                'SELECT * FROM favorite WHERE user_id = :id AND housing_id = :housing_id'
            );
            $check_fav->execute([
                ':id'=>$_SESSION['id'],
                ':housing_id'=>$row['id']
            ]);
            $result_check_fav = $check_fav->fetchAll();

            }
            ?>
            <div class="house-item">
                <div class="house-img" id="house-img-<?= $i; ?>">
                    <div class="slider-nav">
                    <div class='arrow-left'>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </div>
                        <div class='arrow-right'>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </div>
                    </div>
                    <div class="slider-content">
                        <?php 
                        foreach($result_housing_img as $img) {
                            ?>
                            <div class="slider-content-item">
                            <img src="<?= $path . $img['image'] ?>" alt="Housing_photo">
                            </div>

                        <?php } ?>
                    </div>
                </div>
                <div class="house-important">
                    <div class="house-important-top">
                        <div class="house-title"><h2><?= $row['title'] ?></h2></div>
                        <div class="house-district"><p><?= $row['district'] ?></p></div>
                    </div>
                    <div class="house-important-bottom">
                        <div class="house-area"><p><?= $row['number_of_pieces'] ?> Pièces - <?= $row['area'] ?> m²</p></div>
                        <div class="house-capacity"><p><?= $row['capacity'] ?> voyageurs</p></div>
                        <div class="house-icon">
                            <div class="icon-self">
                                <div class='icon-img'><img src="../assets/images/agreement.svg" alt=""></div>
                                <div class='icon-txt'><p>Meeting Room</p></div>
                            </div>
                            <div class="icon-self">
                                <div class='icon-img'><img src="../assets/images/piano.svg" alt=""></div>
                                <div class='icon-txt'><p>Piano</p></div>
                            </div>
                            <div class="icon-self">
                                <div class='icon-img'><img src="../assets/images/audio.svg" alt=""></div>
                                <div class='icon-txt'><p>Home Cinema</p></div>
                            </div>
                        </div>
                    </div> 
                </div>
                <div class="house-description-btn">
                    <div class="house-description"><p><?= $row['description'] ?></p></div>
                    <div class="house-btn-heart">
                        <div class="house-btn">
                            <a href="./housing.php?id=<?= $row['id']?>"><button>Voir plus</button></a>
                        </div>
                        <div class="house-heart">

                            <?php 
                            if($connected == true) {
                            if(!$result_check_fav) {
                            ?>
                            <svg data-value='<?= $row['id'] ?>' class='svg-heart' width="24" height="24" viewBox="0 0 24 24" style="fill: none;" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.8401 4.60987C20.3294 4.09888 19.7229 3.69352 19.0555 3.41696C18.388 3.14039 17.6726 2.99805 16.9501 2.99805C16.2276 2.99805 15.5122 3.14039 14.8448 3.41696C14.1773 3.69352 13.5709 4.09888 13.0601 4.60987L12.0001 5.66987L10.9401 4.60987C9.90843 3.57818 8.50915 2.99858 7.05012 2.99858C5.59109 2.99858 4.19181 3.57818 3.16012 4.60987C2.12843 5.64156 1.54883 7.04084 1.54883 8.49987C1.54883 9.95891 2.12843 11.3582 3.16012 12.3899L4.22012 13.4499L12.0001 21.2299L19.7801 13.4499L20.8401 12.3899C21.3511 11.8791 21.7565 11.2727 22.033 10.6052C22.3096 9.93777 22.4519 9.22236 22.4519 8.49987C22.4519 7.77738 22.3096 7.06198 22.033 6.39452C21.7565 5.72706 21.3511 5.12063 20.8401 4.60987Z" stroke="#DD3F57" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            
                            <?php }else{
                        ?>
                             <svg data-value='<?= $row['id'] ?>' class='svg-heart' width="24" height="24" viewBox="0 0 24 24" style="fill: #DD3F57;" xmlns="http://www.w3.org/2000/svg">
                             <path d="M20.8401 4.60987C20.3294 4.09888 19.7229 3.69352 19.0555 3.41696C18.388 3.14039 17.6726 2.99805 16.9501 2.99805C16.2276 2.99805 15.5122 3.14039 14.8448 3.41696C14.1773 3.69352 13.5709 4.09888 13.0601 4.60987L12.0001 5.66987L10.9401 4.60987C9.90843 3.57818 8.50915 2.99858 7.05012 2.99858C5.59109 2.99858 4.19181 3.57818 3.16012 4.60987C2.12843 5.64156 1.54883 7.04084 1.54883 8.49987C1.54883 9.95891 2.12843 11.3582 3.16012 12.3899L4.22012 13.4499L12.0001 21.2299L19.7801 13.4499L20.8401 12.3899C21.3511 11.8791 21.7565 11.2727 22.033 10.6052C22.3096 9.93777 22.4519 9.22236 22.4519 8.49987C22.4519 7.77738 22.3096 7.06198 22.033 6.39452C21.7565 5.72706 21.3511 5.12063 20.8401 4.60987Z" stroke="#DD3F57" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php } }?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                    $i++;
                    }
            ?>
        </div>
    </div>
    <footer>
        <div class="logo_footer">
            <div class='separator-footer'></div>
            <div class='footer-logo-txt' id="footer-logo"><p>OIKOS</p></div>
            <div class='separator-footer'></div>
        </div>
        <div class="footer_elements">
            <div class="footer_column_left">
                <div class="footer_column_left_title">
                    <h3>Assistance</h3>
                </div>
                <div class="footer_column_left_elements">
                    <p>Nous contacter</p>
                    <p>Centre d'aide</p>
                    <p>Annulation</p>
                    <p>Signaler un problème</p>
                </div>
            </div>
            <div class="footer_column_middle">
                <div class="footer_column_middle_title">
                    <h3>Nos offres</h3>
                </div>
                <div class="footer_column_middle_elements">
                    <p>Location saisonnière</p>
                    <p>Location longue durée</p>
                    <p>Nos garanties</p>
                    <p>Nos services</p>
                </div>
            </div>
            <div class="footer_column_right">
                <div class="footer_column_right_title">
                    <h3>Politique</h3>
                </div>
                <div class="footer_column_right_elements">
                    <p>Protection des données</p>
                    <p>Conditions générales</p>
                    <p>Fonctionnement du site</p>
                    <p>Gérer mes cookies</p>
                </div>
            </div>
        </div>
    </footer>
    <script src="../assets/js/header_public.js"></script>
    <script src="../assets/js/adding_favorite.js"></script>
    <script src="../assets/js/carousel.js"></script>
    <script>
        const backTop = document.getElementById("footer-logo")

        backTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior:"smooth"
            })
        })
    </script>
</body>
</html>