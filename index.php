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
				addNode($_GET['node'], 
					$mId[0], 
					$r_message->{'EndPoint'});
				addPeer($_GET['node'], $mId[0]);
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
}
	//print out input string and messages
	require('form.php');
?>
<!-- Reload messages every so often -->
<script language="JavaScript" type="text/javascript" src="./jquery.js"></script>
<div id="tableHolder"></div>
<script type="text/javascript">
    $(document).ready(function(){
      refreshTable();
    });

    function refreshTable(){
        $('#tableHolder').load('./displayMessages.php?node=<?php echo$_GET['node']?>', function(){
           setTimeout(refreshTable, 1000);
        });
    }
</script>
</body>
</html>
