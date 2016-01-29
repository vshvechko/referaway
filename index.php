<?php
use App\Exception\StatusException;
use App\Helper\ResponseDataFormatter;
use App\Resource\AbstractResource as Resource;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Slim\App(include __DIR__ . '/config/settings.php');
(new \App\Configurator())->loadDependencyDefaults($app->getContainer())
    ->initMiddleware($app);
$app->getContainer()->get('logger');
// Get
$app->get('/{resource}[/{id}]', function(ServerRequestInterface $request, Response $response, $resource, $id = null) {
    /**
     * @var ResponseDataFormatter $formatter
     */
    $formatter = $this->get('dataFormatter');
    try {
        $resource = Resource::load($resource, $request, $response, $this);

        return $response->withJson($formatter->getSuccess($resource->get($id)));
    } catch (StatusException $e) {
        return $response->withJson($formatter->getFailure($e->getMessage()), $e->getCode());
    }
});

// Post
$app->post('/{resource}', function(ServerRequestInterface $request, Response $response, $resource) {
    /**
     * @var ResponseDataFormatter $formatter
     */
    $formatter = $this->get('dataFormatter');
    try {
        $resource = Resource::load($resource, $request, $response, $this);

        return $response->withJson($formatter->getSuccess($resource->post()));
    } catch (StatusException $e) {
        return $response->withJson($formatter->getFailure($e->getMessage()), $e->getCode());
    }
});

// Put
$app->put('/{resource}/{id}', function(ServerRequestInterface $request, Response $response, $resource, $id = null) {
    /**
     * @var ResponseDataFormatter $formatter
     */
    $formatter = $this->get('dataFormatter');
    try {
        $resource = Resource::load($resource, $request, $response, $this);

        return $response->withJson($formatter->getSuccess($resource->put($id)));
    } catch (StatusException $e) {
        return $response->withJson($formatter->getFailure($e->getMessage()), $e->getCode());
    }
});

// Delete
$app->delete('/{resource}/{id}', function(ServerRequestInterface $request, Response $response, $resource, $id = null) {
    /**
     * @var ResponseDataFormatter $formatter
     */
    $formatter = $this->get('dataFormatter');
    try {
        $resource = Resource::load($resource, $request, $response, $this);
        $resource->delete($id);
        return $response->withJson($formatter->getSuccess());
    } catch (StatusException $e) {
        return $response->withJson($formatter->getFailure($e->getMessage()), $e->getCode());
    }
});

// Options
$app->options('/{resource}', function(ServerRequestInterface $request, Response $response, $resource) {
    /**
     * @var ResponseDataFormatter $formatter
     */
    $formatter = $this->get('dataFormatter');
    try {
        $resource = Resource::load($resource, $request, $response, $this);
        return $response->withJson($formatter->getSuccess($resource->options()));
    } catch (StatusException $e) {
        return $response->withJson($formatter->getFailure($e->getMessage()), $e->getCode());
    }
});

$app->run();