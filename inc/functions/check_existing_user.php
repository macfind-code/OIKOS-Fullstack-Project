<?php
    function check_existing_user($pdo, $mail) {

        $check_existing_user = $pdo->prepare('
        SELECT * FROM user
        WHERE mail = :mail
        ');
    
        $check_existing_user->execute([
            ':mail' => $mail
        ]);
    
        $check_existing_user_result = $check_existing_user->fetch(PDO::FETCH_ASSOC);
        
        return $check_existing_user_result;
    }
?>