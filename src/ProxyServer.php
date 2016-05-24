<?php
/**
 * @license see LICENSE
 */

namespace Serps\ProxyServer;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory as LoopFactory;
use Clue\React\Socks\Server as SocksServer;
use React\Http\Request;
use React\Http\Response;
use React\Socket\Server as SocketServer;
use React\Http\Server as HttpServer;

class ProxyServer
{

    /**
     * @var LoopInterface
     */
    protected $loop;

    public function __construct()
    {
        $this->loop = LoopFactory::create();
    }

    /**
     * @param $port
     * @param string $host
     * @return SocksServer
     * @throws \React\Socket\ConnectionException
     */
    public function listenSocks4($port, $host = '127.0.0.1')
    {
        $socket = new SocketServer($this->loop);
        $socket->listen($port, $host);
        $server = new SocksServer($this->loop, $socket);
        $server->setProtocolVersion(4);
        return $server;
    }

    /**
     * @param $port
     * @param string $host
     * @return SocksServer
     * @throws \React\Socket\ConnectionException
     */
    public function listenSocks5($port, $host = '127.0.0.1')
    {
        $socket = new SocketServer($this->loop);
        $socket->listen($port, $host);
        $server = new SocksServer($this->loop, $socket);
        $server->setProtocolVersion(5);
        return $server;
    }

    /**
     * http proxy implementation is fast and very basic (only supports GET method, bad header support, etc...)
     */
    public function listenHttp($port, $host = '127.0.0.1')
    {
        $socket = new SocketServer($this->loop);

        $http = new HttpServer($socket);
        $http->on('request', function (Request $request, Response $response) {
            $curl = curl_init();

            $baseHeaders = $request->getHeaders();
            $headers = [];
            foreach ($baseHeaders as $header => $headerValue) {
                $headers[] = "$header: $headerValue";
            }

            $headers[] = 'X-Proxy: http';
            
            curl_setopt($curl, CURLOPT_URL, $request->getUri()->__toString());
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec($curl);

            list($headers, $body) = explode("\r\n\r\n", $data, 2);

            $response->writeHead(200);
            $response->end($body);
        });

        $socket->listen($port, $host);
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }
}
