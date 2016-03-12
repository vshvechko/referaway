<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @MappedSuperclass
 * @HasLifecycleCallbacks()
 */
abstract class AbstractEntity {

    public function __construct() {
    }

    protected $notPopulatedFields = [];

    /**
     * @param array $data
     * @return $this
     */
    public function populate(array $data) {
        if (is_array($data)) {
            $reflObj = new \ReflectionObject($this);
            $reflMethods = $reflObj->getMethods();

            $notPopulated = $this->getNotPopulatedFields();

            foreach ($data as $k => $v) {
                if (!in_array($k, $notPopulated)) {
                    $name = str_replace('_', '', strtolower($k));
                    foreach ($reflMethods as $reflMethod) {
                        if (strtolower('set' . $name) == strtolower($reflMethod->getName()) && $reflMethod->isPublic()) {
                            $this->{$reflMethod->getName()}($v);
                            break;
                        }
                    }
                }
            }
        }

        return $this;
    }

    protected function getNotPopulatedFields() {
        if (!is_array($this->notPopulatedFields)) {
            $this->notPopulatedFields = [];
        }
        return $this->notPopulatedFields;
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }

}