<?php
header('Content-Type: application/json');
$new_array = $GLOBALS;
$index = array_search('GLOBALS',array_keys($new_array));
echo json_encode(array_splice($new_array, $index, $index-1), JSON_PRETTY_PRINT);
echo json_encode($_SERVER, JSON_PRETTY_PRINT);
echo json_encode($_REQUEST, JSON_PRETTY_PRINT);
echo json_encode($_SESSION, JSON_PRETTY_PRINT);
echo json_encode($_ENV, JSON_PRETTY_PRINT);
?>
