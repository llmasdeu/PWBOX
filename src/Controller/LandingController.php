<?php

namespace Pwbox\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class LandingController{

    protected $container;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(Request $request, Response $response) {
        $action = $request->getQueryParam('action');
        $statusMessage = $request->getQueryParam('status');

        return $this->container->get('view')->render($response, 'landing.html.twig', ['action' => $action, 'statusMessage' => $statusMessage]);
    }
}
