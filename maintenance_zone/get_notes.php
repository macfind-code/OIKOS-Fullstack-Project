<?php
require '../inc/pdo.php';


if (isset($_POST['maintenance_id'])) {
    $maintenanceId = $_POST['maintenance_id'] ?? $_GET['maintenance_id'] ?? null;


    $notes = $website_pdo->prepare('
        SELECT content
        FROM maintenance_note
        WHERE maintenance_id = :maintenanceId
    ');
    $notes->bindParam(':maintenanceId', $maintenanceId, PDO::PARAM_INT);
    $notes->execute();
    $maintenanceNotes = $notes->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($maintenanceNotes)) {
            // Génère la liste des notes de maintenance au format HTML
            $html = "";
            foreach ($maintenanceNotes as $note) {
                $html .= $note . "<br>";
            }

            // Renvoie la liste des notes de maintenance au format HTML
            echo $html;
    
    } else {
        echo "Aucune note de maintenance trouvée.";}

} else {
    echo "ID de maintenance non spécifié.";
}
?>