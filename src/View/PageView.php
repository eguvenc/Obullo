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
     * Request query method
     * 
     * @var string
     */
    private $_queryMethod;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->view = new ViewModel;
        $this->layout = new LayoutModel;
        $this->reflection = new ReflectionClass($this);
        $namespace = $this->reflection->getNamespaceName();

        $module = strstr($namespace, '\Pages', true);

        // let's load a default template for each module by default
        //
        $this->layout->setTemplate($module.'\Pages\Templates\DefaultLayout');
    }

    /**
     * Set query method
     * 
     * @param string $queryMethod the method name comes with query parameters
     */
    public function setQueryMethod($queryMethod = null)
    {
        $this->_queryMethod = $queryMethod;
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

        $templateName = substr($class, 0, -5); // remove "Model" word from the end

        if ($this->_queryMethod) {  // change template name for method queries
            $namespaceArray = explode('/', $class);
            array_pop($namespaceArray);
            $templateName = implode('/', $namespaceArray).'/'.substr($this->_queryMethod, 2);
        }
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
