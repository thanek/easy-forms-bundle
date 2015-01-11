<?php
namespace Xis\EasyFormsBundle\Configuration;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class FormAcceptor extends Annotation
{
    /** @var string */
    protected $starter;
    /** @var string */
    protected $rejector;

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getStarter()
    {
        return $this->starter;
    }

    /**
     * @return string
     */
    public function getRejector()
    {
        return $this->rejector;
    }
}