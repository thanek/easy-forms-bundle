<?php
namespace Xis\EasyFormsBundle\Configuration;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class BaseFormListener
{
    /** @var ContainerInterface */
    protected $container;
    /** @var Form[] */
    protected $formAnnotations = [];
    /** @var Controller */
    protected $controller;
    /** @var string */
    protected $controllerClassName;
    /** @var string */
    protected $methodName;

    /**
     * @param ContainerInterface $container
     */
    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FilterControllerEvent $event
     */
    protected function initControllerData(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        list($this->controller, $this->methodName) = $controller;
        $this->controllerClassName = ClassUtils::getClass($this->controller);

        $this->formAnnotations = $this->getFormAnnotations();
    }

    /**
     * @return Form[]
     */
    protected function getFormAnnotations()
    {
        /** @var $annotationReader Reader */
        $annotationReader = $this->container->get('annotation_reader');
        $reflectionClass = new \ReflectionClass($this->controllerClassName);

        $formAnnotations = [];
        $allAnnotations = $annotationReader->getClassAnnotations($reflectionClass);
        foreach ($allAnnotations as $annotation) {
            if ($annotation instanceof Form) {
                $formAnnotations[$annotation->value] = $annotation;
            }
        }
        return $formAnnotations;
    }

    /**
     * @param Form $formAnnotation
     * @param $params
     *
     * @return FormInterface
     */
    protected function createForm(Form $formAnnotation, $params)
    {
        $formCreateMethod = $formAnnotation->getMethod();
        $form = call_user_func_array([$this->controller, $formCreateMethod], $params);
        return $form;
    }
}