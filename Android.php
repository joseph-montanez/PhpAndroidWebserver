<?php
class Android 
{
    public function __call($name, $args) 
    {
        //echo $name, "\n";
    }
    
    public function view($url)
    {
        `$(gconftool-2 -g /desktop/gnome/applications/browser/exec) $url`;
    }
    
    public function notify($title, $text)
    {
        // Ubuntu: sudo apt-get install libnotify-bin
        `notify-send "$title" "$text"`;
    } 
}
?>
