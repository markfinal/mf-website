<?php
require_once 'errorcodes.php';

function userhostmachine_table_get_id($userid, $hostid)
{
    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $connection->prepare('SELECT id FROM UserHostMachine WHERE user=:user AND host=:host');
    $query->bindParam(':user', $userid, PDO::PARAM_INT);
    $query->bindParam(':host', $hostid, PDO::PARAM_INT);
    $query->execute();
    $user_machine_id = $query->fetchColumn(0);
    if (0 == $user_machine_id)
    {
        $response = array();
        $response['errormessage'] = 'The MAC address is not associated with the user';
        $response['errorcode'] = ERR_MAC_ADDRESS_NOT_ASSOCIATED_WITH_USER;

        header('Content-Type: application/json', true, 404);
        echo json_encode($response);
        exit();
    }

    unset($connection);

    return $user_machine_id;
}
?>
