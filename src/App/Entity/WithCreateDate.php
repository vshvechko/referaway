<?php

namespace App\Entity;


trait WithCreateDate {
    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    protected $created;

    /**
     * @param $created
     * @return $this
     */
    public function setCreated($created) {
        $this->created = $created;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }
}