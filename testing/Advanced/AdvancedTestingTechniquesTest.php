<?php

declare(strict_types=1);

namespace Tests\Advanced;

use App\Domain\ValueObject\MoneyAmount;
use PHPUnit\Framework\TestCase;

/**
 * Técnicas Avanzadas de Testing
 * 
 * Este archivo demuestra técnicas avanzadas de testing:
 * - Data Providers con múltiples parámetros
 * - Testing de excepciones con expectException
 * - Testing con setUpBeforeClass y tearDownAfterClass
 * - Testing con anotaciones especiales
 * - Testing de performance (opcional)
 */
class AdvancedTestingTechniquesTest extends TestCase
{
    /**
     * setUpBeforeClass: Se ejecuta UNA VEZ antes de todos los tests
     * 
     * Útil para inicializar recursos costosos que se comparten
     * entre todos los tests de la clase.
     */
    public static function setUpBeforeClass(): void
    {
        // Ejemplo: Conectar a base de datos de test
        // Ejemplo: Cargar fixtures grandes
    }

    /**
     * tearDownAfterClass: Se ejecuta UNA VEZ después de todos los tests
     * 
     * Útil para limpiar recursos compartidos.
     */
    public static function tearDownAfterClass(): void
    {
        // Ejemplo: Cerrar conexión a base de datos
        // Ejemplo: Limpiar archivos temporales
    }

    /**
     * Test con Data Provider complejo
     * 
     * @dataProvider moneyOperationProvider
     */
    public function testMoneyOperations_WithDataProvider(
        float $amount1,
        float $amount2,
        string $operation,
        float $expected
    ): void {
        // Arrange
        $money1 = MoneyAmount::fromFloat($amount1, 'ARS');
        $money2 = MoneyAmount::fromFloat($amount2, 'ARS');
        
        // Act
        $result = match ($operation) {
            'add' => $money1->add($money2),
            default => throw new \InvalidArgumentException("Operación desconocida: $operation")
        };
        
        // Assert
        $this->assertEquals($expected, $result->toFloat());
    }

    /**
     * Data Provider con múltiples parámetros y nombres descriptivos
     */
    public static function moneyOperationProvider(): array
    {
        return [
            'suma de números positivos' => [
                'amount1' => 100.0,
                'amount2' => 50.0,
                'operation' => 'add',
                'expected' => 150.0,
            ],
            'suma con cero' => [
                'amount1' => 100.0,
                'amount2' => 0.0,
                'operation' => 'add',
                'expected' => 100.0,
            ],
            'suma de decimales' => [
                'amount1' => 10.50,
                'amount2' => 20.25,
                'operation' => 'add',
                'expected' => 30.75,
            ],
        ];
    }

    /**
     * Test que verifica múltiples aserciones
     * 
     * Nota: Idealmente cada test debe tener una aserción,
     * pero a veces es útil verificar múltiples cosas relacionadas.
     */
    public function testMultipleAssertions_OnSameObject(): void
    {
        // Arrange
        $amount = MoneyAmount::fromFloat(100.0, 'ARS');
        
        // Act & Assert
        $this->assertEquals(100.0, $amount->toFloat());
        $this->assertEquals('ARS', $amount->currency());
        $this->assertInstanceOf(MoneyAmount::class, $amount);
    }

    /**
     * Test con expectException (método antiguo)
     * 
     * PHPUnit también soporta expectException() para
     * verificar que se lanza una excepción.
     */
    public function testException_WithExpectException(): void
    {
        // Arrange
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Fecha inválida');
        
        // Act - Esto debería lanzar una excepción
        \App\Domain\ValueObject\InvoiceDate::fromIsoString('fecha-invalida');
    }

    /**
     * Test que verifica que NO se lanza excepción
     */
    public function testNoException_WithValidData(): void
    {
        // Arrange & Act
        $amount = MoneyAmount::fromFloat(100.0, 'ARS');
        
        // Assert: Si llegamos aquí, no se lanzó excepción
        $this->assertNotNull($amount);
    }

    /**
     * Test con @depends: Depende de otro test
     * 
     * Este test se ejecutará después de testCreateAmount
     * y recibirá su resultado como parámetro.
     * 
     * @depends testCreateAmount
     */
    public function testDependsOnOtherTest(MoneyAmount $amount): void
    {
        // Este test recibe el resultado del test anterior
        $this->assertNotNull($amount);
        $this->assertEquals(100.0, $amount->toFloat());
    }

    /**
     * Test del que otros dependen
     */
    public function testCreateAmount(): MoneyAmount
    {
        $amount = MoneyAmount::fromFloat(100.0, 'ARS');
        $this->assertInstanceOf(MoneyAmount::class, $amount);
        return $amount; // Retornar para que otros tests lo usen
    }

    /**
     * Test que se salta condicionalmente
     * 
     * Útil para tests que requieren condiciones específicas
     * (extensión PHP, versión, etc.)
     */
    public function testSkipped_WithCondition(): void
    {
        if (!extension_loaded('some_extension')) {
            $this->markTestSkipped('Extensión some_extension no está disponible');
        }
        
        // Test continúa solo si la extensión está disponible
        $this->assertTrue(true);
    }

    /**
     * Test que verifica tipos de datos
     */
    public function testTypeAssertions(): void
    {
        $amount = MoneyAmount::fromFloat(100.0, 'ARS');
        
        // Diferentes tipos de aserciones
        $this->assertIsFloat($amount->toFloat());
        $this->assertIsString($amount->currency());
        $this->assertIsObject($amount);
        $this->assertInstanceOf(MoneyAmount::class, $amount);
    }
}
