<?php

// Ici in veut checker si l'utilisateur est connecté pour pourvoir afficher le bon header
require '../inc/pdo.php';
require '../inc/functions/token_function.php';
session_start();

if (isset($_SESSION['id'])){
    $id = $_SESSION['id'];
    // J'ai pas les sessions donc j'ai mis ça
    $check = token_check($_SESSION['token'], $website_pdo, $_SESSION['id']);

    if($check == 'true'){
        $check_status = $website_pdo->prepare('
        SELECT * FROM user WHERE id = :id
        ');
        $check_status->execute([
            ':id' => $id
        ]);
        $result_check_status = $check_status->fetch(PDO::FETCH_ASSOC);
        $management = $result_check_status['management_role'];
        $maintenance = $result_check_status['maintenance_role'];
        $admin = $result_check_status['admin_role'];
    
        if($management == 0){
            $ismanager = false;
        }else{
            $ismanager = true;
        }
    
        if($maintenance == 0){
            $ismaintenance = false;
        }else{
            $ismaintenance = true;
        }
    
        if($admin == 0){
            $isadmin = false;
        }else{
            $isadmin = true;
        }
    
        $data = [
            'isconnected' => true,
            'ismanager' => $ismanager,
            'ismaintenance' => $ismaintenance,
            'isadmin' => $isadmin
        ];
        echo json_encode($data, true);
        exit();
    }
} else {
    $data = [
        'isconnected' => false
    ];
    echo json_encode($data, true);
    exit();
}




// if($check == 'true'){
//     $check_status = $website_pdo->prepare('
//     SELECT * FROM user WHERE id = :id
//     ');
//     $check_status->execute([
//         ':id' => $id
//     ]);
//     $result_check_status = $check_status->fetch(PDO::FETCH_ASSOC);
//     $management = $result_check_status['management_role'];
//     $maintenance = $result_check_status['maintenance_role'];
//     $admin = $result_check_status['admin_role'];

//     if($management == 0){
//         $ismanager = false;
//     }else{
//         $ismanager = true;
//     }

//     if($maintenance == 0){
//         $ismaintenance = false;
//     }else{
//         $ismaintenance = true;
//     }

//     if($admin == 0){
//         $isadmin = false;
//     }else{
//         $isadmin = true;
//     }

//     $data = [
//         'isconnected' => true,
//         'ismanager' => $ismanager,
//         'ismaintenance' => $ismaintenance,
//         'isadmin' => $isadmin
//     ];
//     echo json_encode($data, true);
//     exit();
// }else{
//     echo "yo";
//     $data = [
//         'isconnected' => false
//     ];
//     echo json_encode($data, true);
//     exit();
// }
