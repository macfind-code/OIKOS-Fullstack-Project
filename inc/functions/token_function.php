<?php
    function token()
    {
        $token = [];
        for ($i = 1; $i <= 30; $i++) {
            $random = rand(48, 122);
            $str = chr($random);
            $token[] = $str;
        }
        $token = implode($token);
        return $token;
    }


    function token_check($token, $pdo, $user_id)
    {
        $requete = $pdo->prepare("
        SELECT * FROM token WHERE token = :token AND user_id = :user_id;
        ");
        $requete->execute([
            ":token" => $token,
            ":user_id" => $user_id
        ]);
        $check_token = $requete->fetch(PDO::FETCH_ASSOC);

        if ($check_token !== false){
            return "true";
        }else{
            return "false";
        }
    }
?>