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

    // is the MAC address associated to the user?
    $user_machine_id = userhostmachine_table_get_id($user_id, $host_id);

    unset($connection);

    $response = array();
    $response['token'] = 'some magic token';

    header('Content-Type: application/json', true, 200);
    echo json_encode($response);

    /*
    if (!array_key_exists('email', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'An email address must be provided';

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // is there a user with the given email address?
    $matching_users = $connection->prepare('SELECT COUNT(*) FROM User WHERE email=:email');
    $matching_users->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
    $matching_users->execute();

    $response = array();
    $response['userexists'] = (1 == $matching_users->fetchColumn(0));

    if (array_key_exists('publickey', $_POST))
    {
        $get_private_key = $connection->prepare('SELECT privatekey FROM User WHERE email=:email');
        $get_private_key->bindparam(':email', $_POST['email'], PDO::PARAM_STR);
        $get_private_key->execute();

        $pkey_str = $get_private_key->fetchColumn(0);
        $pkey_resource = openssl_pkey_get_private($pkey_str);

        $private_key_details = openssl_pkey_get_details($pkey_resource);
        $public_keyDB = $private_key_details['key'];

        $public_keyPO = $_POST['publickey'];

        $compare = strcmp($public_keyDB, $public_keyPO);

        $response['publickeyvalid'] = (0 == $compare);

        header('Content-Type: application/json', true, 200);
        echo json_encode($response);
    }
    else
    {
        $response['publickeyvalid'] = false;

        header('Content-Type: application/json', true, 200);
        echo json_encode($response);
    }

    unset($connection);
    */
}
?>
