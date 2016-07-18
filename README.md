Simple proxy server aimed to provide unit tests for proxies


Usage
-----


```php
use Serps\ProxyServer\ProxyServer;

$server = new ProxyServer();

// Adds a SOCKS4 proxy server listening on localhost:20104
$server->listenSocks4(20104, 'localhost');
// Adds a SOCKS5 proxy server listening on localhost:20105
$server->listenSocks5(20105, 'localhost');
// Adds a HTTP proxy server listening on localhost:20106
$server->listenHttp(20106, 'localhost');

// Starts all servers
$server->getLoop()->run();
```
