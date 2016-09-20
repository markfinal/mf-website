<?php

require_once 'api/v1/errorcodes.php';

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
    if (!array_key_exists('publickey', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'A public key must be provided';
        $response['errorcode'] = ERR_PUBLICKEY_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

/* TESTING encryption, decryption, signing and verifying
    $publickey = $_POST['publickey'];
    error_log('Public key');
    error_log(strlen($publickey));
    error_log($publickey);
    $privatekey= $_POST['privatekey'];
    error_log('Private key');
    error_log(strlen($privatekey));
    error_log($privatekey);

    $private_res = openssl_pkey_get_private($privatekey);
    if (0 == $private_res)
    {
        error_log(openssl_error_string());
    }

    // note that this HAS to be an x509 certificate
    $pk = openssl_pkey_get_public($publickey);
    if (0 == $pk)
    {
        error_log(openssl_error_string());
    }

    $cleartext = "Hello world";
    if (!openssl_public_encrypt($cleartext, $encrypted, $pk))
    {
        error_log(openssl_error_string());
    }
    if (!openssl_private_decrypt($encrypted, $decrypted, $privatekey))
    {
        error_log(openssl_error_string());
    }
    error_log('Original '.$cleartext);
    error_log('Encrypt  '.$encrypted);
    error_log('Decrypt  '.$decrypted);



    if (!openssl_sign($cleartext, $signature, $privatekey))
    {
        error_log(openssl_error_string());
    }
    error_log('Signature '.$signature);

    $verify = openssl_verify($cleartext, $signature, $pk);
    if (1 == $verify)
    {
        error_log("Verified");
    }
    else if (0 == $verify)
    {
        error_log("Not Verified");
    }
    else
    {
        error_log(openssl_error_string());
    }
    */

    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!$connection->beginTransaction())
    {
        $response = array();
        $response['errormessage'] = 'Could not start a transaction';

        header('Content-Type: application/json', true, 500);
        echo json_encode($response);
        return;
    }

    $insert_user = $connection->prepare('INSERT INTO User (email,publickey) VALUES (:email,:publickey)');
    $insert_user->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
    $insert_user->bindParam(':publickey', $_POST['publickey'], PDO::PARAM_STR);
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

    //send_email($_POST['email'], 'User registration', 'Please find your public key attached', array('publickey.txt'=>$public_key));

    $response = array();
    $response['userid'] = $userid;

    header('Content-Type: application/json', true, 201);
    echo json_encode($response);

    unset($connection);
}
?>
