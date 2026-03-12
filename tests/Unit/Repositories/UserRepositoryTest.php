<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\UserRepository;
use App\Models\Usuario;
use App\Models\Puntos;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository();
    }

    /** @test */
    public function it_can_find_user_by_email()
    {
        // Arrange
        $user = Usuario::factory()->create([
            'email' => 'test@example.com'
        ]);

        // Act
        $foundUser = $this->userRepository->findByEmail('test@example.com');

        // Assert
        $this->assertInstanceOf(Usuario::class, $foundUser);
        $this->assertEquals('test@example.com', $foundUser->email);
    }

    /** @test */
    public function it_returns_null_when_user_email_not_found()
    {
        // Act
        $foundUser = $this->userRepository->findByEmail('nonexistent@example.com');

        // Assert
        $this->assertNull($foundUser);
    }

    /** @test */
    public function it_can_find_user_by_id()
    {
        // Arrange
        $user = Usuario::factory()->create();

        // Act
        $foundUser = $this->userRepository->find($user->id);

        // Assert
        $this->assertInstanceOf(Usuario::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }

    /** @test */
    public function it_can_get_all_clients()
    {
        // Arrange
        Usuario::factory()->count(3)->create(['rol' => 'cliente']);
        Usuario::factory()->count(2)->create(['rol' => 'admin']);

        // Act
        $clients = $this->userRepository->getAllClients();

        // Assert
        $this->assertCount(3, $clients);
        $this->assertTrue($clients->every(fn($user) => $user->rol === 'cliente'));
    }

    /** @test */
    public function it_can_get_all_admins()
    {
        // Arrange
        Usuario::factory()->count(2)->create(['rol' => 'admin']);
        Usuario::factory()->count(3)->create(['rol' => 'cliente']);

        // Act
        $admins = $this->userRepository->getAllAdmins();

        // Assert
        $this->assertCount(2, $admins);
        $this->assertTrue($admins->every(fn($user) => $user->rol === 'admin'));
    }

    /** @test */
    public function it_can_get_top_users_by_points()
    {
        // Arrange
        $user1 = Usuario::factory()->create(['rol' => 'cliente']);
        $user2 = Usuario::factory()->create(['rol' => 'cliente']);
        $user3 = Usuario::factory()->create(['rol' => 'cliente']);

        Puntos::create(['usuario_id' => $user1->id, 'saldo' => 100]);
        Puntos::create(['usuario_id' => $user2->id, 'saldo' => 500]);
        Puntos::create(['usuario_id' => $user3->id, 'saldo' => 300]);

        // Act
        $topUsers = $this->userRepository->getTopUsersByPoints(3);

        // Assert
        $this->assertCount(3, $topUsers);
        $this->assertEquals($user2->id, $topUsers[0]->id); // Usuario con más puntos primero
        $this->assertEquals(500, $topUsers[0]->saldo);
    }

    /** @test */
    public function it_can_count_users_by_role()
    {
        // Arrange
        Usuario::factory()->count(5)->create(['rol' => 'cliente']);
        Usuario::factory()->count(2)->create(['rol' => 'admin']);

        // Act
        $clientCount = $this->userRepository->countByRole('cliente');
        $adminCount = $this->userRepository->countByRole('admin');

        // Assert
        $this->assertEquals(5, $clientCount);
        $this->assertEquals(2, $adminCount);
    }

    /** @test */
    public function it_can_create_user()
    {
        // Act
        $user = $this->userRepository->create([
            'email' => 'new@example.com',
            'password' => bcrypt('password'),
            'nombres' => 'New',
            'apellido_paterno' => 'User',
            'apellido_materno' => 'Test',
            'fecha_nacimiento' => '1990-01-01',
            'telefono' => '1234567890',
            'rol' => 'cliente'
        ]);

        // Assert
        $this->assertInstanceOf(Usuario::class, $user);
        $this->assertDatabaseHas('usuarios', [
            'email' => 'new@example.com'
        ]);
    }

    /** @test */
    public function it_can_update_user()
    {
        // Arrange
        $user = Usuario::factory()->create([
            'nombres' => 'Old Name'
        ]);

        // Act
        $updated = $this->userRepository->update($user->id, [
            'nombres' => 'New Name'
        ]);

        // Assert
        $this->assertTrue($updated);
        $this->assertDatabaseHas('usuarios', [
            'id' => $user->id,
            'nombres' => 'New Name'
        ]);
    }

    /** @test */
    public function it_returns_false_when_updating_nonexistent_user()
    {
        // Act
        $updated = $this->userRepository->update(99999, [
            'nombres' => 'New Name'
        ]);

        // Assert
        $this->assertFalse($updated);
    }
}
