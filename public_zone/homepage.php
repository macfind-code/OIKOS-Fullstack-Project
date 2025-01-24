<?php
    require '../inc/pdo.php';
    session_start();

    $heart_icon = '../assets/images/heart.svg';
    $menu_icon =   '../assets/images/menu.svg';
    $account_icon = '../assets/images/account.svg';
    $link_favorite = '../client_zone/profile/favorites.php';
    $homepage_link = "";

    $array_district = ['Tour Eiffel', 'Le Marais', 'Panthéon', 'Montmartre', 'Champs-Elysées', 'Opéra'];
    $method = filter_input(INPUT_SERVER, "REQUEST_METHOD");
    if($method == "POST") {
        $district = filter_input(INPUT_POST, "district_name");
        $first_day_search = filter_input(INPUT_POST, "first_day_search");
        $end_day_search = filter_input(INPUT_POST, "end_day_search");
        $capacity = filter_input(INPUT_POST, "capacity");
        header('Location: ./housing_list.php?district='.$district.'&first_day_search='.$first_day_search.'&end_day_search='.$end_day_search.'&capacity='.$capacity);
    }
    ?>


<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/homepage.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/header_publiczone.css">
    <link rel="stylesheet" href="../assets/css/font.css">
    <title>OIKOS | Acceuil</title>
</head>
<body>
    <?php require '../inc/tpl/header_publiczone.php' ?>
            <div class="page_filtre">
                <form method='POST' class="filter">
                    <div class="arrondissement">
                        <label for="arrondissement">Quartier</label>
                        <select name="district_name" id="">
                            <option value="" disabled selected>Arrondissement</option>
                        <?php foreach($array_district as $district) :?>
                            <option value="<?= $district ?>"><?= $district?></option>
                        <?php endforeach ?>
                        </select>
                    </div>
                    <div class="arrive">
                        <label for="arrive">Arrivée</label>
                        <input type="date" name="first_day_search" id="arrive">
                    </div>
                    <div class="depart">
                        <label for="depart">Départ</label>
                        <input type="date" name="end_day_search" id="depart">
                    </div>
                    <div class="voyager">
                        <label for="voyager">Voyageurs</label>
                        <input id="voyager" name="capacity_search" type="number" min="1" max="30" value="0">
                    </div>
                    <div class="btn">
                        <input type="submit" value="Rechercher" name="submit_booking"></input>
                    </div>
                </div>
            </div>
            <div class="presentation">
                <div class='presentation-title'><h2>Qui sommes-nous ?</h2></div>
                <div class='presentation-txt'><p>Bienvenue sur OIKOS !</p>
                <p>Découvrez l'essence du luxe à Paris avec OIKOS. Nous sommes fiers de vous présenter une sélection exclusive d'appartements de luxe situés dans cinq quartiers prestigieux de la capitale française : Montmartre, Le Marais, Tour Eiffel, Champs-Élysées et Opéra. </p>
                <p>Chez OIKOS, nous nous engageons à offrir à nos clients une expérience exceptionnelle. Chaque appartement a été soigneusement choisi pour son design élégant, son confort ultime, et sa localisation privilégiée. Que vous visitiez Paris pour affaires ou pour le plaisir, nos appartements vous offriront le cadre idéal pour profiter pleinement de votre séjour. </p>
                </p>
                <p>Réservez dès maintenant votre appartement de luxe à Paris avec OIKOS et préparez-vous à vivre une expérience inoubliable. Nous sommes impatients de vous accueillir et de vous offrir le meilleur de Paris dans le confort et l'élégance.</p>
                </div>
                <div class='separator'></div>
            </div>
    <div class="all_arrondissement">
        <div class="all_arrondissement_title">
            <h1>Découvrir tous les quartiers</h1>
        </div>
        <div class="all_arrondissement_card">
            <div class="all_arrondissement_top">
                <div class="all_arrondissement_eiffel">
                    <div class="all_arrondissement_eiffel_img">
                        <img src="../assets/images/eiffel-img.png" alt="">
                    </div>
                    <div class="all_arrondissement_eiffel_title">
                        <h6>Tour Eiffel</h6>
                    </div>
                    <div class="all_arrondissement_eiffel_text">
                        <p>Plus de 20 logements</p>
                    </div>
                </div>
                <div class="all_arrondissement_top_little">
                    <div class="all_arrondissement_marais">
                        <div class="all_arrondissement_marais_img">
                            <img src="../assets/images/lemarais.png" alt="">
                        </div>
                        <div class="all_arrondissement_marais_title">
                            <h6>Le Marais</h6>
                        </div>
                        <div class="all_arrondissement_marais_text">
                            <p>Plus de 40 logements</p>
                        </div>
                    </div>
                    <div class="all_arrondissement_opera">
                        <div class="all_arrondissement_opera_img">
                            <img src="../assets/images/opera.png" alt="">
                        </div>
                        <div class="all_arrondissement_opera_title">
                            <h6>Opéra</h6>
                        </div>
                        <div class="all_arrondissement_opera_text">
                            <p>Plus de 30 logements</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="all_arrondissement_bottom">
                <div class="all_arrondissement_bottom_little">
                    <div class="all_arrondissement_montmartre">
                            <div class="all_arrondissement_montmartre_img">
                                <img src="../assets/images/montmartre.png" alt="">
                            </div>
                            <div class="all_arrondissement_montmartre_title">
                                <h6>Montmartre</h6>
                            </div>
                            <div class="all_arrondissement_montmartre_text">
                                <p>Plus de 40 logements</p>
                            </div>
                        </div>
                        <div class="all_arrondissement_champs">
                            <div class="all_arrondissement_champs_img">
                                <img src="../assets/images/champs.png" alt="">
                            </div>
                            <div class="all_arrondissement_champs_title">
                                <h6>Champs-Elysées</h6>
                            </div>
                            <div class="all_arrondissement_champs_text">
                                <p>Plus de 20 logements</p>
                            </div>
                        </div>
                </div>
                    <div class="all_arrondissement_pantheon">
                        <div class="all_arrondissement_pantheon_img">
                            <img src="../assets/images/pantheon.png" alt="">
                        </div>
                        <div class="all_arrondissement_pantheon_title">
                            <h6>Panthéon</h6>
                        </div>
                        <div class="all_arrondissement_pantheon_text">
                            <p>Plus de 30 logements</p>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <div class="assurance">
        <div class="nosservices">
            <h1>Un pannel de services à votre disposition</h1>
        </div>
        <div class="elements"> 
            <div class="elements_top">
                <div class="chauffeur">
                    <div class="assurance_icon">
                        <img src="../assets/images/cars.svg" alt="">
                    </div>
                    <div class="assurance_title">
                        <h6>Vos déplacements assurés</h6>
                    </div>
                    <div class="assurance_text_chauffeur">
                        <p>Un chauffeur vous est assigné afin de vous déplacer rapidement et en toute sécurité, et cela dès votre arrivée</p>
                    </div>
                </div>
                <div class="guide">
                    <div class="assurance_icon">
                        <img src="../assets/images/map.svg" alt="">
                    </div>
                    <div class="assurance_title">
                        <h6>Votre voyage personnalisé</h6>
                    </div>
                    <div class="assurance_text_guide">
                        <p>Pour un voyage enrichissant, nous mettons un(e) guide à votre disposition pour vous accompagner lors de vos visites</p>
                    </div>
                </div>
                <div class="chef">
                    <div class="assurance_icon">
                        <img src="../assets/images/cheef.svg" alt="">
                    </div>
                    <div class="assurance_title">
                        <h6>Vos papilles emerveillées</h6>
                    </div>
                    <div class="assurance_text_chef">
                        <p>Parce que l’on veut aussi vous faire vivre un voyage culinaire, un(e) chef(fe) est à vos services</p>
                    </div>
                </div>
            </div>
            <div class="elements_bottom">
                <div class="child">
                    <div class="assurance_icon">
                        <img src="../assets/images/child.svg" alt="">
                    </div>
                    <div class="assurance_title">
                        <h6>Vos enfants accompagnés</h6>
                    </div>
                    <div class="assurance_text_child">
                        <p>Car parfois, on souhaite aussi se retrouver à deux, nous mettons en place un service de garde d’enfants</p>
                    </div>
                </div>
                <div class="conciergerie">
                    <div class="assurance_icon">
                        <img src="../assets/images/smile.svg" alt="">
                    </div>
                    <div class="assurance_title">
                        <h6>Vos soucis envolés</h6>
                    </div>
                    <div class="assurance_text_conciergerie">
                        <p>Un problème ? Notre service de conciergerie s’en occupe, disponible 24/7</p>
                    </div>
                </div>
                <div class="ballon">
                    <div class="assurance_icon">
                        <img src="../assets/images/bi_balloon.svg" alt="">
                    </div>
                    <div class="assurance_title">
                        <h6>Evènements spéciaux</h6>
                    </div>
                    <div class="assurance_text_ballon">
                        <p>Vous prévoyez d'arriver pour une occasion particulière ? Nous préparons votre arrivée spéciale selon vos souhaits</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
            <div class='video'>
                <video controls>
                    <source src="../assets/video/video_homepage.mp4" type="video/mp4">
                </video>
            </div>
                 </div>

            </div>
    

        <footer>
        <div class="logo_footer">
            <div class='separator-footer'></div>
            <div class='footer-logo-txt' id="footer-logo"><p>OIKOS</p></div>
            <div class='separator-footer'></div>
        </div>
        <div class="footer_elements">
            <div class="footer_column_left">
                <div class="footer_column_left_title">
                    <h3>Assistance</h3>
                </div>
                <div class="footer_column_left_elements">
                    <p>Nous contacter</p>
                    <p>Centre d'aide</p>
                    <p>Annulation</p>
                    <p>Signaler un problème</p>
                </div>
            </div>
            <div class="footer_column_middle">
                <div class="footer_column_middle_title">
                    <h3>Nos offres</h3>
                </div>
                <div class="footer_column_middle_elements">
                    <p>Location saisonnière</p>
                    <p>Location longue durée</p>
                    <p>Nos garanties</p>
                    <p>Nos services</p>
                </div>
            </div>
            <div class="footer_column_right">
                <div class="footer_column_right_title">
                    <h3>Politique</h3>
                </div>
                <div class="footer_column_right_elements">
                    <p>Protection des données</p>
                    <p>Conditions générales</p>
                    <p>Fonctionnement du site</p>
                    <p>Gérer mes cookies</p>
                </div>
            </div>
        </div>
    </footer> 
    <script src="../assets/js/header_public.js"></script>
    <script>
        const backTop = document.getElementById("footer-logo")

        backTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior:"smooth"
            })
        })
    </script>
</body>
</html>