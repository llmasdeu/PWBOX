<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 17/05/2018
 * Time: 17:54
 */

namespace Pwbox\Controller;

namespace Pwbox\Controller;
use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class DeleteUserController {
    protected $container;

    /**
     * DeleteUserController constructor.
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response) {
        try {
            $data = $request->getParsedBody();
            $service = $this->container->get('delete_user_repository');
            $service($data);
            $status = 200;
        } catch (\Exception $e) {
            $status = 302;
        }

        $protocol = $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
            || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';

        $response = $response
            ->withStatus($status)
            ->withHeader('Location', $protocol . $_SERVER['SERVER_NAME'] . '/?status=' . $status);

        return $response;
    }

}
