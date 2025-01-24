<?php
session_start();
require '../inc/pdo.php';
require '../inc/functions/token_function.php';

if(isset($_SESSION['token'])){
    $check = token_check($_SESSION["token"], $website_pdo, $_SESSION['id']);
    if($check == 'false'){
        header('Location: ../connection/login.php');
        exit();
    }else {
        if ($_SESSION['status'] == 0) {
            header ('Location: ../inc/tpl/inactive_user.html');
            exit(); 
        }
        if ($_SESSION['maintenance_role'] == 0 && $_SESSION['admin_role'] == 0){
            header ('Location: ../public_zone/homepage.php');
            exit();
        }
    }   
}elseif(!isset($_SESSION['token'])){
    header('Location: ../connection/login.php');
    exit();
}

$heart_icon = '../assets/images/heart.svg';
$menu_icon =   '../assets/images/menu.svg';
$account_icon = '../assets/images/account.svg';
$link_favorite = '../client_zone/profile/favorites.php';
$homepage_link = "../public_zone/homepage.php";

$currentMonth = date('Y-m');

$maintenance_requete = $website_pdo->prepare('
    SELECT DISTINCT m.id, m.status, m.title, m.schedule_date, m.housing_id, hi.image, h.title AS housing_title
    FROM maintenance m
    JOIN housing_image hi ON m.housing_id = hi.housing_id  
    JOIN housing h ON m.housing_id = h.id
    WHERE DATE_FORMAT(m.schedule_date, "%Y-%m") = :currentMonth
');
$maintenance_requete->bindParam(':currentMonth', $currentMonth, PDO::PARAM_STR);
$maintenance_requete->execute();
$maintenance_result = $maintenance_requete->fetchAll(PDO::FETCH_ASSOC);

$housing_id = array();
for ($i = 0; $i < count($maintenance_result); $i++) {
    array_push($housing_id, $maintenance_result[$i]['housing_id']);
}

$title = "Tâches à venir pour le mois en cours: ";
    
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/header_maintenance.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/font.css">
    <title>Liste des tâches à venir</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php require '../inc/tpl/header_maintenance.php' ?>
    <h2><?php echo $title?></h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Date prévue</th>
            <th>logement</th>
            <th>Image</th>
            <th>Statut</th>
            <th>Titre</th>
        </tr>
        <?php foreach ($maintenance_result as $maintenance) { ?>
            <tr>
                <td><?php echo $maintenance['id']; ?></td>
                <td><?php echo $maintenance['schedule_date']; ?></td>
                <td><?php echo $maintenance['housing_title']; ?></td>
                <td><img src="<?php echo $maintenance['image']; ?>" alt="Image du logement"></td>
                <td><?php echo $maintenance['status']; ?></td>
                <div class="link"><td><a href="../maintenance_zone/maintenance_details.php?id=<?php echo $maintenance['id']; ?>"><?php echo $maintenance['title']; ?><a/></td>
            </tr>
        <?php } ?>
    </table>
    <script src="../assets/js/header_public.js"></script>
</body>
</html>
