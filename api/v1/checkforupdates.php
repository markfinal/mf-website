<?php
require_once 'api/v1/errorcodes.php';
require_once 'api/v1/log.php';
require_once 'api/v1/opensslutils.php';
require_once 'api/v1/product_table_queries.php';
require_once 'api/v1/productupdate_table_queries.php';

// ensure that the session passed in the JSON can be found in the database
// and that the signature of the JSON data can be verified by the public key for the user
function verifysession()
{
    if (!array_key_exists('json', $_POST) || empty($_POST['json']))
    {
        storelog('No JSON in the check for update data');
        $response = array();
        $response['errormessage'] = 'No check for update data was provided.';
        $response['errorcode'] = ERR_PRODUCTUPDATE_DATA_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        exit();
    }
    if (!array_key_exists('sig', $_POST) || empty($_POST['sig']))
    {
        storelog('No JSON signature in the check for update data');
        $response = array();
        $response['errormessage'] = 'No check for update signature was provided.';
        $response['errorcode'] = ERR_PRODUCTUPDATE_SIG_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        exit();
    }

    $raw_json = base64_decode($_POST['json']);
    $json = json_decode($raw_json, true);

    $session_data = licensesession_getdata_ifvalid($json['session']);
    if (NULL == $session_data)
    {
        storelog('License session \''.$json['session'].'\' was not found in the database');
        $response = array();
        $response['errormessage'] = 'Session token is invalid.';
        $response['errorcode'] = ERR_PRODUCTUPDATE_SESSION_TOKEN_INVALID;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        exit();
    }

    $user_id = license_getuserid($session_data['license']);
    $certificate = user_table_getcertificate($user_id);

    $sigb64 = $_POST['sig'];
    $sig = base64_decode($sigb64);

    $verified = verify_client_request($raw_json, $sig, $certificate);
    if (1 == $verified)
    {
        return $json;
    }

    if (0 == $verified)
    {
        storelog('Check for update data could not be verified by user certificate: '.openssl_error_string());
        $response = array();
        $response['errormessage'] = 'Cannot verify check for update for user.';
        $response['errorcode'] = ERR_PRODUCTUPDATE_DATA_NOT_VERIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        exit();
    }
    else
    {
        storelog('OpenSSL error verifying check for update data: '.openssl_error_string());
        $response = array();
        $response['errormessage'] = 'Cannot verify check for update for user.';
        $response['errorcode'] = ERR_SERVER_ERROR;

        header('Content-Type: application/json', true, 500);
        echo json_encode($response);
        exit();
    }
}

function checkforupdates()
{
    $json = verifysession();

    $product_id = product_getid($json['productname']);
    $update_message = productupdate_getupdatemessage(
        $product_id,
        $json['product_majorversion'],
        $json['product_minorversion'],
        $json['product_patchversion'],
        $json['product_build'],
        $json['product_phase']);

    $response = array();
    if (is_null($update_message))
    {
        $response['errorcode'] = ERR_PRODUCTUPDATE_ALREADY_UP_TO_DATE;
        $response['errormessage'] = 'There are no updates available.';
    }
    else
    {
        $response['errorcode'] = ERR_NONE;
        $response['errormessage'] = $update_message;
    }

    header('Content-Type: application/json', true, 200);
    echo json_encode($response);
}
?>
