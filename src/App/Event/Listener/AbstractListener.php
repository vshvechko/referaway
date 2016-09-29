<?php

namespace App\Event\Listener;


use Interop\Container\ContainerInterface;

class AbstractListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

}