<?php
require_once '../../inc/pdo.php';;
session_start();
// /save message
$client_id = $_GET['client_id'];
$sender_id =  $_SESSION['id'];
$message = $_GET['message'];
$booking_id = $_GET['booking_id'];
//enregistrer le message dans la base de donnees
$requete = $website_pdo->prepare("INSERT INTO booking_messaging (client_id, booking_id, sender_id, message) VALUES (:client_id, :booking_id, :sender_id, :message)");
$requete->execute(
    array(
        ':client_id' => $client_id,
        ':booking_id' => $booking_id,
        ':sender_id' => $sender_id,
        ':message' => $message
    )
);
//envoyer le message a l'utilisateur
echo json_encode(array('client_id'=>$client_id, 'message'=>$message, 'booking_id'=>$booking_id, 'envoyer'=>1,));
?>
