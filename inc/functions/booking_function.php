<?php 
function getBookingFuture(){  
    require '../../inc/pdo.php';
    // Réservation futur    
    $get_bookings_future = $website_pdo -> prepare('
    SELECT DISTINCT b.start_date_time,b.end_date_time,b.housing_id, h.title, h.id,b.id, h.district,h.number_of_pieces,h.area
    FROM booking b 
    JOIN housing h ON b.housing_id = h.id
    WHERE b.user_id = :user_id 
    AND b.start_date_time > NOW();
    ');
    $get_bookings_future -> execute([
        ':user_id' => $_SESSION['id']
    ]);
    $get_future_bookings = $get_bookings_future->fetchAll(PDO::FETCH_ASSOC);
    return $get_future_bookings;
}
function getBookingPast(){
    require '../../inc/pdo.php';
    // Réservation passé
    $get_bookings_past = $website_pdo -> prepare('
    SELECT DISTINCT b.start_date_time,b.end_date_time,b.housing_id, h.title, h.id,b.id, h.district,h.number_of_pieces,h.area
    FROM booking b 
    JOIN housing h ON b.housing_id = h.id
    WHERE b.user_id = :user_id 
    AND b.end_date_time < NOW();
    ');
    $get_bookings_past -> execute([
        ':user_id' => $_SESSION['id']
    ]);
    $get_past_bookings = $get_bookings_past->fetchAll(PDO::FETCH_ASSOC);
    return $get_past_bookings ;
}
function getBookingCurrent(){
    require '../../inc/pdo.php';
    // Réservations actuelle
    $get_bookings_current = $website_pdo -> prepare('
    SELECT DISTINCT b.start_date_time,b.end_date_time,b.housing_id, h.title, h.id,b.id, h.district,h.number_of_pieces,h.area
    FROM booking b 
    JOIN housing h ON b.housing_id = h.id
    WHERE b.user_id = :user_id 
    AND b.end_date_time >= NOW()
    AND b.start_date_time <= NOW();
    ');
    $get_bookings_current -> execute([
        ':user_id' => $_SESSION['id']
    ]);
    $get_current_bookings = $get_bookings_current->fetchAll();
    return $get_current_bookings;
}