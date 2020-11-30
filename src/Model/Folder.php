<?php
  namespace Pwbox\Model;

  class Folder {
    private $hashId;
    private $name;
    private $adminRole = true;
    private $rootFolder = false;
    private $path;
    private $folders;
    private $files;
    private $guest = false;
    private $sharedList = false;

    public function __construct($hashId, $name) {
      $this->hashId = $hashId;
      $this->name = $name;
    }

    public function getHashId() {
      return $this->hashId;
    }

    public function setHashId($hashId) {
      $this->hashId = $hashId;
    }

    public function getName() {
      return $this->name;
    }

    public function setName($name) {
      $this->name = $name;
    }

    public function getAdminRole() {
      return $this->adminRole;
    }

    public function setAdminRole($adminRole) {
      $this->adminRole = $adminRole;
    }

    public function getRootFolder() {
      return $this->rootFolder;
    }

    public function setRootFolder($rootFolder) {
      $this->rootFolder = $rootFolder;
    }

    public function getPath() {
      return $this->path;
    }

    public function setPath($path) {
      $this->path = $path;
    }

    public function getFolders() {
      return $this->folders;
    }

    public function setFolders($folders) {
      $this->folders = $folders;
    }

    public function getFiles() {
      return $this->files;
    }

    public function setFiles($files) {
      $this->files = $files;
    }

    public function getGuest() {
      return $this->guest;
    }

    public function setGuest($guest) {
      $this->guest = $guest;
    }

    public function getSharedList() {
      return $this->sharedList;
    }
    
    public function setSharedList($sharedList) {
      $this->sharedList = $sharedList;
    }
  }
?>
