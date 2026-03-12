<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PointsService;
use App\Models\Usuario;
use App\Models\Puntos;
use App\Models\TransaccionPuntos;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PointsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $pointsService;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pointsService = new PointsService();
        
        // Crear usuario de prueba
        $this->user = Usuario::factory()->create();
        
        // Crear registro de puntos para el usuario
        Puntos::create([
            'usuario_id' => $this->user->id,
            'saldo' => 100
        ]);
    }

    /** @test */
    public function it_can_get_user_balance()
    {
        // Act
        $balance = $this->pointsService->getUserBalance($this->user->id);

        // Assert
        $this->assertEquals(100, $balance);
    }

    /** @test */
    public function it_can_add_points_to_user()
    {
        // Act
        $result = $this->pointsService->addPoints(
            $this->user->id,
            50,
            'Compra de prueba',
            $this->user->id
        );

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(150, $result['nuevo_saldo']);
        
        // Verificar que se creó la transacción
        $this->assertDatabaseHas('transacciones_puntos', [
            'usuario_id' => $this->user->id,
            'tipo' => 'compra',
            'puntos' => 50
        ]);
    }

    /** @test */
    public function it_can_deduct_points_from_user()
    {
        // Act
        $result = $this->pointsService->deductPoints(
            $this->user->id,
            30,
            'Canje de cupón',
            $this->user->id
        );

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals(70, $result['nuevo_saldo']);
        
        // Verificar que se creó la transacción
        $this->assertDatabaseHas('transacciones_puntos', [
            'usuario_id' => $this->user->id,
            'tipo' => 'canje',
            'puntos' => 30
        ]);
    }

    /** @test */
    public function it_fails_to_deduct_points_when_insufficient_balance()
    {
        // Act
        $result = $this->pointsService->deductPoints(
            $this->user->id,
            200, // Más que el saldo actual (100)
            'Intento de canje',
            $this->user->id
        );

        // Assert
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Saldo insuficiente', $result['message']);
    }

    /** @test */
    public function it_can_calculate_points_from_purchase_amount()
    {
        // Act & Assert
        $this->assertEquals(100, $this->pointsService->calculatePointsFromPurchase(100.00));
        $this->assertEquals(50, $this->pointsService->calculatePointsFromPurchase(50.75));
        $this->assertEquals(1, $this->pointsService->calculatePointsFromPurchase(1.99));
    }

    /** @test */
    public function it_can_get_transaction_history()
    {
        // Arrange - Crear algunas transacciones
        TransaccionPuntos::create([
            'usuario_id' => $this->user->id,
            'tipo' => 'compra',
            'puntos' => 50,
            'descripcion' => 'Compra 1',
            'registrado_por' => $this->user->id
        ]);

        TransaccionPuntos::create([
            'usuario_id' => $this->user->id,
            'tipo' => 'canje',
            'puntos' => 20,
            'descripcion' => 'Canje 1',
            'registrado_por' => $this->user->id
        ]);

        // Act
        $history = $this->pointsService->getTransactionHistory($this->user->id);

        // Assert
        $this->assertCount(2, $history);
        $this->assertEquals('canje', $history[0]['tipo']); // La más reciente primero
    }

    /** @test */
    public function it_returns_zero_balance_for_user_without_points_record()
    {
        // Arrange - Crear usuario sin registro de puntos
        $newUser = Usuario::factory()->create();

        // Act
        $balance = $this->pointsService->getUserBalance($newUser->id);

        // Assert
        $this->assertEquals(0, $balance);
    }
}
