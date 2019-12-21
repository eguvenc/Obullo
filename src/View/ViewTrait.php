<?php

namespace Obullo\View;

use Zend\View\View;
use Obullo\View\LayoutModel;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;
use Obullo\Http\RequestAwareTrait;
use Obullo\Container\ContainerAwareTrait;

trait ViewTrait
{
    use ContainerAwareTrait;
    use RequestAwareTrait;

    /**
     * @var object
     */
    public $view;

    /**
     * @var object
     */
    public $layout;

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
        $viewClass = $container->get(View::class);

        $model->request = $this->request;
        $model->setOption('has_parent', true);

        if (false == ($model instanceof LayoutModel)) { // If developer don't want to use layout
            $this->setViewModelTemplate($templateName);
            return $viewClass->render($model);
        }
        $this->view->request = $this->request;
        $this->view->setOption('has_parent', true);
        $this->setViewModelTemplate($templateName);

        $model->addChild($this->view);

        return $viewClass->render($model);
    }

    /**
     * Set view model template name
     * 
     * @param string $templateName name
     */
    private function setViewModelTemplate($templateName)
    {
        $currentTemplate = $this->view->getTemplate();
        if ($currentTemplate == '') {
            $this->view->setTemplate($templateName);
        }
    }
}