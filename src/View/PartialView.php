<?php

namespace Obullo\View;

use Obullo\View\ViewModel;
use Laminas\View\Model\ModelInterface;
use Psr\Http\Message\ResponseInterface as Response;

class PartialView extends AbstractPageView
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->view = new ViewModel;
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
        $templateName = substr($class, 0, -5); // Remove "Model" word from end

        $model->setOption('has_parent', true);
        $this->view->setTemplate($templateName);

        return $this->getView()->render($model);
    }
}
