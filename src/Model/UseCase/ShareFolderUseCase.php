<?php

  namespace Pwbox\Model\UseCase;

  use \Pwbox\Model\FileRepository;

  class ShareFolderUseCase {
    private $repo;

    public function __construct(FileRepository $repo) {
      $this->repo = $repo;
    }

    public function __invoke($userId, $folderId, $email, $admin) {
      return $this->repo->shareFolder($userId, $folderId, $email, $admin);
    }
  }
?>
