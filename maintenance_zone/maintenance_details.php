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

// Vérifier si l'ID de la maintenance est spécifié dans l'URL
if (isset($_GET['id'])) {
    $maintenanceId = $_GET['id'];
    // Vérifier si le formulaire a été soumis
    if (isset($_POST['submit'])) {
        // Récupérer les valeurs cochées et les stocker dans la variable de session
        $_SESSION['maintenance_check'][$maintenanceId] = $_POST['maintenance_check'];
    } else {
        // Si le formulaire n'a pas été soumis, vérifier si les valeurs sont déjà stockées dans la variable de session
        if (isset($_SESSION['maintenance_check'][$maintenanceId])) {
            $checkedValues = $_SESSION['maintenance_check'][$maintenanceId];
        } else {
            $checkedValues = []; // Si aucune valeur n'est stockée, initialiser avec un tableau vide
        }
    }

} else {
    echo "ID de maintenance non spécifié.";
    header('Location: ./maintenance_list.php');
    exit();
}

// Vérifier si l'ID de la maintenance est spécifié dans l'URL
if (isset($_GET['id'])) {
    $maintenanceId = $_GET['id'];

    // Récupérer les détails de la maintenance à partir de l'ID
    $maintenance_requete = $website_pdo->prepare('
        SELECT m.id, m.status, m.title, m.schedule_date, m.housing_id, hi.image, h.title AS housing_title
        FROM maintenance m
        JOIN housing_image hi ON m.housing_id = hi.housing_id  
        JOIN housing h ON m.housing_id = h.id
        WHERE m.id = :maintenanceId
    ');
    $maintenance_requete->bindParam(':maintenanceId', $maintenanceId, PDO::PARAM_INT);
    $maintenance_requete->execute();
    $maintenance_details = $maintenance_requete->fetch(PDO::FETCH_ASSOC);

} else {
    echo "ID de maintenance non spécifié.";
}


$title = "Checklist: ";

// Traitement du formulaire après soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maintenanceNote = $_POST['maintenance_note'];

    // Déclaration de la variable $isChecked
    $isChecked = false;
    $maintenance_check = []; // Initialize the variable

    if (isset($_POST['submit']) && isset($_POST['maintenance_check'])) {
        $maintenance_check = $_POST['maintenance_check'];
        // Vérifier si toutes les cases sont cochées
        $isChecked = true; // On considère initialement que toutes les cases sont cochées
        foreach ($maintenance_check as $check) {
            if ($check != 'on') {
                $isChecked = false;
                break;
            }
        }
    }

    // Mettre à jour le statut en fonction de la validation
    if (count($maintenance_check) == 8) {
        $status = 'fait';
    } elseif (count($maintenance_check) > 0) {
        $status = 'en cours';
    } else {
        $status = 'à faire';
    }

    // Supprimer la maintenance si la date limite est atteinte
    $currentDate = date('Y-m-d');
    if ($scheduleDate === $currentDate) {
        // Supprimer la maintenance
        $deleteMaintenance = $website_pdo->prepare('DELETE FROM maintenance WHERE id = :maintenanceId');
        $deleteMaintenance->bindParam(':maintenanceId', $maintenanceId, PDO::PARAM_INT);
        $deleteMaintenance->execute();
    }

    // Mettre à jour le statut dans la table de maintenance
    $updateMaintenance = $website_pdo->prepare('UPDATE maintenance SET status = :status WHERE id = :maintenanceId');
    $updateMaintenance->bindParam(':status', $status, PDO::PARAM_STR);
    $updateMaintenance->bindParam(':maintenanceId', $maintenanceId, PDO::PARAM_INT);
    $updateMaintenance->execute();

    // Insérer la note de maintenance si elle n'est pas vide ou trop courte
    if (strlen($maintenanceNote) >= 2) {
        $insertNote = $website_pdo->prepare('INSERT INTO maintenance_note (maintenance_id, user_id, content) VALUES (:maintenanceId, :userId, :content)');
        $insertNote->bindParam(':maintenanceId', $maintenanceId, PDO::PARAM_INT);
        $insertNote->bindParam(':userId', $_SESSION['id'], PDO::PARAM_INT);
        $insertNote->bindParam(':content', $maintenanceNote, PDO::PARAM_STR);
        $insertNote->execute();
    }

    header('Location: maintenance_list.php');
    exit();
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title ?></title>
    <link rel="stylesheet" href="../assets/css/header_maintenance.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/font.css">
</head>
<body>
    <?php require '../inc/tpl/header_maintenance.php' ?>

    <h2><?php echo $title ?></h2>

    <form id="maintenance-form" action="" method="POST">
        <h3><?php echo $maintenance_details['housing_title']?></h3>
        <p><?php echo "Date limite: " . $maintenance_details['schedule_date']?></p>

        <div class="maintenance-details-section">
        <h4>Entretien de surface</h4>
        <input type="checkbox" name="maintenance_check[]" value="Nettoyage des sols" <?php if(in_array('Nettoyage des sols', (array)$checkedValues)) echo 'checked'; ?>>Nettoyage des sols<br>
        <input type="checkbox" name="maintenance_check[]" value="Lavage des vitres, écrans et mirroirs" <?php if(in_array('Lavage des vitres, écrans et mirroirs', (array)$checkedValues)) echo 'checked'; ?>>Lavage des vitres, écrans et mirroirs<br>
        <input type="checkbox" name="maintenance_check[]" value="Poussière et cirage des meubles" <?php if(in_array('Poussière et cirage des meubles', (array)$checkedValues)) echo 'checked'; ?>>Poussière et cirage des meubles<br>
        <input type="checkbox" name="maintenance_check[]" value="Changer les draps" <?php if(in_array('Changer les draps', (array)$checkedValues)) echo 'checked'; ?>>Changer les draps<br>
        <input type="checkbox" name="maintenance_check[]" value="Désinfections interrupteurs et poignées" <?php if(in_array('Désinfections interrupteurs et poignées', (array)$checkedValues)) echo 'checked'; ?>>Désinfections interrupteurs et poignées<br>
        <input type="checkbox" name="maintenance_check[]" value="Changer les serviettes" <?php if(in_array('Changer les serviettes', (array)$checkedValues)) echo 'checked'; ?>>Changer les serviettes<br>
        <!-- Ajouter d'autres cases à cocher pour l'entretien de surface si nécessaire -->
    </div>

    <div class="maintenance-details-section">
        <h4>Vérifications techniques</h4>
        <input type="checkbox" name="maintenance_check[]" value="Vérifications des lumières et appareils doméstiques" <?php if(in_array('Vérifications des lumières et appareils doméstiques', (array)$checkedValues)) echo 'checked'; ?>>Vérification des lumières et appareils doméstiques<br>
        <input type="checkbox" name="maintenance_check[]" value="Vérification des fuites et évacuation des eaux" <?php if(in_array('Vérification des fuites et évacuation des eaux', (array)$checkedValues)) echo 'checked'; ?>>Vérification des fuites et évacuation des eaux<br>
        <!-- Ajouter d'autres cases à cocher pour les vérifications techniques si nécessaire -->
    </div>

        <div class="maintenance-details-section">
            <h4>Ajouter une note</h4>
            <textarea name="maintenance_note" rows="4" cols="50"></textarea>
        </div>

        <input type="submit" name="submit" value="Valider">
        
        <input type="hidden" id="maintenance-id-input" name="maintenance_id" value="<?php echo $maintenanceId; ?>">


    </form>
    
    <input type="button" id="show-notes-btn" value="Voir toutes les notes">
    <div id="maintenance-notes-container" style="display: none;">
        <div class='maintenance-notes'>
            <h4>D'autres notes :</h4>
            <ul id="maintenance-notes-list">
            </ul>
        </div>
    </div>

    <script src="../assets/js/maintenance_zone_js/get_notes.js"></script>
    <script src="../assets/js/header_public.js"></script>
</body>
</html>