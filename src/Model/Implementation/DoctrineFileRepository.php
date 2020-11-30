<?php

namespace Pwbox\Model\Implementation;

use \PDO;
use \DateTime;
use \Hashids\Hashids;
use \Doctrine\DBAL\Driver\Connection;
use \Pwbox\Model\File;
use \Pwbox\Model\Folder;
use \Pwbox\Model\FileRepository;

class DoctrineFileRepository implements FileRepository {
  const DATE_FORMAT = 'Y-m-d';
  private $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public function getContentByFolder($userHashId, $folderHashId) {
    $folder = $this->getFolderByIdAndOwner($userHashId, $folderHashId);
    $subfolders = array();

    if ($folder->getHashId() == '-1') {
      $folder = $this->getSharedFolderById($userHashId, $folderHashId);
    }

    if ($folder->getHashId() != '-1') {
      $subfolders = $this->getSubfoldersByParentId($userHashId, $folderHashId);
      $files = $this->getFilesByFolder($folderHashId);

      $folder->setFolders($subfolders);
      $folder->setFiles($files);
    }

    return $folder;
  }

  private function getFolderByIdAndOwner($userHashId, $folderHashId) {
    $id = -1;
    $hashId = '-1';
    $name = '';
    $root = false;

    $sql = 'SELECT f.* FROM folders f, users u WHERE u.hash_id LIKE :user_hash_id AND u.id = f.user_id AND f.hash_id LIKE :folder_hash_id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('user_hash_id', $userHashId, 'string');
    $stmt->bindValue('folder_hash_id', $folderHashId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $id = $row['user_id'];
      $hashId = $row['hash_id'];
      $name = $row['folder_name'];
      $root = $row['root_folder'];
    }

    $folder = new Folder($hashId, $name);
    $folder->setRootFolder($root);

    $sql = 'SELECT hash_id FROM users WHERE id = ' . $id;
    $stmt = $this->database->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $hashId = $row['hash_id'];
    }

    $folder->setPath($hashId);

    return $folder;
  }

  private function getSubfoldersByParentId($userHashId, $folderHashId) {
    $id = -1;
    $subfolders = array();
    $folderOwner = $this->getFolderOwnerIdByHashId($folderHashId);

    $sql = 'SELECT id FROM users WHERE hash_id LIKE :hash_id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('hash_id', $userHashId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach ($result as $row) {
      $id = $row['id'];
    }

    $folderId = $this->getFolderIdByHashId($folderHashId);
    $sql = 'SELECT f.* FROM folders f, subfolders s WHERE s.parent_folder = ' . $folderId . ' AND s.child_folder = f.id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('folder_hash_id', $folderHashId, 'string');
    //$stmt->bindValue('user_hash_id', $userHashId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $hashId = $row['hash_id'];
      $name = $row['folder_name'];

      $subfolders[] = new Folder($hashId, $name);
    }

    return $subfolders;
  }

  private function getFilesByFolder($folderHashId) {
    $sql = 'SELECT fi.* FROM files fi, folders fo WHERE fo.hash_id LIKE :hash_id AND fo.id = fi.folder_id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('hash_id', $folderHashId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    $files = array();

    foreach($result as $row) {
      $hashId = $row['hash_id'];
      $name = $row['file_name'];
      $path = $row['file_path'];
      $aux = new File($name);
      $aux->setHashId($hashId);
      $aux->setPath($path);
      $files[] = $aux;
    }

    return $files;
  }

  private function getSharedFolderById($userHashId, $folderHashId) {
    $hashId = '-1';
    $name = '';
    $admin = false;

    $sql = 'SELECT f.hash_id, f.folder_name, s.admin FROM folders f, users u, shared s WHERE f.hash_id LIKE :folder_hash_id AND f.id = s.folder_id AND s.user_id = u.id AND u.hash_id LIKE :user_hash_id';
    //$sql = 'SELECT f.*, s.* FROM folders f, users u, shared s WHERE f.hash_id LIKE :folder_hash_id AND f.id = s.folder_id AND s.user_id LIKE :user_hash_id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('folder_hash_id', $folderHashId, 'string');
    $stmt->bindValue('user_hash_id', $userHashId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $hashId = $row['hash_id'];
      $name = $row['folder_name'];
      $admin = $row['admin'];
    }

    $folder = new Folder($hashId, $name);
    $folder->setAdminRole($admin);
    $folder->setGuest(true);
    $folderPath = '';

    if ($hashId != '-1') {
      $folderPath = $this->getFolderPath($folderHashId);
    }

    $folder->setPath($folderPath);

    return $folder;
  }

  public function saveFile($userId, $folderId, $fileName, $fileSize, $fileExtension) {
    $admin = $this->checkFolderPermissions($userId, $folderId);
    $folderPath = '';
    $error = false;

    $hashids = new Hashids((new \DateTime())->format('Y-m-d H:i:s') . $userId . $folderId . $fileName);
    $filePath = $hashids->encode(1, 2, 3);

    if ($admin == true) {
      $folderPath = $this->getFolderPath($folderId);

      if (!$this->updateRemainingStorage($folderId, $fileSize)) {
        $error = true;
      } else {
        if (!$this->registerFile($fileName, $filePath, $folderId, $fileExtension)) {
          $error = true;
        } else {
        }
      }
    } else {
      $error = true;
    }

    if ($error == true) {
      return '';
    }

    return $folderPath . '/' . $filePath . '.' . $fileExtension;
  }

  private function checkFolderPermissions($userId, $folderId) {
    $idUser = $this->getUserIdByHashId($userId);
    $idFolder = $this->getFolderIdByHashId($folderId);
    $ownerId = $this->getFolderOwnerIdByHashId($folderId);

    if ($idUser == $ownerId) {
      return true;
    }

    $admin = false;

    $sql = 'SELECT admin FROM shared WHERE folder_id = ' . $idFolder . ' AND user_id = ' . $idUser;
    $stmt = $this->database->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $admin = $row['admin'];
    }

    return $admin;
  }

  private function getFolderPath($folderId) {
    $folderPath = '';

    $sql = 'SELECT u.* FROM folders f, users u WHERE f.hash_id LIKE :folder_id AND f.user_id = u.id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('folder_id', $folderId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $folderPath = $row['folder_path'];
    }

    return $folderPath;
  }

  private function updateRemainingStorage($folderId, $fileSize) {
    $id = -1;
    $remainingStorage = 0;

    $sql = 'SELECT u.* FROM folders f, users u WHERE f.hash_id LIKE :folder_id AND f.user_id = u.id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('folder_id', $folderId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $id = $row['id'];
      $remainingStorage = $row['remaining_storage'];
    }

    if ($fileSize <= $remainingStorage) {
      $remainingStorage = $remainingStorage - $fileSize;

      $sql = 'UPDATE users SET remaining_storage = ' . $remainingStorage . ' WHERE id = ' . $id;
      $stmt = $this->database->prepare($sql);
      $stmt->execute();

      return true;
    }

    return false;
  }

  private function registerFile($fileName, $filePath, $folderId, $fileExtension) {
    $id = -1;
    $flag = false;

    $sql = 'SELECT id FROM folders WHERE hash_id LIKE :folder_id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('folder_id', $folderId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $id = $row['id'];
    }

    if ($id != -1) {
      $sql = 'INSERT INTO files(hash_id, folder_id, file_name, file_path) VALUES(:hash_id, ' . $id . ', :file_name, :file_path)';
      $stmt = $this->database->prepare($sql);
      $stmt->bindValue('hash_id', $filePath, 'string');
      $stmt->bindValue('file_name', $fileName, 'string');
      $stmt->bindValue('file_path', $filePath . '.' . $fileExtension, 'string');
      $stmt->execute();
      $flag = true;
    }

    return $flag;
  }

  public function deleteFile($userId, $fileId) {
    if ($this->checkFilePermissions($userId, $fileId)) {
      $sql = 'DELETE FROM files WHERE hash_id LIKE :file_id';
      $stmt = $this->database->prepare($sql);
      $stmt->bindValue('file_id', $fileId, 'string');
      $stmt->execute();

      return true;
    } else {
      return false;
    }
  }

  public function changeFileName($userId, $fileId, $filename) {
    if ($this->checkFilePermissions($userId, $fileId)) {
      $sql = 'UPDATE files SET file_name = :file_name WHERE hash_id LIKE :file_id';
      $stmt = $this->database->prepare($sql);
      $stmt->bindValue('file_name', $filename, 'string');
      $stmt->bindValue('file_id', $fileId, 'string');
      $stmt->execute();

      return true;
    } else {
      return false;
    }
  }

  private function checkFilePermissions($userId, $fileId) {
    $id = -1;

    $sql = 'SELECT u.* FROM folders f, files fi, users u WHERE fi.hash_id LIKE :file_id AND fi.folder_id = f.id AND f.user_id = u.id AND u.hash_id LIKE :user_id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('file_id', $fileId, 'string');
    $stmt->bindValue('user_id', $userId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $id = $row['id'];
    }

    if ($id == -1) {
      $admin = false;

      $sql = 'SELECT s.admin FROM shared s, files fi, folders fo, users u WHERE fi.hash_id LIKE :file_id AND u.hash_id AND fi.folder_id = fo.id AND fo.id = s.folder_id AND u.hash_id LIKE :user_id AND u.id = s.user_id';
      $stmt = $this->database->prepare($sql);
      $stmt->bindValue('file_id', $fileId, 'string');
      $stmt->bindValue('user_id', $userId, 'string');
      $stmt->execute();
      $result = $stmt->fetchAll();

      foreach($result as $row) {
        $admin = $row['admin'];
      }

      return $admin;
    } else {
      return true;
    }
  }

  public function saveFolder($userId, $folderId, $folderName) {
    if ($this->checkFolderPermissions($userId, $folderId)) {
      $hashids = new Hashids($userId . $folderId . $folderName . (new \DateTime())->format('Y-m-d H:i:s'));
      $subfolderHashId = $hashids->encode(1, 2, 3);
      $owner = $this->getFolderOwnerIdByHashId($folderId);

      $sql = 'INSERT INTO folders(hash_id, user_id, folder_name) VALUES (:hash_id, ' . $owner . ', :folder_name)';
      $stmt = $this->database->prepare($sql);
      $stmt->bindValue('hash_id', $subfolderHashId, 'string');
      $stmt->bindValue('folder_name', $folderName, 'string');
      $stmt->execute();

      $parentId = $this->getFolderIdByHashId($folderId);
      $subfolderId = $this->getFolderIdByHashId($subfolderHashId);

      if ($parentId != -1 && $subfolderId != -1) {
        $sql = 'INSERT INTO subfolders(parent_folder, child_folder) VALUES (' . $parentId . ', ' . $subfolderId . ')';
        $stmt = $this->database->prepare($sql);
        $stmt->bindValue('hash_id', $subfolderHashId, 'string');
        $stmt->bindValue('folder_name', $folderName, 'string');
        $stmt->execute();

        return true;
      }
    }

    return false;
  }

  private function getFolderOwnerIdByHashId($folderId) {
    $id = -1;

    $sql = 'SELECT u.id FROM users u, folders f WHERE f.hash_id LIKE :folder_id AND f.user_id = u.id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('folder_id', $folderId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $id = $row['id'];
    }

    return $id;
  }

  private function getFolderIdByHashId($folderId) {
    $id = -1;

    $sql = 'SELECT id FROM folders WHERE hash_id LIKE :folder_id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('folder_id', $folderId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $id = $row['id'];
    }

    return $id;
  }

  public function deleteFolder($userId, $folderId) {
    if ($this->checkFolderPermissions($userId, $folderId)) {
      $id = $this->getFolderIdByHashId($folderId);

      $sql = 'DELETE FROM shared WHERE folder_id = ' . $id;
      $stmt = $this->database->prepare($sql);
      $stmt->execute();

      $sql = 'DELETE FROM files WHERE folder_id = ' . $id;
      $stmt = $this->database->prepare($sql);
      $stmt->execute();

      $sql = 'DELETE FROM subfolders WHERE parent_folder = ' . $id . ' OR child_folder = ' . $id;
      $stmt = $this->database->prepare($sql);
      $stmt->execute();

      $sql = 'DELETE FROM folders WHERE id = ' . $id;
      $stmt = $this->database->prepare($sql);
      $stmt->execute();

      return true;
    }

    return false;
  }

  public function renameFolder($userId, $folderId, $folderName) {
    if ($this->checkFolderPermissions($userId, $folderId)) {
      $sql = 'UPDATE folders SET folder_name = :folder_name WHERE hash_id LIKE :hash_id';
      $stmt = $this->database->prepare($sql);
      $stmt->bindValue('folder_name', $folderName, 'string');
      $stmt->bindValue('hash_id', $folderId, 'string');
      $stmt->execute();

      return true;
    }

    return false;
  }

  public function shareFolder($userId, $folderId, $email, $admin) {
    if ($admin) {
      echo "YES!!!";
    } else {
      echo "NO!!!!";
    }
    if ($this->checkFolderPermissions($userId, $folderId)) {
      $userIdShare = $this->getUserIdByEmail($email);
      $folderIdShare = $this->getFolderIdByHashId($folderId);

      if ($userIdShare != -1 && $folderIdShare != -1 && !$this->isFolderShared($userIdShare, $folderIdShare)) {
        $sql = 'INSERT INTO shared(folder_id, user_id, admin) VALUES (' . $folderIdShare . ', ' . $userIdShare . ', :admin)';
        $stmt = $this->database->prepare($sql);
        $stmt->bindValue(':admin', $admin, PDO::PARAM_BOOL);
        $stmt->execute();

        return true;
      }

      if ($this->isFolderShared($userIdShare, $folderIdShare)) {
        $sql = 'UPDATE shared SET admin = :admin WHERE folder_id = ' . $folderIdShare . ' AND user_id = ' . $userIdShare;
        $stmt = $this->database->prepare($sql);
        $stmt->bindValue(':admin', $admin, PDO::PARAM_BOOL);
        $stmt->execute();

        return true;
      }
    }

    return false;
  }

  private function getUserIdByEmail($email) {
    $id = -1;

    $sql = 'SELECT id FROM users WHERE email LIKE :email';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('email', $email, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $id = $row['id'];
    }

    return $id;
  }

  private function getUserIdByHashId($userId) {
    $id = -1;

    $sql = 'SELECT id FROM users WHERE hash_id LIKE :hash_id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('hash_id', $userId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $id = $row['id'];
    }

    return $id;
  }

  private function isFolderShared($userId, $folderId) {
    $sql = 'SELECT * FROM shared WHERE folder_id = ' . $folderId . ' AND user_id = ' . $userId;
    $stmt = $this->database->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchColumn();

    return $rows > 0;
  }

  public function getSharedFoldersWithUser($userId) {
    $sql = 'SELECT f.hash_id, f.folder_name, s.admin FROM folders f, users u, shared s WHERE u.hash_id LIKE :hash_id AND u.id = s.user_id AND s.folder_id = f.id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('hash_id', $userId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    $folder = new Folder('', 'Folders shared with me');
    $folder->setGuest(true);
    $folder->setAdminRole(false);

    $subfolders = array();

    foreach($result as $row) {
      $hashId = $row['hash_id'];
      $folderName = $row['folder_name'];
      $admin = $row['admin'];

      $aux = new Folder($hashId, $folderName);
      $aux->setAdminRole($admin);
      $aux->setGuest(true);
      $subfolders[] = $aux;
    }

    $folder->setFolders($subfolders);
    $folder->setSharedList(true);

    return $folder;
  }
}
