<?php

namespace Pwbox\Model;

interface FileRepository{
  public function getContentByFolder($userHashId, $folderHashId);
  public function saveFile($userId, $folderId, $fileName, $fileSize, $fileExtension);
  public function deleteFile($userId, $fileId);
  public function changeFileName($userId, $fileId, $filename);
  public function saveFolder($userId, $folderId, $folderName);
  public function deleteFolder($userId, $folderId);
  public function renameFolder($userId, $folderId, $folderName);
  public function shareFolder($userId, $folderId, $email, $admin);
  public function getSharedFoldersWithUser($userId);
}
