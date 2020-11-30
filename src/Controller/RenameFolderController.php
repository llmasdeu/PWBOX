<?php

  namespace Pwbox\Controller;

  use Psr\Container\ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class RenameFolderController {
    protected $container;

    public function __construct(ContainerInterface $container) {
      $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args) {
      $flag = true;
      session_start();

      if (session_status() == PHP_SESSION_ACTIVE) {
        if (isset($_SESSION['user_id']) && isset($args['parent_id']) && isset($args['folder_id'])) {
          $userId = $_SESSION['user_id'];
          $parentId = $args['parent_id'];
          $folderId = $args['folder_id'];
          $folderName = $request->getParsedBody()['folder_name'];

          if ($folderName != '') {
            $service = $this->container->get('update_folder_repository');
            $flag = $service($userId, $folderId, $folderName);
          } else {
            $flag = false;
          }
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
        $url = $url . '/dash/' . $parentId . '?action=rename_folder&status=success';
      } else {
        $status = 302;
        $url = $url . '/dash/' . $parentId . '?action=rename_folder&status=error';
      }

      $response = $response
                    ->withStatus($status)
                    ->withHeader('Location', $url);

      return $response;
    }
  }
?>
