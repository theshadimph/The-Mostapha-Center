<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Alpine.js ships bundled inside Livewire's own script (see resources/js/app.js
 * — there is no separate Alpine import), and Livewire only auto-injects that
 * script on pages that render an actual Livewire component. Every plain Blade
 * layout must therefore declare @livewireScripts explicitly, or every
 * x-data/x-show/x-cloak/dropdown/theme-toggle on that layout silently does
 * nothing — this exact regression happened once already.
 */
class AlpineAssetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_load_the_livewire_script(): void
    {
        $this->get(route('home'))->assertSee('livewire.js', escape: false);
    }

    public function test_auth_pages_load_the_livewire_script(): void
    {
        $this->get(route('login'))->assertSee('livewire.js', escape: false);
    }

    public function test_dashboard_loads_the_livewire_script(): void
    {
        $user = User::factory()->role(UserRole::Librarian)->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('livewire.js', escape: false);
    }
}
