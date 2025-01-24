<?php
require_once '../../inc/pdo.php';
session_start();
$booking_id = $_GET['booking_id'];
$client_id = $_GET['client_id'];
//recuperer les messages de la base de donnees
$requete = $website_pdo->prepare("SELECT * FROM booking_messaging where booking_id = :booking_id and client_id = :client_id");
$requete->execute(
    array(
        ':booking_id' => $booking_id,
        ':client_id' => $client_id
    )
);
$messages = $requete->fetchAll(PDO::FETCH_ASSOC);
//envoyer les messages a l'utilisateur
echo json_encode($messages);