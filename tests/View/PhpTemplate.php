<?php

use Zend\I18n\View\Helper\Translate;
use Zend\ServiceManager\ServiceManager;
use League\Plates\Engine;
use Obullo\View\PlatesPhp;
use Obullo\View\Plates\Template;
use Obullo\View\Helper;
use League\Plates\Extension\Asset;
use Obullo\Router\{
    RequestContext,
    RouteCollection,
    Router,
    Builder
};
use Obullo\Router\Types\{
    StrType,
    IntType,
    TranslationType
};

class PhpTemplateTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        $engine = new Engine(ROOT.'/tests/var/view');
        $engine->setFileExtension('php');
        $engine->addFolder('templates', ROOT.'/tests/var/templates');
        $engine->loadExtension(new Asset('/var/assets/', true));

		$container = new ServiceManager;
        $container->setFactory('loader', 'Tests\App\Services\LoaderFactory');
        $container->setFactory('translator', 'Tests\App\Services\TranslatorFactory');

        $context = new RequestContext;
        $context->setPath('/');
        $context->setMethod('GET');
        $context->setHost('example.com');

        $collection = new RouteCollection(array(
            'types' => [
                new IntType('<int:id>'),
                new IntType('<int:page>'),
                new StrType('<str:name>'),
                new TranslationType('<locale:locale>'),
            ]
        ));
        $collection->setContext($context);
        $builder = new Builder($collection);

        $routes = $container->get('loader')
            ->load(ROOT, '/tests/var/config/routes.yaml');
        $collection = $builder->build($routes->toArray());

        $router = new Router($collection);
        $container->setService('router',$router);

        // -------------------------------------------------------------------
        // View helpers
        // -------------------------------------------------------------------
        //
        $engine->registerFunction('url', (new Helper\Url)->setRouter($router));
        $engine->registerFunction('translate', (new Translate)->setTranslator($container->get('translator')));
        $engine->registerFunction('escapeHtml', new Helper\EscapeHtml);
        $engine->registerFunction('escapeHtmlAttr', new Helper\EscapeHtmlAttr);
        $engine->registerFunction('escapeCss', new Helper\EscapeCss);
        $engine->registerFunction('escapeJs', new Helper\EscapeJs);
        $engine->registerFunction('escapeUrl', new Helper\EscapeUrl);

        $view = new PlatesPhp($engine);
        $view->setContainer($container); 

        $this->view = $view;
	}

    public function testRender()
    {
        $view = $this->view->render('test', ['var' => 'variable']);
        $this->assertEquals('Test variable:variable', $view);
    }

    public function testGetEngine()
    {
        $this->assertInstanceOf('League\Plates\Engine', $this->view->getEngine());
    }

    public function testHelperUrl()
    {
        $this->template = new Template($this->view->getEngine(), 'test');
        $urlString = $this->template->url('test', ['locale' => 'en']);
        
        $this->assertEquals($urlString, '/en/test');
    }

    public function testHelperEscapeHtml()
    {
        $this->template = new Template($this->view->getEngine(), 'test');
        $escapedHtml = $this->template->escapeHtml('<b>bold</b>');

        $this->assertEquals($escapedHtml, '&lt;b&gt;bold&lt;/b&gt;');
    }

    public function testHelperEscapeHtmlAttr()
    {
        $this->template = new Template($this->view->getEngine(), 'test');
        $escapedHtmlAttr = $this->template->escapeHtmlAttr('<test>');
        
        $this->assertEquals($escapedHtmlAttr, '&lt;test&gt;');
    }

    public function testHelperEscapeCss()
    {
$input = "
body {
    background-image: url('http://example.com/foo.jpg?</style><script>alert(1)</script>');
}
";
        $this->template = new Template($this->view->getEngine(), 'test');
        $escapedCss = $this->template->escapeCss($input);

        $this->assertEquals($escapedCss, '\A body\20 \7B \A \20 \20 \20 \20 background\2D image\3A \20 url\28 \27 http\3A \2F \2F example\2E com\2F foo\2E jpg\3F \3C \2F style\3E \3C script\3E alert\28 1\29 \3C \2F script\3E \27 \29 \3B \A \7D \A ');
    }

    public function testHelperEscapeJs()
    {
$input = <<<INPUT
bar&quot;; alert(&quot;Meow!&quot;); var xss=&quot;true
INPUT;

        $this->template = new Template($this->view->getEngine(), 'test');
        $escapedJs = $this->template->escapeJs($input);

        $this->assertEquals($escapedJs, 'bar\x26quot\x3B\x3B\x20alert\x28\x26quot\x3BMeow\x21\x26quot\x3B\x29\x3B\x20var\x20xss\x3D\x26quot\x3Btrue');
    }

    public function testHelperEscapeUrl()
    {
$input = <<<INPUT
" onmouseover="alert('test')
INPUT;

        $this->template = new Template($this->view->getEngine(), 'test');
        $escapedUrl = $this->template->escapeUrl($input);

        $this->assertEquals($escapedUrl, '%22%20onmouseover%3D%22alert%28%27test%27%29');
    }
}