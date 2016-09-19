<?php

require_once 'send_email.php';
require_once 'errorcodes.php';

function registeruser()
{
    if (!array_key_exists('email', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'An email address must be provided';
        $response['errorcode'] = ERR_EMAIL_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $response = array();
        $response['errormessage'] = 'The email address used an incorrect format';
        $response['errorcode'] = ERR_EMAIL_INCORRECT_FORMAT;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // generate a public/private key for the new user
    $private_key_resource = openssl_pkey_new();
    openssl_pkey_export($private_key_resource, $private_key);

    if (!$connection->beginTransaction())
    {
        $response = array();
        $response['errormessage'] = 'Could not start a transaction';

        header('Content-Type: application/json', true, 500);
        echo json_encode($response);
        return;
    }

    $insert_user = $connection->prepare('INSERT INTO User (email,privatekey) VALUES (:email,:private_key)');
    $insert_user->bindParam(':email', $emailaddress, PDO::PARAM_STR);
    $insert_user->bindParam(':private_key', $private_key, PDO::PARAM_STR);
    try
    {
        $insert_user->execute();
    }
    catch (PDOException $e)
    {
        if (MYSQL_ERRCODE_DUPLICATE_KEY === $e->errorInfo[1])
        {
            $response = array();
            $response['errormessage'] = 'The email address is already in use';
            $response['errorcode'] = ERR_EMAIL_ALREADY_INUSE;

            header('Content-Type: application/json', true, 409);
            echo json_encode($response);
            return;
        }
        throw $e;
    }
    $userid = intval($connection->lastInsertId());

    $connection->commit();

    $private_key_details = openssl_pkey_get_details($private_key_resource);
    $public_key = $private_key_details['key'];

    $response = array();
    $response['userid'] = $userid;

    send_email($emailaddress, 'User registration', 'Please find your public key attached', array('publickey.txt'=>$public_key));

    header('Content-Type: application/json', true, 201);
    echo json_encode($response);

    unset($connection);
}
?>
