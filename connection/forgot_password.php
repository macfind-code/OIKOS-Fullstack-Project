<?php
    session_start();
    require '../inc/pdo.php';
    require '../inc/functions/check_existing_user.php';
    $method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

    if ($method == "POST"){
        $mail = trim(filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL));

        if($mail){
            if (filter_var($mail, FILTER_VALIDATE_EMAIL)){
                $check_existing_user_result = check_existing_user($website_pdo, $mail);

                if ($check_existing_user_result){
                    $_SESSION['existing_user'] = true;
                    $_SESSION['id'] = $check_existing_user_result['id'];
                    $_SESSION['security_question'] = $check_existing_user_result['security_question'];
                    $_SESSION['security_answer'] = $check_existing_user_result['security_answer'];
                    
                }else{
                    $inexisting_user = true;
                }
            }else{
                $invalid_email = true;
            }
        }else{
            $empty = true ;
        }
    }

    $jsonData = file_get_contents('php://input');
    $formData = json_decode($jsonData, true);

    if (isset($formData)){
        $_SESSION['form_security_answer'] = trim($formData['securityAnswer']);
        $new_password = trim($formData['newPassword']);
        $confirm_new_password = trim($formData['confirmNewPassword']);

        if ($_SESSION['form_security_answer'] == ''){
            header('Content-Type: application/json');
            echo json_encode(["error" => "Veuillez renseigner une réponse"]);
            exit();
        }elseif ((!$new_password || !$confirm_new_password) && $_SESSION['form_security_answer'] != ''){
            header('Content-Type: application/json');
            echo json_encode(["error" => "Veuillez remplir tous les champs"]);
            exit();
        }else{
            if ($_SESSION['security_answer'] != $_SESSION['form_security_answer']){
                header('Content-Type: application/json');
                echo json_encode(["error" => "Reponse incorrecte."]);
                exit();
            }else{
                if ($new_password == $confirm_new_password){
                    $new_password = password_hash(trim($new_password), PASSWORD_BCRYPT);

                    $new_password_request = $website_pdo -> prepare("
                    UPDATE user SET password = :new_password WHERE id = :id;
                    ");
                
                    $new_password_request -> execute([
                        ":id" => $_SESSION['id'],
                        ":new_password" => $new_password
                    ]);
                
                    $new_password_result = $new_password_request -> fetch(PDO::FETCH_ASSOC);

                    if(isset($new_password_result)){
                        header('Content-Type: application/json');
                        echo json_encode(["success" => "Mot de passe modifié"]);
                        exit();
                    }
                }else{
                    header('Content-Type: application/json');
                    echo json_encode(["error" => "Les mots de passe ne correspondent pas."]);
                    exit();
                }
            }
        }
    }
    
    // ----- PREMIERE PARTIE DU FORMULAIRE -----

    if ($method == 'GET' || isset($inexisting_user) || isset($empty) || isset($invalid_email)): ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../assets/css/forgot_password.css">
        <link rel="stylesheet" href="../assets/css/font.css">
        <title>OIKOS | Mot de passe oublié</title>
    </head>
    <body>
        <div class='form'>
            <div class='logo'><h1>OIKOS</h1></div>
            <div class='title'><h1>Mot de passe oublié</h1></div>
            <form method="POST">
                <div class='label-input-container'>
                    <label for="mail">Email</label>
                    <input type="text" id="mail" name="mail" required>
                </div>

                <?php if (isset($empty)){?>
                    <div><p>Veuillez renseigner une addresse email.</p></div>
                <?php } ?>
                <?php if (isset($inexisting_user)){?>
                    <div><p>Aucun compte n'est associé à cette addresse email.</p></div>
                <?php } ?>
                <?php if (isset($invalid_email)){?>
                    <div><p>Veuillez renseigner addresse email valide.</p></div>
                <?php } ?>

                <div class='label-input-container'>
                    <div class="btn"><input type="submit" name="submit" value="Valider"></div>
                </div>
            </form>
            <div class=""><button id="cancel-btn">Annuler</button></div>
        </div>
        <div class="background-img"></div>
        <div class="copyright"><p>&copy; OIKOS - 2023</p></div>
        <!-- <script src="../assets/js/connection_js/forgot_password.js"></script> -->
        <script>
            const cancelBtn = document.getElementById("cancel-btn")
            cancelBtn.addEventListener('click', () => {
                window.location.href = './login.php'
            })
        </script>
    </body>
    </html>


    <!-- DEUXIEME ET TROISIEME PARTIES DU FORMULAIRE -->

    <?php elseif ($method == "POST" && isset($_SESSION['existing_user'])) : 
        if ($_SESSION['security_question']) {
            switch ($_SESSION['security_question']) {
                case 'first-pet-name':
                    $_SESSION['security_question'] = 'Quel était le nom de votre premier animal de compagnie ?';
                    break;
                case 'mother-birth-place':
                    $_SESSION['security_question'] = 'Quel est le lieu de naissance de votre mère ?';
                    break;
                case 'first-school-name':
                    $_SESSION['security_question'] = 'Quel est le  nom de votre première école ?';
                    break;
                case 'dream-work':
                    $_SESSION['security_question'] = 'Quel est le métier de vos rêveS ?';
                    break;
                case 'first-love-name':
                    $_SESSION['security_question'] = 'Quel est le nom de votre premier amour ?';
                    break;
            }
        }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../assets/css/forgot_password.css">
        <link rel="stylesheet" href="../assets/css/font.css">
        <title>OIKOS | Mot de passe oublié</title>
    </head>
    <body>
        <div class='form'>
        <div class='logo'><h1>OIKOS</h1></div>
            <div class='title'><h1>Mot de passe oublié</h1></div>
            <form method="POST">
                <div class='label-input-container'>
                    <label for="security-question">Votre question de sécurité :</label>
                    <div class="question"><p><?= $_SESSION['security_question'] ?></p></div>
                </div>
                <div class='label-input-container'>
                    <label for="security-answer">Votre réponse :</label>
                    <input type="text" id="security-answer" name="security-answer" required>
                </div>

                <div class='label-input-container'>
                    <div class="btn" id="button"><button>Valider</button></div>
                </div>
            </form>
            <div class=""><button id="cancel-btn2">Annuler</button></div>
        </div>
    <div class="background-img"></div>
    <div class="modal" id="modal">
            <div class="modal-container" id="modal-container">
                <div class='modal-container-title'><h2>Nouveau mot de passe</h2></div>
                <form method="POST">
                    <div class='modal-label-input-container'>
                        <label for="new-password">Mot de passe</label>
                        <input type="password" id="new-password" name="new-password" required>
                    </div>

                    <div class='modal-label-input-container'>
                        <label for="confirm-new-password">Confirmez le mot de passe</label>
                        <input type="password" id="confirm-new-password" name="confirm-new-password" required>
                    </div>

                    <div id="display-error"></div>

                    <div class="modal-label-input-container">
                        <button class="submit" id="submit-modal-form">Valider</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="copyright"><p>&copy; OIKOS - 2023</p></div>
        <script src="../assets/js/connection_js/forgot_password.js"></script>
        <script>
            const cancelBtn2 = document.getElementById("cancel-btn2")
            cancelBtn2.addEventListener('click', () => {
                window.location.href = './login.php'
            })
        </script>
    </body>
    </html>

<?php endif; ?>