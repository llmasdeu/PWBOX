<?php

namespace Pwbox\Controller;

use Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Pwbox\Model\UseCase\PostUserUseCase;

class PostUserController{
 protected $container;

    public function __contruct(ContainerInterface $container){
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response)
    {
        try{
            $data = $request->getParsedBody();
            /** @var PostUserUseCase $service */
            $service = $this->container->get('post_user_service');
            $service($data);
        }catch (\Exception $e){
            $response = $response
                -> withStatus(500)
                -> withHeader('Content-Type', 'text/html')
                -> write('Something was wrong!');
        }
        return $response;
    }
}



