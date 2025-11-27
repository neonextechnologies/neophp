<?php

namespace App\Modules\User\Controllers;

use NeoPhp\Core\Attributes\Controller;
use NeoPhp\Core\Attributes\Get;
use NeoPhp\Core\Attributes\Post;
use NeoPhp\Http\Request;
use NeoPhp\Http\Response;
use App\Modules\User\Services\UserService;

#[Controller(prefix: '/api/users')]
class UserController
{
    public function __construct(
        protected UserService $service
    ) {
    }

    #[Get('/')]
    public function index(Request $request): Response
    {
        $users = $this->service->findAll();
        
        return response()->json([
            'data' => $users
        ]);
    }

    #[Get('/{id}')]
    public function show(Request $request, string $id): Response
    {
        $user = $this->service->findById((int) $id);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json([
            'data' => $user
        ]);
    }

    #[Post('/')]
    public function create(Request $request): Response
    {
        $data = $request->all();
        
        // Simple validation
        if (empty($data['name']) || empty($data['email'])) {
            return response()->json([
                'error' => 'Name and email are required'
            ], 400);
        }
        
        $id = $this->service->create($data);
        
        return response()->json([
            'id' => $id,
            'message' => 'User created successfully'
        ], 201);
    }
}
