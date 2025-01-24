<?php  
    require "../inc/pdo.php";
    require "../inc/functions/token_function.php";
    require "../inc/functions/check_existing_user.php";
    session_start();
    
    $heart_icon = '../assets/images/heart.svg';
    $menu_icon =   '../assets/images/menu.svg';
    $account_icon = '../assets/images/account.svg';
    $link_favorite = '../client_zone/profile/favorites.php';


    if(isset($_SESSION['token'])){
        $check = token_check($_SESSION["token"], $website_pdo, $_SESSION['id']);
        if($check == 'false'){
            header('Location: ../connection/login.php');
            exit();
        }
        else {
            if ($_SESSION['status'] == 0) {
                header ('Location: ../inc/tpl/inactive_user.html');
                exit(); 
            }
            if ($_SESSION['admin_role'] == 0){
                header ('Location: ../public_zone/homepage.php');
                exit();
            }
        }   
    }elseif(!isset($_SESSION['token'])){
        header('Location: ../connection/login.php');
        exit();
    }
    
    $method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

    if ($method == "POST"){
        $role_mail = trim(filter_input(INPUT_POST, "role-mail", FILTER_SANITIZE_EMAIL));
        $management_role = filter_input(INPUT_POST, "management-role");
        $maintenance_role = filter_input(INPUT_POST, "maintenance-role");
        $admin_role = filter_input(INPUT_POST, "admin-role");

        if ($role_mail){
            if (filter_var($role_mail, FILTER_VALIDATE_EMAIL)){
                $check_existing_user_result = check_existing_user($website_pdo, $role_mail);

                if ($check_existing_user_result){
                    if (isset($management_role)){
                        $management_role = 1;
                    }else {
                        $management_role = 0;
                    }
                    if (isset($maintenance_role)){
                        $maintenance_role = 1;
                    }else {
                        $maintenance_role = 0;
                    }
                    if (isset($admin_role)){
                        $admin_role = 1;
                    }else {
                        $admin_role = 0;
                    }

                    $modify_role = $website_pdo->prepare("
                        UPDATE user
                        SET management_role = :management_role, maintenance_role = :maintenance_role, admin_role = :admin_role
                        WHERE mail = :mail
                    ");

                    $modify_role->execute([
                        ":management_role" => $management_role,
                        ":maintenance_role" => $maintenance_role,
                        ":admin_role" => $admin_role,
                        ":mail" => $role_mail
                    ]);
                    $modify_role = true;

                } else {
                    $inexisting_user = true;
                }
            } else {
                $invalid_email = true;
            }
        } else {
            $empty = true ;
        }
    }


    $json_data = file_get_contents('php://input');
    $form_data = json_decode($json_data, true);

    if (isset($form_data['type'])){
        $type = $form_data['type'];

        if ($type == 'activate'){
            $mail = trim($form_data['activateMail']);
            if ($mail){
                if (filter_var($mail, FILTER_VALIDATE_EMAIL)){
                    $check_existing_user_result = check_existing_user($website_pdo, $mail);

                    if ($check_existing_user_result){
                        $user_status = $check_existing_user_result['status'];
                        if ($user_status == 0){

                            $activate_account = $website_pdo->prepare ("
                                UPDATE user SET status = :status WHERE mail = :mail
                            ");

                            $activate_account->execute([
                                ":status" => 1,
                                ":mail" => $mail
                            ]);
                            echo json_encode(["success" => "Le compte a bien été activé."]);
                            exit();

                        } elseif ($user_status == 1){
                            echo json_encode(["error" => "Ce compte est déja actif."]);
                            exit();
                        }
                    } else {
                        echo json_encode(["error" => "Aucun compte n'est associé à cette addresse email."]);
                        exit();
                    }
                } else {
                    echo json_encode(["error" => "Veuillez renseigner une addresse email valide."]);
                    exit();
                }
            } else {
                echo json_encode(["error" => "Veuillez renseigner une addresse email"]);
                exit(); 
            }

        } elseif ($type == 'desactivate'){
            $mail = trim($form_data['desactivateMail']);
            if ($mail){
                if (filter_var($mail, FILTER_VALIDATE_EMAIL)){
                    $check_existing_user_result = check_existing_user($website_pdo, $mail);

                    if ($check_existing_user_result){
                        $user_status = $check_existing_user_result['status'];
                        if ($user_status == 1){

                            $desactivate_account = $website_pdo->prepare ("
                                UPDATE user SET status = :status WHERE mail = :mail
                            ");

                            $desactivate_account->execute([
                                ":status" => 0,
                                ":mail" => $mail
                            ]);
                            echo json_encode(["success" => "Le compte a bien été désactivé."]);
                            exit();

                        } elseif ($user_status == 0){
                            echo json_encode(["error" => "Ce compte est déjà inactif."]);
                            exit();
                        }
                    } else {
                        echo json_encode(["error" => "Aucun compte n'est associé à cette addresse email."]);
                        exit();
                    }
                } else {
                    echo json_encode(["error" => "Veuillez renseigner une addresse email valide."]);
                    exit();
                }
            } else {
                echo json_encode(["error" => "Veuillez renseigner une addresse email"]);
                exit(); 
            }

        } elseif ($type == 'delete'){
            $mail = trim($form_data['deleteMail']);
            if ($mail){
                if (filter_var($mail, FILTER_VALIDATE_EMAIL)){
                    $check_existing_user_result = check_existing_user($website_pdo, $mail);
                    if ($check_existing_user_result){

                        $user_bookings = $website_pdo->prepare("
                            SELECT id FROM booking WHERE user_id = (SELECT id FROM user WHERE mail = :mail)
                        ");
                        $user_bookings->execute([
                            ":mail" => $mail
                        ]);

                        $user_bookings_result = $user_bookings->fetchAll(PDO::FETCH_ASSOC);
                
                        foreach ($user_bookings_result as $user_booking){
                            $booking_id = $user_booking['id'];
                
                            $delete_booking_service = $website_pdo->prepare("
                                DELETE FROM booking_service WHERE booking_id = :booking_id
                            ");
                            $delete_booking_service->execute([
                                ":booking_id" => $booking_id
                            ]);
                        }
                
                        $delete_token = $website_pdo->prepare("
                            DELETE FROM token WHERE user_id = (SELECT id FROM user WHERE mail = :mail)
                        ");
                        $delete_token->execute ([
                            ":mail" => $mail
                        ]);                
                
                        $delete_maintenance_note = $website_pdo->prepare ("
                            DELETE FROM maintenance_note WHERE user_id = (SELECT id FROM user WHERE mail = :mail)
                        ");
                        $delete_maintenance_note->execute([
                            ":mail" => $mail
                        ]);                
                
                        $delete_housing_review = $website_pdo->prepare("
                            DELETE FROM housing_review WHERE user_id = (SELECT id FROM user WHERE mail = :mail)
                        ");
                        $delete_housing_review->execute([
                            ":mail" => $mail
                        ]);
                                
                        $delete_favorite = $website_pdo->prepare("
                            DELETE FROM favorite WHERE user_id = (SELECT id FROM user WHERE mail = :mail)
                        ");
                        $delete_favorite->execute([
                            ":mail" => $mail
                        ]);
                                
                        $delete_booking = $website_pdo->prepare("
                            DELETE FROM booking WHERE user_id = (SELECT id FROM user WHERE mail = :mail)
                        ");
                        $delete_booking->execute([
                            ":mail" => $mail
                        ]);                
                
                        $delete_user = $website_pdo->prepare ("
                            DELETE FROM user WHERE mail = :mail
                        ");
                        $delete_user->execute([
                            ":mail" => $mail
                        ]);
                        echo json_encode(["success" => "Le compte a bien été supprimé."]);
                        exit();

                    } elseif (!$check_existing_user_result) {
                        echo json_encode(["error" => "Aucun compte n'est associé à cette adresse email."]);
                        exit();
                    }
                } else {
                    echo json_encode(["error" => "Veuillez renseigner une adresse email valide."]);
                    exit();
                }
            } else {
                echo json_encode(["error" => "Veuillez renseigner une adresse email"]);
                exit(); 
            }
        }
    }


    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../assets/css/font.css">
        <link rel="stylesheet" href="../assets/css/global.css">
        <link rel="stylesheet" href="../assets/css/header_admin.css">
        <title>Zone Administration</title>
    </head>
    <body>
        <?php require '../inc/tpl/header_admin.php' ?>
            <div class='section-roles'>
                <form method="POST">
                <div class='section-roles-left'>
                    <div class='roles-title'><h2>Choisir le rôle utilisateur :</h2></div>
                    <div class='role-mail'>
                        <label for="role-mail">Addresse Email : </label>
                        <input type="text" id="role-mail" name="role-mail" placeholder="name@example.com">
                        <div class="dropdown" id="role-dropdown" style="background-color : blue; width : 100px; height : 100px"></div>
                    </div>
                </div>
                <div class='section-roles-right'>
                    <div class='role-checkbox'>
                        <div><h3>Rôles :</h3></div>                
                        <label for="management-role">Rôle gestion</label>
                        <input type="checkbox" id="management-role" name="management-role" value="management-role">

                        <label for="maintenance-role">Rôle entretien</label>
                        <input type="checkbox" id="maintenance-role" name="maintenance-role" value="maintenance-role">                    

                        <label for="admin-role">Rôle admin</label>
                        <input type="checkbox" id="admin-role" name="admin-role" value="admin-role">
                    </div>
                    <div><input type="submit" id="submit" name="submit" value="Confirmer le(s) rôle(s)"></div>

                    <?php if (isset($modify_role)){ ?>
                        <div><p>Les rôles de l'utilisateur ont bien été modifiés.</p></div>
                    <?php } ?>
                    <?php if (isset($empty)){?>
                        <div><p>Veuillez renseigner une addresse email.</p></div>
                    <?php } ?>
                    <?php if (isset($inexisting_user)){?>
                        <div><p>Aucun compte n'est associé  à cette addresse email.</p></div>
                    <?php } ?>
                    <?php if (isset($invalid_email)){?>
                        <div><p>Veuillez renseigner une addresse email valide.</p></div>
                    <?php } ?>
                </div>
                </form>
            </div>

            <div>
                <div><h2>Activer un compte :</h2></div>
                <form method="POST">
                    <div>
                        <label for="activate-mail">Addresse Email</label>
                        <input type="text" id="activate-mail" name="activate-mail" placeholder="name@example.com">
                        <div class="dropdown" id="activate-dropdown" style="background-color : blue; width : 100px; height : 100px"></div>
                    </div>
                    <div><button id="activate-btn">Activer le compte</button></div>
                    <div id="activate-msg"></div>
                </form>
            </div>

            <div>
                <div><h2>Désactiver un compte :</h2></div>
                <form method="POST">
                    <div>
                        <label for="desactivate-mail">Addresse Email</label>
                        <input type="text" id="desactivate-mail" name="desactivate-mail" placeholder="name@example.com">
                        <div class="dropdown" id="desactivate-dropdown" style="background-color : blue; width : 100px; height : 100px"></div>
                    </div>
                    <div><button id="desactivate-btn">Désactiver le compte</button></div>
                    <div id="desactivate-msg"></div>
                </form>
            </div>

            <div>
                <div><h2>Supprimer un compte :</h2></div>
                <form method="POST">
                    <div>
                        <label for="delete-mail">Addresse Email</label>
                        <input type="text" id="delete-mail" name="delete-mail" placeholder="name@example.com">
                        <div class="dropdown" id="delete-dropdown" style="background-color : blue; width : 100px; height : 100px"></div>
                    </div>
                    <div><button id="delete-btn">Supprimer le compte</button></div>
                    <div id="delete-msg"></div>
                </form>
            </div>

            <!-- <div><button id="link-btn">Voir tous les comptes</button></div> -->
        <div id="modal" style="display: none;">
            <div id="modal-container">
                <form method="POST">
                    <div><h3>Voulez-vous vraiment supprimer le compte ?</h3></div>
                    <div><button id="confirm-deletion">Confirmer</button></div>
                    <div><button id="cancel-deletion">Annuler</button></div>
                </form>
            </div>
        </div>

        <script src="../assets/js/admin_zone_js/users_options.js"></script>
        <script src="../assets/js/header_public.js"></script>
        <script>
            const cancelDeletion = document.getElementById('cancel-deletion')
            cancelDeletion.addEventListener('click', function(event) {
                event.preventDefault();
                modal.style.display = 'none'
            })
        </script>
    </body>
    </html>