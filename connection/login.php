<?php 
    require '../inc/pdo.php';
    require '../inc/functions/token_function.php';
    require '../inc/functions/check_existing_user.php';
    session_start();
    $method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

    if ($method == "POST"){
        $mail = trim(filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL, FILTER_VALIDATE_EMAIL));
        $password = trim(filter_input(INPUT_POST, "password", FILTER_SANITIZE_FULL_SPECIAL_CHARS));

        if ($mail && $password){
            $check_existing_user_result = check_existing_user($website_pdo, $mail);

            if ($check_existing_user_result){

                if (password_verify($password, $check_existing_user_result['password'])){
                    $token = token();
                    $update_token = $website_pdo->prepare("
                        UPDATE token SET token = :token
                        WHERE user_id = (SELECT id FROM user WHERE mail = :mail)
                    ");

                    $update_token->execute([
                        ":token" => $token,
                        ":mail" => $mail
                    ]);

                    $_SESSION['token'] = $token;
                    $_SESSION['id'] = $check_existing_user_result['id'];
                    $_SESSION['mail'] = $check_existing_user_result['mail'];
                    $_SESSION['lastname'] = $check_existing_user_result['lastname'];
                    $_SESSION['firstname'] = $check_existing_user_result['firstname'];
                    $_SESSION['birth_date'] = $check_existing_user_result['birth_date'];
                    $_SESSION['phone_number'] = $check_existing_user_result['phone_number'];
                    $_SESSION['pp_image'] = $check_existing_user_result['pp_image'];
                    $_SESSION['management_role'] = $check_existing_user_result['management_role'];
                    $_SESSION['maintenance_role'] = $check_existing_user_result['maintenance_role'];
                    $_SESSION['admin_role'] = $check_existing_user_result['admin_role'];
                    $_SESSION['status'] = $check_existing_user_result['status'];
                    $_SESSION['registration_date_time'] = $check_existing_user_result['registration_date_time'];

                    header('Location: ../public_zone/homepage.php');
                    exit();
                    
                    // if ($_SESSION['status'] == 1){
                    //     header('Location: ../public_zone/homepage.php');
                    //     exit();
                    // }elseif ($_SESSION['status'] == 0){
                    //     header('Location: ../inc/tpl/inactive_user.html');
                    //     exit();
                    // }
                }else{
                    $error = true;
                }
            }else{
                $invalid_user = true;
            }
        }
    }
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/font.css"> <!-- Import des polices -->
    <link rel="stylesheet" href="../assets/css/login.css">
    <title>OIKOS | Connexion</title>
</head>
<body>
    <div class='logo'><h1>OIKOS</h1></div>
    <div class='form'>
        <div class='title'><h1>Connectez-vous</h1></div>
        <form method="POST">
            <div class='label-input-container'>
                <label for="mail">Email</label>
                <input type="text" id="mail" name="mail" required>
            </div>
            <div class='label-input-container'>
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class='forgot'><a href="./forgot_password.php"><p>Mot de passe oublié ?</p></a></div>
            <?php if(isset($error)){ ?>
                <div class="error"><p>Identifiants incorrects</p></div>
            <?php } ?>
            <?php if(isset($invalid_user)){ ?>
                <div class="error"><p>Aucun compte n'est associé à cette addresse email</p></div>
            <?php } ?>
            <div class='label-input-container'>
                <input type="submit" class="submit" value="Connexion">
            </div>
        </form>
        <div class='link'><a href="./register.php"><p>Inscrivez-vous <span>ici</span></p></a></div>
    </div>
    <div class="background-img"></div>
    <div class="copyright"><p>&copy; OIKOS - 2023</p></div>
</body>
</html>
