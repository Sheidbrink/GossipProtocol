<html>
<head><title>Gossip</title></head>
<body>
<?php
require_once('db.php');

$nodes = getDBNodes();

echo 'All Nodes: ';
foreach($nodes as $node) {
	echo "<a href=\"?node=$node\">$node</a> ";
}
echo '<br/>';
//if get string is one of those nodes
if(isset($_GET['node']) && in_array($_GET['node'], $nodes)) {
	//add any messages
	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		if(isset($_POST['message'])) {
			//RECEIVED FORM SUBMIT
			addOwnMessage($_GET['node'], 
				$_POST['originator'], 
				$_POST['message'], 
				$_POST['endpoint']);
		}
		elseif(isset($_POST['addPeer'])) {
			//Add peer request
			addNode($_GET['node'], 
				$_POST['addPeer'], 
				$_POST['endpoint']);
			addPeer($_GET['node'], $_POST['addPeer']);
		}
		else {
			$r_message = json_decode(
					file_get_contents("php://input"));
			if(isset($r_message->{'Rumor'})) {
				//Received Rumor
				$mId = explode(':', $r_message->{'Rumor'}->{'MessageID'});
				addMessage($_GET['node'],
				 $mId[0], 
				 $mId[1], 
				 $r_message->{'Rumor'}->{'Originator'}, 
				 $r_message->{'Rumor'}->{'Text'}, 
				 $r_message->{'EndPoint'});
			}
			elseif(isset($r_message->{'Want'})) {
				//RECEIVED WANT
				require_once('connection.php');
				$myMessages = getMessages($_GET['node']);
				foreach($r_message->{'Want'} as $id => $num) {
					foreach($myMessages as $m) {
						if($m[0] == $id && intval($m[1]) > intval($num)) {
							sendPost($r_message->{'EndPoint'},
								 messageToRumor($m));
						}
					}
				}
			}
		}
	}
	//print out input string and messages
	require('form.php');
	$messages = getMessages($_GET['node']);
	echo '<table cellpadding="5">
		<tr>
			<td>MessageID</td>
			<td>Originator</td>
			<td>Text</td>
			<td>EndPoint</td>
		</tr>';
	foreach($messages as $message) {
		echo '<tr>';
		echo '<td>'.$message[0].':'.$message[1].'</td>';
		echo '<td>'.$message[2].'</td>';
		echo '<td>'.$message[3].'</td>';
		echo '<td>'.$message[4].'</td>';
		echo '</tr>';
	}
	echo '</table>';
}
?>
</body>
</html>
