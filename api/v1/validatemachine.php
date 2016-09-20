<?php
function validatemachine()
{
    if (!array_key_exists('MAC', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'A MAC address must be provided';

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

    // force all MAC addresses to be upper case
    $macaddress = strtoupper($_POST['MAC']);

    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // is the MAC address already in the DB?
    $mac_address_exists = $connection->prepare('SELECT id FROM HostMachine WHERE MAC=:MAC');
    $mac_address_exists->bindParam(':MAC', $macaddress, PDO::PARAM_STR);
    $mac_address_exists->execute();
    $mac_address_id = intval($mac_address_exists->fetchColumn(0));

    // add it if not
    if (0 == $mac_address_id)
    {
        if (!$connection->beginTransaction())
        {
            $response = array();
            $response['errormessage'] = 'Could not start a transaction';

            header('Content-Type: application/json', true, 500);
            echo json_encode($response);
            return;
        }

        $insert_mac_address = $connection->prepare('INSERT INTO HostMachine (MAC) VALUES (:MAC)');
        $insert_mac_address->bindParam(':MAC', $macaddress, PDO::PARAM_STR);
        $insert_mac_address->execute();
        $mac_address_id = intval($connection->lastInsertId());

        $connection->commit();

        header('Content-Type: application/json', true, 201);
    }
    else
    {
        header('Content-Type: application/json', true, 200);
    }

    $response = array();
    $response['mac_address_id'] = $mac_address_id;

    echo json_encode($response);

    unset($connection);
}
?>
