<?php
function registeruser()
{
    if (!array_key_exists('email', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'An email address must be provided';

        header('Content-Type: application/json', true, 500);
        echo json_encode($response);
        return;
    }

    // generate a public/private key for the new user
    $private_key_resource = openssl_pkey_new();
    openssl_pkey_export($private_key_resource, $private_key);

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

    $insert_user = $connection->prepare('INSERT INTO User (email,privatekey) VALUES (:email,:private_key)');
    $insert_user->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
    $insert_user->bindParam(':private_key', $private_key, PDO::PARAM_STR);
    $insert_user->execute();
    $userid = intval($connection->lastInsertId());

    $connection->commit();

    $private_key_details = openssl_pkey_get_details($private_key_resource);
    $public_key = $private_key_details['key'];

    $response = array();
    $response['userid'] = $userid;
    $response['publickey'] = $public_key;

    header('Content-Type: application/json', true, 201);
    echo json_encode($response);

    unset($connection);
}
?>
