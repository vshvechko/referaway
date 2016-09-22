<?php

namespace App\Entity;


/**
 * @Entity
 * @Table(name="device")
 */
class Device extends AbstractEntity
{
    use WithAuthoincrementId;
    use WithCreateUpdateDates;

    const TYPE_APNS = 1;
    const TYPE_GCM = 2;

    protected $notPopulatedFields = ['user'];

    public function __construct()
    {
        $this->setCreated(new \DateTime('now'));
        $this->setUpdatedValue();
    }

    /**
     * @Column(type="integer", length=1, nullable=false)
     * @var int
     */
    protected $type;

    /**
     * @Column(name="token", type="text", nullable=false)
     * @var string
     */
    protected $token;

    /**
     * Associated user
     *
     * @OneToOne(targetEntity="User", inversedBy="device")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        $user->setDevice($this);
        return $this;
    }


}