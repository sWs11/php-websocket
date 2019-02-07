<?php

class Chat {

    public function sendHeaders($headers_text, $new_socket, $host, $port) {
        $headers = [];
        $tmp_line = preg_split("~\r\n~", $headers_text);

        foreach ($tmp_line as $line) {
            $line = rtrim($line);
            if(preg_match("~^(\S+): (.*)$~", $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }

        $key = $headers['Sec-WebSocket-Key'];

        $SecWebSocketAccept = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $header_str = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
                "Websocket-Origin: $host\r\n" .
                "Websocket-Location: ws://$host:$port/chat/server.php\r\n" .
            "Sec-WebSocket-Accept:".$SecWebSocketAccept."\r\n\r\n"
        ;

        socket_write($new_socket, $header_str, strlen($header_str));
    }

    public function newConnectionACK($client_ip) {
        $message = "New connect: " . $client_ip . "\r\n";

        $data = [
            'message' => $message,
            'type' => 'new_connection'
        ];

        $ask = $this->seal(json_encode($data));
        return $ask;
    }

    public function seal($data) {
        $b1 = 0x81;
        $length = strlen($data);
        $header = "";

        if($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } elseif ($length > 65536) {
            $header = pack('CCNN', $b1, 127, $length);
        }

        return $header . $data;
    }

    public function send($message, $client_socket_array) {
        $message_length = strlen($message);

        foreach ($client_socket_array as $client_socket) {
            @socket_write($client_socket, $message, $message_length);
        }

        return true;
    }
}