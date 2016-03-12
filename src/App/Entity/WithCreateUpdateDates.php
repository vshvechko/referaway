<?php

namespace App\Entity;


trait WithCreateUpdateDates {
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

    public function setUpdatedValue() {
        $this->setUpdated(new \DateTime());
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
}