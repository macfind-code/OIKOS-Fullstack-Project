<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
session_start();
class Chat implements MessageComponentInterface {
 
        protected $clients;
        protected $rooms;
    
        public function __construct() {
            $this->clients = new \SplObjectStorage;
            $this->rooms = array();
        }
    
        public function onOpen(ConnectionInterface $conn)
        {
            //recuperer lid du client dans lurl de connexion
            $query = $conn->httpRequest->getUri()->getQuery();
            parse_str($query, $data);
            $id = $data['identifiant'];

            
           // definir lid du client comme etant l'id de la session
            $conn->resourceId = $id ;

            $this->clients->attach($conn);
            echo "New connection! ({$conn->resourceId})\n";
            // ...
        }
        
    
        public function onMessage(ConnectionInterface $from, $msg) {
            $data = json_decode($msg);

            if ($data->type === 'join') {
                
                //pouvoir appartenir a une seule room a la fois et si on change de room on quitte la precedente room
                if (isset($this->rooms[$from->resourceId])) {
                    $this->rooms[$from->resourceId]->detach($from);
                }
                //creer une nouvelle room si elle nexiste pas
                if (!isset($this->rooms[$data->room])) {
                    $this->rooms[$data->room] = new \SplObjectStorage;
                }
                //ajouter le client a la room
                $this->rooms[$data->room]->attach($from);
                //definir la room du client
                $from->room = $data->room;
                //envoyer un message de confirmation au client
              
            
            } else if ($data->type === 'message') {
                $message = $data->msg;
                $id = $data->id;
                $hour = $data->time;
                //envoyer le message dans la room
                foreach ($this->rooms[$from->room] as $client) {
                    if ($from !== $client) {
                        $client->send(json_encode(array('type'=>'message', 'msg'=>$message, 'id'=>$id, 'time'=>$hour)));
                    }
            

                }
                }
            }

        
        
    
    public function onClose(ConnectionInterface $conn) {
        session_destroy();
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}