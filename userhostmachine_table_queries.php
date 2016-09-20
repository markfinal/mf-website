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

function expireSpecificMachineAuthorisationLink($connection,$id)
{
    if (!$connection->beginTransaction())
    {
        $response = array();
        $response['errormessage'] = 'Could not start a transaction';

        header('Content-Type: application/json', true, 500);
        echo json_encode($response);
        exit();
    }
    $update_expired_requests = $connection->prepare('UPDATE UserHostMachineRequest SET expired=1 WHERE id=:id');
    $update_expired_requests->bindParam(':id', $id, PDO::PARAM_INT);
    $update_expired_requests->execute();
    $connection->commit();
}

function expireMachineAuthorisationLinks()
{
    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $interval = '24 HOUR';
    $find_expired_requests = $connection->prepare('SELECT id FROM UserHostMachineRequest WHERE created < NOW() - INTERVAL '.$interval.' AND expired=0');
    $find_expired_requests->execute();
    if ($find_expired_requests->rowCount() > 0)
    {
        if (!$connection->beginTransaction())
        {
            $response = array();
            $response['errormessage'] = 'Could not start a transaction';

            header('Content-Type: application/json', true, 500);
            echo json_encode($response);
            exit();
        }
        $update_expired_requests = $connection->prepare('UPDATE UserHostMachineRequest SET expired=1 WHERE id=:id');
        while ($row = $find_expired_requests->fetch(PDO::FETCH_ASSOC))
        {
            error_log('Request '.$row['id'].' has now expired');
            $update_expired_requests->bindParam(':id', $row['id'], PDO::PARAM_INT);
            $update_expired_requests->execute();
        }

        $connection->commit();
    }

    unset($connection);
}
?>
