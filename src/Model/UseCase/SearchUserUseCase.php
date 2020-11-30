<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 16/05/2018
 * Time: 19:45
 */
namespace Pwbox\Model\UseCase;

use \Pwbox\Model\UserRepository;

class SearchUserUseCase {
    private $repo;

    public function __construct(UserRepository $repo) {
        $this->repo = $repo;
    }

    public function __invoke() {
        return $this->repo->searchUser();
    }
}