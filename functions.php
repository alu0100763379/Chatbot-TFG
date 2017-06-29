<?php
function distanceCalculation($point1_lat, $point1_long, $point2_lat, $point2_long, $unit = 'km', $decimals = 2) {
	// Cálculo de la distancia en grados
	$degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));
 
	// Conversión de la distancia en grados a la unidad escogida (kilómetros, millas o millas naúticas)
	switch($unit) {
		case 'km':
			$distance = $degrees * 111.13384; // 1 grado = 111.13384 km, basándose en el diametro promedio de la Tierra (12.735 km)
			break;
		case 'mi':
			$distance = $degrees * 69.05482; // 1 grado = 69.05482 millas, basándose en el diametro promedio de la Tierra (7.913,1 millas)
			break;
		case 'nmi':
			$distance =  $degrees * 59.97662; // 1 grado = 59.97662 millas naúticas, basándose en el diametro promedio de la Tierra (6,876.3 millas naúticas)
	}
	return round($distance, $decimals);
}

function getResponse($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function getClosestFeature($url, $userLat, $userLong){
    $result = getResponse($url);
    $result = json_decode($result);
    $result = $result->features;
    
    $distanceMin = 999999999999;
    
    foreach($result as $feature){
        $arrayLatLong = $feature->geometry->coordinates; // [0] Longitud, [1] Latitud || Ubicación Restaurante
        $featureLat = $arrayLatLong[1];
        $featureLong = $arrayLatLong[0];
        
        $distance = distanceCalculation($userLat, $userLong, $featureLat, $featureLong); // Calcula la distancia en kilómetros (por defecto)
    
        if($distanceMin > $distance){
            $distanceMin = $distance;
            $closestFeature = $feature;
        }
    }
    return $closestFeature;
}

function getData($feature){
    $name = $feature->properties->nombre;
    $address = $feature->properties->dir;
    $cp = $feature->properties->cp;
    $num = $feature->properties->num;
    $email = $feature->properties->email;
    $web = $feature->properties->web;
    $telephone = $feature->properties->tf;
    
    if (empty($name)) { $name = "No disponible"; }
    if (empty($email)) { $email = "No disponible"; }
    if (empty($web)) { $web = "No disponible"; }
    if (empty($telephone)) { $telephone = "No disponible"; }
    
    if (empty($address)) { 
        $address = "No disponible"; 
        
    }
    else {
        if (!empty($num)) {
            $address = $address." nº ".$num;
        }
        if (!empty($cp)) { 
            $address = $address.". ".$cp;
        }
    }
    
    $data = array(
                    'name'      => $name,
                    'address'   => $address,
                    'email'     => $email,
                    'web'       => $web,
                    'telephone' => $telephone
            );
    
    return $data;
}

?>