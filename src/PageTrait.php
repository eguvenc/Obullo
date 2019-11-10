<?php

namespace Obullo;

use Zend\View\View;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;

trait PageTrait
{
    /**
     * @var object
     */
    public $viewModel;

    /**
     * Render view
     *
     * @param  ModelInterface $layoutModel model
     * @return string
     */
    public function render(ModelInterface $layoutModel)
    {
        $layoutModel->setTemplate('Layout/Layout.phtml'); // default layout template

        $pageModelName = substr(strrchr(get_class($this), "\\"), 1);
        $templateName = $pageModelName.'.phtml';
        $renderer = $container->get(RendererInterface::class);
        $view = $container->get(View::class);

        $this->viewModel->setTemplate($templateName);

        return $view->render($layoutModel);
    }
}
