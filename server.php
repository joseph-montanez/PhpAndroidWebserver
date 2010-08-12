<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

set_time_limit(0);
ob_implicit_flush();
date_default_timezone_set('UTC');
require_once("Android.php");
$droid = new Android();
$running = true;
$_dir = dirname($_SERVER['PHP_SELF']) . '/';
global $running, $_dir, $droid;

function start($args) {
    global $_dir;
    $file = $_dir . 'gui.html';
    return file_get_contents($file);
}

function stop($args) {
    global $running;
    $running = false;
    return "Daemon stopping.";
}

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

echo"starting server... \r\n";
$bind_address = '127.0.0.1';
$port = 8000;
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("socket_create()failed.");
socket_bind($socket, $bind_address, $port) or die("socket_bind()failed");
socket_listen($socket, 5) or die("socket_listen()failed");

socket_set_block($socket);
socket_set_option($socket, SOL_SOCKET, SO_LINGER, array('l_onoff' => 1, 'l_linger' => 0));



$droid->notify("Running", "Server is up and running ....");
$droid->view("http://localhost:$port/start", "text/html");
do {
    if (($accept_socket_resource = socket_accept($socket)) < 0) {
        echo "socket_accept() failed \r\n";
        break;
    }
    if (FALSE === ($buffer_text = socket_read($accept_socket_resource, 2048))) {
        echo "socket_read() failed \r\n";
        break;
    }
    $request_headers = explode("\r\n", $buffer_text);
    
    // Parse the header
    $fstln = explode(" ", $request_headers[0]);
    $request = parse_url($fstln[1]);
    $query = isset($request['query']) ? $request['query'] : '';
    $path = $request['path'];
    $params = array();
    if (!empty($query)) {
        $params = parse_str($query);
    }
    $function = str_replace('/', '', $path);
    if (function_exists($function)) {
        $file_contents = $function($params);
    } else {
        $file_contents = "Error calling fn: " . $function;
    }
    echo "Path: $path\n\t$query\n";
    $file_length = strlen($file_contents);
    $today = gmdate('D, d M Y H:i:s \G\M\T');
    $header = array(
        "HTTP/1.1 200 OK",
        "Server: Browsergui daemon",
        "Date: $today",
        "Content-Type:text/html; charset=iso-8859-2;",
        "Content-Language: en-us",
        "Content-Length: $file_length"
    );
    $header = implode("\r\n", $header) . "\r\n";
    
    // Send Header
    socket_send($accept_socket_resource, $header, strlen($header), 0);
    
    $start_chunk = 0;
    while ($start_chunk <= $file_length) {
        $text_chunk = substr($file_contents, $start_chunk, 2048);
        socket_write($accept_socket_resource, $text_chunk, strlen($text_chunk));
        $start_chunk+=2048;
    }
    socket_close($accept_socket_resource);
    
} while ($running);

socket_shutdown($socket);
socket_close($socket);

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
