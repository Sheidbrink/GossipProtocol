<?php
//mysqli_report(MYSQLI_REPORT_ALL);
function createDB($uuid) {
	$sql = connect(NULL);
	$stmt = $sql->prepare('CREATE DATABASE IF NOT EXISTS ' . $uuid);
	$stmt->execute();
	$stmt->close();

	$sql->select_db($uuid);

	$stmt = $sql->prepare('CREATE TABLE IF NOT EXISTS messages(uuid VARCHAR(30) NOT NULL, seq_num INT UNSIGNED NOT NULL, originator VARCHAR(30) NOT NULL, text VARCHAR(10000) NOT NULL, endpoint VARCHAR(80) NOT NULL)');
	$stmt->execute();
	$stmt->close();

	$stmt = $sql->prepare('CREATE TABLE IF NOT EXISTS nodes(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, uuid VARCHAR(30) UNIQUE NOT NULL, endpoint VARCHAR(80) NOT NULL)');
	$stmt->execute();
	$stmt->close();

	addNode($uuid, $uuid, 'http://localhost/?node='.$uuid);
	
	$stmt = $sql->prepare('CREATE TABLE IF NOT EXISTS peers(root INT UNSIGNED NOT NULL, peer INT UNSIGNED NOT NULL, FOREIGN KEY(root) REFERENCES nodes(id), FOREIGN KEY(peer) REFERENCES nodes(id))');
	$stmt->execute();
	$stmt->close();

	$sql->close();
}

function getDBNodes() {
	$sql = connect(NULL);
	$result = $sql->query('SHOW DATABASES');
	$toReturn = array();
	while($row = $result->fetch_assoc()) {
		if(strpos($row['Database'], 'gossip') !== FALSE) {
			array_push($toReturn, $row['Database']);
		}
	}
	$sql->close();
	return $toReturn;
}

function connect($uuid) {
	if($uuid != NULL) {
		$sql = new mysqli('localhost', 'root', 'password', $uuid);
	}
	else {
		$sql = new mysqli('localhost', 'root', 'password');
	}
	if($sql->connect_error) {
		die('Connection Error: ' . $sql->connect_error);
	}
	return $sql;
}

function addNode($uid, $toAdd, $endpoint) {
	if(getNodeID($uid, $toAdd) != NULL) {
		return NULL;
	}
	$sql = connect($uid);
	$stmt = $sql->prepare('INSERT INTO nodes (uuid, endpoint) VALUES (?, ?)');
	$stmt->bind_param('ss', $toAdd, $endpoint);
	$stmt->execute();
	$toReturn = $stmt->errno;
	$stmt->close();
	$sql->close();
	return $toReturn;
}

function getNodeID($uid, $node) {
	$sql = connect($uid);
	$stmt = $sql->prepare('SELECT id FROM nodes WHERE uuid=?');
	$stmt->bind_param('s', $node);
	$stmt->execute();
	if(!$stmt->errno) {
		$stmt->bind_result($id);
		$stmt->fetch();
	}
	$stmt->free_result();
	$stmt->close();
	$sql->close();
	return $id;
}

function getNode($uid, $indx) {
	$sql = connect($uid);
	$stmt = $sql->prepare('SELECT uuid FROM nodes WHERE id=?');
	$stmt->bind_param('i', $indx);
	$stmt->execute();
	if(!$stmt->errno) {
		$stmt->bind_result($uuid);
		$stmt->fetch();
	}
	$stmt->free_result();
	$stmt->close();
	$sql->close();
	return $uuid;

}

function getEndpoint($uid, $node) {
	$sql = connect($uid);
	$stmt = $sql->prepare('SELECT endpoint FROM nodes WHERE uuid=?');
	$stmt->bind_param('s', $node);
	$stmt->execute();
	$stmt->bind_result($toReturn);
	$stmt->fetch();
	$stmt->close();
	$sql->close();
	return $toReturn;
}

function addPeer($uid, $peer) {
	$sql = connect($uid);
	$check_query = $sql->prepare('SELECT * FROM peers WHERE root=? AND peer=?');
	$check_query->bind_param('ss', getNodeID($uid, $uid), getNodeID($uid, $peer));
	$check_query->execute();
	$toReturn = $check_query->errno;
	$check_query->bind_result($r_uid, $r_peer);
	if(!$check_query->fetch()) {
		$add_stmt = $sql->prepare('INSERT INTO peers (root, peer) VALUES (?, ?)');
		$add_stmt->bind_param('ss', getNodeID($uid, $uid), getNodeID($uid, $peer));
		$add_stmt->execute();
		$toReturn = $add_stmt->errno;
		$add_stmt->close();
	}
	$check_query->free_result();
	$check_query->close();
	$sql->close();
	return $toReturn;
}

function getPeers($uid) {
	$sql = connect($uid);
	$query = $sql->prepare('SELECT peer FROM peers WHERE root=?');
	$query->bind_param('s', getNodeID($uid, $uid));
	$query->execute();
	if(!$query->errno) {
		$query->bind_result($result);
		$toReturn = array();
		while($query->fetch()) {
			array_push($toReturn, $result);
		}
		$query->free_result();
	}
	$query->close();
	$sql->close();
	return $toReturn;
}

function addOwnMessage($uuid, $originator, $text, $endpoint) {
	return addMessage($uuid, $uuid, getSeqNum($uuid, $uuid)+1, $originator, $text, $endpoint);
}

function addMessage($uuid, $from, $seq, $originator, $text, $endpoint) {
	$sql = connect($uuid);

	$stmt = $sql->prepare('SELECT * FROM messages WHERE uuid=? AND seq_num=?');
	$stmt->bind_param('si', $from, intval($seq));
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows != 0) {
		return FALSE;
	}
	$stmt->close();

	$stmt = $sql->prepare('INSERT INTO messages (uuid, seq_num, originator, text, endpoint) VALUES (?, ?, ?, ?, ?)');
	$stmt->bind_param('sisss', $from, $seq, $originator, $text, $endpoint);
	$stmt->execute();
	$toReturn = $stmt->errno;
	$stmt->close();
	$sql->close();
	return $toReturn;
}

function getMessages($uuid) {
	$sql = connect($uuid);
	$stmt = $sql->prepare('SELECT * FROM messages ORDER BY uuid, seq_num ASC');
	$stmt->execute();
	$toReturn = array();
	if(!$stmt->errno) {
		$stmt->bind_result($uuid, $seq_num, $originator, $text, $endpoint);
		while($stmt->fetch()) {
			array_push($toReturn, array($uuid, $seq_num, $originator, $text, $endpoint));
		}
		$stmt->free_result();
	}
	$stmt->close();
	$sql->close();
	return $toReturn;
}

function getSeqNum($uuid, $toGet) {
	$seq_num = -1;
	$sql = connect($uuid);
	$stmt = $sql->prepare('SELECT seq_num FROM messages WHERE uuid=? ORDER BY seq_num DESC');
	$stmt->bind_param('s', $toGet);
	$stmt->execute();
	if(!$stmt->errno) {
		$stmt->bind_result($seq_num);
		$stmt->fetch();
		$stmt->free_result();
	}
	$stmt->close();
	$sql->close();
	return $seq_num;
}

?>
