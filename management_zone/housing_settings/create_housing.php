<?php 
    
    session_start();
    require '../../inc/pdo.php';
    require '../../inc/functions/token_function.php';
    require '../../inc/functions/check_existing_user.php';
    require '../../inc/functions/booking_function.php';

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
            if ($_SESSION['management_role'] == 0 && $_SESSION['admin_role'] == 0){
                header ('Location: ../../public_zone/homepage.php');
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

    $id = $_SESSION['id'];
    $user_info_request = $website_pdo->prepare('
        SELECT id, mail, password, security_question, security_answer, lastname, firstname, birth_date, phone_number, pp_image, client_role, management_role, maintenance_role, admin_role, status, registration_date_time FROM user
        WHERE id = :id
    ');

    $user_info_request->execute([
        ':id' => $id
    ]);

    $result_user_info_request = $user_info_request->fetch(PDO::FETCH_ASSOC);

    $housing_title_error = '';
    $housing_price_error = '';
    $housing_type_error = '';
    $housing_capacity_error = '';
    $housing_district_error = '';
    $housing_localisation_error = '';
    $housing_area_error = '';
    $housing_description_error = '';
    $housing_piece_error = '';
    $housing_services_error = '';
    $housing_img_error ='';
    $error_msg= '';

    if (isset($_POST['housing-create'])) {
        $housing_title = trim(filter_input(INPUT_POST, 'housing-title', FILTER_DEFAULT));
        if (!$housing_title) {
            $housing_title_error = 'Veuillez choisir un titre pour votre logement.';
        }
        $housing_price = trim(filter_input(INPUT_POST, 'housing-price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_VALIDATE_FLOAT));
        if (!$housing_price || ($housing_price && !is_numeric($housing_price))) {
            $housing_price_error = 'Veuillez indiquez le prix par nuit du logement.';
        }
        $housing_capacity = trim(filter_input(INPUT_POST, 'housing-capacity', FILTER_SANITIZE_NUMBER_INT, FILTER_VALIDATE_INT));
        if (!$housing_capacity || ($housing_capacity && !is_numeric($housing_capacity))) {
            $housing_capacity_error = 'Veuillez indiquez la capacité maximale du logement.';
        }        
        $housing_type = trim(filter_input(INPUT_POST, 'housing-type', FILTER_DEFAULT));
        if (!$housing_type) {
            $housing_type_error = 'Veuillez selectionné un type de logement.';
        }
        $housing_district = trim(filter_input(INPUT_POST, 'housing-district', FILTER_DEFAULT));
        if (!$housing_district) {
            $housing_district_error = 'Veuillez selectionné un quartier..';
        }
        $housing_localisation = trim(filter_input(INPUT_POST, 'housing-localisation', FILTER_DEFAULT));
        if (!$housing_localisation) {
            $housing_localisation_error = 'Veuillez indiquez une adresse.';
        }
        $housing_area = trim(filter_input(INPUT_POST, 'housing-area', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_VALIDATE_FLOAT));
        if (!$housing_area || ($housing_area && !is_numeric($housing_area))) {
            $housing_area_error = 'Veuillez remplir ce champ.';
        }   
        $housing_description = trim(filter_input(INPUT_POST, 'housing-description', FILTER_DEFAULT));
        if (!$housing_description) {
            $housing_description_error = 'Veuillez ajouté une description.';
        }
        $housing_piece = trim(filter_input(INPUT_POST, 'housing-piece', FILTER_SANITIZE_NUMBER_INT, FILTER_VALIDATE_INT));
        if (!$housing_piece || ($housing_piece && !is_numeric($housing_piece))) {
            $housing_piece_error = 'Veuillez renseigner le nombre de pièce que possède le logement.';
        } 
        $housing_services = filter_input(INPUT_POST, 'housing-services', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if ($housing_services == '') {
            $housing_services_error = 'Veuillez selectionner au moins un service.';
        }
        $input_array = [$housing_title, $housing_price, $housing_capacity, $housing_type, $housing_district, $housing_localisation, $housing_area, $housing_area, $housing_description, $housing_piece];
        $input_all_true = array_reduce($input_array, function($acc, $current) {
            return $acc && $current;
        }, true);

        if ($_FILES['img-file']['name'][0] == '') {
            $housing_img_error = 'Veuillez ajouter au moins une image.';
        }

        if ($input_all_true && ($housing_services[0] != '') && ($_FILES['img-file']['name'][0]) != '') {
            $photo_count = 0;
            $everything_ok = false;
            $img_array = $_FILES['img-file'];
            $photo_count_match = count($img_array['tmp_name']);
            $services_count = 0;
            $services_count_match = count($housing_services);
            $babysitting = 0;
            $driver = 0;
            $chef = 0;
            $conciergerie = 0;
            $guide = 0;
            $valid_services = ['babysitting', 'driver', 'chef', 'conciergerie', 'guide'];
            for ($i = 0;  $i < $photo_count_match; $i++) {
                $img_name = $img_array['name'][$i];
                $img_tmp_name = $img_array['tmp_name'][$i];
                $img_error = $img_array['error'][$i];
                if(is_uploaded_file($img_tmp_name)) {
                    $mime_type = mime_content_type($img_tmp_name);
                    $allowed_file_type = ['image/jpeg', 'image/png'];
                    if(!in_array($mime_type, $allowed_file_type)) {
                        echo "$img_name n'a pas le bon format d'image.";
                    } elseif (filesize($img_tmp_name) > 3000000) {
                        echo 'Le fichier est trop volumineux';
                    } elseif ($img_error == UPLOAD_ERR_OK) {
                        $photo_count++;
                    }
                }
            }

            for ($i = 0; $i < $services_count_match; $i++) {
                if (in_array($housing_services[$i], $valid_services)) {
                    switch ($housing_services[$i]) {
                        case 'babysitting':
                            $babysitting = 1;
                            break;
                        case 'driver':
                            $driver = 1;
                            break;
                        case 'chef':
                            $chef = 1;
                            break;
                        case 'conciergerie':
                            $conciergerie = 1;
                            break;
                        case 'guide':
                            $guide = 1;
                            break;

                    }
                    $services_count++;
                }
            }

            if (($photo_count == $photo_count_match) && ($services_count == $services_count_match)) {
                $everything_ok = true;
            }

            $checking_title_exist = $website_pdo->prepare('
                SELECT title FROM housing
                WHERE title = :title
            ');

            $checking_title_exist->execute([
                ':title' => $housing_title
            ]);

            $checking_title_exist_result = $checking_title_exist->fetch(PDO::FETCH_ASSOC);

            if ($everything_ok && !$checking_title_exist_result) {
                $create_housing_request = $website_pdo->prepare('
                    INSERT INTO housing (title, place, district, number_of_pieces, area, price, description, capacity, type) 
                    VALUES (:title, :place, :district, :number_of_pieces, :area, :price, :description, :capacity, :type)
                ');
                $create_housing_request->execute([
                    ':title' => $housing_title,
                    ':place' => $housing_localisation,
                    ':district' => $housing_district,
                    ':number_of_pieces' => $housing_piece,
                    ':area' => $housing_area, 
                    ':price' => $housing_price,
                    ':description' => $housing_description,
                    ':capacity' => $housing_capacity,
                    ':type' => $housing_type
                ]);
                $housing_id_request = $website_pdo->prepare('
                    SELECT id FROM housing
                    WHERE title = :title
                ');
                $housing_id_request->execute([
                    ':title' => $housing_title
                ]);
                $housing_id_request_result = $housing_id_request->fetch(PDO::FETCH_ASSOC);
                $housing_id = $housing_id_request_result['id'];
                for ($i = 0;  $i < count($img_array['tmp_name']); $i++) {
                    $img_name = $img_array['name'][$i];
                    $img_tmp_name = $img_array['tmp_name'][$i];
                    $img_name = time().$img_name;
                    $adding_housing_img_request = $website_pdo->prepare('
                        INSERT INTO housing_image (housing_id, image)
                        VALUES (:housing_id, :image)
                    ');
                    $adding_housing_img_request->execute([
                        ':housing_id' => $housing_id,
                        ':image' => $img_name
                    ]);
                    $target = "../../uploads/$img_name";
                    move_uploaded_file($img_tmp_name, $target);
                }

                $adding_housing_services_request = $website_pdo->prepare('
                    INSERT INTO housing_service (housing_id, concierge, driver, chef, babysitter, guide)
                    VALUES (:housing_id, :concierge, :driver, :chef, :babysitter, :guide)
                ');

                $adding_housing_services_request->execute([
                    ':housing_id' => $housing_id,
                    ':concierge' => $conciergerie,
                    ':driver' => $driver,
                    ':chef' => $chef,
                    ':babysitter'=> $babysitting,
                    ':guide' => $guide
                ]);
                header('Location: ../housing_list.php');
                exit();
            } elseif ($everything_ok && $checking_title_exist_result) {
                $error_msg = 'Un logement porte déja ce nom .';
            } else {
                $error_msg = 'Veuillez remplir tous les champs .';
            }
         } else {
            $error_msg = 'Veuillez remplir tous les champs .';
        }
    }

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/font.css">
    <link rel="stylesheet" href="../../assets/css/header_gestion.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assetS/css/create_housing.css">
    <title>Ajout d'un nouveau logement.</title>
</head>
<body>
    <?php require '../../inc/tpl/header_gestion.php' ?>
    <div class="create-housing-main-content" id="create-housing-main-content">
        <h2 class="page-title">Création d'une Annonce</h2>
        <form id="formu" action="create_housing.php" method="POST" enctype="multipart/form-data">
            <div class="create-housing-left-content">
                <?php if(isset($error_msg)): ?>
                    <p class="error-msg"><?= $error_msg ?></p>
                <?php endif; ?>
                <div class="input-block-text">
                    <label for="housing-title">Titre du logement :</label>
                    <input type="text" name="housing-title" id="housing-title" class="input-text <?php if($housing_title_error): ?>error-line<?php endif; ?>" placeholder="Le fabuleux">
                </div>

                <div class="input-block-text">
                    <label for="housing-price">Prix (par nuit) € :</label>
                    <input type="number" name="housing-price" id="housing-price" class="input-text <?php if($housing_price_error): ?>error-line<?php endif; ?>" placeholder="1500€">
                </div>

                <div class="input-block-text">
                    <label for="housing-capacity">Capacité (Nb de personne) :</label>
                    <input type="number" name="housing-capacity" id="housing-capacity" class="input-text <?php if($housing_capacity_error): ?>error-line<?php endif; ?>" placeholder="10">
                </div>

                <div class="input-block">
                    <label for="housing-type">Type :</label>
                    <select name="housing-type" id="housing-type" class="input-select <?php if($housing_type_error): ?>error-line<?php endif; ?>">
                        <option selected disabled hidden value="">Choisissez un type de logement.</option>
                        <option value="Appartement" name="housing-type">Appartement</option>
                        <option value="Maison" name="housing-type">Maison</option>
                        <option value="Loft" name="housing-type">Loft</option>
                        <option value="Duplex" name="housing-type">Duplex</option>
                        <option value="Pavillon">Pavillon</option>
                    </select>
                </div>

                <div class="input-block">
                    <label for="">Quartier :</label>
                    <select name="housing-district" id="housing-district" class="input-select <?php if($housing_district_error): ?>error-line<?php endif; ?>">
                        <option selected disabled hidden value="">Choisissez le quartier du Logement .</option>
                        <option value="Champs-Elysées">Paris - Champs-Elysées</option>
                        <option value="Le Marais">Paris - Le Marais</option>
                        <option value="Montmartre">Paris - Montmartre</option>
                        <option value="Opéra">Paris - Opéra</option>
                        <option value="Panthéon">Paris - Panthéon</option>
                        <option value="Tour Eiffel">Paris - Tour Eiffel</option>
                    </select>
                </div>

                <div class="input-block" id="select-img-block">
                    <label for="img-file"><span class="checkbox-label <?php if($housing_services_error): ?>error-line<?php endif; ?>">Images :</span></label>
                    <div class="img-btn">
                        <label for="img-file" class="fake-btn">Charger des images</label>
                        <input type="file" id="img-file" accept="image/*" name="img-file[]" hidden multiple>
                    </div>
                </div>
            </div>

            <div class="create-housing-right-content">
                <div class="input-block-text">
                    <label for="housing-localisation">Adresse :</label>
                    <input type="text" name="housing-localisation" id="housing-localisation" class="input-text <?php if($housing_localisation_error): ?>error-line<?php endif; ?>" placeholder="Adresse">
                </div>

                <div class="input-block-text ">
                    <label for="housing-area">Surface en m² :</label>
                    <input type="number" name="housing-area" id="housing-area" class="input-text <?php if($housing_area_error): ?>error-line <?php endif; ?>" placeholder="250">
                </div>

                <div class="input-block-text ">
                    <label for="housing-price">Nombre de pièces :</label>
                    <input type="number" name="housing-piece" id="housing-piece" class="input-text <?php if($housing_piece_error): ?>error-line<?php endif; ?>" placeholder="8">
                </div>

                <div class="input-block-text">
                    <label for="housing-description"><span class="checkbox-label <?php if($housing_description_error): ?>error-line<?php endif; ?>">Description : </span></label>
                    <!-- <input type="text" name="housing-description" id="housing-description" class="input-text"> -->
                    <textarea class="input-text" name="housing-description" id="housing-description" cols="30" rows="10" maxlength="500"></textarea>
                </div>

                <div class="checkbox-block">
                    <p ><span class="checkbox-label <?php if($housing_services_error): ?>error-line<?php endif; ?>">Services proposés :</span></p>

                    <div class="checkbox-options">
                        <div class="input-checkbox">
                            <label  for="babysitting">Baby-Sitter</label>
                            <input type="checkbox" name="housing-services[]" id="babysitting" value="babysitting">
                        </div>

                        <div class="input-checkbox">
                            <label for="conciergerie">Chauffeur</label>
                            <input type="checkbox" name="housing-services[]" id="driver" value="driver">
                        </div>

                        <div class="input-checkbox">
                            <label for="chef">Chef cuisinier</label>
                            <input type="checkbox" name="housing-services[]" id="chef" value="chef">
                        </div>

                        <div class="input-checkbox">
                            <label for="driver">Conciergerie</label>
                            <input type="checkbox" name="housing-services[]" id="conciergerie" value="conciergerie">
                        </div>

                        <div class="input-checkbox">
                            <label for="guide">Guide</label>
                            <input type="checkbox" name="housing-services[]" id="guide" value="guide">
                        </div>
                    </div>
                </div>

                <input class="housing-create-btn" type="submit" value="Créer" name="housing-create">
            </div>
        </form>
    </div>
    <script src="../../assets/js/header_public.js"></script>
</body>
</html>