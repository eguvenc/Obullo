<?php

namespace Obullo\View;

use ReflectionClass;
use Obullo\View\ViewModel;
use Obullo\View\LayoutModel;
use Laminas\View\Model\ModelInterface;

class View extends AbstractView
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
     * Initialize view models
     */
    public function init()
    {
        $this->view = new ViewModel;
        $this->layout = new LayoutModel;
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
        $defaultViewTemplate = $this->view->getTemplate();
        $defaultLayoutTemplate = $this->layout->getTemplate();

        if ($defaultLayoutTemplate == '') {
            $this->setDefaultLayout();
        }
        $model->setOption('has_parent', true);

        if ($defaultViewTemplate == '') {
            $templateName = $this->getTemplateName();
            $this->view->setTemplate($templateName);
        }
        if (false == ($model instanceof LayoutModelInterface)) { // if user don't want to use layout
            return $this->getView()->render($model);
        }
        $this->view->setOption('has_parent', true);
        
        $plugin = $this->getViewPhpRenderer()->getHelperPluginManager();
        $viewModel = $plugin->get('view_model');
        $viewModel->setRoot($model);

        $model->addChild($this->view);
        return $this->getView()->render($model);
    }

    /**
     * Returns to template nname
     *
     * Template path separator must be forward slash "/" for OS compability
     *
     * @return string fully qualified template name
     */
    private function getTemplateName()
    {
        $class = get_class($this);
        $class = str_replace('\\', '/', $class);
        $templateName = substr($class, 0, -5); // remove "Model" word from the end

        // change template name for method queries
        //
        if ($this->_queryMethod) {
            $namespaceArray = explode('/', $class);
            array_pop($namespaceArray);
            $templateName = implode('/', $namespaceArray).'/'.substr($this->_queryMethod, 2);
        }
        return $templateName;
    }

    /**
     * Set default layout template
     *
     * @return void
     */
    private function setDefaultLayout() : void
    {
        $this->reflection = new ReflectionClass($this);
        $namespace = $this->reflection->getNamespaceName();

        $module = strstr($namespace, '\Pages', true);
        $module = str_replace('\\', '/', $module);

        // let's load a default template for each module by default
        //
        $this->layout->setTemplate($module.'/Pages/Templates/DefaultLayout');
    }
}
