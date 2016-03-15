<?php

namespace App\Entity;


/**
 * @Entity
 * @Table(name="contact_custom_field")
 */
class ContactCustomField extends AbstractCustomField
{
    use WithAuthoincrementId;

    /**
     * @var Contact
     * @ManyToOne(targetEntity="Contact")
     * @JoinColumn(name="contact_id", referencedColumnName="id", nullable=false)
     */
    protected $contact;

    /**
     * @return Contact
     */
    public function getContact() {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     * @return $this
     */
    public function setContact($contact) {
        $this->contact = $contact;
        return $this;
    }


}