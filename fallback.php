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

function user_registration()
{
	header('Content-Type: application/json');

	$password = explode("\n", file_get_contents('phppasswd'));

	$connection = new PDO('mysql:host=localhost;dbname=markfina_licensing;charset=utf8', 'markfina_php', $password[0]);

	$matching_users = $connection->prepare('SELECT COUNT(*) FROM users WHERE email=:email');
	$matching_users->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
	$matching_users->execute();
	if (0 == $matching_users->fetchColumn())
	{
		echo json_encode('Can register '.$_POST['email']);
	}
	else
	{
		echo json_encode('Already registered');
	}

	$connection = null;
}

if ($_SERVER['REQUEST_URI'] === '/api/v1/register')
{
	user_registration();
}
else
{
    header($_SERVER['SERVER_PROTOCOL'].' 404 not found', true, 404);
    echo json_encode('Unrecognized path: '.$_SERVER['REQUEST_URI']);
}
?>
