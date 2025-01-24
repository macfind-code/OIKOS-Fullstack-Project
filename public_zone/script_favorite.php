<?php
require '../inc/pdo.php';
session_start();

$user_id = $_SESSION['id'];
$id = $_POST['id'];
$state = $_POST['state'];

if($state == 'add'){
    $fav_insert = $website_pdo->prepare('
    INSERT INTO favorite (user_id, housing_id) VALUES (:user_id, :housing_id)'
);
$fav_insert->execute([
    ':user_id'=>$user_id,
    ':housing_id'=>$id
]);
}elseif ($state == 'remove') {
    $fav_remove = $website_pdo->prepare('
    DELETE FROM favorite WHERE user_id = :user_id AND housing_id = :housing_id
    ');
    $fav_remove->execute([
        ':user_id'=>$user_id,
        ':housing_id'=>$id
    ]);
}

$recup_housing = $website_pdo->prepare(
    'SELECT f.user_id, f.housing_id as housing_id, h.title, h.place, h.number_of_pieces, h.area, h.price, h.description, h.capacity, h.type
    FROM housing h
    JOIN favorite f ON h.id = f.housing_id
    ORDER BY f.housing_id'
);
$recup_housing->execute();
$result_recup_housing = $recup_housing->fetch();

if($result_recup_housing){
    header('Location: ../client_zone/profile/favorite.php?user_id='. $user_id .'&housing_id='. $id);
}

var_dump($result_recup_housing);


// $send = [
//     'id' => $id,
//     'state' => $state
// ];

header('Content-Type: application/json');
// echo json_encode($send);
?>
