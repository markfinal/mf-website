<?php
require_once 'api/v1/send_email.php';
require_once 'api/v1/validateuser.php';
require_once 'api/v1/registeruser.php';
require_once 'api/v1/registermacaddress.php';
require_once 'api/v1/associatemachinewithuser.php';
require_once 'api/v1/validatemachine.php';
require_once 'api/v1/stringutils.php';
require_once 'api/v1/dynamicurl.php';

// TODO: is this shutdown function needed now that there is an exception handler?
register_shutdown_function( "fatal_handler" );
function fatal_handler()
{
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if (!is_null($error))
    {
        $errno   = $error['type'];
        $errfile = $error['file'];
        $errline = $error['line'];
        $errstr  = $error['message'];

        // avoids errors like 'unknown variable: message' from the xdebug wrapper
        if (!startsWith($errfile, 'xdebug'))
        {
            $response = array();
            $response['errorcode'] = ERR_SERVER_ERROR;
            $response['errormessage'] = "Error no $errno: $errstr in $errfile at line $errline";

            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            echo json_encode($response);
        }
    }
}

// note that in PHP 7.0 this also copes with Errors, such as uncaught errors
set_exception_handler('exception_handler');
function exception_handler(Throwable $e)
{
    error_log("$e");
    header('HTTP/1.1 500 Internal Server Error', true, 500);
    header('Content-Type: application/json');
    echo json_encode(["errormessage" => 'Server error']);
    exit;
}

try
{
    switch ($_SERVER['REQUEST_URI'])
    {
        case '/api/v1/validateuser':
            validateuser();
            break;

        case '/api/v1/registeruser':
            registeruser();
            break;

        case '/api/v1/registermacaddress':
            registermacaddress();
            break;

        case '/api/v1/associatemachinewithuser':
            associatemachinewithuser();
            break;

        default:
            {
                if (!isdynamicurl($_SERVER['REQUEST_URI']))
                {
                    $response = array();
                    $response['errormessage'] = 'Unrecognized path: '.$_SERVER['REQUEST_URI'];

                    header($_SERVER['SERVER_PROTOCOL'].' 404 not found', true, 404);
                    header('Content-Type: application/json', true);
                    echo json_encode($response);
                }
            }
    }
}
catch (PDOException $e)
{
    $response = array();
    $response['errorcode'] = ERR_MYSQL_ERROR;
    $response['errormessage'] = $e->getMessage();
    error_log($e);

    header('Content-Type: application/json', true, 500);
    echo json_encode($response);
}
catch (\ParseError $e)
{
    $response = array();
    $response['errorcode'] = ERR_SERVER_ERROR;
    $response['errormessage'] = $e->getMessage();
    error_log($e);

    header('Content-Type: application/json', true, 500);
    echo json_encode($response);
}
catch (Exception $e)
{
    $response = array();
    $response['errorcode'] = ERR_SERVER_ERROR;
    $response['errormessage'] = $e->getMessage();
    error_log($e);

    header('Content-Type: application/json', true, 500);
    echo json_encode($response);
}
?>
