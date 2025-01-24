<?php

    require '../inc/pdo.php';

    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $booking_id = $_POST['booking_id'];

    if (strtotime($start_date) && strtotime($end_date)) {
        $change_booking_date_request = $website_pdo->prepare('
            UPDATE booking
            SET start_date_time = :start_date_time, end_date_time = :end_date_time
            WHERE id = :id
        ');

        $change_booking_date_request->execute([
            ':id' => $booking_id,
            ':start_date_time' => $start_date,
            ':end_date_time' => $end_date
        ]);

        echo 'Success';
    } else {
        echo 'Mauvais format de date';
    }