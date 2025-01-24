<?php
    require '../inc/pdo.php';
    $img_id = $_POST['img_id'];

    $delete_img_housing = $website_pdo->prepare('
        DELETE FROM housing_image
        WHERE id = :id
    ');
    $delete_img_housing->execute([
        ':id' => $img_id
    ]);
    
    if ($delete_img_housing->rowCount() > 0) {
        // La requête a été exécutée avec succès et au moins une ligne a été supprimée
        $response = [
            'Status' => 'Success',
            'Message' => 'Suppression effectué avec succès.'
        ];
        $response = json_encode($response);
        echo $response;
    } else {
        // Aucune ligne n'a été supprimée
        $response = [
            'Status' => 'Error',
            'Message' => 'Echec de la suppression.'
        ];
        $response = json_encode($response);
        echo $response;
    }