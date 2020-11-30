<?php

  namespace Pwbox\Model\UseCase;

  use \Pwbox\Model\FileRepository;

  class DeleteFolderUseCase {
    private $repo;

    public function __construct(FileRepository $repo) {
      $this->repo = $repo;
    }

    public function __invoke($userId, $folderId) {
      return $this->repo->deleteFolder($userId, $folderId);
    }
  }
?>
