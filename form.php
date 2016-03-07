<?php
	echo 'At Node ' . $_GET['node'] . '<br/>';
	echo '<form method="post">
		<table cellpadding="5">
			<tr>
				<td>UUID:</td>
				<td><input id="addPeer" type="text" name="addPeer"> </id>
			</tr>
			<tr>
				<td>Endpoint:</td>
				<td><input id="endpoint" type="text" name="endpoint"> </td>
			</tr>
			<tr>
				<td><input type="submit" value="AddPeer"></td>
			</tr>
		</table>
		</form>';
	echo '<form method="post">
		<table cellpadding="5">
			<tr>
				<td>Name:</td>
				<td><input id="originator" type="text" name="originator"> </td>
			</tr>
			<tr>
				<td>Message:</td>
				<td><textarea id="message" name="message" rows="10" cols="80"></textarea></td>
			</tr>
			<tr>
				<td><input type="submit" value="Send"></td>
			</tr>
		</table>
		<input id="endpoint" type="hidden" name="endpoint" value="http://45.56.44.13'.$_SERVER['REQUEST_URI'].'">
	</form>
';
?>
