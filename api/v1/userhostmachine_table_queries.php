<?php
require_once 'api/v1/dbutils.php';
require_once 'api/v1/errorcodes.php';

function userhostmachine_table_get_id($userid, $hostid)
{
    $connection = connectdb();

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

function userhostmachine_table_getuserandhost($id)
{
    $connection = connectdb();

    $query = $connection->prepare('SELECT user,host FROM UserHostMachine WHERE id=:id');
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    unset($connection);

    return $result;
}

function userhostmachine_table_get_num_usermachines($userid)
{
    $connection = connectdb();

    $query = $connection->prepare('SELECT COUNT(id) FROM UserHostMachine WHERE user=:user');
    $query->bindParam(':user', $userid, PDO::PARAM_INT);
    $query->execute();
    $num_user_machines = $query->fetchColumn(0);

    unset($connection);

    return $num_user_machines;
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
    $connection = connectdb();

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
