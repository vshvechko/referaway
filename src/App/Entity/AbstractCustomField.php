<?php

namespace App\Entity;


class AbstractCustomField extends AbstractEntity
{
    use WithAuthoincrementId;

    const TYPE_EMAIL = 'email';
    const TYPE_PHONE = 'phone';
    const TYPE_IMAGE = 'image';

    /**
     * @Column(type="string", length=32, nullable=false)
     * @var string
     */
    protected $type;
    /**
     * @Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $value;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }


}