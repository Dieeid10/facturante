<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObject;

use App\Domain\ValueObject\MoneyAmount;
use PHPUnit\Framework\TestCase;

/**
 * Test Unitario: MoneyAmount Value Object
 * 
 * Este test demuestra:
 * - Testing de Value Objects (objetos inmutables)
 * - Testing de métodos estáticos de creación
 * - Testing de operaciones matemáticas
 * - Testing de igualdad
 * - Testing de conversión de tipos
 * 
 * Value Objects deben ser:
 * - Inmutables (no cambian después de crearse)
 * - Comparables por valor, no por referencia
 * - Validar sus invariantes
 */
class MoneyAmountTest extends TestCase
{
    /**
     * Test: Crear MoneyAmount desde float
     * 
     * Verifica que el método fromFloat() crea correctamente
     * un MoneyAmount y convierte correctamente a centavos.
     */
    public function testFromFloat_CreatesMoneyAmount_WithCorrectAmount(): void
    {
        // Arrange & Act
        $amount = MoneyAmount::fromFloat(100.50, 'ARS');
        
        // Assert
        $this->assertEquals(100.50, $amount->toFloat());
        $this->assertEquals('ARS', $amount->currency());
    }

    /**
     * Test: Crear MoneyAmount cero
     * 
     * Verifica que zero() crea un MoneyAmount con valor 0.
     */
    public function testZero_CreatesMoneyAmount_WithZeroValue(): void
    {
        // Arrange & Act
        $zero = MoneyAmount::zero('USD');
        
        // Assert
        $this->assertEquals(0.0, $zero->toFloat());
        $this->assertEquals('USD', $zero->currency());
    }

    /**
     * Test: Sumar dos MoneyAmount
     * 
     * Verifica que add() suma correctamente dos cantidades
     * y devuelve un nuevo objeto (inmutabilidad).
     */
    public function testAdd_WithTwoAmounts_ReturnsNewAmountWithSum(): void
    {
        // Arrange
        $amount1 = MoneyAmount::fromFloat(100.0, 'ARS');
        $amount2 = MoneyAmount::fromFloat(50.0, 'ARS');
        
        // Act
        $result = $amount1->add($amount2);
        
        // Assert
        $this->assertEquals(150.0, $result->toFloat());
        // Verificar inmutabilidad: el original no cambió
        $this->assertEquals(100.0, $amount1->toFloat());
        $this->assertEquals(50.0, $amount2->toFloat());
    }

    /**
     * Test: Comparar igualdad de MoneyAmount
     * 
     * Verifica que equals() compara por valor, no por referencia.
     */
    public function testEquals_WithSameAmounts_ReturnsTrue(): void
    {
        // Arrange
        $amount1 = MoneyAmount::fromFloat(100.0, 'ARS');
        $amount2 = MoneyAmount::fromFloat(100.0, 'ARS');
        
        // Act & Assert
        $this->assertTrue($amount1->equals($amount2));
    }

    /**
     * Test: Comparar desigualdad de MoneyAmount
     * 
     * Verifica que equals() retorna false para cantidades diferentes.
     */
    public function testEquals_WithDifferentAmounts_ReturnsFalse(): void
    {
        // Arrange
        $amount1 = MoneyAmount::fromFloat(100.0, 'ARS');
        $amount2 = MoneyAmount::fromFloat(200.0, 'ARS');
        
        // Act & Assert
        $this->assertFalse($amount1->equals($amount2));
    }

    /**
     * Test: Redondeo de decimales
     * 
     * Verifica que los decimales se redondean correctamente
     * al convertir a centavos.
     */
    public function testFromFloat_WithDecimals_RoundsCorrectly(): void
    {
        // Arrange & Act
        $amount = MoneyAmount::fromFloat(100.999, 'ARS');
        
        // Assert: Debe redondear a 101.00
        $this->assertEquals(101.0, $amount->toFloat());
    }

    /**
     * Test: Diferentes monedas no son iguales
     * 
     * Verifica que MoneyAmount con diferentes monedas
     * no son iguales aunque tengan el mismo valor.
     */
    public function testEquals_WithDifferentCurrencies_ReturnsFalse(): void
    {
        // Arrange
        $arsAmount = MoneyAmount::fromFloat(100.0, 'ARS');
        $usdAmount = MoneyAmount::fromFloat(100.0, 'USD');
        
        // Act & Assert
        $this->assertFalse($arsAmount->equals($usdAmount));
    }

    /**
     * Data Provider: Múltiples casos de prueba para suma
     * 
     * Este método provee datos para múltiples tests,
     * demostrando el uso de data providers.
     * 
     * @dataProvider additionProvider
     */
    public function testAdd_WithDataProvider_ReturnsCorrectSum(
        float $amount1,
        float $amount2,
        float $expected
    ): void {
        // Arrange
        $money1 = MoneyAmount::fromFloat($amount1, 'ARS');
        $money2 = MoneyAmount::fromFloat($amount2, 'ARS');
        
        // Act
        $result = $money1->add($money2);
        
        // Assert
        $this->assertEquals($expected, $result->toFloat());
    }

    /**
     * Data Provider para tests de suma
     */
    public static function additionProvider(): array
    {
        return [
            'cero más cero' => [0.0, 0.0, 0.0],
            'números positivos' => [100.0, 50.0, 150.0],
            'números grandes' => [1000.0, 2000.0, 3000.0],
            'decimales' => [10.50, 20.25, 30.75],
        ];
    }
}
