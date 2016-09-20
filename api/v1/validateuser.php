<?php
require_once 'api/v1/errorcodes.php';
require_once 'api/v1/user_table_queries.php';
require_once 'api/v1/host_table_queries.php';
require_once 'api/v1/userhostmachine_table_queries.php';

function var_dump_error_log($object)
{
    ob_start();
    var_dump($object);
    $contents = ob_get_contents();
    ob_end_clean();
    error_log($contents);
}

// Validate a user can progress to acquire a license, given their email address, public key, and MAC address.
// Returns an expiring security token.
function validateuser()
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

    $userrow = user_table_get_id($_POST['email']);

    if (!array_key_exists('MAC', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'The MAC address of the computer must be provided';
        $response['errorcode'] = ERR_MAC_ADDRESS_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

    // ensure that all MAC addresses are uppercase
    $MACaddress = strtoupper($_POST['MAC']);

    $num_user_machines = userhostmachine_table_get_num_usermachines($userrow['id']);
    $host_id = host_table_get_id($MACaddress, $num_user_machines, $userrow['maxmachines']);

    // as both user and host are confirmed registered, is there a mapping
    // to authorise using this host by this user?
    $user_machine_id = userhostmachine_table_get_id($userrow['id'], $host_id);

    // the user is now authorised to use software on this machine
    // return a token allowing access to licensing code
    // only the owner of the private key will be able to extract the token
    // TODO: generate a token and store in the DB, that is associated with the user-host pairing
    $token = 'I am bloody awesome';//md5(uniqid($_POST['email'].$_POST['MAC'], true));
    $certificate = $userrow['certificate'];
    $public_res = openssl_pkey_get_public($certificate);
    if (!openssl_public_encrypt($token, $encrypted_token, $public_res, OPENSSL_PKCS1_OAEP_PADDING))
    {
        error_log(openssl_error_string());
    }
    openssl_free_key($public_res);

    error_log($token);
    error_log($encrypted_token);
    error_log(base64_encode($encrypted_token));

    $response = array();
    $response['token'] = $token;
    $response['encryptedtoken'] = base64_encode($encrypted_token);

    header('Content-Type: application/json', true, 200);
    $encoded_result = json_encode($response);
    if ($encoded_result)
    {
        echo $encoded_result;
    }
}
?>
