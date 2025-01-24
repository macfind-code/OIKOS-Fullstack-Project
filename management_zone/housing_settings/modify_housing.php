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

    if (!isset($_GET['housing_id'])) {
        header('Location: ./housing_list.php');
    } else {
        $housing_id = $_GET['housing_id'];
        $housing_info_request = $website_pdo->prepare('
            SELECT id, title, place, district, number_of_pieces, area, price, description, capacity, type FROM housing
            WHERE id = :id
        ');
        $housing_info_request->execute([
            ':id' => $housing_id
        ]);
        $housing_info_request_result = $housing_info_request->fetch(PDO::FETCH_ASSOC);

        if (!$housing_info_request_result) {
            header('Location: ./housing_list.php');
        } else {
            $housing_id = $housing_info_request_result['id'];
            $housing_title = $housing_info_request_result['title'];
            $housing_place = $housing_info_request_result['place'];
            $housing_district = $housing_info_request_result['district'];
            $housing_number_of_pieces = $housing_info_request_result['number_of_pieces'];
            $housing_area = $housing_info_request_result['area'];
            $housing_price = $housing_info_request_result['price'];
            $housing_description = $housing_info_request_result['description'];
            $housing_capacity = $housing_info_request_result['capacity'];
            $housing_type = $housing_info_request_result['type'];
            
            $housing_img_request = $website_pdo->prepare('
                SELECT image, id from housing_image
                WHERE housing_id = :housing_id
            ');
            $housing_img_request->execute([
                ':housing_id' => $housing_id
            ]);
            $housing_img_request_result = $housing_img_request->fetchAll(PDO::FETCH_ASSOC);

            $housing_service_request = $website_pdo->prepare('
                SELECT concierge, driver, chef, babysitter, guide FROM housing_service
                WHERE housing_id = :housing_id
            ');
            $housing_service_request->execute([
                ':housing_id' => $housing_id
            ]);
            $housing_service_request_result = $housing_service_request->fetch(PDO::FETCH_ASSOC);

            $housing_concierge = $housing_service_request_result['concierge'];
            $housing_driver = $housing_service_request_result['driver'];
            $housing_chef = $housing_service_request_result['chef'];
            $housing_babysitter = $housing_service_request_result['babysitter'];
            $housing_guide = $housing_service_request_result['guide'];

            $housing_booking_request = $website_pdo->prepare('
                SELECT lastname, firstname, booking.id, user_id, start_date_time, end_date_time, booking_date_time, price, concierge, driver, chef, babysitter, guide FROM booking
                INNER JOIN booking_service ON booking.id =  booking_service.booking_id
                INNER JOIN user ON booking.user_id = user.id 
                WHERE housing_id = :housing_id
                ORDER BY start_date_time DESC
            ');
            $housing_booking_request->execute([
                ':housing_id' => $housing_id
            ]);
            $housing_booking_request_result = $housing_booking_request->fetchAll(PDO::FETCH_ASSOC);

            // echo '<pre>';
            // var_dump($housing_booking_request_result);  
            // echo '</pre>';
            if (isset($_POST['modify-housing-btn'])) {
                $housing_title = trim(filter_input(INPUT_POST, 'housing-title', FILTER_DEFAULT));
                if (!$housing_title) {
                    $housing_title = $housing_info_request_result['title'];
                }
                $housing_price = trim(filter_input(INPUT_POST, 'housing-price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_VALIDATE_FLOAT));
                if (!$housing_price || ($housing_price && !is_numeric($housing_price))) {
                    $housing_price = $housing_info_request_result['price'];
                }
                $housing_capacity = trim(filter_input(INPUT_POST, 'housing-capacity', FILTER_SANITIZE_NUMBER_INT, FILTER_VALIDATE_INT));
                if (!$housing_capacity || ($housing_capacity && !is_numeric($housing_capacity))) {
                    $housing_capacity = $housing_info_request_result['capacity'];
                }        
                $housing_type = trim(filter_input(INPUT_POST, 'housing-type', FILTER_DEFAULT));
                if (!$housing_type) {
                    $housing_type = $housing_info_request_result['type'];
                }
                $housing_district = trim(filter_input(INPUT_POST, 'housing-district', FILTER_DEFAULT));
                
                if (!$housing_district) {
                    $housing_district = $housing_info_request_result['district'];
                }
                $housing_place = trim(filter_input(INPUT_POST, 'housing-place', FILTER_DEFAULT));
                if (!$housing_place) {
                    $housing_place = $housing_info_request_result['place'];
                }
                $housing_area = trim(filter_input(INPUT_POST, 'housing-area', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_VALIDATE_FLOAT));
                if (!$housing_area || ($housing_area && !is_numeric($housing_area))) {
                    $housing_area = $housing_info_request_result['area'];
                }   
                $housing_description = trim(filter_input(INPUT_POST, 'housing-description', FILTER_DEFAULT));
                if (!$housing_description) {
                    $housing_description = $housing_info_request_result['description'];
                }
                $housing_piece = trim(filter_input(INPUT_POST, 'housing-piece', FILTER_SANITIZE_NUMBER_INT, FILTER_VALIDATE_INT));
                if (!$housing_piece || ($housing_piece && !is_numeric($housing_piece))) {
                    $housing_piece = $housing_info_request_result['number_of_pieces'];
                } 
                $housing_services = filter_input(INPUT_POST, 'housing-services', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $services_count = 0;
                $photo_count = 0;
                $everything_ok = false;
                if ($housing_services == '') {
                    $housing_chef = 0;
                    $housing_babysitter = 0;
                    $housing_guide = 0;
                    $services_count_match = 0;
                    $no_service = true;
                } else {
                    $valid_services = ['babysitting', 'chef', 'guide'];
                    $services_count_match = count($housing_services);
                    for ($i = 0; $i < $services_count_match; $i++) {
                        if (in_array($housing_services[$i], $valid_services)) {
                            switch ($housing_services[$i]) {
                                case 'babysitting':
                                    $housing_babysitter = 1;
                                    break;
                                case 'chef':
                                    $housing_chef = 1;
                                    break;
                                case 'guide':
                                    $housing_guide = 1;
                                    break;
                            }
                            $services_count++;
                        }
                    }
                }

                if (isset($_FILES['img-file']['name']) && $_FILES['img-file']['name'][0] != "") {
                    $img_array = $_FILES['img-file'];
                    $photo_count_match = count($img_array['tmp_name']);
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
                } else {
                    $photo_count_match = 0;
                    $no_photo = true;
                }
                
                if (($photo_count == $photo_count_match) && ($services_count == $services_count_match)) {
                    $everything_ok = true;
                }

                if ($everything_ok) {
                    $modify_housing_request = $website_pdo->prepare('
                        UPDATE housing
                        SET title = :title, place = :place, district = :district, number_of_pieces = :number_of_pieces, area = :area, price = :price, description = :description, capacity = :capacity, type = :type
                        WHERE id = :id
                    ');
                    $modify_housing_request->execute([
                        ':title' => $housing_title,
                        ':place' => $housing_place,
                        ':district' => $housing_district,
                        ':number_of_pieces' => $housing_piece,
                        ':area' => $housing_area,
                        ':price' => $housing_price,
                        ':description' => $housing_description,
                        ':capacity' => $housing_capacity,
                        ':type' => $housing_type,
                        ':id'=> $housing_id
                    ]);

                    if (!isset($no_service)) {
                        $modify_housing_service_request = $website_pdo->prepare('
                            UPDATE housing_service
                            SET chef = :chef, babysitter = :babysitter, guide = :guide
                            WHERE housing_id = :housing_id
                        ');
                        $modify_housing_service_request->execute([
                            ':housing_id' => $housing_id,
                            ':chef' => $housing_chef,
                            ':babysitter' => $housing_babysitter,
                            ':guide' => $housing_guide
                        ]);
                    }
                    

                    if (!$no_photo) {
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
                            $target = "../../../uploads/$img_name";
                            move_uploaded_file($img_tmp_name, $target);
                        }
                    }
                    header('Location: ../housing_list.php');
                    exit();
                }
            }
        }
    }



    $date = date('Y-m-d');
    $heart_icon = '../../assets/images/heart.svg';
    $menu_icon =   '../../assets/images/menu.svg';
    $account_icon = '../../assets/images/account.svg';

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/font.css">
    <link rel="stylesheet" href="../../assets/css/header_gestion.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/modify_housing.css">
    <title>Logement</title>
</head>
<body>
    <?php require '../../inc/tpl/header_gestion.php' ?>
    <section id="modify-housing-main-container" class="modify-housing-main-container">
        <section class="modify-housing-main-content" id="modify-housing-main-content">
            <h2 class="page-title" id="page-title-announce-management">Gérer l'annonce</h2>
            <form method="POST" enctype="multipart/form-data" class="modify-housing-form" id="modify-housing-form">
                <section id="modify-housing-left-content" class="modify-housing-left-content">
                    <div class="input-block-text">
                        <label for="housing-title">Titre du logement :</label>
                        <input type="text" id="housing-title" class="input-text" value="<?= $housing_title ?>" name="housing-title">
                    </div>

                    <div class="input-block-text">
                        <label for="housing-price">Prix (par nuit) € :</label>
                        <input type="number" class="input-text" id="housing-price" value="<?= $housing_price ?>" name="housing-price">
                    </div>

                    <div class="input-block-text">
                        <label for="housing-capacity">Capacité (Nb de personne) :</label>
                        <input type="number" class="input-text" id="housing-capacity" value="<?= $housing_capacity ?>" name="housing-capacity">
                    </div>

                    <div class="input-block-select">
                        <label for="housing-type">Type  :</label>
                        <select name="housing-type" id="housing-type" class="input-select">
                            <option value="Appartement" name="housing-type" <?php if ($housing_type == 'Appartement'):?> selected <?php endif; ?>>Appartement</option>
                            <option value="Maison" name="housing-type" <?php if ($housing_type == 'Maison'):?> selected <?php endif; ?>>Maison</option>
                            <option value="Loft" name="housing-type" <?php if ($housing_type == 'Loft'):?> selected <?php endif; ?>>Loft</option>
                            <option value="Duplex" name="housing-type" <?php if ($housing_type == 'Duplex'):?> selected <?php endif; ?>>Duplex</option>
                            <option value="Pavillon" <?php if ($housing_type == 'Pavillon'):?> selected <?php endif; ?>>Pavillon</option>
                        </select>
                    </div>

                    <div class="input-block-select">
                        <label for="housing-district">Quartier :</label>
                        <select name="housing-district" id="housing-district" class="input-select">
                            <option value="Champs-Elysées" <?php if ($housing_district == 'Champs-Elysées' || html_entity_decode($housing_district)  == 'Champs-Elysées'):?> selected <?php endif; ?>>Paris - Champs-Elysées</option>
                            <option value="Le Marais" <?php if ($housing_district == 'Le Marais'|| html_entity_decode($housing_district)  == 'Le Marais'):?> selected <?php endif; ?>>Paris - Le Marais</option>
                            <option value="Montmartre" <?php if ($housing_district == "Montmartre" || html_entity_decode($housing_district)  == 'Montmartre'):?> selected <?php endif; ?>>Paris - Montmartre</option>
                            <option value="Opéra" <?php if ($housing_district == 'Opéra' || html_entity_decode($housing_district)  == 'Opéra'):?> selected <?php endif; ?>>Paris - Opéra</option>
                            <option value="Panthéon" <?php if ($housing_district == 'Panthéon' || html_entity_decode($housing_district)  == 'Panthéon'):?> selected <?php endif; ?>>Paris - Panthéon</option>
                            <option value="Tour Eiffel" <?php if ($housing_district == 'Tour Eiffel'|| html_entity_decode($housing_district)  == 'Tour Eiffel'):?> selected <?php endif; ?>>Paris - Tour Eiffel</option>
                        </select>
                    </div>

                    <section id="img-section" class="img-section">
                        <?php foreach($housing_img_request_result as $img): ?>
                            <div class="img-container" id="<?= $img['id'] ?>">
                                <img class="housing-img"src="../../uploads/<?= $img['image'] ?>" alt="">
                                <img class="delete-img-btn"src="../../assets/images/close_cross.svg" alt="Croix de suppression">
                            </div>
                        <?php endforeach; ?>
                    </section>

                    <div class="img-btn">
                        <label for="img-file" class="fake-btn">Ajout d'images</label>
                        <input type="file" id="img-file" accept="image/*" name="img-file[]" hidden multiple>
                    </div>
                </section>

                <section id="modify-housing-right-content" class="modify-housing-right-content">
                    <div class="input-block-text">
                        <label for="housing-place">Localisation :</label>
                        <input type="text" id="housing-place" class="input-text" value="<?= $housing_place ?>" name="housing-place">
                    </div>

                    <div class="input-block-text">
                        <label for="housing-area">Surface en m² :</label>
                        <input type="number" id="housing-area" class="input-text" value="<?= $housing_area ?>" name="housing-area">
                    </div>

                    <div class="input-block-text">
                        <label for="housing-piece">Nombre de pièces :</label>
                        <input type="number" id="housing-piece" class="input-text" value="<?= $housing_number_of_pieces ?>" name="housing-piece">
                    </div>

                    <div class="input-block-text">
                        <label for="housing-description"><span class="checkbox-label">Description : </span></label>
                        <!-- <input type="text" name="housing-description" id="housing-description" class="input-text"> -->
                        <textarea class="input-text" name="housing-description" id="housing-description" cols="30" rows="10" maxlength="500"><?= $housing_description ?></textarea>
                    </div>
                    
                    <div class="checkbox-block">
                        <p><span class="checkbox-label">Services proposés :</span></p>

                        <div class="checkbox-options">
                            <div class="input-checkbox">
                                <label  for="babysitting">Baby-Sitter</label>
                                <input type="checkbox" name="housing-services[]" id="babysitting" value="babysitting" <?php if ($housing_babysitter == 1): ?> checked <?php endif; ?>>
                            </div>

                            <div class="input-checkbox">
                                <label for="chef">Chef cuisinier</label>
                                <input type="checkbox" name="housing-services[]" id="chef" value="chef" <?php if ($housing_chef == 1): ?> checked <?php endif; ?>>
                            </div>

                            <div class="input-checkbox">
                                <label for="guide">Guide</label>
                                <input type="checkbox" name="housing-services[]" id="guide" value="guide" <?php if ($housing_guide == 1): ?> checked <?php endif; ?>>
                            </div>
                        </div>
                    </div>

                    <input type="submit" id="modify-housing-btn" class="modify-housing-btn" name="modify-housing-btn" value="modifier">
                </section>
            </form>
        </section>
        
        <div id="confirm-delete-housing-background" class="confirm-box-background inactive">
            <div id="confirm-delete-housing-box" class="confirm-box">
                <p id="confirm-delete-housing-msg" class="confirm-msg">Etes vous sur de vouloir supprimer ce logement ?</p>

                <div id="confirm-delete-housing-box-btn" class="confirm-box-btn">
                    <button id="confirm-delete-housing-btn" class="confirm-btn">Confirmer</button>
                    <button id="cancel-delete-housing-btn" class="cancel-btn">Annuler</button>
                </div>
            </div>
        </div>

        <div id="confirm-delete-booking-background" class="confirm-box-background inactive">
            <div id="confirm-delete-booking-box" class="confirm-box">
                <p id="confirm-delete-booking-msg" class="confirm-msg">Etes vous sur de vouloir supprimer cette reservation ?</p>

                <div id="confirm-delete-booking-box-btn" class="confirm-box-btn">
                    <button id="confirm-delete-booking-btn" class="confirm-btn">Confirmer</button>
                    <button id="cancel-delete-booking-btn" class="cancel-btn">Annuler</button>
                </div>
            </div>
        </div>
        
        <button id="delete-housing-btn" class="delete-housing-btn" name="<?= $housing_id ?>">Supprimer</button>

        <section id="booking-management-section-container" class="booking-management-section-container">
            <h2 class="page-title" id="page-title-booking-management">Gerer les reservations</h2>
            <section id="booking-management-section" class="booking-management-section">
                <table class="booking-management-table">
                    <thead>
                        <tr class="table-title-row">
                            <th class="table-title">Nom</th>
                            <th class="table-title">Prenom</th>
                            <th class="table-title">Arrivée</th>
                            <th class="table-title">Départ</th>
                            <th class="table-title">Etat</th>
                        </tr>
                    </thead>
                    <?php foreach ($housing_booking_request_result as $booking_info): ?>
                        <tr id="<?= $booking_info['id'] ?>" class="table-text-row">
                            <td class="table-text"><?= $booking_info['lastname'] ?></td>
                            <td class="table-text"><?= $booking_info['firstname'] ?></td>
                            <td id="start-date" class="table-text date"><?= $booking_info['start_date_time'] ?></td>
                            <td  id="end-date" class="table-text date"><?= $booking_info['end_date_time'] ?></td>
                            <?php if($date < $booking_info['start_date_time']) :?>
                                <td class="table-text">Futur <img class="booking-cancel" src="../../assets/images/close_cross.svg" alt="croix d'annulation"></td>
                            <?php elseif ($date > $booking_info['end_date_time']): ?>
                                <td class="table-text">Passée</td>
                            <?php elseif (($date >= $booking_info['start_date_time']) && ($date <= $booking_info['end_date_time'])): ?>
                                <td class="table-text">En Cours</td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </section>
        </section>

        <div id="date-modal-background" class="confirm-box-background inactive">
            <div id="date-modal-box" class="date-modal-box">
                <div id="date-modal-start-block" class="date-input-block">
                    <label for="date-modal-start">Nouvelle date de départ</label>
                    <input type="date" name="date-modal-start" id="date-modal-start">
                </div>

                <div id="date-modal-end-block" class="date-input-block">
                    <label for="date-modal-end">Nouvelle de date d'arrivée</label>
                    <input type="date" name="date-modal-end" id="date-modal-end">
                </div>

                <div id="change-booking-box-btn" class="confirm-box-btn">
                    <button id="confirm-change-booking-btn" class="confirm-btn">Confirmer</button>
                    <button id="cancel-change-booking-btn" class="cancel-btn">Annuler</button>
                </div>
            </div>
        </div>
    </section>
    <script src="../../assets/js/management_zone/modify_housing.js"></script>
    <script src="../../assets/js/header_public.js"></script>
</body>
</html>