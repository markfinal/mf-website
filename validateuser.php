<?php
function var_dump_error_log($object)
{
    ob_start();
    var_dump($object);
    $contents = ob_get_contents();
    ob_end_clean();
    error_log($contents);
}

function validateuser()
{
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
}
?>
