<?php

use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

use function Zend\Diactoros\normalizeServer;
use function Zend\Diactoros\normalizeUploadedFiles;
use function Zend\Diactoros\marshalHeadersFromSapi;
use function Zend\Diactoros\parseCookieHeader;
use function Zend\Diactoros\marshalUriFromSapi;
use function Zend\Diactoros\marshalMethodFromSapi;
use function Zend\Diactoros\marshalProtocolVersionFromSapi;
use Obullo\Http\ServerRequest;

class ServerRequestTest extends TestCase
{
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
        $this->assertInstanceOf('Zend\Diactoros\ServerRequest', $this->request);
    }

    public function testMethodIsOptions()
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_OPTIONS);
        $this->assertTrue($request->isOptions());
    }

    public function testMethodIsPropFind()
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_PROPFIND);
        $this->assertTrue($request->isPropFind());
    }

    public function testMethodIsGet()
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_GET);
        $this->assertTrue($request->isGet());
    }

    public function testMethodIsHead()
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_HEAD);
        $this->assertTrue($request->isHead());
    }

    public function testMethodIsPost()
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_POST);
        $this->assertTrue($request->isPost());
    }

    public function testMethodIsPut()
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_PUT);
        $this->assertTrue($request->isPut());
    }

    public function testMethodIsDelete()
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_DELETE);
        $this->assertTrue($request->isDelete());
    }

    public function testMethodIsTrace()
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_TRACE);
        $this->assertTrue($request->isTrace());
    }

    public function testMethodIsConnect()
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_CONNECT);
        $this->assertTrue($request->isConnect());
    }

    public function testMethodIsPatch()
    {
        $request = $this->request->withMethod(ServerRequest::METHOD_PATCH);
        $this->assertTrue($request->isPatch());
    }

    public function testIsXmlHttpRequest()
    {
        $request = $this->request->withHeader('x-requested-with', 'XMLHttpRequest');
        $this->assertTrue($request->isXmlHttpRequest());
    }
}
