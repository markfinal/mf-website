<?php
require_once 'api/v1/dbutils.php';
require_once 'api/v1/errorcodes.php';

function user_table_get_id($email)
{
    $connection = connectdb();

    $query = $connection->prepare('SELECT id,certificate,maxmachines FROM User WHERE email=:email');
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    if (0 == $result['id'])
    {
        $response = array();
        $response['errormessage'] = 'The email address has not been registered';
        $response['errorcode'] = ERR_EMAIL_UNREGISTERED;

        header('Content-Type: application/json', true, 404);
        echo json_encode($response);
        exit();
    }

    unset($connection);

    return $result;
}

function user_table_getcertificate($id)
{
    $connection = connectdb();

    $query = $connection->prepare('SELECT certificate FROM User WHERE id=:id');
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    unset($connection);

    return $result['certificate'];
}
?>
