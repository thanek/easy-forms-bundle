<?php
namespace Xis\EasyFormsBundle\Configuration;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Form extends Annotation
{
    /** @var string */
    protected $method;

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}