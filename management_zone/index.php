<?php

require '../inc/pdo.php';

if (isset($_POST['value'])) {
    $value = $_POST['value'];
    $ok = 'bien reçu';
    $get_housing =  $website_pdo->prepare("
    SELECT hi.image, h.id as housing_id, h.title, h.place, h.district, h.number_of_pieces, h.area, h.price, h.description, h.capacity, h.type
            FROM housing h
            JOIN housing_image hi ON h.id = hi.housing_id
            WHERE h.title LIKE :value OR h.id LIKE :value
            ORDER BY h.title, hi.housing_id
            ");

    $get_housing->execute([
        ':value' => '%' . $value . '%'
    ]);


    $get_housing_result = $get_housing->fetchALL(PDO::FETCH_ASSOC);

    echo json_encode($get_housing_result);
} else {
    $housing_list_request = $website_pdo->prepare('
        SELECT housing.id as housing_id, title, capacity, area, district, image, number_of_pieces, description, place, price, type FROM housing
        JOIN housing_image ON housing.id = housing_image.housing_id
    ');
    $housing_list_request->execute();
    $housing_list_request_result = $housing_list_request->fetchAll(PDO::FETCH_ASSOC);

    $response = json_encode($housing_list_request_result);
    echo $response;
}

?>

