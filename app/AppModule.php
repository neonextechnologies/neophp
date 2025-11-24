<?php

namespace App;

use NeoPhp\Core\Attributes\Module;
use App\Modules\User\UserModule;

#[Module(
    imports: [
        UserModule::class,
    ],
    controllers: [],
    providers: []
)]
class AppModule
{
    //
}
