<?php

declare(strict_types=1);

namespace Obullo\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class SetLocaleMiddleware implements MiddlewareInterface
{
    protected $translator;

    /**
     * Constructor
     * 
     * @param TranslatorInterface $translator laminas translator
     * @param array               $config     laminas config
     */
    public function __construct(TranslatorInterface $translator, array $config)
    {
        $this->allowedLanguages = $config['translator']['allowed_languages'] ?? [];
        $this->translator = $translator;
    }

    /**
     * Process
     *
     * @param  ServerRequestInterface  $request request
     * @param  RequestHandlerInterface $handler request handler
     *
     * @return object|exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $locale = substr($request->getUri()->getHost(), 0, 2);

        if (in_array($locale, $this->allowedLanguages)) {
            $this->translator->setLocale($locale);
        }
        return $handler->handle($request);
    }
}
