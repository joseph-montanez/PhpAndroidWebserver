<?php
/*
Original script is from http://www.jezra.net/blog/proof_of_concept_webserver_written_in_PHP
Modified for working with PFA by Aryes
*/

error_reporting(E_ALL);
ini_set('display_errors', 'On');

set_time_limit(0);
ob_implicit_flush();
date_default_timezone_set('UTC');

require_once("Response.php");
require_once("Request.php");
require_once("Android.php");

define('SITE_PATH', dirname($_SERVER['PHP_SELF']));

interface IHandler {
    public function get ();
}

class Start implements IHandler {
    public function get() {
        $file = SITE_PATH . '/gui.html';
        
        ob_start();
        include $file;
        $output = ob_get_contents();    
        ob_end_clean();
        
        return $output;
    }
}


class Stop implements IHandler {
    public function get() {
        global $running;
        $running = false;
        return "Daemon stopping.";
    }
}

/*
function p1($args) {
    global $droid;
    $droid->vibrate(30);
    return 'Button pushed';
}

function p2($args) {
    global $droid;
    $droid->vibrate(30);
    return 'Button pushed';
}

function p3($args) {
    global $droid;
    $droid->vibrate(30);
    return 'Button pushed';
}

function r1($args) {
    global $droid;
    $droid->vibrate(30);
    return 'Button released';
}

function r2($args) {
    global $droid;
    $droid->vibrate(30);
    return 'Button released';
}

function r3($args) {
    global $droid;
    $droid->vibrate(30);
    return 'Button released';
}
*/

$droid = new Android();
$droid->notify("Starting", "Starting server...");

$bind_address = '127.0.0.1';
$port = 8000;
$server_socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("socket_create()failed.");
socket_bind($server_socket, $bind_address, $port) or die("socket_bind()failed");
socket_listen($server_socket, 5) or die("socket_listen()failed");

// Stop socket from handing when killed
socket_set_block($server_socket);
socket_set_option($server_socket, SOL_SOCKET, SO_LINGER, array('l_onoff' => 1, 'l_linger' => 0));

$droid->notify("Running", "Server is up and running ....");
$droid->view("http://localhost:$port/start", "text/html");

$running = true;
do {
    if (($socket = socket_accept($server_socket)) < 0) {
        echo "socket_accept() failed \r\n";
        break;
    }
    $request  = new Request();
    $request->setSocket($socket);
    $request->getBuffer();
    $request->parseBuffer();
    
    $response = new Response();
    $response->setRequest($request);
    if(class_exists($request->getUri())) {
        $handler_name = $request->getUri();
        $handler = new $handler_name();
        $response->setHandler($handler);
    } else {
        // TODO: 404
        echo "cannot find class " . $request->getUri();
    }
    $response->sendData();
    unset($response);
    
} while ($running);

socket_shutdown($server_socket);
socket_close($server_socket);

try {
    $droid->exit();
    die("Script exited normally\r\n");
} catch (Exception $e) {

    $droid->vibrate(30);
    echo "Exception:" . $e->getMessage();
    $droid->notify('Exception', $e->getMessage());
    $droid->exit();
    die("script exited with an exception");
}
?> 
