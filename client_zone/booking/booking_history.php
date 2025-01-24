<?php

session_start();
require '../../inc/pdo.php';
require "../../inc/functions/token_function.php";
require '../../inc/functions/booking_function.php';
$method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

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

$verify_existing_booking = $website_pdo -> prepare ('
    SELECT housing_id 
    FROM booking WHERE user_id = :user_id;
');
$verify_existing_booking -> execute([
    ':user_id'=> $_SESSION['id']
]);

$result_existing_booking = $verify_existing_booking->fetchAll(PDO::FETCH_ASSOC);

if($result_existing_booking){
    $housing_id = array();
    for($i = 0; $i < count($result_existing_booking);$i++){
        array_push($housing_id, $result_existing_booking[$i]['housing_id']);
    }
    $BookingFuture = getBookingFuture();
    $bookingPast = getBookingPast();
    $BookingCurrent = getBookingCurrent();
    

    // var_dump($get_current_bookings);
    // var_dump($picture);

}
$heart_icon = '../../assets/images/heart.svg';
$menu_icon = '../../assets/images/menu.svg';
$account_icon = '../../assets/images/account.svg';
$path = 'http://localhost/OIKOS-Fullstack-Project/uploads/';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/booking_history.css">
    <link rel="stylesheet" href="../../assets/css/font.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/header_publiczone.css">
    <title>OIKOS | Mes Réservations</title>
</head>

<body>
    <?php require '../../inc/tpl/header_publiczone.php' ?>

    <h1>Mes Réservations</h1>
    <div class="sidebar">
        <button class="side" onclick="showFutureBookings(this)">Réservation future</button>
        <button class="side" onclick="showPastBookings(this)">Réservation passée</button>
        <button class="side active" onclick="showCurrentBookings(this)">Réservation actuelle</button>
    </div>

    <div id="futureBookings" style="display: none;" >
        <?php if(isset($BookingFuture) && $BookingFuture != []){
                foreach($BookingFuture as $row){
                    $update_picture = $website_pdo -> prepare ('
                    SELECT image FROM housing_image
                    WHERE housing_id = :housing_id;
                    ');
                    $update_picture -> execute([
                        ":housing_id" => $row['housing_id']
                    ]);
                    $picture = $update_picture->fetchAll();?>
            <a href="./booking_details.php?booking_id=<?= $row['id'] ?>">
            <div class = "Booking">
                <div class = "image_bookings">
                    <img src="<?= $path.$picture[0]['image'] ?>" alt="">
                </div>
                <div class = "information_booking">
                    <ul>
                        <h2><?php echo $row['title'] ?></h2>
                        <p class = "district"><?php echo $row['district'] ?></p>
                        <div class = "line"></div>
                        <p class = "capacity"><?php echo $row['number_of_pieces'] ?> Pièces - <?php echo $row['area'] ?>m²</p>
                        <div class = "date_details">
                        <li class="check"><p>Check in : </p> <span><?php echo $row['start_date_time'] ?></span></li>
                        <li class="check"><p>Check out : </p> <span><?php echo $row['end_date_time'] ?></span></li>
                      
                </div>
                    </ul>
                </div>
            </div>
            <?php }}
            else{?>
                <p>Vous n'avez pas de réservation.</p>
            <?php } ?>  </a>
   </div>
 
   <a href="./booking_details.php?booking_id=<?= $row['id'] ?>"><div id="pastBookings" style="display: none;">
        <?php if (isset($bookingPast) && $bookingPast != []){
                foreach($bookingPast as $row){
                    $update_picture = $website_pdo -> prepare ('
                    SELECT image FROM housing_image
                    WHERE housing_id = :housing_id;
                    ');
                    $update_picture -> execute([
                        ":housing_id" => $row['housing_id']
                    ]);
                    $picture = $update_picture->fetchAll(); ?>
            <div class = "Booking">
                <div class = "image_bookings">
                    <img src="<?= $path.$picture[0]['image'] ?>" alt="">
                </div>
                <div class = "information_booking">
                    <ul>
                        <h2><?php echo $row['title'] ?></h2>
                        <p class = "district"><?php echo $row['district'] ?></p>
                                <div class = "line"></div>
                                <p class = "capacity"><?php echo $row['number_of_pieces'] ?> Pièces - <?php echo $row['area'] ?>m²</p>
                                <div class = "date_details">
                                    <li class="check"><p>Check in : </p>   <span><?php echo $row['start_date_time'] ?></span></li>
                                    <li class="check"><p>Check out : </p> <span><?php echo $row['end_date_time'] ?></span></li>
                               
                        </div>
                    </ul>
                </div>
            </div>
            <?php }}
            else{?>
                <p>Vous n'avez pas de réservation.</p>
            <?php } ?>
    </div>
    </a>
    <a href="./booking_details.php?booking_id=<?= $row['id'] ?>"><div id="currentBookings" style="display: none;">
            <?php if (isset( $BookingCurrent) &&  $BookingCurrent != []){
                 foreach($BookingCurrent as $row){
                    $update_picture = $website_pdo -> prepare ('
                    SELECT image FROM housing_image
                    WHERE housing_id = :housing_id;
                    ');
                    $update_picture -> execute([
                        ":housing_id" => $row['housing_id']
                    ]);
                    $picture = $update_picture->fetchAll();?>
                <div class = "Booking">
                    <div class = "image_bookings">
                        <img src="<?= $path.$picture[0]['image']?>" alt="">
                    </div>
                        <div class = "information_booking">
                            <ul>
                                <h2><?php echo $row['title'] ?> </h2>
                                <p class = "district"><?php echo $row['district'] ?></p>
                                <div class = "line"></div>
                                <p class = "capacity"><?php echo $row['number_of_pieces'] ?> Pièces - <?php echo $row['area'] ?>m²</p>
                                <div class = "date_details">
                                    <li class="check"><p>Check in : </p>   <span><?php echo $row['start_date_time'] ?></span></li>
                                    <li class="check"><p>Check out : </p> <span><?php echo $row['end_date_time'] ?></span></li>
                                </div>
                                
                            </ul>
                        </div>
                </div>
                <?php }}
                else{?>>
                    <p >Vous n'avez pas de réservation.</p>
                <?php } ?>
    </div>
    </a>
    <script src="../../assets/js/header_public.js"></script>
    <script>
          
        let buttons = document.querySelectorAll(".side")
        
        showCurrentBokings()
        function showFutureBookings(e) {
            buttons.forEach(element =>{
                element.classList.remove("active")
            })
            document.getElementById("futureBookings").style.display = "flex";
            document.getElementById("pastBookings").style.display = "none";
            document.getElementById("currentBookings").style.display = "none";
            e.classList.add("active")
    
        }
    
        function showPastBookings(e) {
            document.getElementById("futureBookings").style.display = "none";
            document.getElementById("pastBookings").style.display = "flex";
            document.getElementById("currentBookings").style.display = "none";
            buttons.forEach(element =>{
                element.classList.remove("active")
            })
            e.classList.add("active")
        }
    
        function showCurrentBookings(e) {
            document.getElementById("futureBookings").style.display = "none";
            document.getElementById("pastBookings").style.display = "none";
            document.getElementById("currentBookings").style.display = "flex";
            buttons.forEach(element =>{
                element.classList.remove("active")
            })
            e.classList.add("active")
        }
        function showCurrentBokings() {
            document.getElementById("futureBookings").style.display = "none";
            document.getElementById("pastBookings").style.display = "none";
            document.getElementById("currentBookings").style.display = "flex";

        }
    </script>
</body>
</html>