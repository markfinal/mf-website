<?php
require_once 'api/v1/errorcodes.php';
require_once 'api/v1/stringutils.php';

function isdynamicurl($url)
{
    if (startswith($url, '/api/v1/authorizemachine/'))
    {
        authorisemachine($url);
        return true;
    }
    return false;
}
?>
