<?php
    require '../inc/pdo.php';

    $value = $_POST['value'];

    $get_email = $website_pdo->prepare("
        SELECT mail FROM user WHERE mail LIKE :value LIMIT 5
    ");

    $get_email->execute([
        ":value" => "%".$value."%"
    ]);

    $get_email_result = $get_email->fetchAll();

    echo json_encode($get_email_result);