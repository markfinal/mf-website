<?php
$new_array = $GLOBALS;
$index = array_search('GLOBALS',array_keys($new_array));
echo json_encode(array_splice($new_array, $index, $index-1));
?>
