<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 16/05/2018
 * Time: 19:17
 */

namespace Pwbox\Controller;
use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



class UpdateUserController
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response) {
        $error = $this->validateUser();
        $statusMessage = "";

        if ($error != 0) {
            $status = 302;
            $statusMessage = "error";
        } else {
            try {
                $data = $request->getParsedBody();
                $service = $this->container->get('update_user_repository');
                $service($data);
                $status = 200;
                $statusMessage = "success";
            } catch (\Exception $e) {
                $status = 302;
                $statusMessage = "error";
            }
        }
        $protocol = $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
            || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';

        $response = $response
            ->withStatus($status)
            ->withHeader('Location', $protocol . $_SERVER['SERVER_NAME'] . '/profile?action=update_user&status=' . $statusMessage);

        return $response;
    }

    public function validateUser(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $password = '';
            $error = 0;

            foreach ($_POST as $name => $val) {
                if($name == 'email') {
                    if (!preg_match("/^\S+@\S+\.\S+$/", $val)) {
                        $error = 1;
                    }
                } elseif ($name == 'password') {
                    $password = $val;

                    if (!preg_match("/^(?=(?:.*\d){1})(?=(?:.*[A-Z]){1})\S+$/", $val) || strlen($val) < 6 || strlen($val) > 12) {
                        $error = 2;
                    }
                } elseif ($name == 'confirmPassword') {
                    if ($val != $password) {
                        $error = 3;
                    }
                }
            }
            return $error;
        }
        return -1;
    }


}
