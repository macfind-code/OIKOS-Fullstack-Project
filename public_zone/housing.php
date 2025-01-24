<?php 
require '../inc/pdo.php';
require '../inc/functions/token_function.php';
session_start();

$heart_icon = '../assets/images/heart.svg';
$menu_icon =   '../assets/images/menu.svg';
$account_icon = '../assets/images/account.svg';
$link_favorite = '../client_zone/profile/favorites.php';
$homepage_link = "./homepage.php";

$i = 1;

if(isset($_POST['submit_booking'])) {
if(isset($_SESSION['token'])){
    $check = token_check($_SESSION["token"], $website_pdo, $_SESSION['id']);
    if($check == 'false'){
        header('Location: ../connection/login.php');
        exit();
    } elseif($_SESSION['status'] == 0) {
        header('Location: ../inc/tpl/inactive_user.html');
        exit();  
    }
}elseif(!isset($_SESSION['token'])){
    header('Location: ../connection/login.php');
    exit();

}
}

if(isset($_SESSION['id'])) {
    $connected = true;
}else{
    $connected = false;
};

$path = 'http://localhost/OIKOS-Fullstack-Project/uploads/';

$price = 1000;

$housing_id = $_GET['id'];
$housing_info = $website_pdo->prepare(
    "SELECT * FROM housing WHERE id = :id"
);
$housing_info->execute([
':id' => $housing_id
]);
$result_housing_id = $housing_info->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['first_day_booking'], $_POST['end_day_booking'])){
$first_day_booking = ($_POST['first_day_booking']);
$end_day_booking = ($_POST['end_day_booking']);

$check_booking_date = $website_pdo->prepare(
    "SELECT * FROM booking
     WHERE (start_date_time < :end_date_time) AND (end_date_time > :start_date_time);"
);
$check_booking_date->execute([
    ":end_date_time"=>$end_day_booking,
    ":start_date_time"=>$first_day_booking
    ]);
    
$result_check_booking_date = $check_booking_date->fetchAll();
if(count($result_check_booking_date) > 0) {
    $reservationcompleted = false;
exit;

} else {
    $reservationcompleted = true;

$booking_date = $website_pdo->prepare(
    "INSERT INTO booking (user_id,housing_id, price, start_date_time, end_date_time, booking_date_time)
    VALUES (:user_id,:housing_id, :price, :start_date_time, :end_date_time, NOW());"
);
$booking_date->execute([
    ":user_id"=>$_SESSION['id'],
    ":housing_id"=>$housing_id,
    ":price"=>$price,
    ":start_date_time"=>$first_day_booking,
    ":end_date_time"=>$end_day_booking
]);
$last_insert_id = $website_pdo->lastInsertId();

$service_concierge = isset($_POST['concierge']) ? "1" : "0";
$service_driver = isset($_POST['driver']) ? "1" : "0";
$service_chef = isset($_POST['chef']) ? "1" : "0";
$service_babysitter = isset($_POST['babysitter']) ? "1" : "0";
$service_guide = isset($_POST['guide']) ? "1" : "0";

$client_booking_service = $website_pdo->prepare(
    "INSERT INTO booking_service (booking_id, concierge, driver, chef, babysitter, guide)
    VALUES (:booking_id, :concierge, :driver, :chef, :babysitter, :guide);"
);
$client_booking_service->execute([
    ':booking_id'=>$last_insert_id,
    ':concierge'=>$service_concierge,
    ':driver'=>$service_driver,
    ':chef'=>$service_chef,
    ':babysitter'=>$service_babysitter,
    ':guide'=>$service_guide
]);
}
}

$comment = $website_pdo->prepare(
    'SELECT * FROM housing_review WHERE housing_id = :housing_id'
);
$comment->execute([
    ':housing_id'=>$housing_id
]);
$result_comment = $comment->fetchAll();
// var_dump($result_comment);
$commentsnumber = count($result_comment);
// echo sizeof(get_object_vars($result_comment))

$check_housing_services = $website_pdo->prepare(
    'SELECT concierge, driver, chef, babysitter, guide FROM housing_service WHERE housing_id = :id'
);

$check_housing_services->execute([
    ':id' => $housing_id
]);

$result_check_housing_services = $check_housing_services->fetch(PDO::FETCH_ASSOC);


if(isset($_POST['comment'])){
    $review = ($_POST['comment']);
$insert_comment = $website_pdo->prepare(
    'INSERT INTO housing_review (housing_id, user_id, review, review_date_time)
    VALUES (:housing_id, :user_id, :review, NOW())'
);
$insert_comment->execute([
    ':housing_id'=>$housing_id,
    ':user_id'=>$_SESSION['id'],
    ':review'=>$review
]);
echo 'Votre commentaire a Ã©tait ajouter';
header('Location: ./housing.php?id='. $housing_id);

}

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
    <link rel="stylesheet" href="../assets/css/housing.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>
    <title>OIKOS</title>
</head>
<body>
    <?php require '../inc/tpl/header_publiczone.php' ?>
    <div class='housing-mainimg-title'>
        <?php 
            $get_housing_image = $website_pdo->prepare("
            SELECT image FROM housing_image where housing_id = :id
            ");
            $get_housing_image->execute([
                ":id" => $housing_id
            ]);
            $get_housing_image_result = $get_housing_image->fetchAll();

        ?>
        <div class='mainimg'><img src="<?= $path . $get_housing_image_result[0]['image'] ?>" alt=""></div>
        <div class='title'><p> <?= $result_housing_id['title'] ?></p></div>
    </div>
    <div class='section-infos'>
        <div class='section-info-left'>
            <div class='info-left-top'>
                <div>
                    <p><?= $result_housing_id['district'] ?>, 9e arrondissement, Paris</p>
                </div>
                <div>
                    <p><?= $result_housing_id['capacity'] ?> voyageurs - 5 chambres - 5 lits - 2 salles de bains</p>
                </div>
            </div>
            <div class='info-left-bottom'>
                <div class='btn-icon'>
                    <div class="list-icons">
                        <div class="icon-self">
                            <img src="../assets/images/agreement.svg" alt="">
                            <p>Meeting Room</p>
                        </div>
                        <div class="icon-self">
                            <img src="../assets/images/piano.svg" alt="">
                            <p>Piano</p>
                        </div>
                        <div class="icon-self">
                            <img src="../assets/images/audio.svg" alt="">
                            <p>Home Cinema</p>
                        </div>
                        <div class="icon-self">
                            <img src="../assets/images/bar.svg" alt="">
                            <p>Bar</p>
                        </div>
                        <div class="icon-self">
                            <img src="../assets/images/sun.svg" alt="">
                            <p>Panorama</p>
                        </div>
                        <div class="icon-self">
                            <img src="../assets/images/pmr.svg" alt="">
                            <p>PMR</p>
                        </div>
                    </div>
                    <div class='btn'>
                        <button>Afficher les équipements</button>
                    </div>
                </div>
                <div class="house-services">
                    <div class='services-title'><p>Préstations incluses</p></div>
                    <div class='services-list'>
                        <div><p>• Diner</p></div>
                        <div><p>• Collations</p></div>
                        <div><p>• Room Tour</p></div>
                        <div><p>• SPA</p></div>
                    </div>
                </div>
                <div class='housing-description'>
                    <div class='housing-description-title'><p>Le logement</p></div>
                    <div class='housing-description-txt'><p><?= $result_housing_id['description'] ?></p></div>
                </div>
            </div>
        </div>
        <div class='section-info-right'>
            <div class='infos-right-price-comments'>
                <div class='price'><p><?= $result_housing_id['price'] ?>€ par nuit</p></div>
                <div class='comments'><p><?= $commentsnumber ?> • témoignages</p></div>
            </div>
            <form method="POST" action="./housing.php?id=<?=$housing_id?>" >
                <div class="arrivée">
                    <div class="arrivée-left">
                        <label for="first_day_booking">Arrivée</label>
                            <input type="date" name="first_day_booking">
                    </div>
                    <div class='arrivee-separator'></div>
                    <div class="arrivée-right">
                    <label for="end_day_booking">Départ</label>
                    <input type="date" name="end_day_booking">
                    </div>
                </div>
                <div class='form-capacity'>
                    <label for="">Voyageurs</label>
                    <input type="number" value='1'>
                </div>
                <div class='select-services'>
                    <div class='services-title'><h4>Choissisez vos services</h4></div>
                    <div class='services-list'>
                        <?php foreach($result_check_housing_services as $key => $service){
                            if($service == '1'){ ?>
                                <div class='services-self'>
                                    <input type="checkbox" name="<?= $key ?>" value="1">
                                    <label for="<?= $key ?>"><?= $key ?></label>
                                </div>

                            <?php }} ?>
                    </div>
                </div>

                <div class='btn-txt'><p>Un service souhaité non mentionné ? Contactez le service client</p></div>
                <?php if($connected == true){?>
                    <div class='btn-services'><input type="submit" value="Réserver" name="submit_booking"></div>
                    <?php }else {
                    echo "Tu dois crée un compte ou t'inscrire pour pouvoir réserver"
                    ?>
                    <a href="../connection/login.php">Connexion</a>
                    <a href="../connection/register.php">Inscription</a>
                    <?php }?>
            </form>
            <div class='payement'>
                <div class='payement-night'>
                    <div><p>9 nuits</p></div>
                    <div><p>20 880 €</p></div>
                </div>
                <div class='payement-taxe'>
                    <div><p>Taxe de séjour</p></div>
                    <div><p>207 €</p></div>
                </div>
                <div class='payement-total'>
                    <div><p>Total</p></div>
                    <div><p>21 087 €</p></div>
                </div>
                <div class='checkout-message'>
                    <?php if(isset($reservationcompleted)){
                             if(!$reservationcompleted){ ?>
                    <p>La période spécifiée n'est pas disponible à la réservation</p>
                <?php } elseif ($reservationcompleted) { ?>
                    <p>Reservation effectuée</p>
                <?php }} ?>
                </div>
            </div>
       
        </div>
    </div>
    <div class="photo_room_tour">
        <div class="photo_room_tour_1qr">
            <div class="photo_room_tour_1">
                <img src="<?= $path . $get_housing_image_result[1]['image'] ?>" alt="">
            </div>
            <div class="room_tour_2_photos">
                <div class="photo_room_tour_2">
                    <img src="<?= $path . $get_housing_image_result[2]['image'] ?>" alt="">
                </div>
                <div class="photo_room_tour_3">
                    <img src="<?= $path . $get_housing_image_result[3]['image'] ?>" alt="">
                </div>
            </div>
        </div>
        <div class="photo_room_tour_2qr">
            <div class="photo_room_tour_4">
                <img src="<?= $path . $get_housing_image_result[4]['image'] ?>" alt="">
            </div>
        </div>
    </div>
    <div class='container-map'>
        <div id="map"></div>
    </div>
    <div class='temoignages'>
        <div class='temoignages-content'>
            <div class='temoignages-left'>
            <?php
    foreach($result_comment as $comment){
        $user_info = $website_pdo->prepare(
            'SELECT * FROM user WHERE id = :user_id'
        );
        $user_info->execute([
            ':user_id'=>$comment['user_id']
        ]);
        $result_user_info = $user_info->fetchAll();
        foreach($result_user_info as $user_info){
            ?>
                <div class='comments-self'>
                    <div class="comments-self-top">
                        <div class='author-img'><img src="<?= $path . $user_info['pp_image']?>" alt=""></div>
                        <div class='author-name'><p><?= $user_info['firstname'] . " " . $user_info ['lastname'] ?></p></div>
                    </div>
                    <div class='comments-content'>
                        <p><?= $comment['review'] ?></p>
                    </div>
                </div>
            <?php
        }
        
    }
    ?>
            </div>
            <div class='temoignages-right'>
            <form  class="form-comments" method="POST">
                <h2>Ajoutez un témoignage</h2>
                    <textarea name="comment" placeholder="Exprimez-vous..."></textarea>
                    <?php if($connected == true){?>
                        <div class='btn-services'><input type="submit" value='Envoyer' name="submit_comment"></div>
                    <?php }else {
                    echo "Veuillez vous connecter pour accéder à cette fonction"
                    ?>
                    <a href="../connection/login.php">Connexion</a>
                    <a href="../connection/register.php">Inscription</a>
                    <?php }?>
            </form>
            </div>
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
    <script>
        const backTop = document.getElementById("footer-logo")

        backTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior:"smooth"
            })
        })
    </script>
    <script>
    var map = L.map('map');
    map.setView([48.8534, 2.3488], 12);
    let dataResponse = [];

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    let place = '<?= $result_housing_id['place'] ?>'
    console.log(place)
    // Appel api 
    fetch(`https://api-adresse.data.gouv.fr/search/?q=${place}`)
        .then(response => {
            return response.json();
        })
        .then(data => {
            const dataArray = data['features'][0]['geometry']['coordinates']
            dataArray.forEach(element => {
                dataResponse.push(element)
            });
        let long = dataResponse[0]
        let lat = dataResponse[1]
        let marker = L.marker([lat,long], { keepInView: true }).addTo(map);
        })
    </script>
    <script src="../assets/js/header_public.js"></script>
</body>
</html>