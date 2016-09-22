<?php
require_once 'api/v1/errorcodes.php';
require_once 'api/v1/log.php';
require_once 'api/v1/usertoken_table_queries.php';
require_once 'api/v1/license_table_queries.php';
require_once 'api/v1/licensesession_table_queries.php';
require_once 'api/v1/opensslutils.php';

// ensure that the session passed in the JSON can be found in the database
// and that the signature of the JSON data can be verified by the public key for the user
function verifyreturn()
{
    clear_openssl_errors();
    if (!array_key_exists('json', $_POST))
    {
        storelog('No JSON in the license return data');
        $response = array();
        $response['errormessage'] = 'No license return data was provided';
        $response['errorcode'] = ERR_LICENSE_RETURN_DATA_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        exit();
    }
    if (!array_key_exists('sig', $_POST))
    {
        storelog('No JSON signature in the license return data');
        $response = array();
        $response['errormessage'] = 'No license return signature was provided';
        $response['errorcode'] = ERR_LICENSE_RETURN_SIG_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        exit();
    }

    $raw_json = base64_decode($_POST['json']);
    $json = json_decode($raw_json, true);

    $session_data = licensesession_getdata_ifvalid($json['session']);
    if (NULL == $session_data)
    {
        storelog('License session was not found in the database');
        $response = array();
        $response['errormessage'] = 'Session token is invalid';
        $response['errorcode'] = ERR_LICENSE_SESSION_TOKEN_INVALID;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        exit();
    }

    $user_id = license_getuserid($session_data['license']);
    $certificate = user_table_getcertificate($user_id);

    $sigb64 = $_POST['sig'];
    $sig = base64_decode($sigb64);

    $public_res = openssl_pkey_get_public($certificate);
    $result = openssl_verify($raw_json, $sig, $public_res, OPENSSL_ALGO_SHA1);
    openssl_free_key($public_res);
    if ($result < 1)
    {
        storelog('License return data could not be verified by user certificate: '.openssl_error_string());
        $response = array();
        $response['errormessage'] = 'Cannot verify license return for user';
        $response['errorcode'] = ERR_LICENSE_RETURN_DATA_NOT_VERIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        exit();
    }

    return $session_data['id'];
}

function licensereturn()
{
    $session_id = verifyreturn();

    licensesession_end($session_id);

    $response = array();
    $response['errorcode'] = ERR_NONE;
    $response['errormessage'] = 'License session returned';

    header('Content-Type: application/json', true, 200);
    echo json_encode($response);
}
?>
