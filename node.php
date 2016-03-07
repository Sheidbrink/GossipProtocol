<?php
require_once('./db.php');
require_once('./connection.php');

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
	// Rand Rumor or want message
	if(rand(0, 1)) {
		// RUMOR
		foreach(getMessages($uuid) as $message) {
			// If we have never sent to this node before
			if(!isset($state[$message[0]])) {
				$state[$message[0]] = array();
			}
			// If this message isn't from who we are sending to
			// AND we haven't already sent it
			if($message[0] != $to && !in_array($message[1], $state[$message[0]])) {
				array_push($state[$message[0]], $message[1]);
				return messageToRumor($message);
			}
		}
	}
	else {
		// WANT
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

while(TRUE) {
	// FULLY connect nodes
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
	if(count($peers) == 0) {
		sleep($stime);
		continue;
	}
	// Get a random peer
	$peer = getNode($uuid, $peers[array_rand($peers)]);
	// If we have never handled this peer before
	if(!isset($state[$peer])) {
		$state[$peer] = array();
	}
	$tosend = prepareMsg($uuid, $state[$peer], $peer);
	$url = getEndpoint($uuid, $peer);
	if($tosend) {
		sendPost($url, $tosend);
	}
	sleep($stime);
}
?>
