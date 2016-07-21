<?php

namespace App\Resource;


use App\DAO\CategoryDAO;
use App\Exception\StatusException;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;
use App\Resource\ViewModel\Helper\Category as CategoryHelper;
use App\Entity\Category as CategoryEntity;

class Category extends AbstractResource {

    /**
     * @var CategoryDAO
     */
    private $service;

    public function __construct(ServerRequestInterface $request, Response $response, ContainerInterface $di)
    {
        parent::__construct($request, $response, $di);
        $this->setService(new CategoryDAO($this->getEntityManager()));
    }

    /**
     * @return CategoryDAO
     */
    protected function getService() {
        return $this->service;
    }

    /**
     * @param $service
     */
    public function setService($service) {
        $this->service = $service;
    }

    public function get($id, $subId = null) {

        if ($id) {
            $category = $this->getService()->findById($id);
            if (is_null($category)) {
                throw new StatusException('Not found', self::STATUS_NOT_FOUND);
            }
            return ['category' => (new CategoryHelper())->exportArray($category)];

        } else {
            $categories = $this->getService()->loadTree();

            /**
             * @var CategoryEntity $category
             */
            $data = [];
            foreach ($categories as $category) {
                $data[] = (new CategoryHelper())->exportArray($category, true);
            }

            return ['categories' => $data];
        }

    }

}