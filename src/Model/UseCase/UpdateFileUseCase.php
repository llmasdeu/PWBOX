<?php

  namespace Pwbox\Model\UseCase;

  use \Pwbox\Model\FileRepository;

  class UpdateFileUseCase {
    private $repo;

    public function __construct(FileRepository $repo) {
      $this->repo = $repo;
    }

    public function __invoke($userId, $fileId, $filename) {
      return $this->repo->changeFileName($userId, $fileId, $filename);
    }
  }
?>
