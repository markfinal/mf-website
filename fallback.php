<?php
header('Content-Type: application/json');

// TODO: don't embed passwords like this
$mysqli = new mysqli("localhost", "markfina_admin", "T3st!!", "markfina_licensing");
if (mysqli_connect_errno())
{
	echo mysqli_connect_error();
}

if ($result = $mysqli->query("SELECT DATABASE()"))
{
	$row = $result->fetch_row();
	echo $row[0];
	$result->close();
}

$mysqli->close();

$new_array = $GLOBALS;
$index = array_search('GLOBALS',array_keys($new_array));
echo json_encode(array_splice($new_array, $index, $index-1), JSON_PRETTY_PRINT);
echo json_encode($_SERVER, JSON_PRETTY_PRINT);
echo json_encode($_REQUEST, JSON_PRETTY_PRINT);
echo json_encode($_SESSION, JSON_PRETTY_PRINT);
echo json_encode($_ENV, JSON_PRETTY_PRINT);
?>
