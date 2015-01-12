<?php
namespace Xis\EasyFormsBundle\Configuration;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FormAcceptorListener extends BaseFormListener
{
    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $this->initControllerData($event);

        $formAcceptor = $this->getFormAcceptorAnnotation();
        if (!empty($formAcceptor)) {
            $formName = $formAcceptor->getValue();
            if (empty($this->formAnnotations[$formName])) {
                throw new \InvalidArgumentException(
                    'No such form ' . $formName . '. Did you forget to add the @Form annotation?');
            }
            $response = $this->executeStarter($formAcceptor, $event);
            if ($response->getStatusCode() == '200') {
                $this->processForm($formAcceptor, $event);
            }
        }
    }

    /**
     * @return FormAcceptor
     */
    protected function getFormAcceptorAnnotation()
    {
        /** @var $annotationReader Reader */
        $annotationReader = $this->container->get('annotation_reader');
        $reflectionClass = new \ReflectionClass($this->controller);
        $reflectionMethod = $reflectionClass->getMethod($this->methodName);

        $allAnnotations = $annotationReader->getMethodAnnotations($reflectionMethod);
        foreach ($allAnnotations as $annotation) {
            if ($annotation instanceof FormAcceptor) {
                return $annotation;
            }
        }
        return null;
    }

    /**
     * @param FormAcceptor $form
     * @param FilterControllerEvent $event
     *
     * @return Response
     */
    private function executeStarter(FormAcceptor $form, FilterControllerEvent $event)
    {
        $starterActionName = $this->getStarterActionName($form);

        $this->request->attributes->set('_controller', $starterActionName);

        $kernel = $event->getKernel();
        $response = $kernel->handle($this->request, HttpKernelInterface::SUB_REQUEST);

        return $response;
    }

    /**
     * @param FormAcceptor $formAcceptor
     * @param FilterControllerEvent $event
     */
    protected function processForm(FormAcceptor $formAcceptor, FilterControllerEvent $event)
    {
        $formName = $formAcceptor->getValue();
        $templateParams = $this->request->attributes->get('_form_starter_templateParams', []);

        /** @var FormInterface $form */
        $form = $this->request->attributes->get('_form_starter_form_' . $formName);
        if (!$form) {
            throw new \LogicException('Form ' . $formName . ' not found');
        }

        $form->handleRequest($this->request);
        if (!$form->isValid()) {
            $rejector = $formAcceptor->getRejector();
            if (!empty($rejector)) {
                call_user_func_array([$this->controller, $rejector], $templateParams);
            }

            $templateParams[$formName] = $form->createView();
            $event->setController(function () use ($templateParams) {
                return $templateParams;
            });
        } else {
            foreach ($templateParams as $k => $v) {
                $this->request->attributes->set($k, $v);
            }
        }
    }

    /**
     * @param FormAcceptor $form
     * @return string
     */
    private function getStarterActionName(FormAcceptor $form)
    {
        $starterMethod = $form->getStarter();
        if (empty($starterMethod)) {
            throw new \InvalidArgumentException('You should add "starter" property in @FormAcceptor annotation.');
        }

        if ($this->controllerAsService) {
            $starterActionName = $this->controllerName . ':' . $starterMethod;
            return $starterActionName;
        } else {
            $starterActionName = $this->controllerName . '::' . $starterMethod;
            return $starterActionName;
        }
    }
}