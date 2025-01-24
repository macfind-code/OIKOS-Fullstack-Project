<?php
require_once '../../inc/pdo.php';;
session_start();
$client_id = $_GET['client_id'];
//recuperer les messages de la base de donnees
$requete = $website_pdo->prepare("SELECT * FROM booking where user_id = :client_id");
$requete->execute(
    array(
        ':client_id' => $client_id
    )
);
$messages = $requete->fetchAll(PDO::FETCH_ASSOC);

foreach ($messages as $key => $value) {
    $requete = $website_pdo->prepare("SELECT * FROM housing where id = :housing_id");
    $requete->execute(
        array(
            ':housing_id' => $value['housing_id']
        )
    );
    $housing = $requete->fetch(PDO::FETCH_ASSOC);
    $messages[$key]['housing'] = $housing;
}
//envoyer les messages a l'utilisateur
echo json_encode($messages);