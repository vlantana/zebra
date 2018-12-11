<?php

namespace Zebra;

class Client
{
    /**
     * The endpoint.
     *
     * @var resource
     */
    protected $socket;
    
    /**
     * @var string
     */
    protected $host;
    
    /**
     * @var int
     */
    protected $port = 9100;
    
    /**
     * Create an instance.
     *
     * @param string $host Optionally with port [:9100]
     */
    public function __construct(string $host)
    {
        $exploded = explode(':', $host);
        
        $this->host = $exploded[0];
        
        if (isset($exploded[1])) {
            $this->port = $exploded[1];
        }
        
        $this->connect();
    }
    
    /**
     * Destroy an instance.
     */
    public function __destruct()
    {
        $this->disconnect();
    }
    
    /**
     * Create an instance statically.
     *
     * @param string $host
     * @return Client
     */
    public static function printer(string $host)
    {
        return new static($host);
    }
    
    /**
     * Connect to printer.
     *
     * @throws CommunicationException if the connection fails.
     */
    protected function connect()
    {
        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if (!$this->socket || !@socket_connect($this->socket, $this->host, $this->port)) {
            $error = $this->getLastError();
            throw new CommunicationException($error['message'], $error['code']);
        }
    }
    
    /**
     * Close connection to printer.
     */
    protected function disconnect()
    {
        @socket_close($this->socket);
    }
    
    /**
     * Send ZPL data to printer.
     *
     * @param string $zpl
     * @throws CommunicationException if writing to the socket fails.
     */
    public function send(string $zpl)
    {
        if (false === @socket_write($this->socket, $zpl)) {
            $error = $this->getLastError();
            throw new CommunicationException($error['message'], $error['code']);
        }
    }
    
    /**
     * Get the last socket error.
     *
     * @return array
     */
    protected function getLastError()
    {
        $code = socket_last_error($this->socket);
        $message = socket_strerror($code);
        
        return compact('code', 'message');
    }
}