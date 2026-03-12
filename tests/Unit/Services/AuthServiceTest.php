<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AuthService;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    /** @test */
    public function it_can_authenticate_user_with_valid_credentials()
    {
        // Arrange
        $user = Usuario::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'nombres' => 'Test',
            'apellido_paterno' => 'User',
            'rol' => 'cliente'
        ]);

        // Act
        $result = $this->authService->login('test@example.com', 'password123');

        // Assert
        $this->assertTrue($result['success']);
        $this->assertInstanceOf(Usuario::class, $result['user']);
        $this->assertEquals('test@example.com', $result['user']->email);
    }

    /** @test */
    public function it_fails_authentication_with_invalid_credentials()
    {
        // Arrange
        $user = Usuario::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        // Act
        $result = $this->authService->login('test@example.com', 'wrongpassword');

        // Assert
        $this->assertFalse($result['success']);
        $this->assertNull($result['user']);
    }

    /** @test */
    public function it_can_check_if_user_is_authenticated()
    {
        // Arrange
        $user = Usuario::factory()->create();
        $this->actingAs($user);

        // Act
        $isAuthenticated = $this->authService->isAuthenticated();

        // Assert
        $this->assertTrue($isAuthenticated);
    }

    /** @test */
    public function it_can_check_if_user_has_specific_role()
    {
        // Arrange
        $admin = Usuario::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin);

        // Act & Assert
        $this->assertTrue($this->authService->hasRole('admin'));
        $this->assertFalse($this->authService->hasRole('cliente'));
    }

    /** @test */
    public function it_can_check_if_user_is_admin()
    {
        // Arrange
        $admin = Usuario::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin);

        // Act & Assert
        $this->assertTrue($this->authService->isAdmin());
    }

    /** @test */
    public function it_can_check_if_user_is_client()
    {
        // Arrange
        $client = Usuario::factory()->create(['rol' => 'cliente']);
        $this->actingAs($client);

        // Act & Assert
        $this->assertTrue($this->authService->isClient());
    }

    /** @test */
    public function it_can_get_current_authenticated_user()
    {
        // Arrange
        $user = Usuario::factory()->create([
            'email' => 'current@example.com'
        ]);
        $this->actingAs($user);

        // Act
        $currentUser = $this->authService->getCurrentUser();

        // Assert
        $this->assertInstanceOf(Usuario::class, $currentUser);
        $this->assertEquals('current@example.com', $currentUser->email);
    }

    /** @test */
    public function it_returns_null_when_no_user_is_authenticated()
    {
        // Act
        $currentUser = $this->authService->getCurrentUser();

        // Assert
        $this->assertNull($currentUser);
    }
}
