<?php

namespace Obullo\Factory;

use Obullo\Http\ServerRequest;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use function Zend\Diactoros\{
    normalizeServer,
    normalizeUploadedFiles,
    marshalHeadersFromSapi,
    parseCookieHeader,
    marshalUriFromSapi,
    marshalMethodFromSapi,
    marshalProtocolVersionFromSapi
};
class RequestFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $server = normalizeServer($_SERVER,
            is_callable('apache_request_headers') ? 'apache_request_headers' : null
        );
        $files   = normalizeUploadedFiles($_FILES);
        $headers = marshalHeadersFromSapi($server);

        $cookies = null;
        if (null === $_COOKIE && array_key_exists('cookie', $headers)) {
            $cookies = parseCookieHeader($headers['cookie']);
        }
        return new ServerRequest(
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
}