<?php

namespace App\Repositories\User;

interface UserRepositoryInterface
{
    public function register(array $data);
    public function login(array $credentials);
    public function logout();
}
