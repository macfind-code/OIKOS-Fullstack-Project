<?php

session_start();
require '../../inc/pdo.php';
require '../../inc/functions/token_function.php';
require '../../inc/functions/check_existing_user.php';
require '../../inc/functions/booking_function.php';

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
        if ($_SESSION['management_role'] == 0 && $_SESSION['admin_role'] == 0){
            header ('Location: ../../public_zone/homepage.php');
            exit();
        }
    }   
}elseif(!isset($_SESSION['token'])){
    header('Location: ../../connection/login.php');
    exit();
}

$client_id= $_SESSION['id'];
if(isset($_GET['booking_id'])){
    $booking_id=$_GET['booking_id'];
}
$heart_icon = '../../assets/images/heart.svg';
$menu_icon = '../../assets/images/menu.svg';
$account_icon = '../../assets/images/account.svg';
$path = 'http:/OIKOS-Fullstack-Project/uploads/';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="gestion.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../assets/css/booking_history.css">
    <link rel="stylesheet" href="../../assets/css/font.css">
</head>

<body class="body-gestion">


  
    
    <div id="close">
                    <svg  width="20" height="20" viewBox="0 0 8 8" fill="#323232" xmlns="http://www.w3.org/2000/svg">
                      <path d="M6.59 0L4 2.59L1.41 0L0 1.41L2.59 4L0 6.59L1.41 8L4 5.41L6.59 8L8 6.59L5.41 4L8 1.41L6.59 0Z"/>
                    </svg>
    </div>
    <nav>
        <div id="oikos" class='logo'>
            <div class='logo-txt'>
                <a href=""><p>OIKOS</p></a>
            </div>
            <p id="oikos_text">---</p>
        </div>
        <div class='icon'>
            <div class="icon-heart"><img src=<?= $heart_icon ?> alt=""></div>
            <div class="icon-account-menu">
                <div class="icon-menu"><img src=<?= $menu_icon ?> alt=""></div>
                <div class="icon-account"><img src=<?= $account_icon ?> alt=""></div>
            </div>
        </div>
    </nav>
    <div id="contenaire">
        <div id="zone-client">
            <div id="header-liste">
                Assistance en ligne
            </div>
        <ul id="liste-clients">
            
        </ul>
        </div>
        <div id="zone" class="message-zone">
     
            <div id="espace-messages">
            
            </div>
            <div id="write-zone">
                <input type="text" id="message" placeholder="ecrivez un message ...">
                <button id="send">
                    <svg id="arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 0L6.59 1.41L12.17 7H0V9H12.17L6.59 14.59L8 16L16 8L8 0Z" fill="#323232"/></svg>
                </button>
            </div>
            
        </div>
    </div>

   
    <script>
         var write_zone=document.getElementById('write-zone');
         write_zone.style.display="none";
        //requete ajax pour recuperer les discussions
        var getData;
        var liste_li=document.querySelectorAll('.privatechat');
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_discussions.php');
        xhr.onload = function() {
            if (xhr.status === 200) {
                getData = JSON.parse(xhr.responseText);
                console.log(getData);
                for (let i = 0; i < getData.length; i++) {
                    var li=document.createElement('li');
                 
                    li.classList.add('privatechat');
                   
                    li.id=getData[i].id;
                    var name= getData[i].firstname+" "+getData[i].lastname;
                    li.setAttribute('data-id',name);
                   li.innerHTML="<div class='circle_image'><img src='https://static.vecteezy.com/system/resources/previews/005/544/718/original/profile-icon-design-free-vector.jpg' alt='profile'></div><div class='name_message ' ><p>"+name+"</p><p></p></div>";
                   document.getElementById('liste-clients').appendChild(li);
                   var id=getData[i].id;
               
                   document.getElementById(`${id}`).addEventListener('click',()=>{
                
                 
                    document.getElementById('oikos').children[0].classList.add('lueur');
                   
                    let ide=document.getElementById(`${id}`).id;
                    showreservation(getData[i].id,getData[i].firstname);

       
                              })

                            
   
    

                }
                li.children[0].click()
               
            }
            else {
                alert('Request failed.  Returned status of ' + xhr.status);
            }

        };
        xhr.send();
        var send=document.getElementById('send');
        var arrow=document.getElementById('arrow');
        send.addEventListener('click',()=>{
             arrow.classList.toggle('zap');
             setTimeout(()=>{
                 arrow.classList.remove('zap');
             },800)
        })
   
    
var espace_message = document.getElementById('espace-messages');
const message = document.getElementById('message');

const contenaire = document.getElementById('contenaire');
const message_zone = document.getElementById('zone');
const client = document.querySelectorAll('.privatechat');
const zone_client = document.getElementById('zone-client');
const closes = document.getElementById('close');
var etat = false;




send.onclick = function() {
   
  
    var msg = message.value;
    var id= 0;
    room = localStorage.getItem('room');
    var content = {
            type: "message",
              msg: msg,
              id: id,
              room: room,
              time: Date.now(),}
              console.log(content);
              conn.send(JSON.stringify(content));
              console.log(content);
    espace_message.innerHTML +=" <div  class=' recever_box '><div class='row'><div class='receiver bullemessage draggableElement' id=ee data-id=ee  ><p>"+message.value+"</p></div></div><div class='time'></div></div>";
    message.value = "";
    var client_id = localStorage.getItem('client_id');
    var booking_id = localStorage.getItem('booking_id');
    //save message in database
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'save_message.php?client_id='+client_id+'&message='+msg+'&booking_id='+booking_id+'&gestion_id=1', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            console.log(xhr.responseText);
        }
        else {
            alert('Request failed.  Returned status of ' + xhr.status);
        }
    };
    xhr.send();
}

write_zone.addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
        event.preventDefault();
        send.click();
    }
}
);
var taille=false
//function pour le responsive
function checkwindow(){
    if (window.innerWidth<900) {
        taille=true;
    } else {
        taille=false;
    }
}

var n=0
function showreservation(clienti_id,na){
    write_zone.style.display="none";
     document.getElementById("espace-messages").innerHTML="<div class='residence_liste'></div>";
    var xhr = new XMLHttpRequest();
    document.getElementById('oikos_text').innerHTML = "<h3>"+na+"</h3>"
    xhr.open('GET', 'get_reservation.php?client_id='+clienti_id, true);
    xhr.onload = function() {
        document.getElementById('oikos').children[0].classList.remove('lueur')
        if (xhr.status == 200) {
            var getData = JSON.parse(xhr.responseText);
            console.log(getData);
            var box = document.createElement('div');
            box.classList.add('residence_box');
            if(getData.length<1){
            document.getElementById("espace-messages").innerHTML="<div class='residence_liste' ><h2 class='reservation_vide'>Aucune réservation</h2></div>";
        }
        else{ for(let i=0; i<getData.length; i++){
       
      
            var box=document.createElement('div');
            box.classList.add('residence_box');
            box.id=getData[i].id+`${getData[i].housing_id}`;
            box.innerHTML="<div  ><div class='title'>"+getData[i].housing.title+"</div><div  class='date'><span>Debut:</span> "+" "+getData[i].start_date_time+"</div><div  class='date'><span>Fin:</span> "+" "+getData[i].end_date_time+"</div></div>";
            document.querySelector('.residence_liste').appendChild(box);
            var id=getData[i].id+`${getData[i].housing_id}`;
            console.log(id);
            document.getElementById(`${id}`).addEventListener('click',()=>{
                        localStorage.setItem('booking_id',getData[i].id);
                        console.log('ok');
                        document.getElementById('oikos').children[0].classList.add('lueur');
                        localStorage.setItem('room',clienti_id);
                        localStorage.setItem('room',getData[i].id);
                        showdiscussion(clienti_id,getData[i].id,na);

        
                                })
        }
       
        }}
        else {
            alert('Request failed.  Returned status of ' + xhr.status);
        }
        
    };
    xhr.send();
    var id=clienti_id;

   
                              
    

    if (window.innerWidth<900) {
        message_zone.style.display="flex";
    message_zone.style.height="100vh";
    message_zone.style.width="100vw";
    message_zone.style.transform="translate(0,0)";
    message_zone.style.position="initial";
    zone_client.style.display="none";
    etat=true;
    check();
    } else {
        taille=false;
    }
    closes.addEventListener('click',()=>{
    if (window.innerWidth<900) {
       
    message_zone.style.transform="translate(150vw,0)";
    message_zone.style.position="absolute";
    etat=false;
    check();
    } else {
        taille=false;
    }})



closes.addEventListener('click',()=>{
    if (window.innerWidth<900) {
       
    message_zone.style.transform="translate(150vw,0)";
    message_zone.style.position="absolute";
    etat=false;
    check();
    } else {
        taille=false;
    }
   
   
  
})}

function showdiscussion(clienti_id,booking_id,na){
    document.getElementById("espace-messages").innerHTML=""
    console.log(booking_id);

if(n==1){
    conn.close();
}
   //variable globale pour la connexion websocket
   globalThis.conn = new WebSocket('ws://localhost:8080?identifiant='+booking_id+'');
   n=1
    conn.onopen = function(e) {
    console.log("Connection established!");
    var room = booking_id;
        var joinMessage = {
    type: 'join',
    room: room
  };
  conn.send(JSON.stringify(joinMessage));
}
conn.onmessage = function(e) {
    console.log(e.data);
    var data = JSON.parse(e.data);
 
    if(data.id==0){
        espace_message.innerHTML +=" <div  class=' sender_box '><div class='row'><div class='sender bullemessage draggableElement'  data-id="+data.id+"  ><p>"+data.msg+"</p></div></div><div class='time'></div></div>";
    }else{
        espace_message.innerHTML +=" <div  class=' recever_box '><div class='row'><div class='circle_image'></div><div class='receiver bullemessage draggableElement'  data-id="+data.id+"  ><p>"+data.msg+"</p></div></div><div class='time'></div></div>";
    }
    espace_message.scrollTop = espace_message.scrollHeight;
   
  
}
    //requete ajax pour recuperer les messages
  
   localStorage.setItem('client_id', clienti_id);
   liste_client = document.querySelectorAll('.privatechat');
    for (let i = 0; i < liste_client.length; i++) {
         liste_client[i].classList.remove('active');
    }
   document.getElementById(`${clienti_id}`).classList.add('active')
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_message.php?client_id='+clienti_id+'&booking_id='+booking_id, true);
    //save on local storage
   

   xhr.onload = function() {
   
    console.log(document.getElementById('oikos').children[0]);
        if (xhr.status === 200) {
            write_zone.style.display="flex";
            document.getElementById('oikos').children[0].classList.remove('lueur');
            var getData = JSON.parse(xhr.responseText);
            console.log(getData);
            espace_message.innerHTML="";
            for (let i = 0; i < getData.length; i++) {
                if (getData[i].sender_id ==0) {
                    console.log(getData[i].client_id);
                                //ecrire lheure sans les secondes dans la variable time
                               
                                //laisser que les minutes et les heures
                                
                                
                               
                                espace_message.innerHTML +=  "<div  class=' recever_box messagecontentant'><div class='row'><div class='receiver bullemessage draggableElement' id="+getData[i].client_id+" data-id="+getData[i].id+"  ><p> " + getData[i].message + "</p></div></div><div class='time'></div></div>";
                             

                } else{
                                
                                //laisser que les minutes et les heures
                                
                                espace_message.innerHTML +=   "<div  class='sender_box messagecontentant'><div class='row_not_reverse'><div class='sender bullemessage draggableElementleft' id="+getData[i].client_id+" data-id="+getData[i].id+" ><p>" + getData[i].message + "</p></div></div><div class='time'></div></div>";
                               
                                
                            }
               
            }
            //scroll to bottom
            espace_message.scrollTop = espace_message.scrollHeight;
        }
        else {
            alert('Request failed.  Returned status of ' + xhr.status);
        }
    };
    xhr.send();
    //fin de la requete ajax


    if (window.innerWidth<900) {
        message_zone.style.display="flex";
    message_zone.style.height="100vh";
    message_zone.style.width="100vw";
    message_zone.style.transform="translate(0,0)";
    message_zone.style.position="initial";
    zone_client.style.display="none";
    etat=true;
    check();
    } else {
        taille=false;
    }
    closes.addEventListener('click',()=>{
 
       
    message_zone.style.transform="translate(150vw,0)";
    message_zone.style.position="absolute";
    etat=false;
    
    })

}

closes.addEventListener('click',()=>{
    if (window.innerWidth<900) {
       
    message_zone.style.transform="translate(150vw,0)";
    message_zone.style.position="absolute";
    etat=false;
    check();
    } else {
        taille=false;
    }
   
   
  
})


function check(){
    if (etat == true) {
        closes.style.opacity="1";
        
    } else {
        closes.style.opacity="0";
        zone_client.style.display="flex";

        
        
       
    }
}

    </script>
    <script src="../../assets/js/header_public.js"></script>
</body>
</html>