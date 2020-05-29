<?php

namespace Obullo\View;

use ReflectionClass;
use Obullo\View\ViewModel;
use Obullo\View\LayoutModel;
use Laminas\View\Model\ModelInterface;

class PageView extends AbstractPageView
{
    /**
     * Reserved for user layout model (view model object for layout)
     *
     * @var object
     */
    public $layout;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->view = new ViewModel;
        $this->layout = new LayoutModel;
        $this->reflection = new ReflectionClass($this);
        $namespace  = $this->reflection->getNamespaceName();

        // let's load a default template for each module by default
        //
        $this->layout->setTemplate($namespace.'\Templates\DefaultLayout');
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
        $class = str_replace('\\', '/', $class);
        $templateName = substr($class, 0, -5); // remove "Model" word from end

        $model->setOption('has_parent', true);

        if (false == ($model instanceof LayoutModelInterface)) { // if developer don't want to use layout
            $this->view->setTemplate($templateName);
            return $this->getView()->render($model);
        }
        $this->view->setOption('has_parent', true);
        $this->view->setTemplate($templateName);

        $plugin = $this->getViewPhpRenderer()->getHelperPluginManager();
        $viewModel = $plugin->get('view_model');
        $viewModel->setRoot($model);

        $model->addChild($this->view);
        return $this->getView()->render($model);
    }
}
