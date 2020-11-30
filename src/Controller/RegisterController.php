<?php
namespace Pwbox\Controller;

use \DateTime;
use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class RegisterController {
  protected $container;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  public function __invoke(Request $request, Response $response) {
    $error = $this->validateUser();
    $status = 0;
    $statusMessage = "error";

    if ($error != 0) {
      $status = 302;
    } else {
      try {
        $data = $request->getParsedBody();
        $service = $this->container->get('post_user_repository');
        $service($data);
        $status = 200;
        $statusMessage = "success";
      } catch (\Exception $e) {
        $status = 302;
      }
    }

    $protocol = $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                  || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    $response = $response
                  ->withStatus($status)
                  ->withHeader('Location', $protocol . $_SERVER['SERVER_NAME'] . '/?action=register_user&status=' . $statusMessage);

    return $response;
  }

  public function validateUser(){
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $password = '';
      $error = 0;

      foreach ($_POST as $name => $val) {
        if ($name == 'username') {
          if (!preg_match("/^[A-Za-z0-9_-]+$/i", $val) || strlen($val) > 20) {
            $error = 1;
          }
        } elseif($name == 'email') {
          if (!preg_match("/^\S+@\S+\.\S+$/", $val)) {
            $error = 2;
          }
        } elseif ($name == 'birthday') {
          $year = substr($val, 0, 4);

          if($year < 1850 || $year > 2018){
            $error = 3;
          }
        } elseif ($name == 'password') {
          $password = $val;

          if (!preg_match("/^(?=(?:.*\d){1})(?=(?:.*[A-Z]){1})\S+$/", $val) || strlen($val) < 6 || strlen($val) > 12) {
            $error = 4;
         }
        } elseif ($name == 'confirmPassword') {
          if ($val != $password) {
            $error = 5;
          }
        }
      }
      return $error;
    }
    return -1;
  }
}
