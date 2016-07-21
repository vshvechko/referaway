<?php

namespace App\Resource\ViewModel\Helper;
use App\Entity\Category as CategoryEntity;

class Category {

    /**
     * @param CategoryEntity|null $category
     * @param bool $loadTree
     * @return array|null
     */
    public function exportArray($category, $loadTree = false) {
        if (is_null($category)) {
            return null;
        }

        $result = [
            'id' => $category->getId(),
            'title' => $category->getTitle(),
            'parent' => $this->exportArray($category->getParent())
        ];

        if ($loadTree) {
            $result['sub'] =  array_map(
                function (CategoryEntity $sub) {
                    return $this->exportArray($sub, true);
                },
                $category->getChildren()
            );
        }

        return $result;
    }

}