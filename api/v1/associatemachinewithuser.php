<?php

require_once 'api/v1/dbutils.php';
require_once 'api/v1/send_email.php';
require_once 'api/v1/errorcodes.php';
require_once 'api/v1/authorisemachine.php';
require_once 'api/v1/userhostmachine_table_queries.php';

function associatemachinewithuser()
{
    if (!array_key_exists('email', $_POST) || empty($_POST['email']))
    {
        $response = array();
        $response['errormessage'] = 'An email address must be provided.';
        $response['errorcode'] = ERR_EMAIL_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $response = array();
        $response['errormessage'] = 'The email address used an incorrect format.';
        $response['errorcode'] = ERR_EMAIL_INCORRECT_FORMAT;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }
    if (!array_key_exists('MAC', $_POST) || empty($_POST['MAC']))
    {
        $response = array();
        $response['errormessage'] = 'The MAC address of the computer must be provided.';
        $response['errorcode'] = ERR_MAC_ADDRESS_NOT_SPECIFIED;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }
    if (!filter_var($_POST['MAC'], FILTER_VALIDATE_MAC))
    {
        $response = array();
        $response['errormessage'] = 'The MAC address used an incorrect format.';
        $response['errorcode'] = ERR_MAC_INCORRECT_FORMAT;

        header('Content-Type: application/json', true, 400);
        echo json_encode($response);
        return;
    }

    // ensure that all MAC addresses are uppercase
    $MACaddress = strtoupper($_POST['MAC']);

    expireMachineAuthorisationLinks();

    $connection = connectdb();

    $find_existing_request = $connection->prepare('SELECT id,url,expired FROM UserHostMachineRequest WHERE email=:email AND MAC=:MAC');
    $find_existing_request->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
    $find_existing_request->bindParam(':MAC', $MACaddress, PDO::PARAM_STR);
    $find_existing_request->execute();
    $request = $find_existing_request->fetch(PDO::FETCH_ASSOC);
    if (0 == $request['id'])
    {
        createTransaction($connection);

        $url = '/api/v1/authorisemachine/';
        $url .= md5(uniqid($_POST['email'].$MACaddress, true));

        $insert_new_request = $connection->prepare('INSERT INTO UserHostMachineRequest (email,MAC,url) VALUES (:email,:MAC,:url)');
        $insert_new_request->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
        $insert_new_request->bindParam(':MAC', $MACaddress, PDO::PARAM_STR);
        $insert_new_request->bindParam(':url', $url, PDO::PARAM_STR);
        $insert_new_request->execute();

        $connection->commit();

        header('Content-Type: application/json', true, 201);
    }
    else
    {
        $expired = $request['expired'];
        if (0 != $expired)
        {
            $response = array();
            $response['errorcode'] = ERR_AUTH_MACHINE_LINK_EXPIRED;
            $response['errormessage'] = 'Machine authorisation has expired.';

            header('Content-Type: application/json', true, 404);
            echo json_encode($response);
            return;
        }

        // this is like re-sending the email
        $url = $request['url'];
        header('Content-Type: application/json', true, 200);
    }

    $full_url = $_SERVER['REQUEST_SCHEME'];
    $full_url .= '://';
    $full_url .= $_SERVER['HTTP_HOST'];
    $full_url .= $url;

    $email_message = '<html>';
    $email_message .= '<body>';
    $email_message .= '<p>To authorise your machine, click <a href=\''.$full_url.'\'>here</a>. This link is valid for 24 hours only.</p>';
    $email_message .= '</body>';
    $email_message .= '</html>';

    send_email($_POST['email'], 'Machine activation', $email_message);

    $response = array();
    $response['url'] = $url;
    $response['errorcode'] = ERR_NONE;

    echo json_encode($response);

    unset($connection);
}
?>
