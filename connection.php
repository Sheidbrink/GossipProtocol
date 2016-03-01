<?php
function sendPost($url, $tosend) {
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $tosend);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	curl_exec($ch);
	curl_close($ch);
}

function messageToRumor($message) {
	$toReturn = array();
	$jsonMessage['MessageID'] = $message[0] . ':' . $message[1];
	$jsonMessage['Originator'] = $message[2];
	$jsonMessage['Text'] = $message[3];
	$toReturn['Rumor'] = $jsonMessage;
	$toReturn['EndPoint'] = $message[4];
	return json_encode($toReturn);
}

?>
