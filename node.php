<?php
require_once('./db.php');

if(sizeof($argv) > 2) {
	$uuid = $argv[2];
	if(strpos($uuid, 'gossip') === FALSE) {
		$uuid = 'gossip' . $uuid;
	}
}
else {
	$uuid = uniqid('gossip');
}
$stime = $argv[1];
createDB($uuid);

$state = array();

function prepareMsg($uuid, &$state, $to) {
	if(rand(0, 1)) {
		foreach(getMessages($uuid) as $message) {
			if(!isset($state[$message[0]])) {
				$state[$message[0]] = array();
			}
			if($message[0] != $to && !in_array($message[1], $state[$message[0]])) {
				$toReturn = array();
				$jsonMessage['MessageID'] = $message[0] . ':' . $message[1];
				$jsonMessage['Originator'] = $message[2];
				$jsonMessage['Text'] = $message[3];
				$toReturn['Rumor'] = $jsonMessage;
				$toReturn['EndPoint'] = $message[4];
				array_push($state[$message[0]], $message[1]);
				return json_encode($toReturn);
			}
		}
	}
	else {
		$peers = array();
		foreach(getPeers($uuid) as $indx) {
			$peer = getNode($uuid, $indx);
			$peers[$peer] = getSeqNum($uuid, $peer);
		}
		$toReturn = array();
		$toReturn['Want'] = $peers;
		$toReturn['EndPoint'] = getEndpoint($uuid, $uuid);
		return json_encode($toReturn);
	}
}

function send($url, $tosend) {
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $tosend);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	curl_exec($ch);
	curl_close($ch);
}

while(TRUE) {
	/*foreach(getDBNodes() as $node) {
		if($node == $uuid) {
			continue;
		}
		else {
			addNode($uuid, $node, getEndpoint($node, $node));
			addPeer($uuid, $node);
		}
	}*/
	$peers = getPeers($uuid);
	$peer = getNode($uuid, $peers[array_rand($peers)]);
	if(!isset($state[$peer])) {
		$state[$peer] = array();
	}
	$tosend = prepareMsg($uuid, $state[$peer], $peer);
	$url = getEndpoint($uuid, $peer);
	if($tosend) {
		send($url, $tosend);
	}
	sleep($stime);
}
?>
