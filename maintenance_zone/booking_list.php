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

// Récupérer le mois actuel
$currentMonth = date('Y-m');

// Vérifier si un mois différent a été sélectionné
if (isset($_GET['month'])) {
    $selectedMonth = $_GET['month'];
} else {
    $selectedMonth = $currentMonth;
}

// Requête pour récupérer les dates de réservation de chaque logement
$reservationQuery = "
    SELECT housing.id AS housing_id, housing.title, booking.start_date_time, booking.end_date_time
    FROM housing
    LEFT JOIN booking ON housing.id = booking.housing_id
    WHERE DATE_FORMAT(booking.start_date_time, '%Y-%m') = :selectedMonth
    ORDER BY housing.id, booking.start_date_time
";
$reservationStmt = $website_pdo->prepare($reservationQuery);
$reservationStmt->bindParam(':selectedMonth', $selectedMonth, PDO::PARAM_STR);
$reservationStmt->execute();
$reservations = $reservationStmt->fetchAll(PDO::FETCH_ASSOC);

// Tableau pour stocker les dates de réservation de chaque logement
$housingReservations = [];

// Organiser les dates de réservation par logement
foreach ($reservations as $reservation) {
    $housingId = $reservation['housing_id'];

    if (!isset($housingReservations[$housingId])) {
        $housingReservations[$housingId] = [
            'title' => $reservation['title'],
            'dates' => []
        ];
    }

    $housingReservations[$housingId]['dates'][] = [
        'start_date' => $reservation['start_date_time'],
        'end_date' => $reservation['end_date_time']
    ];
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/header_maintenance.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/font.css">
    <title>Booking</title>
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

    <script src="../assets/js/maintenance_zone_js/header_maintenance.js"></script>

    <h2>Booking à venir</h2>

    <div class="month">
    <?php echo $selectedMonth;
    echo '<a href="?month=' . date('Y-m', strtotime($selectedMonth . ' -1 month')) . '">&lt; Mois précédent</a> | ';
    echo '<a href="?month=' . date('Y-m', strtotime($selectedMonth . ' +1 month')) . '">Mois suivant &gt;</a>'; ?>
    </div>

    <div class="main">
    <?php if (count($housingReservations) > 0) {
            echo "<table>";
            echo "<tr>";
            echo "<th>ID du logement</th>";
            echo "<th>Logement</th>";
            echo "<th>Dates de réservation</th>";
            echo "</tr>";

            foreach ($housingReservations as $housingId => $housingReservation) {
                echo "<tr>";
                echo "<td>" . $housingId . "</td>";
                echo "<td>" . htmlspecialchars($housingReservation['title']) . "</td>";
                echo "<td>";

                foreach ($housingReservation['dates'] as $reservationDate) {
                    $startDate = date_format(date_create($reservationDate['start_date']), 'd/m/Y');
                    $endDate = date_format(date_create($reservationDate['end_date']), 'd/m/Y');
                    echo "Début du séjour : " . $startDate . " - " . "Fin du séjour: " . $endDate . "<br>";
                }

                echo "</td>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "Pas de réservation pour ce mois.";
        }
        ?>
    </div>
</body>
</html>
