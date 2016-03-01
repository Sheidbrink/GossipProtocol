<?php
	require_once('db.php');
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

?>
