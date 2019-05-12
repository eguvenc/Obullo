<?php

use Obullo\Http\ServerRequest;
use Obullo\Router\{
    RouteCollection,
    RequestContext,
    Builder,
    Router
};
use Obullo\Router\Types\{
    StrType,
    IntType,
    TranslationType
};
use Obullo\Pages\Plugin\{
	Asset,
	EscapeHtml,
	EscapeHtmlAttr,
	EscapeCss,
	EscapeJs,
	EscapeUrl,
	Url
};
class PluginTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {   
        $types = [
            new IntType('<int:id>'),
            new IntType('<int:page>'),
            new StrType('<str:name>'),
            new TranslationType('<locale:locale>'),
        ];
        $context = new RequestContext;
        $context->fromRequest(new ServerRequest);
         
        $collection = new RouteCollection(['types' => $types]);
        $collection->setContext($context);

        $routes  = [
            'test' => [
                'path'   => '/<locale:locale>/test',
                'handler'=> 'handler',
            ],
        ];
        $builder = new Builder($collection);
        $collection = $builder->build($routes);
        $this->router = new Router($collection);
    }

    public function testUrl()
    {
        $url = new Url;
        $url->setRouter($this->router);
        $urlString = $url('test', ['locale' => 'en']);
        
        $this->assertEquals($urlString, '/en/test');
    }

    public function testAsset()
    {
        $asset = new Asset(ROOT.'/public/', false);
        $src = $asset('/css/test.css');

        $this->assertContains('/css/test.css?v=', $src);
    }

    public function testEscapeCss()
    {
        $escapeCss = new EscapeCss;
        $input = "
body {
    background-image: url('http://example.com/foo.jpg?</style><script>alert(1)</script>');
}
";
        $escapedCss = $escapeCss($input);
        $this->assertEquals($escapedCss, '\A body\20 \7B \A \20 \20 \20 \20 background\2D image\3A \20 url\28 \27 http\3A \2F \2F example\2E com\2F foo\2E jpg\3F \3C \2F style\3E \3C script\3E alert\28 1\29 \3C \2F script\3E \27 \29 \3B \A \7D \A ');
    }

    public function testEscapeHtml()
    {
        $escapeHtml = new EscapeHtml;
        $input = '<b>bold</b>';
        $escapedHtml = $escapeHtml($input);

        $this->assertEquals($escapedHtml, '&lt;b&gt;bold&lt;/b&gt;');
    }

    public function testEscapeHtmlAttr()
    {
        $escapeHtmlAttr  = new EscapeHtmlAttr;
        $escapedHtmlAttr = $escapeHtmlAttr('<test>');
        
        $this->assertEquals($escapedHtmlAttr, '&lt;test&gt;');
    }

    public function testEscapeJs()
    {
  		$escapeJs = new EscapeJs;

$input = <<<INPUT
bar&quot;; alert(&quot;Meow!&quot;); var xss=&quot;true
INPUT;
        $escapedJs = $escapeJs($input);
        $this->assertEquals($escapedJs, 'bar\x26quot\x3B\x3B\x20alert\x28\x26quot\x3BMeow\x21\x26quot\x3B\x29\x3B\x20var\x20xss\x3D\x26quot\x3Btrue');
    }

    public function testEscapeUrl()
    {
    	$escapeUrl = new EscapeUrl;
$input = <<<INPUT
" onmouseover="alert('test')
INPUT;
        $escapedUrl = $escapeUrl($input);
        $this->assertEquals($escapedUrl, '%22%20onmouseover%3D%22alert%28%27test%27%29');
    }

}
