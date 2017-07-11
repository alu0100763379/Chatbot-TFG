<?php

    $access_token = "EAAbG65Bro8UBANPJarqd8wzDk8GgZCsGHtK5IHZBZBpcaRfX3mhjLlXObjz8eEsxfZBjh7gxjXyqHhbjZBKv3yCgMJjAhbvZAViedQR3sDOOX3XctYNuB2oaVZAORYtnQGuT9x2bZA8LJZBv6MxLqS0F2yuZBv8WndS8ce4nolMwM8jjrHNk6XLTx5TFWbDnwC1Y4ZD";
    $verify_token = "bot";
    $hub_verify_token = null;
    
    if(isset($_REQUEST['hub_challenge'])) {
        $challenge = $_REQUEST['hub_challenge'];
        $hub_verify_token = $_REQUEST['hub_verify_token'];
    }
    
    if ($hub_verify_token === $verify_token) {
        echo $challenge;
    }
    
    $input = json_decode(file_get_contents('php://input'), true); //Get the information that the bot return
    
    file_put_contents("fb.txt", print_r($input,true)); //Set in fb.txt the result to view it
    
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $type = $input['entry'][0]['messaging'][0]['message']['attachments'][0]['type']; //Get the type of the message
    $message_to_reply = '';

    /**
     * Some Basic rules to validate incoming messages
     */
     
    include 'functions.php';
    
    if($type == "location"){
        $coordinates = $input['entry'][0]['messaging'][0]['message']['attachments'][0]['payload']['coordinates'];
        $latitude = $coordinates['lat'];
        $longitude = $coordinates['long'];
        
        $url = 'http://www.tenerifedata.com/dataset/8f1efe35-483c-41fd-9087-1faf20b2bf4a/resource/7b46dbb0-3688-454f-8db5-4a4a1d0d380c/download/hosteleriayrestauracion?_sort=title&_pageSize=10&_page=0';
        
        $finalFeature = getClosestFeature($url, $latitude, $longitude);
        $data = getData($finalFeature);
        
        $message_to_reply =  "El establecimiento ".$data['name']." lo podrás encontrar en la siguiente dirección: ".$data['address'].". Algunos datos de interés. Teléfono: ".$data['telephone'].". E-Mail: ".$data['email'].". Página web: ".$data['web'].".";
    }
    else {
        $message_to_reply = "Hola, soy Botman. Si desea saber qué establecimiento de hostelería y restauración está más cerca de usted envíe su ubicación y yo se lo diré.";
    }
    
    //API Url
    
    $url = 'https://graph.facebook.com/v2.6/me/messages?access_token='.$access_token;
    
    //Initiate cURL.
    
    $ch = curl_init($url);
    
    //The JSON data.
    
    $jsonData = '{
        "recipient":{
            "id":"'.$sender.'"
        },
        "message":{
            "text":"'.$message_to_reply.'"
        }
    }';
    
    //Encode the array into JSON.
    $jsonDataEncoded = $jsonData;
    
    //Tell cURL that we want to send a POST request.
    curl_setopt($ch, CURLOPT_POST, 1);
    
    //Attach our encoded JSON string to the POST fields.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
    
    //Set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    
    //Execute the request
    if(!empty($input['entry'][0]['messaging'][0]['message'])){
        $result = curl_exec($ch);
    }
?>