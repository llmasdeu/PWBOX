<?php

$app->get('/', '\Pwbox\Controller\LandingController')->add('\Pwbox\Controller\Middleware\SessionMiddleware');
$app->post('/registeruser', '\Pwbox\Controller\RegisterController');
$app->post('/loginuser', '\Pwbox\Controller\LoginController');
$app->get('/profile', '\Pwbox\Controller\ShowUpdateUserController');
$app->post('/updateuser', '\Pwbox\Controller\UpdateUserController');
$app->post('/deleteuser', '\Pwbox\Controller\DeleteUserController');
$app->post('/refresh', '\Pwbox\Controller\LoginController');
$app->get('/dash/shared', '\Pwbox\Controller\DashboardSharedController');
$app->get('/dash/{folder_id}', '\Pwbox\Controller\DashboardController');
$app->post('/addfile/{folder_id}', '\Pwbox\Controller\AddFileController');
$app->post('/addfolder/{folder_id}', '\Pwbox\Controller\AddFolderController');
$app->get('/removefile/{folder_id}/{file_id}', '\Pwbox\Controller\RemoveFileController');
$app->get('/removefolder/{parent_id}/{folder_id}', '\Pwbox\Controller\RemoveFolderController');
$app->post('/renamefile/{folder_id}/{file_id}', '\Pwbox\Controller\RenameFileController');
$app->post('/renamefolder/{parent_id}/{folder_id}', '\Pwbox\Controller\RenameFolderController');
$app->post('/sharefolder/{parent_id}/{folder_id}', '\Pwbox\Controller\ShareFolderController');
$app->get('/logout', '\Pwbox\Controller\LogoutController');
