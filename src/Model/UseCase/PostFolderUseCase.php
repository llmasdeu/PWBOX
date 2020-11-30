<?php

  namespace Pwbox\Model\UseCase;

  use \Pwbox\Model\FileRepository;

  class PostFolderUseCase {
    private $repo;

    public function __construct(FileRepository $repo) {
      $this->repo = $repo;
    }

    public function __invoke($userId, $folderId, $folderName) {
      return $this->repo->saveFolder($userId, $folderId, $folderName);
    }
  }
?>
