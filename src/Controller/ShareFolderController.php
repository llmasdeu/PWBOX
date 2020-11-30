<?php

  namespace Pwbox\Controller;

  use \Psr\Container\ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class ShareFolderController {
    protected $container;

    public function __construct(ContainerInterface $container) {
      $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args) {
      $flag = true;
      session_start();

      if (session_status() == PHP_SESSION_ACTIVE) {
        if (isset($_SESSION['user_id']) && isset($args['folder_id']) && isset($args['parent_id']) && isset($request->getParsedBody()['email']) && isset($request->getParsedBody()['admin'])) {
          $userId = $_SESSION['user_id'];
          $parentId = $args['parent_id'];
          $folderId = $args['folder_id'];
          $email = $request->getParsedBody()['email'];
          $admin = $request->getParsedBody()['admin'];

          if ($admin == 'Yes') {
            echo "YES";
            $admin = true;
          } else {
            echo "NO";
            $admin = false;
          }

          $service = $this->container->get('share_folder_repository');
          $flag = $service($userId, $folderId, $email, $admin);
        } else {
          $flag = false;
        }
      } else {
        $flag = false;
      }

      $protocol = $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
                    || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
      $url = $protocol . $_SERVER['SERVER_NAME'];

      if ($flag == true) {
        $status = 200;
        $url = $url . '/dash/' . $parentId . '?action=share_folder&status=success';
      } else {
        $status = 302;
        $url = $url . '/dash/' . $parentId . '?action=share_folder&status=error';
      }

      $response = $response
                    ->withStatus($status)
                    ->withHeader('Location', $url);

      return $response;
    }
  }
?>
