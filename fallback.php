<?php
require 'send_email.php';
require 'validateuser.php';
require 'registeruser.php';

// TODO: is this shutdown function needed now that there is an exception handler?
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

// note that in PHP 7.0 this also copes with Errors, such as uncaught errors
set_exception_handler('handler');
function handler(Throwable $e)
{
    error_log("$e");
    header('HTTP/1.1 500 Internal Server Error', true, 500);
    header('Content-Type: application/json');
    echo json_encode(["errormessage" => 'Server error']);
    exit;
}

function user_registration()
{
    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // determine if there is an existing user
    $matching_users = $connection->prepare('SELECT COUNT(*) FROM User WHERE email=:email');
    $matching_users->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
    $matching_users->execute();
    if (0 == $matching_users->fetchColumn())
    {
        // generate a public/private key for the new user
        $private_key_resource = openssl_pkey_new();
        openssl_pkey_export($private_key_resource, $private_key);

        // add the user into the db
        /* TEMPORARILY DISABLE DURING MAIL TESTS
        $insert_user = $connection->prepare('INSERT INTO User (email,privatekey) VALUES (:email,:private_key)');
        $insert_user->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
        $insert_user->bindParam(':private_key', $private_key, PDO::PARAM_STR);
        $insert_user->execute();
        */

        $private_key_details = openssl_pkey_get_details($private_key_resource);
        $public_key = $private_key_details['key'];

        // send the public key as an email attachment
        send_email($_POST['email'], 'Access', 'Please find attached...', array('publickey.txt'=>$public_key));

        header('Content-Type: application/json', true, 201);

        $response = array();
        $response['success'] = 'yes';
        $response['email'] = $_POST['email'];
        $response['publickey'] = $public_key;
        echo json_encode($response);
    }
    else
    {
        header('Content-Type: application/json', true, 500);
        $response = array();
        $response['success'] = 'no';
        $response['email'] = $_POST['email'];
        echo json_encode($response);
    }

    unset($connection);
}

try
{
    switch ($_SERVER['REQUEST_URI'])
    {
        case '/api/v1/register':
            user_registration();
            break;

        case '/api/v1/validateuser':
            validateuser();
            break;

        case '/api/v1/registeruser':
            registeruser();
            break;

        default:
        {
            $response = array();
            $response['errormessage'] = 'Unrecognized path: '.$_SERVER['REQUEST_URI'];

            header($_SERVER['SERVER_PROTOCOL'].' 404 not found', true, 404);
            header('Content-Type: application/json', true);
            echo json_encode($response);
        }
    }
}
catch (Exception $e)
{
    $response = array();
    $response['errormessage'] = $e->getMessage();

    header('Content-Type: application/json', true, 500);
    echo json_encode($response);
}
?>
