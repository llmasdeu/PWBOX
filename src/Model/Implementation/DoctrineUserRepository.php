<?php

namespace Pwbox\Model\Implementation;

use \Hashids\Hashids;
use \Doctrine\DBAL\Driver\Connection;
use \Pwbox\Model\User;
use \Pwbox\Model\UserRepository;

class DoctrineUserRepository implements UserRepository {
  const DATE_FORMAT = 'Y-m-d';
  private $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public function saveUser(User $user) {
    $user->generateHashId();

    $sql = 'INSERT INTO users(hash_id, username, email, birth_date, password, folder_path) VALUES(:hash_id, :username, :email, :birth_date, :password, :folder_path)';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('hash_id', $user->getHashId(), 'string');
    $stmt->bindValue('username', $user->getUsername(), 'string');
    $stmt->bindValue('email', $user->getEmail(), 'string');
    $stmt->bindValue('birth_date', $user->getBirthdate(), 'string');
    $stmt->bindValue('password', $user->getEncryptedPassword(), 'string');
    $stmt->bindValue('folder_path', $user->getHashId(), 'string');
    $stmt->execute();

    $directoryPath = __DIR__ . '/../../../public/uploads/' . $user->getHashId() . '/';

    if (!file_exists($directoryPath)) {
      mkdir($directoryPath, 0777, true);
    }

    $id = -1;

    $sql = 'SELECT id FROM users WHERE hash_id LIKE :hash_id';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('hash_id', $user->getHashId(), 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $id = $row['id'];
    }

    if ($id != -1) {
      $hashids = new Hashids($user->getUsername() . $user->getEmail() . 'root');
      $folderHashId = $hashids->encode(1, 2, 3);

      $sql = 'INSERT INTO folders(hash_id, user_id, folder_name, root_folder) VALUES(:hash_id, '. $id . ', :folder_name, TRUE)';
      $stmt = $this->database->prepare($sql);
      $stmt->bindValue('hash_id', $folderHashId, 'string');
      $stmt->bindValue('folder_name', 'root', 'string');
      $stmt->execute();
    }
  }

  public function getUserId($email, $password) {
    $hashId = '-1';
    $rootFolderId = '-1';

    $sql = 'SELECT hash_id FROM users WHERE email LIKE :email AND password LIKE :password';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('email', $email, 'string');
    $stmt->bindValue('password', $password, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $hashId = $row['hash_id'];
    }

    $sql = 'SELECT f.hash_id FROM folders f, users u WHERE u.hash_id LIKE :hash_id AND u.id = f.user_id AND f.root_folder = TRUE';
    $stmt = $this->database->prepare($sql);
    $stmt->bindValue('hash_id', $hashId, 'string');
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach($result as $row) {
      $rootFolderId = $row['hash_id'];
    }

    $data = array('user_id' => $hashId, 'root_folder' => $rootFolderId);

    return $data;
  }

  public function updateUser(){
      session_start();
      $id =$_SESSION['user_id'];
      if($id != -1){
          $email = $_POST['email'];
          $password = md5($_POST['password']);

          $sql = 'UPDATE users SET email = :email, password = :password WHERE hash_id LIKE :hash_id';
          $stmt = $this->database->prepare($sql);
          $stmt->bindValue('email', $email, 'string');
          $stmt->bindValue('password', $password, 'string');
          $stmt->bindValue('hash_id', $id, 'string');
          $stmt->execute();
      }
  }

  public function deleteUser(){
      session_start();

      $hash_id = -1;
      $hash_id = $_SESSION['user_id'];

      if($hash_id != -1){

          //Buscamos el directorio de la carpeta del usuario
          $sql = 'SELECT u.folder_path FROM users u, folders f WHERE u.hash_id LIKE :hash_id AND u.id = f.user_id';
          $stmt = $this->database->prepare($sql);
          $stmt->bindValue('hash_id', $hash_id, 'string');
          $stmt->execute();
          $result = $stmt->fetchAll();

          //Borramos las carpetas y archivos fisicos
          foreach($result as $row) {
              $folder_path = "../public/uploads/";
              $folder_path .= $row['folder_path'];

              function removeDirectory($folder_path) {
                  $files = glob($folder_path . '/*');
                  foreach ($files as $file) {
                      is_dir($file) ? removeDirectory($file) : unlink($file);
                  }
                  rmdir($folder_path);
                  return;
              }

              removeDirectory($folder_path);
          }

          //buscamos el id del usuario
          $sql = 'SELECT id FROM users WHERE hash_id LIKE :hash_id';
          $stmt = $this->database->prepare($sql);
          $stmt->bindValue('hash_id', $hash_id, 'string');
          $stmt->execute();
          $result = $stmt->fetchAll();

          foreach($result as $row) {
              $user_id = $row['id'];
          }

          //Buscamos el id de las carpetas
          $sql = 'SELECT id FROM folders WHERE user_id LIKE :user_id';

          $stmt = $this->database->prepare($sql);
          $stmt->bindValue('user_id', $user_id, 'string');
          $stmt->execute();
          $result = $stmt->fetchAll();

          foreach($result as $row) {
              $folder_id = $row['id'];
              //Borramos dependencias subcarpetas
              $sql = 'DELETE FROM subfolders WHERE parent_folder LIKE :folder_id OR child_folder LIKE :folder_id';
              $stmt = $this->database->prepare($sql);
              $stmt->bindValue('folder_id', $folder_id, 'string');
              $stmt->bindValue('folder_id', $folder_id, 'string');
              $stmt->execute();

              //Borramos dependencias files
              $sql = 'DELETE FROM files WHERE folder_id LIKE :folder_id';
              $stmt = $this->database->prepare($sql);
              $stmt->bindValue('folder_id', $folder_id, 'string');
              $stmt->execute();

              //Borramos dependencias carpetas compartidas
              $sql = 'DELETE FROM shared WHERE user_id LIKE :user_id';
              $stmt = $this->database->prepare($sql);
              $stmt->bindValue('user_id', $user_id, 'string');
              $stmt->execute();
          }

          //Borramos dependencia con la carpeta principal
          $sql = 'DELETE FROM folders WHERE user_id LIKE :user_id';
          $stmt = $this->database->prepare($sql);
          $stmt->bindValue('user_id', $user_id, 'string');
          $stmt->execute();


          //Borramos el usuario
          $sql = 'DELETE FROM users WHERE id LIKE :user_id';
          $stmt = $this->database->prepare($sql);
          $stmt->bindValue('user_id', $user_id, 'string');
          $stmt->execute();


          //Reedirigimos a la landing page y cerramos la session
          session_destroy();

      }
  }

  public function searchUser(){
      $hash_id = -1;
      $hash_id = $_SESSION['user_id'];

      if($hash_id != -1){
          $sql = 'SELECT username, birth_date FROM users WHERE hash_id LIKE :hash_id';
          $stmt = $this->database->prepare($sql);
          $stmt->bindValue('hash_id', $hash_id, 'string');
          $stmt->execute();
          $result = $stmt->fetchAll();

          foreach($result as $row) {
              $username = $row['username'];
              $birthday = $row['birth_date'];
          }

          $data = array('username' => $username, 'birthday' => $birthday);
          return $data;
      }
      return -1;
  }
}
