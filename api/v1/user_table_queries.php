<?php
require_once 'api/v1/errorcodes.php';

function user_table_get_id($email)
{
    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $connection->prepare('SELECT id,certificate,maxmachines FROM User WHERE email=:email');
    $query->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    if (0 == $result['id'])
    {
        $response = array();
        $response['errormessage'] = 'The email address is not known';
        $response['errorcode'] = ERR_UNKNOWN_EMAIL;

        header('Content-Type: application/json', true, 404);
        echo json_encode($response);
        exit();
    }

    unset($connection);

    return $result;
}
?>
