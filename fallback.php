<?php
register_shutdown_function( "fatal_handler" );

function fatal_handler()
{
  $errfile = "unknown file";
  $errstr  = "shutdown";
  $errno   = E_CORE_ERROR;
  $errline = 0;

  $error = error_get_last();

  if (!is_null($error))
  {
    $errno   = $error["type"];
    $errfile = $error["file"];
    $errline = $error["line"];
    $errstr  = $error["message"];
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    echo json_encode($errstr);
  }
}

header('Content-Type: application/json');

$password=explode("\n", file_get_contents('phppasswd'));

$connection = new PDO('mysql:host=localhost;dbname=markfina_licensing;charset=utf8', 'markfina_php', $password[0]);

echo json_encode('Hello', JSON_PRETTY_PRINT);
echo json_encode($_SERVER['REQUEST_URI'], JSON_PRETTY_PRINT);
echo json_encode($_POST, JSON_PRETTY_PRINT);

/*
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
*/
?>
