<?php
class Request
{
    public $header;
    public $socket;
    public $buffer;
    public $uri;
    
    public function __construct() {
    
    }
    
    public function getBuffer() {
        $this->buffer = '';
        while (FALSE !== ($buffer_text = socket_read($this->socket, 2048))) {
            $this->buffer .= $buffer_text;
            // This changes if this is a POST and not GET
            // If a POST event happens, depend on Content-Length
            if(strstr($this->buffer, "\r\n\r\n")) {
                break;
            }
        }
        
        return $this;
    }
    
    public function parseBuffer() {
        $request_headers = explode("\r\n", $this->buffer);
        // Parse the header
        $fstln = explode(" ", $request_headers[0]);
        $request = parse_url($fstln[1]);
        $query = isset($request['query']) ? $request['query'] : '';
        $path = $request['path'];
        $params = array();
        if (!empty($query)) {
            parse_str($query, $params);
            $_GET = $params;
        }
        
        $this->uri = str_replace('/', '', $path);
        
        return $this;
    }
    
    public function getUri() {
            return $this->uri;
    }
    
    public function setSocket($socket) {
        $this->socket = $socket;
    }
}
?>
