# Building a Real-Time Chat Application

Create a real-time chat application with WebSockets, typing indicators, and online presence.

## Project Overview

Features we'll build:
- Real-time messaging with WebSockets
- Private and group conversations
- Typing indicators
- Online/offline status
- Message read receipts
- File attachments
- Message search

## Database Schema

### Create Migrations

```bash
php neo make:migration create_conversations_table
php neo make:migration create_conversation_participants_table
php neo make:migration create_messages_table
php neo make:migration create_message_reads_table
```

### Conversations Table

```php
<?php

use NeoPhp\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema->create('conversations', function($table) {
            $table->id();
            $table->string('name')->nullable();
            $table->enum('type', ['private', 'group'])->default('private');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('last_message_at');
        });
    }
    
    public function down(): void
    {
        $this->schema->dropIfExists('conversations');
    }
};
```

### Conversation Participants Table

```php
public function up(): void
{
    $this->schema->create('conversation_participants', function($table) {
        $table->id();
        $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamp('joined_at')->useCurrent();
        $table->timestamp('last_read_at')->nullable();
        $table->boolean('is_admin')->default(false);
        $table->boolean('muted')->default(false);
        $table->timestamps();
        
        $table->unique(['conversation_id', 'user_id']);
        $table->index('user_id');
    });
}
```

### Messages Table

```php
public function up(): void
{
    $this->schema->create('messages', function($table) {
        $table->id();
        $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained();
        $table->foreignId('reply_to_id')->nullable()->constrained('messages')->onDelete('set null');
        $table->text('content')->nullable();
        $table->enum('type', ['text', 'image', 'file', 'system'])->default('text');
        $table->json('attachments')->nullable();
        $table->boolean('edited')->default(false);
        $table->timestamp('edited_at')->nullable();
        $table->softDeletes();
        $table->timestamps();
        
        $table->index(['conversation_id', 'created_at']);
        $table->index('user_id');
    });
}
```

### Message Reads Table

```php
public function up(): void
{
    $this->schema->create('message_reads', function($table) {
        $table->id();
        $table->foreignId('message_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamp('read_at')->useCurrent();
        
        $table->unique(['message_id', 'user_id']);
        $table->index(['message_id', 'read_at']);
    });
}
```

Run migrations:

```bash
php neo migrate
```

## Models

### Conversation Model

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class Conversation extends Model
{
    protected array $fillable = [
        'name', 'type', 'created_by', 'last_message_at'
    ];
    
    protected array $casts = [
        'last_message_at' => 'datetime',
    ];
    
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['joined_at', 'last_read_at', 'is_admin', 'muted'])
            ->withTimestamps();
    }
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Helpers
    public function isPrivate(): bool
    {
        return $this->type === 'private';
    }
    
    public function isGroup(): bool
    {
        return $this->type === 'group';
    }
    
    public function hasParticipant(User $user): bool
    {
        return $this->participants->contains($user);
    }
    
    public function addParticipant(User $user, bool $isAdmin = false): void
    {
        $this->participants()->attach($user->id, [
            'joined_at' => now(),
            'is_admin' => $isAdmin
        ]);
    }
    
    public function removeParticipant(User $user): void
    {
        $this->participants()->detach($user->id);
    }
    
    public function getUnreadCount(User $user): int
    {
        $lastRead = $this->participants()
            ->where('user_id', $user->id)
            ->first()
            ?->pivot
            ?->last_read_at;
        
        return $this->messages()
            ->when($lastRead, fn($q) => $q->where('created_at', '>', $lastRead))
            ->where('user_id', '!=', $user->id)
            ->count();
    }
}
```

### Message Model

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class Message extends Model
{
    use SoftDeletes;
    
    protected array $fillable = [
        'conversation_id', 'user_id', 'reply_to_id',
        'content', 'type', 'attachments', 'edited', 'edited_at'
    ];
    
    protected array $casts = [
        'attachments' => 'array',
        'edited' => 'boolean',
        'edited_at' => 'datetime',
    ];
    
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }
    
    public function reads()
    {
        return $this->hasMany(MessageRead::class);
    }
    
    public function readBy()
    {
        return $this->belongsToMany(User::class, 'message_reads')
            ->withPivot('read_at');
    }
    
    // Mark as read
    public function markAsRead(User $user): void
    {
        MessageRead::firstOrCreate([
            'message_id' => $this->id,
            'user_id' => $user->id,
        ]);
    }
    
    public function isReadBy(User $user): bool
    {
        return $this->readBy->contains($user);
    }
}
```

### User Model Extension

```php
<?php

namespace App\Models;

use NeoPhp\Database\Model;

class User extends Model
{
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['joined_at', 'last_read_at', 'is_admin', 'muted'])
            ->withTimestamps()
            ->orderBy('last_message_at', 'desc');
    }
    
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    
    public function isOnline(): bool
    {
        return Cache::has("user:online:{$this->id}");
    }
    
    public function setOnline(): void
    {
        Cache::put("user:online:{$this->id}", true, 300); // 5 minutes
    }
    
    public function setOffline(): void
    {
        Cache::forget("user:online:{$this->id}");
    }
    
    public function getOrCreatePrivateConversation(User $otherUser): Conversation
    {
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', fn($q) => $q->where('user_id', $this->id))
            ->whereHas('participants', fn($q) => $q->where('user_id', $otherUser->id))
            ->first();
        
        if (!$conversation) {
            $conversation = Conversation::create([
                'type' => 'private',
                'created_by' => $this->id
            ]);
            
            $conversation->addParticipant($this);
            $conversation->addParticipant($otherUser);
        }
        
        return $conversation;
    }
}
```

## WebSocket Server

### WebSocket Configuration

```php
// config/websocket.php
return [
    'host' => env('WEBSOCKET_HOST', '0.0.0.0'),
    'port' => env('WEBSOCKET_PORT', 6001),
    'ssl' => env('WEBSOCKET_SSL', false),
];
```

### WebSocket Server

```php
<?php

namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface
{
    protected $clients;
    protected $users = [];
    
    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }
    
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection: {$conn->resourceId}\n";
    }
    
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        
        switch ($data['type']) {
            case 'auth':
                $this->handleAuth($from, $data);
                break;
            
            case 'message':
                $this->handleMessage($from, $data);
                break;
            
            case 'typing':
                $this->handleTyping($from, $data);
                break;
            
            case 'read':
                $this->handleRead($from, $data);
                break;
        }
    }
    
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        if (isset($this->users[$conn->resourceId])) {
            $user = $this->users[$conn->resourceId];
            unset($this->users[$conn->resourceId]);
            
            $this->broadcast([
                'type' => 'user_offline',
                'user_id' => $user['id']
            ]);
        }
        
        echo "Connection closed: {$conn->resourceId}\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
    
    private function handleAuth(ConnectionInterface $conn, array $data)
    {
        $userId = $this->validateToken($data['token']);
        
        if ($userId) {
            $this->users[$conn->resourceId] = [
                'id' => $userId,
                'conn' => $conn
            ];
            
            $conn->send(json_encode([
                'type' => 'auth_success',
                'user_id' => $userId
            ]));
            
            $this->broadcast([
                'type' => 'user_online',
                'user_id' => $userId
            ], $conn);
        } else {
            $conn->close();
        }
    }
    
    private function handleMessage(ConnectionInterface $from, array $data)
    {
        $message = Message::create([
            'conversation_id' => $data['conversation_id'],
            'user_id' => $this->users[$from->resourceId]['id'],
            'content' => $data['content'],
            'type' => $data['type'] ?? 'text',
            'reply_to_id' => $data['reply_to_id'] ?? null
        ]);
        
        $conversation = $message->conversation;
        $conversation->update(['last_message_at' => now()]);
        
        $this->broadcastToConversation($conversation, [
            'type' => 'new_message',
            'message' => $this->formatMessage($message)
        ]);
    }
    
    private function handleTyping(ConnectionInterface $from, array $data)
    {
        $userId = $this->users[$from->resourceId]['id'];
        
        $this->broadcastToConversation(
            Conversation::find($data['conversation_id']),
            [
                'type' => 'typing',
                'user_id' => $userId,
                'conversation_id' => $data['conversation_id'],
                'is_typing' => $data['is_typing']
            ],
            $from
        );
    }
    
    private function handleRead(ConnectionInterface $from, array $data)
    {
        $userId = $this->users[$from->resourceId]['id'];
        $message = Message::find($data['message_id']);
        
        if ($message) {
            $message->markAsRead(User::find($userId));
            
            $this->broadcastToConversation($message->conversation, [
                'type' => 'message_read',
                'message_id' => $message->id,
                'user_id' => $userId
            ]);
        }
    }
    
    private function broadcastToConversation(Conversation $conversation, array $data, ConnectionInterface $except = null)
    {
        $participantIds = $conversation->participants->pluck('id')->toArray();
        
        foreach ($this->users as $user) {
            if (in_array($user['id'], $participantIds) && $user['conn'] !== $except) {
                $user['conn']->send(json_encode($data));
            }
        }
    }
    
    private function broadcast(array $data, ConnectionInterface $except = null)
    {
        foreach ($this->clients as $client) {
            if ($client !== $except) {
                $client->send(json_encode($data));
            }
        }
    }
    
    private function validateToken(string $token): ?int
    {
        $apiToken = ApiToken::where('token', hash('sha256', $token))->first();
        return $apiToken?->user_id;
    }
    
    private function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'user_id' => $message->user_id,
            'content' => $message->content,
            'type' => $message->type,
            'created_at' => $message->created_at->toIso8601String()
        ];
    }
}
```

### Start WebSocket Server

```php
<?php
// bin/websocket-server.php

require __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\ChatServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    6001
);

echo "WebSocket server started on port 6001\n";
$server->run();
```

## Controllers

### ConversationController

```php
<?php

namespace App\Controllers;

use App\Models\{Conversation, User};
use NeoPhp\Http\Request;

class ConversationController
{
    public function index(Request $request)
    {
        $conversations = $request->user()
            ->conversations()
            ->with(['lastMessage.user', 'participants'])
            ->get()
            ->map(function($conversation) use ($request) {
                return [
                    'id' => $conversation->id,
                    'name' => $this->getConversationName($conversation, $request->user()),
                    'type' => $conversation->type,
                    'last_message' => $conversation->lastMessage,
                    'unread_count' => $conversation->getUnreadCount($request->user()),
                    'participants' => $conversation->participants,
                    'updated_at' => $conversation->last_message_at
                ];
            });
        
        return response()->json(['data' => $conversations]);
    }
    
    public function show(Conversation $conversation, Request $request)
    {
        if (!$conversation->hasParticipant($request->user())) {
            abort(403);
        }
        
        $messages = $conversation->messages()
            ->with(['user', 'replyTo', 'readBy'])
            ->latest()
            ->paginate(50);
        
        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:private,group',
            'name' => 'required_if:type,group',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:users,id'
        ]);
        
        if ($validated['type'] === 'private' && count($validated['participant_ids']) !== 1) {
            return response()->json(['error' => 'Private chat requires exactly one participant'], 422);
        }
        
        if ($validated['type'] === 'private') {
            $otherUser = User::find($validated['participant_ids'][0]);
            $conversation = $request->user()->getOrCreatePrivateConversation($otherUser);
        } else {
            $conversation = Conversation::create([
                'name' => $validated['name'],
                'type' => 'group',
                'created_by' => $request->user()->id
            ]);
            
            $conversation->addParticipant($request->user(), true);
            
            foreach ($validated['participant_ids'] as $userId) {
                $conversation->addParticipant(User::find($userId));
            }
        }
        
        return response()->json(['data' => $conversation], 201);
    }
    
    public function addParticipant(Conversation $conversation, Request $request)
    {
        if (!$conversation->isGroup()) {
            return response()->json(['error' => 'Cannot add participants to private chat'], 422);
        }
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        
        $conversation->addParticipant(User::find($validated['user_id']));
        
        return response()->json(['message' => 'Participant added']);
    }
    
    public function removeParticipant(Conversation $conversation, User $user, Request $request)
    {
        if (!$conversation->isGroup()) {
            return response()->json(['error' => 'Cannot remove participants from private chat'], 422);
        }
        
        $conversation->removeParticipant($user);
        
        return response()->json(['message' => 'Participant removed']);
    }
    
    private function getConversationName(Conversation $conversation, User $currentUser): string
    {
        if ($conversation->isGroup()) {
            return $conversation->name;
        }
        
        $otherUser = $conversation->participants
            ->where('id', '!=', $currentUser->id)
            ->first();
        
        return $otherUser?->name ?? 'Unknown';
    }
}
```

### MessageController

```php
<?php

namespace App\Controllers;

use App\Models\{Conversation, Message};
use NeoPhp\Http\Request;
use NeoPhp\Storage\Facades\Storage;

class MessageController
{
    public function store(Conversation $conversation, Request $request)
    {
        if (!$conversation->hasParticipant($request->user())) {
            abort(403);
        }
        
        $validated = $request->validate([
            'content' => 'required_without:attachments|string',
            'type' => 'required|in:text,image,file',
            'reply_to_id' => 'nullable|exists:messages,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240' // 10MB
        ]);
        
        $attachments = [];
        
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('chat-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType()
                ];
            }
        }
        
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'] ?? null,
            'type' => $validated['type'],
            'reply_to_id' => $validated['reply_to_id'] ?? null,
            'attachments' => !empty($attachments) ? $attachments : null
        ]);
        
        $conversation->update(['last_message_at' => now()]);
        
        return response()->json(['data' => $message->load(['user', 'replyTo'])], 201);
    }
    
    public function update(Message $message, Request $request)
    {
        if ($message->user_id !== $request->user()->id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'content' => 'required|string'
        ]);
        
        $message->update([
            'content' => $validated['content'],
            'edited' => true,
            'edited_at' => now()
        ]);
        
        return response()->json(['data' => $message]);
    }
    
    public function destroy(Message $message, Request $request)
    {
        if ($message->user_id !== $request->user()->id) {
            abort(403);
        }
        
        $message->delete();
        
        return response()->json(null, 204);
    }
    
    public function markAsRead(Message $message, Request $request)
    {
        $message->markAsRead($request->user());
        
        // Update last_read_at for participant
        $conversation = $message->conversation;
        $conversation->participants()
            ->updateExistingPivot($request->user()->id, [
                'last_read_at' => now()
            ]);
        
        return response()->json(['message' => 'Marked as read']);
    }
}
```

## Frontend Implementation

### JavaScript WebSocket Client

```javascript
class ChatClient {
    constructor(token) {
        this.token = token;
        this.ws = null;
        this.handlers = {};
    }
    
    connect() {
        this.ws = new WebSocket('ws://localhost:6001');
        
        this.ws.onopen = () => {
            console.log('Connected to chat server');
            this.authenticate();
        };
        
        this.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleMessage(data);
        };
        
        this.ws.onclose = () => {
            console.log('Disconnected from chat server');
            setTimeout(() => this.connect(), 5000); // Reconnect after 5 seconds
        };
        
        this.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
    }
    
    authenticate() {
        this.send({
            type: 'auth',
            token: this.token
        });
    }
    
    sendMessage(conversationId, content, type = 'text', replyToId = null) {
        this.send({
            type: 'message',
            conversation_id: conversationId,
            content: content,
            message_type: type,
            reply_to_id: replyToId
        });
    }
    
    sendTyping(conversationId, isTyping) {
        this.send({
            type: 'typing',
            conversation_id: conversationId,
            is_typing: isTyping
        });
    }
    
    markAsRead(messageId) {
        this.send({
            type: 'read',
            message_id: messageId
        });
    }
    
    send(data) {
        if (this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        }
    }
    
    on(event, handler) {
        this.handlers[event] = handler;
    }
    
    handleMessage(data) {
        const handler = this.handlers[data.type];
        if (handler) {
            handler(data);
        }
    }
}

// Usage
const chat = new ChatClient(apiToken);

chat.on('new_message', (data) => {
    console.log('New message:', data.message);
    appendMessageToUI(data.message);
});

chat.on('typing', (data) => {
    showTypingIndicator(data.user_id, data.is_typing);
});

chat.on('message_read', (data) => {
    updateMessageReadStatus(data.message_id, data.user_id);
});

chat.on('user_online', (data) => {
    updateUserStatus(data.user_id, 'online');
});

chat.on('user_offline', (data) => {
    updateUserStatus(data.user_id, 'offline');
});

chat.connect();
```

## Routes

```php
<?php

use App\Controllers\{ConversationController, MessageController};

Route::middleware('auth')->group(function() {
    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('/conversations/{conversation}/participants', [ConversationController::class, 'addParticipant']);
    Route::delete('/conversations/{conversation}/participants/{user}', [ConversationController::class, 'removeParticipant']);
    
    // Messages
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::put('/messages/{message}', [MessageController::class, 'update']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);
});
```

## Next Steps

- Add voice/video calling
- Implement message reactions
- Add message forwarding
- Create message threads
- Add GIF support
- Implement push notifications

## Resources

- [WebSockets](../advanced/websockets.md)
- [Real-Time Events](../advanced/events.md)
- [File Uploads](../core/file-uploads.md)
