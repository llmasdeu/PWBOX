<?php

namespace Pwbox\Model;

interface UserRepository{
  public function saveUser(User $user);
  public function getUserId($email, $password);
  public function updateUser();
  public function deleteUser();
  public function searchUser();
}
