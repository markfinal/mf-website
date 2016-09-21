<?php
require_once 'api/v1/errorcodes.php';
require_once 'api/v1/usertoken_table_queries.php';

function clear_openssl_errors()
{
    // because there seems to be a backlog of error messages...
    while ($msg = openssl_error_string())
    {
    }
}

// ensure that the token passed in the JSON can be found in the database
// and that the signature of the JSON data can be verified by the public key for the user
function verifydata()
{
    clear_openssl_errors();
    if (!array_key_exists('json', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'No JSON was provided';
        $response['errorcode'] = ERR_EMAIL_NOT_SPECIFIED; // TODO

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }
    if (!array_key_exists('sig', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'No signature was provided';
        $response['errorcode'] = ERR_EMAIL_NOT_SPECIFIED; // TODO

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

    $raw_json = base64_decode($_POST['json']);
    $json = json_decode($raw_json, true);

    $token_data = usertoken_getdata_ifvalid($json['token']);
    if (NULL == $token_data)
    {
        $response = array();
        $response['errormessage'] = 'Access token is invalid';
        $response['errorcode'] = ERR_EMAIL_NOT_SPECIFIED; // TODO

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

    $user_and_host = userhostmachine_table_getuserandhost($token_data['userhost']);

    $certificate = user_table_getcertificate($user_and_host['user']);

    $sigb64 = $_POST['sig'];
    $sig = base64_decode($sigb64);

    $public_res = openssl_pkey_get_public($certificate);
    $result = openssl_verify($raw_json, $sig, $public_res, OPENSSL_ALGO_SHA1);
    openssl_free_key($public_res);
    if ($result < 1)
    {
        error_log(openssl_error_string());
        $response = array();
        $response['errormessage'] = 'Cannot verify data';
        $response['errorcode'] = ERR_EMAIL_NOT_SPECIFIED; // TODO

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }
}

function licenserequest()
{
    verifydata();

    $response = array();

    header('Content-Type: application/json', true, 200);
    echo json_encode($response);
}
?>
