<?php
function expireMachineAuthorisationLinks()
{
    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $find_expired_requests = $connection->prepare('SELECT id FROM UserHostMachineRequest WHERE created < NOW() - INTERVAL 24 HOUR AND expired=0');
    $find_expired_requests->execute();
    if ($find_expired_requests->rowCount() > 0)
    {
        if (!$connection->beginTransaction())
        {
            $response = array();
            $response['errormessage'] = 'Could not start a transaction';

            header('Content-Type: application/json', true, 500);
            echo json_encode($response);
            return;
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

function authorisemachine($url)
{
    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $message = '<html>';
    $message .= '<body>';

    $html_suffix = '</body>';
    $html_suffix .= '</html>';

    $find_existing_request = $connection->prepare('SELECT id,email,MAC,expired FROM UserHostMachineRequest WHERE url=:url');
    $find_existing_request->bindParam(':url', $url, PDO::PARAM_STR);
    $find_existing_request->execute();
    if (0 == $find_existing_request->rowCount())
    {
        $message .= '<p>Invalid machine authorisation for '.$url.'</p>';
        $message .= $html_suffix;

        header('Content-Type: text/html', true, 404);
        echo $message;
        return;
    }

    $request = $find_existing_request->fetch(PDO::FETCH_ASSOC);

    $fetch_user_id = $connection->prepare('SELECT id FROM User WHERE email=:email');
    $fetch_user_id->bindParam(':email', $request['email'], PDO::PARAM_STR);
    $fetch_user_id->execute();

    $fetch_host_id = $connection->prepare('SELECT id FROM Host WHERE MAC=:MAC');
    $fetch_host_id->bindParam(':MAC', $request['MAC'], PDO::PARAM_STR);
    $fetch_host_id->execute();

    $insert_user_machine_association = $connection->prepare('INSERT INTO UserHostMachine (user,host) VALUES (user=:user,host=:host)');
    $insert_user_machine_association->bindParam(':user', $fetch_user_id->fetchColumn(0), PDO::PARAM_INT);
    $insert_user_machine_association->bindParam(':host', $fetch_host_id->fetchColumn(0), PDO::PARAM_INT);
    $insert_user_machine_association->execute();
    $user_machine_id = $insert_user_machine_association->fetchColumn(0);
    if (0 == $user_machine_id)
    {
        $response = array();
        $response['errormessage'] = 'The MAC address is not associated with the user';
        $response['errorcode'] = ERR_MAC_ADDRESS_NOT_ASSOCIATED_WITH_USER;

        header('Content-Type: application/json', true, 404);
        echo json_encode($response);
        return;
    }

    // TODO: delete the authorisation request
    // TODO: write some nice HTML

    $message .= '<p>Id was '.$request['id'].'</p>';
    $message .= $html_suffix;

    header('Content-Type: text/html', true, 200);
    echo $message;

    unset($connection);
}
?>
