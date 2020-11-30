<?php
/**
 * Created by PhpStorm.
 * User: Sergio
 * Date: 14/05/2018
 * Time: 23:18
 */

namespace Pwbox\Model\UseCase;

use \Pwbox\Model\User;
use \Pwbox\Model\UserRepository;


class UpdateUserUseCase
{
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

       $this->repo->updateUser();
    }

}