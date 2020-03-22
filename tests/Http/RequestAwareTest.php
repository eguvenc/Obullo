<?php

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

use function Laminas\Diactoros\normalizeServer;
use function Laminas\Diactoros\normalizeUploadedFiles;
use function Laminas\Diactoros\marshalHeadersFromSapi;
use function Laminas\Diactoros\parseCookieHeader;
use function Laminas\Diactoros\marshalUriFromSapi;
use function Laminas\Diactoros\marshalMethodFromSapi;
use function Laminas\Diactoros\marshalProtocolVersionFromSapi;
use Obullo\Http\ServerRequest;
use Obullo\Http\RequestAwareTrait;
use Obullo\Http\RequestAwareInterface;

class RequestAwareTest extends TestCase
{
    use RequestAwareTrait;

    public function setUp()
    {
        $server = normalizeServer(
            $_SERVER,
            is_callable('apache_request_headers') ? 'apache_request_headers' : null
        );
        $files   = normalizeUploadedFiles($_FILES);
        $headers = marshalHeadersFromSapi($server);

        $cookies = null;
        if (null === $_COOKIE && array_key_exists('cookie', $headers)) {
            $cookies = parseCookieHeader($headers['cookie']);
        }
        $this->request = new ServerRequest(
            $server,
            $files,
            marshalUriFromSapi($server, $headers),
            marshalMethodFromSapi($server),
            'php://input',
            $headers,
            $cookies ?: $_COOKIE,
            $_GET,
            $_POST,
            marshalProtocolVersionFromSapi($server)
        );
    }

    public function testRequest()
    {
        $this->setRequest($this->request);

        $this->assertInstanceOf('Laminas\Diactoros\ServerRequest', $this->getRequest());
    }
}
