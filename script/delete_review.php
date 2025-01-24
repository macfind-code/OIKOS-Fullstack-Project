<?php
    require '../inc/pdo.php';
    $review_id = $_POST['review_id'];
    $delete_review_request = $website_pdo->prepare('
        DELETE FROM housing_review
        WHERE id = :id 
    ');
    $delete_review_request->execute([
        ':id' => $review_id
    ]);

    if ($delete_review_request->rowCount() > 0) {
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