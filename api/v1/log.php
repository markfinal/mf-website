<?php
require_once 'api/v1/dbutils.php';

function storelog($message, $user_id=NULL, $host_id=NULL, $session_id=NULL)
{
    $token = md5(uniqid($message, true));
    error_log('['.$token.'] '.$message);

    try
    {
        $connection = connectdb();

        createTransaction($connection);

        $query = $connection->prepare('INSERT INTO Log (token,message,user,host,session) VALUES (:token,:message,:userid,:hostid,:sessionid)');
        $query->bindParam(':token', $token, PDO::PARAM_STR);
        $query->bindParam(':message', $message, PDO::PARAM_STR);
        $query->bindParam(':userid', $user_id, PDO::PARAM_INT);
        $query->bindParam(':hostid', $host_id, PDO::PARAM_INT);
        $query->bindParam(':sessionid', $session_id, PDO::PARAM_INT);
        $query->execute();

        $connection->commit();
        unset($connection);
    }
    catch (PDOException $e)
    {
        error_log("Could not write to database log: ".$e);
    }

    return $token;
}
?>
