<?php
require_once 'errorcodes.php';

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

    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // is the email address known?
    $query_user = $connection->prepare('SELECT id FROM User WHERE email=:email');
    $query_user->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
    $query_user->execute();
    $user_id = $query_user->fetchColumn(0);
    if (0 == $user_id)
    {
        $response = array();
        $response['errormessage'] = 'The email address is not known';
        $response['errorcode'] = ERR_UNKNOWN_EMAIL;

        header('Content-Type: application/json', true, 404);
        echo json_encode($response);
        return;
    }

    if (!array_key_exists('MAC', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'The MAC address of the computer must be provided';
        $response['errorcode'] = ERR_MAC_ADDRESS_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

    // is the MAC address known about?
    $find_host = $connection->prepare('SELECT id FROM Host WHERE MAC=:MAC');
    $find_host->bindParam(':MAC', $_POST['MAC'], PDO::PARAM_STR);
    $find_host->execute();
    $host_id = $find_host->fetchColumn(0);
    if (0 == $host_id)
    {
        $response = array();
        $response['errormessage'] = 'The MAC address is not known';
        $response['errorcode'] = ERR_UNKNOWN_MAC_ADDRESS;

        header('Content-Type: application/json', true, 404);
        echo json_encode($response);
        return;
    }

    // is the MAC address associated to the user?
    $find_user_machine_association = $connection->prepare('SELECT id FROM UserHostMachine WHERE user=:user AND host=:host');
    $find_user_machine_association->bindParam(':user', $user_id, PDO::PARAM_INT);
    $find_user_machine_association->bindParam(':host', $host_id, PDO::PARAM_INT);
    $find_user_machine_association->execute();
    $user_machine_id = $find_user_machine_association->fetchColumn(0);
    if (0 == $user_machine_id)
    {
        $response = array();
        $response['errormessage'] = 'The MAC address is not associated with the user';
        $response['errorcode'] = ERR_MAC_ADDRESS_NOT_ASSOCIATED_WITH_USER;

        header('Content-Type: application/json', true, 404);
        echo json_encode($response);
        return;
    }

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
