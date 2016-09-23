<?php

require_once 'api/v1/dbutils.php';
require_once 'api/v1/errorcodes.php';
require_once 'api/v1/log.php';

function registermacaddress()
{
    if (!array_key_exists('MAC', $_POST) || empty($_POST['MAC']))
    {
        $response = array();
        $response['errormessage'] = 'A MAC address must be provided';
        $response['errorcode'] = ERR_MAC_ADDRESS_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

    // ensure that all MAC addresses are uppercase
    $MACaddress = strtoupper($_POST['MAC']);

    $connection = connectdb();

    createTransaction($connection);

    $insert_mac_address = $connection->prepare('INSERT INTO Host (MAC) VALUES (:MAC)');
    $insert_mac_address->bindParam(':MAC', $MACaddress, PDO::PARAM_STR);
    try
    {
        $insert_mac_address->execute();
    }
    catch (PDOException $e)
    {
        if (MYSQL_ERRCODE_DUPLICATE_KEY === $e->errorInfo[1])
        {
            $response = array();
            $response['errormessage'] = 'The MAC address is already in use';
            $response['errorcode'] = ERR_MAC_ADDRESS_ALREADY_INUSE;

            header('Content-Type: application/json', true, 409);
            echo json_encode($response);
            return;
        }
        throw $e;
    }
    $mac_address_id = intval($connection->lastInsertId());

    $connection->commit();
    storelog('Registered MAC address '.$MACaddress, $mac_address_id);

    $response = array();
    $response['mac_address_id'] = $mac_address_id;

    header('Content-Type: application/json', true, 201);
    echo json_encode($response);

    unset($connection);
}
?>
