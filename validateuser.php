<?php
require_once 'errorcodes.php';
require_once 'user_table_queries.php';
require_once 'host_table_queries.php';
require_once 'userhostmachine_table_queries.php';

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

    $user_id = user_table_get_id($_POST['email']);

    if (!array_key_exists('MAC', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'The MAC address of the computer must be provided';
        $response['errorcode'] = ERR_MAC_ADDRESS_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

    $host_id = host_table_get_id($_POST['MAC']);

    $user_machine_id = userhostmachine_table_get_id($user_id, $host_id);

    $response = array();
    $response['token'] = 'some magic token';

    header('Content-Type: application/json', true, 200);
    echo json_encode($response);
}
?>
