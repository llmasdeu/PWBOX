<?php

  namespace Pwbox\Model\UseCase;

  use \Pwbox\Model\FileRepository;

  class CheckSharedFoldersUseCase {
    private $repo;

    public function __construct(FileRepository $repo) {
      $this->repo = $repo;
    }

    public function __invoke($userId) {
      return $this->repo->getSharedFoldersWithUser($userId);
    }
  }
?>
