<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @MappedSuperclass
 * @HasLifecycleCallbacks()
 */
abstract class AbstractEntity {

    protected $notPopulatedFields = [];

    /**
     * @var integer
     *
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    protected $created;

    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    protected $updated;

    /**
     * Constructor
     */
    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
    }

    /**
     * @PreUpdate
     */
    public function setUpdatedValue() {
        $this->setUpdated(new \DateTime());
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param $created
     */
    public function setCreated($created) {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @param $updated
     */
    public function setUpdated($updated) {
        $this->updated = $updated;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated() {
        return $this->updated;
    }

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