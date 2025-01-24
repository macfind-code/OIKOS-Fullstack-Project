<?php
require '../../inc/pdo.php';
session_start();
// /save message
$client_id =$_SESSION['id'];
$sender_id= $client_id;
$message = $_GET['message'];
$hooking_id = $_GET['booking_id'];
//enregistrer le message dans la base de donnees
$requete = $website_pdo->prepare("INSERT INTO booking_messaging (client_id,booking_id,sender_id, message) VALUES (:client_id,:hooking_id, :sender, :message)");
$requete->execute(
    array(
        ':client_id' => $client_id,
        ':hooking_id' => $hooking_id,
        ':sender' => $sender_id,    
        ':message' => $message
    )
);
//envoyer le message a l'utilisateur
echo json_encode(array('client_id'=>$client_id, 'message'=>$message));
?>
