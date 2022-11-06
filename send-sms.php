<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://sms.etech-keys.com/ss/sendsms.php',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array(  'sender_id' => '33export','destinataire' => '237699124249','message' => 'Votre code : 5689','login' => '699124249',
                                'password' => 'as!69@81','ext_id' => '12345','programmation' => '0'),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

    /*      Commentaire    

            sender_id:ETECH                 Max 11 CaractÃ¨re
            destinataire:237673767207       Avec Indicatif
            message:API SMS                 Massage
            login:243161185                 Votre Login
            password:test1                  Votre Password
            ext_id:12345                    l'id du SMS Ã  votre Niveau
            programmation:0                 Delais de retardemment d'envoi en ms

    */

?>