<?php 
    require '../inc/pdo.php';

    $housing_id = $_POST['housing_id'];

    try {
        $website_pdo->beginTransaction();
    
        $delete_housing_request = $website_pdo->prepare('
            DELETE FROM housing
            WHERE id = :id
        ');
    
        $delete_housing_request->execute([
            ':id' => $housing_id
        ]);
    
        $delete_housing_image_request = $website_pdo->prepare('
            DELETE FROM housing_image
            WHERE housing_id = :housing_id
        ');
    
        $delete_housing_image_request->execute([
            ':housing_id' => $housing_id
        ]);
    
        $delete_housing_service_request = $website_pdo->prepare('
            DELETE FROM housing_service
            WHERE housing_id = :housing_id
        ');
    
        $delete_housing_service_request->execute([
            ':housing_id' => $housing_id
        ]);
    
        $delete_housing_review_request = $website_pdo->prepare('
            DELETE FROM housing_review
            WHERE housing_id = :housing_id
        ');
    
        $delete_housing_review_request->execute([
            ':housing_id' => $housing_id
        ]);
    
        $website_pdo->commit();

        $response = [
            'Status' => 'Success',
            'Message' => 'Suppression effectué avec succès.'
        ];
        $response = json_encode($response);
        echo $response;
    } catch (PDOException $e) {
        $website_pdo->rollback();
        echo "Une erreur s'est produite lors de l'exécution des requêtes DELETE : " . $e->getMessage();
    }
    