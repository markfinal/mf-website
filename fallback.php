<?php
    $path = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)
    $elements = explode('/', $path);                // Split path on slashes
    echo json_encode($elements);
?>
