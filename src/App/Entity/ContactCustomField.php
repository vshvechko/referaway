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
}