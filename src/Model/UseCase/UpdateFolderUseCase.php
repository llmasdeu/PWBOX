<?php

  namespace Pwbox\Model\UseCase;

  use \Pwbox\Model\FileRepository;

  class UpdateFolderUseCase {
    private $repo;

    public function __construct(FileRepository $repo) {
      $this->repo = $repo;
    }

    public function __invoke($userId, $folderId, $folderName) {
      return $this->repo->renameFolder($userId, $folderId, $folderName);
    }
  }
?>
