<?php
namespace Xis\EasyFormsBundle\Configuration;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class FormStarterListener extends BaseFormListener
{
    /** @var FormStarter[] */
    private $formStarterAnnotations = [];

    /**
     * @param FilterControllerEvent $event
     *
     * @throws \Exception
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $this->initControllerData($event);

        $this->formStarterAnnotations = $this->getFormStarterAnnotations();
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $params = $event->getControllerResult();
        $request = $event->getRequest();
        foreach ($this->formStarterAnnotations as $formName => $formStarter) {
            if (empty($this->formAnnotations[$formName])) {
                throw new \InvalidArgumentException(
                    'No such form ' . $formName . '. Did you forget to add the @Form annotation?');
            }

            $form = $request->attributes->get('_form_starter_form_' . $formName);
            if (empty($form)) {
                $form = $this->createForm($this->formAnnotations[$formName], $params);
                $request->attributes->set('_form_starter_form_' . $formName, $form);
            }

            $params[$formName] = $form->createView();
        }

        $request->attributes->set('_form_starter_templateParams', $params);
        $event->setControllerResult($params);
    }

    /**
     * @return FormStarter[]
     */
    protected function getFormStarterAnnotations()
    {
        /** @var $annotationReader Reader */
        $annotationReader = $this->container->get('annotation_reader');
        $reflectionClass = new \ReflectionClass($this->controllerClassName);
        $reflectionMethod = $reflectionClass->getMethod($this->methodName);

        $formStarterAnnotations = [];
        $allAnnotations = $annotationReader->getMethodAnnotations($reflectionMethod);
        foreach ($allAnnotations as $annotation) {
            if ($annotation instanceof FormStarter) {
                $formStarterAnnotations[$annotation->value] = $annotation;
            }
        }
        return $formStarterAnnotations;
    }
}