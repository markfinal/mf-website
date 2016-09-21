<?php
require_once 'api/v1/dbutils.php';
require_once 'api/v1/errorcodes.php';

function usertoken_deleteexisting($userhost_id)
{
    $connection = connectdb();

    if (!$connection->beginTransaction())
    {
        $response = array();
        $response['errormessage'] = 'Could not start a transaction';

        header('Content-Type: application/json', true, 500);
        echo json_encode($response);
        return;
    }

    $query = $connection->prepare('DELETE FROM UserToken WHERE userhost=:userhost_id');
    $query->bindParam(':userhost_id', $userhost_id, PDO::PARAM_INT);
    $query->execute();

    $num_deleted = $query->rowCount();
    error_log('There were '.$num_deleted.' instances deleted');

    $connection->commit();

    unset($connection);
}

function usertoken_createnew($email,$MAC,$certificate,$userhost_id)
{
    // if a token for this userhost pair already exists, then the user has not
    // used it to acquire a license - delete it, and give out a new token
    usertoken_deleteexisting($userhost_id);

    // the user is now authorised to use software on this machine
    // return a token allowing access to licensing code
    // only the owner of the private key will be able to extract the token
    $token = md5(uniqid($_POST['email'].$_POST['MAC'], true));
 
    $connection = connectdb();

    if (!$connection->beginTransaction())
    {
        $response = array();
        $response['errormessage'] = 'Could not start a transaction';

        header('Content-Type: application/json', true, 500);
        echo json_encode($response);
        return;
    }

    $query = $connection->prepare('INSERT INTO UserToken (token,userhost) VALUES (:token,:userhost_id)');
    $query->bindParam(':token', $token, PDO::PARAM_STR);
    $query->bindParam(':userhost_id', $userhost_id, PDO::PARAM_INT);
    $query->execute();

    $public_res = openssl_pkey_get_public($certificate);

    // Note: this padding type must match that in the C++
    $padding = OPENSSL_PKCS1_OAEP_PADDING;
    //$padding = OPENSSL_PKCS1_PADDING;
    if (!openssl_public_encrypt($token, $encrypted_token, $public_res, $padding))
    {
        error_log(openssl_error_string());
    }
    openssl_free_key($public_res);

    // after the OpenSSL code, commit to the DB
    $connection->commit();
    unset($connection);

    error_log($encrypted_token);
    error_log(base64_encode($encrypted_token));

    $response = array();
    $response['token'] = $token;
    $response['encryptedtoken'] = base64_encode($encrypted_token);
    $response['length'] = strlen($encrypted_token);

    header('Content-Type: application/json', true, 200);
    echo json_encode($response);
}
?>
