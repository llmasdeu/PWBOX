<?php
  namespace Pwbox\Controller;

  use \Psr\Container\ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class LogoutController {
    protected $container;

    public function __construct(ContainerInterface $container) {
      $this->container = $container;
    }

    public function __invoke(Request $request, Response $response) {
      session_start();

      if (session_status() == PHP_SESSION_ACTIVE) {
        session_destroy();
      }

      $protocol = $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                    || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
      $url = $protocol . $_SERVER['SERVER_NAME'] . '/';

      $response = $response->withHeader('Location', $url);

      return $response;
    }
  }
?>
