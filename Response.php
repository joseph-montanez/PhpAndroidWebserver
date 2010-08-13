<?php

class Response
{
    public $buffer;
    public $content = '';
    public $handler;
    public $request;
    
    public function __destruct() {
        socket_close($this->request->socket);
    }
    
    public function setHandler($handler) {
        $this->handler = $handler;
    }
    
    public function setRequest($request) {
        $this->request = $request;
    }
    
    public function generateHeader() {
        $content_length = strlen($this->content); // Needs to be multibyte (unicode friendly)!
        $today = gmdate('D, d M Y H:i:s \G\M\T');
        $header = array(
            "HTTP/1.1 200 OK",
            "Server: Browsergui daemon",
            "Date: $today",
            "Content-Type: text/html; charset=utf-8;",
            "Content-Language: en-us",
            "Content-Length: $content_length"
        );
        $header = implode("\n", $header) . "\n\n";
        
        return $header;
    }
    
    public function sendData() {
        if($this->handler === null) {
            return $this;
        }
        $this->content = $this->handler->get();
        $content_length = mb_strlen($this->content);
        $header = $this->generateHeader();
        // Send Header
        socket_send($this->request->socket, $header, mb_strlen($header), 0);
        
        // Send Body
        $start_chunk = 0;
        $chunk_length = 2048;
        while ($start_chunk <= $content_length) {
            $text_chunk = mb_substr($this->content, $start_chunk, $chunk_length, 'UTF-8');
            echo $text_chunk;
            socket_write($this->request->socket, $text_chunk, mb_strlen($text_chunk));
            $start_chunk += $chunk_length;
        }
        
        return $this;
    }
}
