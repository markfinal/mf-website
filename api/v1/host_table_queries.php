<?php
require_once 'api/v1/dbutils.php';
require_once 'api/v1/errorcodes.php';

function host_table_get_id($MAC, $num_user_machines, $max_machines)
{
    $connection = connectdb();

    $query = $connection->prepare('SELECT id FROM Host WHERE MAC=:MAC');
    $query->bindParam(':MAC', $MAC, PDO::PARAM_STR);
    $query->execute();
    $host_id = $query->fetchColumn(0);
    if (0 == $host_id)
    {
        // check that adding one more machine will not exceed the quota
        if ($num_user_machines + 1 > $max_machines)
        {
            $response = array();
            $response['errormessage'] = 'Quota of machines has been reached';
            $response['errorcode'] = ERR_INSUFFICIENT_FREE_MAC_ADDRESSES;

            header('Content-Type: application/json', true, 412);
            echo json_encode($response);
            exit();
        }

        $response = array();
        $response['errormessage'] = 'The MAC address has not been registered';
        $response['errorcode'] = ERR_MAC_ADDRESS_NOT_REGISTERED;

        header('Content-Type: application/json', true, 404);
        echo json_encode($response);
        exit();
    }

    unset($connection);

    return $host_id;
}
?>
