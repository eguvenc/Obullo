<?php

namespace Obullo\View;

use Zend\View\View;
use Obullo\View\LayoutModel;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;
use Obullo\Http\RequestAwareTrait;
use Obullo\Container\ContainerAwareTrait;

trait ModelTrait
{
    use ContainerAwareTrait;
    use RequestAwareTrait;

    /**
     * @var object
     */
    public $viewModel;

    /**
     * @var object
     */
    public $layoutModel;

    /**
     * Render view
     *
     * @param  ModelInterface $model model
     * @return string
     */
    public function render(ModelInterface $model)
    {
        $class = get_class($this);
        $class = str_replace('\\', '/', $class);
        $templateName  = substr($class, 0, -5); // Remove "Model" word from end
        
        $container = $this->getContainer();
        $renderer = $container->get(RendererInterface::class);
        $view = $container->get(View::class);

        $model->request = $this->request;
        $model->setOption('has_parent', true);

        if (false == ($model instanceof LayoutModel)) { // If developer don't want to use layout
            $this->setViewModelTemplate($templateName);
            return $view->render($model);
        }
        $this->viewModel->request = $this->request;
        $this->viewModel->setOption('has_parent', true);
        $this->setViewModelTemplate($templateName);

        $model->addChild($this->viewModel);

        return $view->render($model);
    }

    /**
     * Set view model template name
     * 
     * @param string $templateName name
     */
    private function setViewModelTemplate($templateName)
    {
        $currentTemplate = $this->viewModel->getTemplate();
        if ($currentTemplate == '') {
            $this->viewModel->setTemplate($templateName);
        }
    }
}