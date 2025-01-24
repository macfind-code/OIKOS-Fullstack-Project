<?php
    session_start();
    require '../inc/pdo.php';
    require '../inc/functions/check_existing_user.php';
    $method = filter_input(INPUT_SERVER, "REQUEST_METHOD");

    if ($method =="POST"){
        $jsonData = file_get_contents('php://input');
        $formData = json_decode($jsonData, true);
        
        $mail = trim($formData['mail']);
        $password = trim($formData['password']);
        $confirm_password = trim($formData['confirmPassword']);
        $security_question = trim($formData['securityQuestion']);
        $security_answer = trim($formData['securityAnswer']);
        $lastname = trim($formData['lastname']);
        $firstname = trim($formData['firstname']);
        $birth_date = trim($formData['birthDate']);
        $phone_number = trim($formData['phoneNumber']);

        if (!$mail || !$password || !$confirm_password || !$security_question || !$security_answer || !$lastname || !$firstname || !$birth_date || !$phone_number){
            echo json_encode(["error" => "Veuillez renseigner tous les champs."]);
            exit();
        }else{
            // $pattern = "^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$";
            $pattern = "/^\+?\d{1,4}?[-.\s]?\(?\d{1,3}?\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}$/";

            if (preg_match($pattern, $phone_number)){

                if (filter_var($mail, FILTER_VALIDATE_EMAIL)){
                    $check_existing_user_result = check_existing_user($website_pdo, $mail);

                    if (!$check_existing_user_result && $password == $confirm_password){
                        $password = password_hash(trim($password), PASSWORD_BCRYPT);
                        $register_user = $website_pdo->prepare("
                            INSERT INTO user (mail, password, security_question, security_answer, lastname, firstname, birth_date, phone_number, pp_image, client_role, management_role, maintenance_role, admin_role, status, registration_date_time)
                            VALUES (:mail, :password, :security_question, :security_answer, :lastname, :firstname, :birth_date, :phone_number, :pp_image, :client_role, :management_role, :maintenance_role, :admin_role, :status, NOW())
                        ");
            
                        $register_user->execute([
                            ':mail' => $mail,
                            ':password' => $password,
                            ':security_question' => $security_question,
                            ':security_answer' => $security_answer,
                            ':lastname' => $lastname,
                            ':firstname' => $firstname,
                            ':birth_date' => $birth_date,
                            ':phone_number' => $phone_number,
                            ':pp_image' => 'default_pp.png',
                            ':client_role' => 1,
                            ':management_role' => 0,
                            ':maintenance_role' => 0,
                            ':admin_role' => 0,
                            ':status' => 1
                        ]);

                        $last_insert_id = $website_pdo->lastInsertId();
                
                        $register_user_token = $website_pdo->prepare("
                            INSERT INTO token (user_id, token)
                            VALUES (:user_id, :token)
                        ");
                
                        $register_user_token->execute([
                            ':user_id' => $last_insert_id,
                            ':token' => 'null'
                        ]);

                        echo json_encode(["success" => "Inscription réussie"]);
                        exit();

                    }elseif($check_existing_user_result){
                        echo json_encode(["error" => "Cette addresse email est déjà utilisée."]);
                        exit();

                    }elseif($password != $confirm_password){
                        echo json_encode(["error" => "Les mots de passe ne correspondent pas."]);
                        exit();
                    }
                }else{
                    echo json_encode(["error" => "Veuillez renseigner une addresse email valide"]);
                    exit();
                }
            }else{
                echo json_encode(["error" => "Veuillez renseigner un numéro de téléphone valide."]);
                exit();
            }
        }
    }


?>
<!DOCTYPE html>
<?php

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/font.css"> <!-- Import des polices -->
    <link rel="stylesheet" href="../assets/css/register.css">
    <title>OIKOS | Inscription</title>
</head>
<body>
    <div class='form'>
    <div class='logo'><h1>OIKOS</h1></div>
    <div class='title'><h1>Bienvenue sur Oikos</h1></div>
        <form method='POST'>
            <div class='form-container-left'>
                <div class='label-input-container'>
                    <label for="lastname">Nom</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>

                <div class='label-input-container'>
                    <label for="mail">Email</label>
                    <input type="email" id="mail" name="mail" required>
                </div>

                <div class='label-input-container'>
                    <label for="phone-number">Numéro de téléphone</label>
                    <input type="tel" id="phone-number" name="phone-number" pattern="/^\+?\d{1,4}?[-.\s]?\(?\d{1,3}?\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}$/" required>
                </div>

                <div class='label-input-container'>
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
            </div>

            <div class='form-container-right'>
                <div class='label-input-container'>
                    <label for="firstname">Prénom</label>
                    <input type="text" id="firstname" name="firstname" required>
                </div>

                <div class='label-input-container'>  
                    <label for="birth-date">Date de naissance</label>
                    <input type="date" id="birth-date" name="birth-date" required>
                </div>

                <div class="label-input-container hide">
                    <label for=""></label>
                    <input type="text" disabled>
                </div>
                <div class='label-input-container'>
                    <label for="confirm-password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>
                </div>
            </div>
        </form>
        <div class="btn" id="btn"><button>S'inscrire</button></div>
        <div class="link"><p>Vous possédez déjà un compte ? <a href="./login.php"><span>Connectez-vous</span></a></p></div>
    </div>
    <div class="background-img"></div>
    <div class="modal" id="modal">
        <div class="modal-container" id="modal-container">
            <div class='modal-container-title'><h2>Sécurité</h2></div>
            <form method="POST" id="second-form">
                <div class='modal-label-input-container'>
                    <label for="security-question">Question de sécurité</label>
                    <select name="security-question">
                        <option value="" selected disabled hidden id="default">Sélectionner une question</option>
                        <option value="first-pet-name">Quel était le nom de votre premier animal de compagnie ?</option>
                        <option value="mother-birth-place">Quel est le lieu de naissance de votre mère ?</option>
                        <option value="first-school-name">Quel est le  nom de votre première école ?</option>
                        <option value="dream-work">Quel est le métier de vos rêveS ?</option>
                        <option value="first-love-name">Quel est le nom de votre premier amour ?</option>
                    </select>
                </div>
                <div class='modal-label-input-container'>
                    <label for="security-answer">Votre réponse</label>
                    <input type="text" id="security-answer" name="security-answer" required>
                </div>

                <!-- div dans laquelle va apparaître l'erreur s'il y en a une -->
                <div id="display-error"></div>

                <div class="modal-label-input-container">
                    <button id="submit-modal-form" class="submit">S'inscrire</button>
                </div>
            </form>
            <div class='warning'><p>Pour garantir votre sécurité, veuillez remplir cette section.</p></div>
        </div>
    </div>
    <script src="../assets/js/connection_js/register.js"></script>
</body>
</html>
