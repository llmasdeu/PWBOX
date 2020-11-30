<?php

namespace Pwbox\Controller;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class LoginController {
  protected $container;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  public function __invoke(Request $request, Response $response) {
    if (session_status() == PHP_SESSION_ACTIVE) {
      session_destroy();
    }

    $error = $this->validateUser();
    $id = 0;

    if ($error != 0) {
      $id = '-1';
    } else {
      try {
        $data = $request->getParsedBody();
        $service = $this->container->get('check_user_repository');
        $data = $service($data);
        $id = $data['user_id'];
        $rootFolder = $data['root_folder'];
        session_start();
      } catch (\Exception $e) {
        $id = '-1';
      }
    }

    $protocol = $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                  || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    $url = $protocol . $_SERVER['SERVER_NAME'];

    if ($id == '-1') {
      $status = 302;
      $url = $url . '/?action=login_user&status=error';
    } else {
      $_SESSION['user_id'] = $id;
      $_SESSION['root_folder'] = $rootFolder;

      $status = 200;
      $url = $url . '/dash/' . $rootFolder . '?action=login_user&status=success';
    }

    $response = $response
                  ->withStatus($status)
                  ->withHeader('Location', $url);

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
        }
      }

      return $error;
    }

    return -1;
  }
}
?>
