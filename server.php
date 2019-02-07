<?php
define('PORT', '8090');

require_once "classes/Chat.php";

$chat = new Chat();

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, 0, PORT);

socket_listen($socket);

$client_socket_array = [$socket];

$i = 0;

while(true) {

    $empty_arr = [];
    $new_socket_array = $client_socket_array;
    socket_select($new_socket_array, $empty_arr, $empty_arr, 0, 10);

    if(in_array($socket, $new_socket_array)) {

        echo "i: " . $i . "\n\r";
        $i++;

        echo "count new_socket_array: " . count($new_socket_array) . "\n\r";

        $new_socket = socket_accept($socket);
        $client_socket_array[] = $new_socket;
        $header = socket_read($new_socket, 1024);
        $chat->sendHeaders($header, $new_socket, "localhost/chat", PORT);

        socket_getpeername($new_socket, $client_ip);

        $connectionACK = $chat->newConnectionACK($client_ip);
//        print_r($client_ip . "\n\r");

        $chat->send($connectionACK, $client_socket_array);
    }
}

socket_close($socket);