<?php
  namespace Pwbox\Model\UseCase;

  use \Pwbox\Model\FileRepository;

  class DeleteFileUseCase {
    private $repo;

    public function __construct(FileRepository $repo) {
      $this->repo = $repo;
    }

    public function __invoke($userId, $fileId) {
      return $this->repo->deleteFile($userId, $fileId);
    }
  }
?>
