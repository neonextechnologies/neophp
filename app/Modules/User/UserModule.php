<?php

namespace App\Modules\User;

use NeoPhp\Core\Attributes\Module;
use App\Modules\User\Controllers\UserController;
use App\Modules\User\Services\UserService;
use App\Modules\User\Repositories\UserRepository;

#[Module(
    controllers: [UserController::class],
    providers: [UserService::class, UserRepository::class]
)]
class UserModule
{
    //
}
