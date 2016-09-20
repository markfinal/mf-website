<?php

require_once 'api/v1/errorcodes.php';

function registermacaddress()
{
    if (!array_key_exists('MAC', $_POST))
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

    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!$connection->beginTransaction())
    {
        $response = array();
        $response['errormessage'] = 'Could not start a transaction';

        header('Content-Type: application/json', true, 500);
        echo json_encode($response);
        return;
    }

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

    $response = array();
    $response['mac_address_id'] = $mac_address_id;

    header('Content-Type: application/json', true, 201);
    echo json_encode($response);

    unset($connection);
}
?>
