<?php

namespace Obullo;

use Zend\View\View;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;
use Obullo\Container\ContainerAwareTrait;

trait PageTrait
{
    use ContainerAwareTrait;

    /**
     * @var object
     */
    public $request;

    /**
     * @var object
     */
    public $viewModel;

    /**
     * Get request
     * 
     * @return object 
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Render view
     *
     * @param  ModelInterface $model model
     * @return string
     */
    public function render(ModelInterface $model)
    {
        $class = get_class($this);
        $names = explode('\\', $class);
        array_shift($names);
        $pageModelName = implode('//', $names);
        $templateName  = substr($pageModelName, 0, -5); // Remove "Model" word from end
        $container = $this->getContainer();
        $renderer = $container->get(RendererInterface::class);
        $view = $container->get(View::class);

        $model->request = $this->getRequest();

        $this->viewModel->request = $this->getRequest();
        $this->viewModel->setTemplate($templateName);
        $this->viewModel->setOption('has_parent', true);

        if ($model->getTemplate() == 'Default.phtml') {  // If developer don't want to use layout
            return $view->render($model);                // render current view model
        }
        $model->setOption('has_parent', true);
        $model->addChild($this->viewModel);

        return $view->render($model);
    }
}
