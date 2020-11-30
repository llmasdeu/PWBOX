<?php

  namespace Pwbox\Controller;

  use \Psr\Container\ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Slim\Http\UploadedFile;
  use \Psr\Http\Message\UploadedFileInterface;

  class AddFileController {
    protected $container;

    public function __construct(ContainerInterface $container) {
      $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args) {
      $flag = true;
      $folderId = '';
      session_start();

      if (session_status() == PHP_SESSION_ACTIVE) {
        if (isset($_SESSION['user_id']) && isset($args['folder_id'])) {
          $folderId = $args['folder_id'];
          $flag = $this->manageFile($request->getUploadedFiles(), $_SESSION['user_id'], $args['folder_id']);
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
        $url = $url . '/dash/' . $folderId . '?action=add_file&status=success';
      } else {
        $status = 302;
        $url = $url . '/dash/' . $folderId . '?action=add_file&status=error';
      }

      $response = $response
                    ->withStatus($status)
                    ->withHeader('Location', $url);

      return $response;
    }

    private function manageFile($uploadedFiles, $userId, $folderId) {
      $flag = true;
      $uploadedFile = $uploadedFiles['file'];

      if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
        $flag = false;
      } else {
        $fileName = $uploadedFile->getClientFilename();
        $fileInfo = pathinfo($fileName);
        $extension = $fileInfo['extension'];

        if (!$this->isValidExtension($extension) || !$this->checkSizeLimit($uploadedFile->getSize())) {
          $flag = false;
        } else {
          $service = $this->container->get('post_file_repository');
          $folderDirectory = $service($userId, $folderId, $fileName, $uploadedFile->getSize(), $extension);
          $directory = __DIR__ . '/../../public/uploads/' . $folderDirectory;
          $uploadedFile->moveTo($directory);
        }
      }

      return $flag;
    }

    private function isValidExtension($extension) {
      return $extension == 'pdf' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'png'
              || $extension == 'gif' || $extension == 'md' || $extension == 'txt';
    }

    private function checkSizeLimit($size) {
      $limit = 2000000;
      return $size <= $limit;
    }
  }
?>
