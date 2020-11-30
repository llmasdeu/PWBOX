<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 14/05/2018
 * Time: 21:25
 */

namespace Pwbox\Controller;
use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ShowUpdateUserController
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response) {
        session_start();
        if (session_status() == PHP_SESSION_ACTIVE) {
           if (isset($_SESSION['user_id'])) {
                $service = $this->container->get('search_user_repository');
                $user = $service();
                $protocol = $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                    || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';


                $url = $protocol . $_SERVER['SERVER_NAME'];
                $action = $request->getQueryParam('action');
                $statusMessage = $request->getQueryParam('status');

                return $this->container->get('view')->render($response, 'updateUser.html.twig',
                    ['username' => $user['username'], 'birthday' => $user['birthday'], 'url' => $url, 'action' => $action, 'statusMessage' => $statusMessage]);
            }
        }
    }
}
