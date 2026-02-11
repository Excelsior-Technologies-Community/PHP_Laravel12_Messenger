# PHP_LARAVEL12_MESSENGER

```php
- Laravel 12 Based One-to-One Messaging Web Application Built using Clean MVC Architecture
- Laravel 12 based Messenger Web Application that allows authenticated users to send and receive messages in a clean and structured interface.
```

# Key Features
```php
- User Authentication (Login / Register)
- One-to-One Messaging System
- Secure Auth Middleware Protection
- Clean MVC Architecture
- Database Driven Messages
- Simple & Clean UI
- Laravel 12 Compatible
- Scalable Structure (Real-time ready)
- Beginner Friendly Setup
```

# Step 1: Install Fresh Laravel 12 Application
Open Terminal / Command Prompt and run:
```php
composer create-project laravel/laravel:^12.0 PHP_LARAVEL12_MESSENGER
```
Move into project directory:
```php
cd PHP_LARAVEL12_MESSENGER
```
Generate application key:
```php
php artisan key:generate
```

# Explanation
```php
- Installs fresh Laravel 12 project
- Generates unique application key
- Required for encryption, sessions, and security
```
# Step 2: Configure Environment & Database
Open .env file and update database configuration:
```php
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=messenger_db
DB_USERNAME=root
DB_PASSWORD=
```
Create database in phpMyAdmin:
```php
messenger_db
```
Run default migrations:
```php
php artisan migrate
```
# Explanation
```php
- .env manages environment settings
- Default migrations create system tables like:
                    users
                    password_reset_tokens
                    failed_jobs
```

# Step 3: Install Authentication (Laravel Breeze)
Install Breeze:
```php
      composer require laravel/breeze --dev
```
Install scaffolding:
```php
php artisan breeze:install
```
Install frontend dependencies:
```php
npm install
npm run dev
```
Run migrations again:
```php
php artisan migrate
```

# Explanation
```php
- Breeze provides:
- Login
- Register
- Password Reset
- Auth Middleware Protection
- Clean Blade UI
```
Authentication is required for messaging system.

# Step 4: Create Message Model & Migration
Create model with migration:
```php
php artisan make:model Message -m
```
Open migration file and update:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
        $table->text('message');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

```
Run migration:
```php
php artisan migrate
```

# Explanation

Creates messages table with:
```php
- Sender ID
- Receiver ID
- Message content
- Created & Updated timestamps
```

# Step 5: Configure Message Model
Open:
```php
app/Models/Message.php
```
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
    'sender_id',
    'receiver_id',
    'message'
];

public function sender()
{
    return $this->belongsTo(User::class, 'sender_id');
}

public function receiver()
{
    return $this->belongsTo(User::class, 'receiver_id');
}

}
```

# Explanation
```php
- Defines mass assignable fields
- Creates relationship with User model
```

# Step 6: Create Message Controller
Generate controller:
```php
php artisan make:controller MessageController
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{

public function index()
{
    $users = User::all();   // current user include
    return view('messenger.index', compact('users'));
}

public function send(Request $request)
{
    Message::create([
        'sender_id' => Auth::id(),
        'receiver_id' => $request->receiver_id,
        'message' => $request->message,
    ]);

    return back();
}

public function chat($id)
{
    $receiver = \App\Models\User::findOrFail($id);

    $messages = \App\Models\Message::where(function($q) use ($id){
        $q->where('sender_id', auth()->id())
          ->where('receiver_id', $id);
    })->orWhere(function($q) use ($id){
        $q->where('sender_id', $id)
          ->where('receiver_id', auth()->id());
    })
    ->orderBy('created_at','asc')
    ->get();

    return view('messenger.chat', compact('messages','receiver'));
}


}
```
# Explanation
```php
- index() → Shows all users except logged-in user
- chat() → Loads conversation between two users
- send() → Stores message in database
```

# Step 7: Define Routes
Open:
```php
routes/web.php
```
```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/messenger', [MessageController::class, 'index'])->name('messenger');

    Route::get('/chat/{id}', [MessageController::class, 'chat'])->name('chat');

    Route::post('/send', [MessageController::class, 'send'])->name('send.message');
});

require __DIR__.'/auth.php';
```

# Explanation
```php
- All routes are protected using auth middleware
- Only logged-in users can access messenger
```

# Step 8: Create Blade Views
Create folder:
```php
resources/views/messenger/
```
Create files:
```php
- index.blade.php (User List Page)
- chat.blade.php (Chat Interface)
```

# UI Features
```php
- Sidebar user list
- Chat message bubbles (Left / Right)
- Scrollable chat window
- Message input form
- Clean layout
```

# Step 9: Run Laravel Project
Start development server:
```php
php artisan serve
```
Open browser:
```php
http://127.0.0.1:8000
```
<img width="1206" height="678" alt="image" src="https://github.com/user-attachments/assets/285de0f2-cbcc-4005-9dce-e7b5994d0dca" />

Register at least two users.

Open Messenger:
```php
http://127.0.0.1:8000/messenger
```
<img width="1338" height="685" alt="image" src="https://github.com/user-attachments/assets/1fefe9f5-ff9f-453c-a10b-f8d837e06327" />
<img width="1317" height="670" alt="image" src="https://github.com/user-attachments/assets/acfd4e5e-6f93-40fe-8090-9676c34344ce" />

# Application Workflow

User Login
```php
→ View Other Users
→ Select User
→ Send Message
→ Message Stored in Database
→ Conversation Displayed in Chat Window
```

# Project Folder Structure
```php
PHP_LARAVEL12_MESSENGER
├── app/
│   ├── Models/
│   │   └── Message.php
│   │   └── User.php
│   └── Http/
│       └── Controllers/
│           └── MessageController.php
│
├── resources/
│   └── views/
│       └── messenger/
│           ├── index.blade.php
│           └── chat.blade.php
│
├── routes/
│   └── web.php
│
├── database/
│   └── migrations/
│
├── .env
├── artisan
└── composer.json
```




