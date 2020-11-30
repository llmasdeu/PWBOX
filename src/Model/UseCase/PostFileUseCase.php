<?php
  namespace Pwbox\Model\UseCase;

  use \Pwbox\Model\FileRepository;

  class PostFileUseCase {
    private $repo;

    public function __construct(FileRepository $repo) {
      $this->repo = $repo;
    }

    public function __invoke($userId, $folderId, $fileName, $fileSize, $fileExtension) {
      return $this->repo->saveFile($userId, $folderId, $fileName, $fileSize, $fileExtension);
    }
  }
?>
