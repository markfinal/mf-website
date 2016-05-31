<?php
header('Content-Type: application/json');
$new_array = $GLOBALS;
$index = array_search('GLOBALS',array_keys($new_array));
$spliced_array = array_splice($new_array, $index, $index-1);
$combined = array_merge($spliced_array, $_SERVER, $_REQUEST, $_SESSION, $_ENV);
echo json_encode($combined, JSON_PRETTY_PRINT);
?>
