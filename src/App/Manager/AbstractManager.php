<?php

namespace App\Manager;


use Interop\Container\ContainerInterface;

abstract class AbstractManager {
    /**
     * @var ContainerInterface
     */
    protected $serviceLocator;

    public function __construct(ContainerInterface $c) {
        $this->serviceLocator = $c;
    }
}