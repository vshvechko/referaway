<?php

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @Entity
 * @Table(name="category")
 */
class Category extends AbstractEntity {
    use WithAuthoincrementId;

    protected $notPopulatedFields = ['parent', 'children'];

    /**
     * @Column(name="title", type="string", length=255, nullable=false)
     * @var string
     */
    protected $title;

    /**
     * @OneToMany(targetEntity="Category", mappedBy="parent")
     */
    protected $children;

    /**
     * @var Category|null
     * @ManyToOne(targetEntity="Category", inversedBy="children")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    public function __construct() {
        $this->children = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getChildren() {
        return $this->children->toArray();
    }

    /**
     * @param mixed $children
     * @return $this
     */
    public function setChildren($children) {
        $this->children->clear();
        foreach ($children as $child) {
            $this->children->add($child);
        }
        return $this;
    }

    /**
     * @return Category|null
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     * @return $this
     */
    public function setParent($parent) {
        $this->parent = $parent;
        return $this;
    }


}