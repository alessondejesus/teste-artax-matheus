<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PackageInstallTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Install Laravel Auditing Package
     *
     * @test
     */
    public function auditable_package_is_installed()
    {
        $output = null;
        exec('composer show', $output);

        $this->assertCount(1, array_filter($output, function($item) {
            return Str::contains($item, 'owen-it/laravel-auditing');
        }));
    }

    /**
     * Setup a package following the documentation
     * - Activate the package for User Model
     * ----------------------------------------------------------------------
     * For this test, make sure that you change the following configuration:
     * audit.console = true;
     * @test
     */
    public function auditable_package_is_working()
    {
        $user = User::factory()->create();
        $user->name = "Fulano de tal";
        $user->save();

        $this->assertCount(2, $user->audits);
    }
}
