<?php

namespace Alexconesap\Commons\Sockets;

use Exception;

/**
 * Basic Socket Client
 *
 * Requires `ext-sockets` in your composer.json file.
 *
 * <code>
 * $socket = new ClientSocket('127.0.0.1', 15001);
 * return $socket->send(json_encode(['version' => '1', 'data' => 'some data|some more info|222']));
 * </code>
 *
 * @author Yakuma, 2020 (alexconesap@gmail.com)
 */
class ClientSocket
{
    private string $server_ip;
    private int $server_port;

    public function __construct(string $server_ip, int $server_port)
    {
        $this->server_ip = $server_ip;
        $this->server_port = $server_port;
    }

    /**
     * Performs the TCP call to a socket (IPv4) and returns the string sent back by the server.
     *
     * @param string $text_to_send The parameters to be sent
     * @return string
     * @throws Exception
     */
    public function send(string $text_to_send): string
    {
        if (! $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
            throw new Exception(
                __METHOD__ . " :: socket_create() call failed: " . socket_strerror(socket_last_error())
            );
        }

        $result = '';
        try {
            try {
                if (@socket_connect($socket, $this->server_ip, $this->server_port) >= 0) {
                    if (@socket_write($socket, $text_to_send, strlen($text_to_send)) !== false) {
                        while ($out = socket_read($socket, 128)) {
                            $result .= $out;
                        }
                    }
                }
            } catch (Exception $ex) {
                throw new Exception(__METHOD__ . ' :: ' . $ex->getMessage() . ': ' . socket_strerror(socket_last_error()));
            }
        } finally {
            socket_close($socket);
        }
        return $result;
    }

}
