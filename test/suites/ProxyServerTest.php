<?php
/**
 * @license see LICENSE
 */
namespace Library\Test;

use Serps\ProxyServer\ProxyServer;

use React\EventLoop\Factory as LoopFactory;
use Clue\React\Socks\Server as SocksServer;
use React\Socket\Server as SocketServer;
use Symfony\Component\Process\Process;

class ProxyServerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Process
     */
    private static $process;

    public static function setUpBeforeClass()
    {
        self::$process = new Process('exec php ' . __DIR__ . '/../resources/server.php');
        self::$process->start();
        sleep(1);
    }


    public function testSocks4Proxy()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_PROXY, '127.0.0.1');
        curl_setopt($curl, CURLOPT_PROXYPORT, 20104);
        curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
        curl_setopt($curl, CURLOPT_URL, 'http://httpbin.org/get');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($curl);

        if (curl_error($curl)) {
            $this->fail(curl_error($curl));
        }
        $data = json_decode($data, true);

        $this->assertEquals('http://httpbin.org/get', $data['url']);
    }

    public function testSocks5Proxy()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_PROXY, '127.0.0.1');
        curl_setopt($curl, CURLOPT_PROXYPORT, 20105);
        curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($curl, CURLOPT_URL, 'http://httpbin.org/get');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($curl);

        if (curl_error($curl)) {
            $this->fail(curl_error($curl));
        }
        $data = json_decode($data, true);

        $this->assertEquals('http://httpbin.org/get', $data['url']);
    }

    public function testHttpProxy()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_PROXY, '127.0.0.1');
        curl_setopt($curl, CURLOPT_PROXYPORT, 20106);
        curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($curl, CURLOPT_URL, 'http://httpbin.org/get');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($curl);

        if (curl_error($curl)) {
            $this->fail(curl_error($curl));
        }
        $data = json_decode($data, true);

        $this->assertEquals('http://httpbin.org/get', $data['url']);
        $this->assertEquals('http', $data['headers']['X-Proxy']);
    }

    public static function tearDownAfterClass()
    {
        if (!self::$process->isRunning()) {
            var_dump(self::$process->getErrorOutput());
            var_dump(self::$process->getOutput());
        }
        self::$process->stop();
    }
}
