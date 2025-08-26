<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // bindings if needed later
    }

    public function boot(): void
    {
        // আলাদা tenant routes ফাইল লোড করতে চাইলে এখানে করলেও পারেন
        // আমরা নিচে routes/web.php থেকেই include করবো
    }
}
