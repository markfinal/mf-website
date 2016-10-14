<?php

define('ERR_NONE',                                 0);

// email related error codes
define('ERR_EMAIL_NOT_SPECIFIED',                  101);
define('ERR_EMAIL_INCORRECT_FORMAT',               102);
define('ERR_EMAIL_UNREGISTERED',                   103);
define('ERR_EMAIL_ALREADY_INUSE',                  104);

// mac address error codes
define('ERR_MAC_ADDRESS_NOT_SPECIFIED',            201);
define('ERR_MAC_ADDRESS_NOT_REGISTERED',           202);
define('ERR_MAC_ADDRESS_NOT_ASSOCIATED_WITH_USER', 203);
define('ERR_MAC_ADDRESS_ALREADY_INUSE',            204);
define('ERR_INSUFFICIENT_FREE_MAC_ADDRESSES',      205);
define('ERR_MAC_ADDRESS_INCORRECT_FORMAT',         206);
define('ERR_MAC_ADDRESS_SIG_NOT_SPECIFIED',        207);
define('ERR_MAC_ADDRESS_NOT_VERIFIED',             208);
define('ERR_MAC_ADDRESS_NOT_ASSOCIATED_WITH_USER_BUT_SENT', 209);

// certificate error codes
define('ERR_CERTIFICATE_NOT_SPECIFIED',            301);
define('ERR_INCORRECT_CERTIFICATE',                302);

// machine registration error codes
define('ERR_AUTH_MACHINE_LINK_EXPIRED',            401);

// license request error codes
define('ERR_LICENSE_REQUEST_DATA_NOT_SPECIFIED',   501);
define('ERR_LICENSE_REQUEST_SIG_NOT_SPECIFIED',    502);
define('ERR_LICENSE_ACCESS_TOKEN_INVALID',         503);
define('ERR_LICENSE_REQUEST_DATA_NOT_VERIFIED',    504);
define('ERR_LICENSE_TYPE_UNKNOWN',                 505);
define('ERR_LICENSE_EXPIRED',                      506);
define('ERR_LICENSE_PRODUCT_UNKNOWN',              507);

// license request error codes
define('ERR_LICENSE_RETURN_DATA_NOT_SPECIFIED',    601);
define('ERR_LICENSE_RETURN_SIG_NOT_SPECIFIED',     602);
define('ERR_LICENSE_RETURN_SESSION_TOKEN_INVALID', 603);
define('ERR_LICENSE_RETURN_DATA_NOT_VERIFIED',     604);

// application update error codes
define('ERR_PRODUCTUPDATE_DATA_NOT_SPECIFIED',     701);
define('ERR_PRODUCTUPDATE_SIG_NOT_SPECIFIED',      702);
define('ERR_PRODUCTUPDATE_SESSION_TOKEN_INVALID',  703);
define('ERR_PRODUCTUPDATE_DATA_NOT_VERIFIED',      704);
define('ERR_PRODUCTUPDATE_ALREADY_UP_TO_DATE',     705);

// something went wrong with the database
define('ERR_MYSQL_ERROR',                         1000);

// something else went wrong on the server
define('ERR_SERVER_ERROR',                        2000);

// aliases for real MySQL errors
define('MYSQL_ERRCODE_DUPLICATE_KEY',             1062);
?>
