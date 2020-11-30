<?php

namespace Pwbox\Model;

use \Hashids\Hashids;

class User {
  private $id;
  private $hashId;
  private $username;
  private $email;
  private $birthdate;
  private $password;

  public function __construct($username, $email, $birthdate, $password) {
    $this->username = $username;
    $this->email = $email;
    $this->birthdate = $birthdate;
    $this->password = $password;
  }

  /**
   * @return mixed
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id) {
    $this->id = $id;
  }

  public function getHashId() {
    return $this->hashId;
  }

  public function setHashId($hashId) {
    $this->hashId = $hashId;
  }

  /**
   * @return mixed
   */
  public function getUsername() {
    return $this->username;
  }

  /**
   * @param mixed $username
   */
  public function setUsername($username) {
    $this->username = $username;
  }

  /**
   * @return mixed
   */
  public function getEmail() {
    return $this->email;
  }

  /**
   * @param mixed $email
   */
  public function setEmail($email) {
    $this->email = $email;
  }

  public function getBirthdate() {
    return $this->birthdate;
  }

  public function setBirthdate($birthdate) {
    $this->birthdate = $birthdate;
  }

  /**
   * @return mixed
   */
  public function getPassword() {
    return $this->password;
  }

  public function getEncryptedPassword() {
    return md5($this->password);
  }

  /**
   * @param mixed $password
   */
  public function setPassword($password) {
    $this->password = $password;
  }

  public function generateHashId() {
    $hashids = new Hashids($this->username . $this->email);
    $this->hashId = $hashids->encode(1, 2, 3);
  }
}
