<?php
function validateuser()
{
    $password = explode("\n", file_get_contents('phppasswd'));

    $connection = new PDO('mysql:host=localhost;dbname=markfina_entitlements;charset=utf8', 'markfina_php', $password[0]);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (array_key_exists('publickey', $_POST))
    {
        $response = array();
        $response['errormessage'] = 'Cannot yet handle public keys';

        header('Content-Type: application/json', true, 500);
        echo json_encode($response);
    }
    else
    {
        // determine if there is an existing user
        $matching_users = $connection->prepare('SELECT COUNT(*) FROM User WHERE email=:email');
        $matching_users->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
        $matching_users->execute();

        $response = array();
        $response['exists'] = (0 == $matching_users->fetchColumn());

        header('Content-Type: application/json', true, 201);
        echo json_encode($response);
    }

    unset($connection);
}
?>
