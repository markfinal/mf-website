<?php
require_once 'api/v1/errorcodes.php';

function host_table_get_id($MAC)
{
    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $connection->prepare('SELECT id FROM Host WHERE MAC=:MAC');
    $query->bindParam(':MAC', $MAC, PDO::PARAM_STR);
    $query->execute();
    $host_id = $query->fetchColumn(0);
    if (0 == $host_id)
    {
        $response = array();
        $response['errormessage'] = 'The MAC address is not known';
        $response['errorcode'] = ERR_UNKNOWN_MAC_ADDRESS;

        header('Content-Type: application/json', true, 404);
        echo json_encode($response);
        exit();
    }

    unset($connection);

    return $host_id;
}
?>
