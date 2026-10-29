<?php

namespace Tests\Feature\Bplo;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StandalonePortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_bplo_portal_routes_are_exposed(): void
    {
        $this->assertTrue(Route::has('bplo.dashboard'));
        $this->assertTrue(Route::has('bplo.applications.index'));
        $this->assertTrue(Route::has('bplo.permits.index'));
        $this->assertTrue(Route::has('verify.lookup'));
        $this->assertTrue(Route::has('verify.show'));
        $this->assertFalse(Route::has('applications.create'));
        $this->assertFalse(Route::has('treasurer.payments.index'));
        $this->assertFalse(Route::has('admin.dashboard'));
    }

    public function test_public_verification_uses_the_bplo_portals_own_database(): void
    {
        $token = 'isolated-bplo-verification-token';

        $this->get("/verify/{$token}")
            ->assertOk()
            ->assertSee('INVALID / NOT FOUND');

        $this->assertDatabaseHas('qr_verifications', [
            'token' => $token,
            'result' => 'NOT_FOUND',
        ]);
    }

    public function test_non_bplo_users_cannot_access_the_portal(): void
    {
        $user = new User;
        $user->role = User::ROLE_INSPECTOR;

        $this->actingAs($user)
            ->get('/bplo/dashboard')
            ->assertForbidden();
    }

    public function test_login_page_has_no_applicant_registration_link(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('Secure staff portal')
            ->assertSee('Sign in to BPLO')
            ->assertDontSee('Register as an applicant');
    }

    public function test_bplo_staff_can_open_the_dashboard_review_and_release_queues(): void
    {
        $user = User::create([
            'name' => 'BPLO Staff',
            'email' => 'bplo@example.test',
            'password' => 'Password123!',
            'is_active' => true,
        ]);
        $user->role = User::ROLE_BPLO;
        $user->save();

        $this->actingAs($user)->get('/bplo/dashboard')
            ->assertOk()
            ->assertSee('BPLO Personnel Portal');

        $this->actingAs($user)->get('/bplo/applications')
            ->assertOk()
            ->assertSee('Application Review');

        $this->actingAs($user)->get('/bplo/permits')
            ->assertOk()
            ->assertSee('Permit Release');
    }
}
