<?php

$container = $app->getContainer();

$container['view'] = function($container){
    $view = new \Slim\Views\Twig(__DIR__ . '/../src/view/templates', []);
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($container['router'], $basePath));
    return $view;
};

$container['doctrine'] = function($container){
    $config = new \Doctrine\DBAL\Configuration();
    $conn = \Doctrine\DBAL\DriverManager::getConnection(
        $container->get('settings')['database'],
        $config
    );
    return $conn;
};

$container['user_repository'] = function ($container){
    $repository = new \Pwbox\Model\Implementation\DoctrineUserRepository(
        $container->get('doctrine')
    );
    return $repository;
};

$container['post_user_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\PostUserUseCase(
        $container->get('user_repository')
    );
    return $service;
};

$container['check_user_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\CheckUserUseCase(
        $container->get('user_repository')
    );
    return $service;
};

$container['file_repository'] = function ($container){
    $repository = new \Pwbox\Model\Implementation\DoctrineFileRepository(
        $container->get('doctrine')
    );
    return $repository;
};

$container['post_file_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\PostFileUseCase(
        $container->get('file_repository')
    );
    return $service;
};

$container['delete_file_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\DeleteFileUseCase(
        $container->get('file_repository')
    );
    return $service;
};

$container['update_file_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\UpdateFileUseCase(
        $container->get('file_repository')
    );
    return $service;
};

$container['check_folder_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\CheckFolderUseCase(
        $container->get('file_repository')
    );
    return $service;
};

$container['post_folder_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\PostFolderUseCase(
        $container->get('file_repository')
    );
    return $service;
};

$container['delete_folder_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\DeleteFolderUseCase(
        $container->get('file_repository')
    );
    return $service;
};

$container['update_folder_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\UpdateFolderUseCase(
        $container->get('file_repository')
    );
    return $service;
};

$container['share_folder_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\ShareFolderUseCase(
        $container->get('file_repository')
    );
    return $service;
};

$container['check_shared_folders_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\CheckSharedFoldersUseCase(
        $container->get('file_repository')
    );
    return $service;
};

$container['update_user_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\UpdateUserUseCase(
        $container->get('user_repository')
    );
    return $service;
};

$container['search_user_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\SearchUserUseCase(
        $container->get('user_repository')
    );
    return $service;
};

$container['delete_user_repository'] = function ($container){
    $service = new \Pwbox\Model\UseCase\DeleteUserUseCase(
        $container->get('user_repository')
    );
    return $service;
};
