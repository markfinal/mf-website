<?php
function clear_openssl_errors()
{
    // because there seems to be a backlog of error messages...
    while ($msg = openssl_error_string())
    {
    }
}
?>
