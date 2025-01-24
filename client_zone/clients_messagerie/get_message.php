<?php
require '../../inc/pdo.php';
session_start();
$client_id = $_SESSION['id'];
$booking_id = $_GET['booking_id'];
//recuperer les messages de la base de donnees
$requete = $website_pdo->prepare("SELECT * FROM booking_messaging where client_id = :client_id and booking_id = :booking_id");
$requete->execute(
    array(
        ':client_id' => $client_id,
        ':booking_id' => $booking_id
    )
);
$messages = $requete->fetchAll(PDO::FETCH_ASSOC);
//envoyer les donnees au client
echo json_encode($messages);