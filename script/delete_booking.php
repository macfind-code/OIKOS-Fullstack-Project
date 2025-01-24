<?php
    require '../inc/pdo.php';
    $booking_id = $_POST['booking_id'];

    try {
        $website_pdo->beginTransaction();
    
        $delete_booking_request = $website_pdo->prepare('
            DELETE FROM booking
            WHERE id = :id
        ');
    
        $delete_booking_request->execute([
            ':id' => $booking_id
        ]);
    
        $delete_booking_messaging_request = $website_pdo->prepare('
            DELETE FROM booking_messaging
            WHERE booking_id = :booking_id
        ');
    
        $delete_booking_messaging_request->execute([
            ':booking_id' => $booking_id
        ]);
    
        $delete_booking_service_request = $website_pdo->prepare('
            DELETE FROM booking_service
            WHERE booking_id = :booking_id
        ');
    
        $delete_booking_service_request->execute([
            ':booking_id' => $booking_id
        ]);
    
        $website_pdo->commit();

        $response = [
            'Status' => 'Success',
            'Message' => 'Requete validée.'
        ];
        $response = json_encode($response);
        echo $response;
    } catch (PDOException $e) {
        $website_pdo->rollback();
        echo "Une erreur s'est produite lors de l'exécution des requêtes DELETE : " . $e->getMessage();
    }
    