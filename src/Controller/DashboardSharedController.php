<?php

namespace Pwbox\Controller;

use Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class DashboardSharedController {
  protected $container;

  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  public function __invoke(Request $request, Response $response, array $args) {
    $userId = '-1';
    $rootFolder = '-1';
    $folderId;

    session_start();

    if (session_status() == PHP_SESSION_ACTIVE) {
      if (isset($_SESSION['user_id']) && isset($_SESSION['root_folder'])) {
        $userId = $_SESSION['user_id'];
      }
    }

    $error = false;

    if ($userId == '-1') {
      $error = true;
    } else {
      $service = $this->container->get('check_shared_folders_repository');
      $folder = $service($userId);

      if ($folder->getName() == '') {
        $error = true;
      }
    }

    $protocol = $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                  || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';

    if ($error == true) {
      $response = $response->withStatus(302)->withHeader('Location', $protocol . $_SERVER['SERVER_NAME'] . '/?status=302');

      return $response;
    }

    $url = $protocol . $_SERVER['SERVER_NAME'];
    $action = $request->getQueryParam('action');
    $statusMessage = $request->getQueryParam('status');

    return $this->container->get('view')->render($response, 'dashboard.html.twig', ['folder' => $folder, 'url' => $url, 'action' => $action, 'statusMessage' => $statusMessage]);
  }
}
