<?php
namespace Pwbox\Model\UseCase;

use \DateTime;
use \Pwbox\Model\User;
use \Pwbox\Model\UserRepository;


class PostUserUseCase {

    /** @var UserRepository */
    private $repo;

    /**
     * PostUserUseCase constructor.
     * @param UserRepository $repo
     */
    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    public function __invoke(array $rawData){
        $user = new User(
            $rawData['username'],
            $rawData['email'],
            $rawData['birthday'],
            $rawData['password']
        );

        $this->repo->saveUser($user);
    }
}
