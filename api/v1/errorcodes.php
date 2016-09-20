<?php

// email related error codes
define('ERR_EMAIL_NOT_SPECIFIED',                  101);
define('ERR_EMAIL_INCORRECT_FORMAT',               102);
define('ERR_UNKNOWN_EMAIL',                        103);
define('ERR_EMAIL_ALREADY_INUSE',                  104);

// mac address error codes
define('ERR_MAC_ADDRESS_NOT_SPECIFIED',            201);
define('ERR_UNKNOWN_MAC_ADDRESS',                  202);
define('ERR_MAC_ADDRESS_NOT_ASSOCIATED_WITH_USER', 203);
define('ERR_MAC_ADDRESS_ALREADY_INUSE',            204);
define('ERR_INSUFFICIENT_FREE_MAC_ADDRESSES',      205);

// certificate error codes
define('ERR_CERTIFICATE_NOT_SPECIFIED',            301);
define('ERR_INCORRECT_CERTIFICATE',                302);

// machine registration error codes
define('ERR_AUTH_MACHINE_LINK_EXPIRED',            401);

// something went wrong with the database
define('ERR_MYSQL_ERROR',                         1000);

// something else went wrong on the server
define('ERR_SERVER_ERROR',                        2000);

// aliases for real MySQL errors
define('MYSQL_ERRCODE_DUPLICATE_KEY',              1062);
?>
