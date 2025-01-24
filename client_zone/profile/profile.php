<?php
session_start();
require '../../inc/pdo.php';
require "../../inc/functions/token_function.php";
$method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

if(isset($_SESSION['token'])){
    $check = token_check($_SESSION["token"], $website_pdo, $_SESSION['id']);
    if($check == 'false'){
        header('Location: ../../connection/login.php');
        exit();
    }else {
        if ($_SESSION['status'] == 0) {
            header ('Location: ../../inc/tpl/inactive_user.html');
            exit(); 
        }
    }   
}elseif(!isset($_SESSION['token'])){
    header('Location: ../../connection/login.php');
    exit();
}

$heart_icon = '../../assets/images/heart.svg';
$menu_icon =   '../../assets/images/menu.svg';
$account_icon = '../../assets/images/account.svg';
$link_favorite = '../../client_zone/profile/favorites.php';
$homepage_link = "../../public_zone/homepage.php";

$last_name_error = "";
$first_name_error = "";
$phone_number_error = "";
$mail_error = "";
$birth_date_error = "";
$error = "";
$success = "";

$path = '../../uploads/';


$heart_icon = '../../assets/images/heart.svg';
$menu_icon = '../../assets/images/menu.svg';
$account_icon = '../../assets/images/account.svg';

if (!isset($_SESSION['id'])) {
    header("Location:../../connection/login.php");
    exit; 
}

$get_value = $website_pdo -> prepare('
    SELECT * FROM user WHERE id = :user_id
');

$get_value -> execute([
    ':user_id' => $_SESSION['id']
]);

$result = $get_value -> fetch(PDO::FETCH_ASSOC);


if($method == 'POST'){
    $first_name = filter_input(INPUT_POST,'firstname');
    $last_name = trim(filter_input(INPUT_POST,'lastname'));
    $phone_number = trim(filter_input(INPUT_POST,'phone_number'));
    $mail = trim(filter_input(INPUT_POST,'mail',FILTER_VALIDATE_EMAIL));
    $birth_date = trim(filter_input(INPUT_POST,'birth_date'));
    $empty = true ;
    if(!$first_name){
        $first_name_error = "Le champ Prénom est requis";
        $empty = false ;
    }
    if(!$last_name){
        $first_name_error = "Le champ Nom est requis";
        $empty = false ;
    }
    if(!$phone_number){
        $phone_number_error = "Le champ Numéro de téléphone est requis";
        $empty = false ;
    }   
    if(!$mail){
        $mail_error = "Me champ mail est requis";
        $empty = false ;
    }
    if(!$birth_date){
        $birth_date_error = "Le champs Date de Naissance est requis";
        $empty = false ;
    }

    if($empty){
        $upload_img_path = '../../uploads/';
        $img  = $_FILES['photo'];

        $img_name = time().$img['name'];
        $img_tmp_name = $img['tmp_name'];
        $target = $upload_img_path.$img_name;
        $img_error = $img['error'];
        if (is_uploaded_file($img_tmp_name)) {
            $mime_type = mime_content_type($img_tmp_name);
            $allowed_file_type = ['image/jpeg', 'image/png'];
            if(!in_array($mime_type, $allowed_file_type)) {
                echo "$img_name n'a pas le bon format d'image.";
            } elseif (filesize($img_tmp_name) > 3000000) {
                echo 'Le fichier est trop volumineux';
            } elseif ($img_error == UPLOAD_ERR_OK) {
                $update_picture = $website_pdo -> prepare ('
                    UPDATE user SET pp_image = :pp_image
                    WHERE id = :id;
                ');
                $update_picture -> execute([
                    ':pp_image' => $img_name,
                    ":id" => $_SESSION['id']
                ]);
                move_uploaded_file($img_tmp_name, $target);
            }
        }
        $update_info = $website_pdo -> prepare('
        UPDATE user SET 
        firstname = :first_name, lastname = :last_name, phone_number = :phone_number, mail = :mail, birth_date = :birth_date 
        WHERE id = :id;
        ');

        $update_info -> execute ([
            ':last_name' => $last_name,
            ':first_name' => $first_name,
            ':phone_number' => $phone_number,
            ':mail' => $mail,
            ':birth_date' => $birth_date,
            ':id' => $_SESSION['id']
        ]);  
         $success = 'Vos informations ont bien été mis à jour';
    }
    else{
        $error = "Tous les champs sont requis";
    }
}
$photo_test = "../../assets/images/minuit.png";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/profile.css">
    <link rel="stylesheet" href="../../assets/css/font.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/header_publiczone.css">
    <title>OIKOS | Mon compte</title>
</head>
<body>
    <?php require '../../inc/tpl/header_publiczone.php' ?>

    <form method="POST" enctype="multipart/form-data">
        <div class = "container">
            <div class ="high">
                <h2>Compte</h2>
                    <label for="photo">
                        <img height="100%" width="100%" src="<?= $path . $result['pp_image']?>" alt="">
                        <input type="file" name="photo" id="photo" hidden>
                    </label>
                <p>Informations personnelles</p>
            </div>
            <div class = "container2">
                <div class = "container-left">
                    <span>Prénom :</span>
                    <input type="text" name = "firstname" value = "<?= $result['firstname'] ?>" class = "input-text <?php if($first_name_error):?> error-line <?php endif ?>" >
                    <span>Numéro de téléphone :</span>
                    <input type="tel" name="phone_number" value = "<?= $result['phone_number'] ?>" pattern= "^(0|\+33|0033)[1-9]([-. ]?[0-9]{2}){4}$" class = "input-text <?php if($first_name_error):?> error-line <?php endif ?>">
                    <span>Date de naissance :</span>
                    <input type="date" name="birth_date" value="<?= $result['birth_date'] ?>" class = "input-text <?php if($first_name_error):?> error-line <?php endif ?>">
                    <input type="submit" value="Sauvegarder">
                    <p class = "success-msg"><?= $success ?></p>
                    <p class = "error-msg" ><?= $error ?></p>
                </div>
                <div class = "container-right">
                    <span>Nom :</span>
                    <input type="text" name="lastname" value = "<?= $result['lastname'] ?>" class = "input-text <?php if($first_name_error):?> error-line <?php endif ?>">
                    <span>Adresse E-mail :</span>
                    <input type="text" name= "mail" value="<?= $result['mail'] ?>" class = "input-text <?php if($first_name_error):?> error-line <?php endif ?>">
                </div> 
            </div>
        </div>  
    </form>
    <script src="../../assets/js/header_public.js"></script>
    <script>
        var error=document.querySelector('.error-msg')
        
    </script>
</body>

</html>