<?php
require_once 'errorcodes.php';
require_once 'stringutils.php';

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
