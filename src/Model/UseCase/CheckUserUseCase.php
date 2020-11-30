<?php
namespace Pwbox\Model\UseCase;

use \Pwbox\Model\User;
use \Pwbox\Model\UserRepository;

class CheckUserUseCase {

    /** @var UserRepository */
    private $repo;

    /**
     * PostUserUseCase constructor.
     * @param UserRepository $repo
     */
    public function __construct(UserRepository $repo) {
        $this->repo = $repo;
    }

    public function __invoke(array $rawData){
        return $this->repo->getUserId($rawData['email'], md5($rawData['password']));
    }
}
