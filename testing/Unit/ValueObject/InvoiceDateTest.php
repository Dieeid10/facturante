<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObject;

use App\Domain\ValueObject\InvoiceDate;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Test Unitario: InvoiceDate Value Object
 * 
 * Este test demuestra:
 * - Testing de validación de entrada
 * - Testing de excepciones
 * - Testing de conversión de formatos
 * - Testing de edge cases (casos límite)
 */
class InvoiceDateTest extends TestCase
{
    /**
     * Test: Crear InvoiceDate desde string ISO válido
     */
    public function testFromIsoString_WithValidDate_CreatesInvoiceDate(): void
    {
        // Arrange & Act
        $date = InvoiceDate::fromIsoString('2026-01-15');
        
        // Assert
        $this->assertInstanceOf(InvoiceDate::class, $date);
        $this->assertEquals('2026-01-15', $date->toIsoString());
    }

    /**
     * Test: Crear InvoiceDate con fecha inválida lanza excepción
     * 
     * Este test verifica que se lanza una excepción
     * cuando se intenta crear una fecha inválida.
     */
    public function testFromIsoString_WithInvalidDate_ThrowsException(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fecha inválida');
        
        // Act
        InvoiceDate::fromIsoString('fecha-invalida');
    }

    /**
     * Test: Convertir a formato AFIP (Ymd)
     * 
     * Verifica que toAfipFormat() convierte correctamente
     * al formato que espera AFIP.
     */
    public function testToAfipFormat_ConvertsToYmdFormat(): void
    {
        // Arrange
        $date = InvoiceDate::fromIsoString('2026-01-15');
        
        // Act
        $afipFormat = $date->toAfipFormat();
        
        // Assert
        $this->assertEquals('20260115', $afipFormat);
    }

    /**
     * Test: Convertir a string ISO
     */
    public function testToIsoString_ConvertsToIsoFormat(): void
    {
        // Arrange
        $date = InvoiceDate::fromIsoString('2026-12-31');
        
        // Act
        $isoString = $date->toIsoString();
        
        // Assert
        $this->assertEquals('2026-12-31', $isoString);
    }

    /**
     * Test: Edge case - último día del año
     */
    public function testFromIsoString_WithLastDayOfYear_WorksCorrectly(): void
    {
        // Arrange & Act
        $date = InvoiceDate::fromIsoString('2026-12-31');
        
        // Assert
        $this->assertEquals('2026-12-31', $date->toIsoString());
        $this->assertEquals('20261231', $date->toAfipFormat());
    }

    /**
     * Test: Edge case - primer día del año
     */
    public function testFromIsoString_WithFirstDayOfYear_WorksCorrectly(): void
    {
        // Arrange & Act
        $date = InvoiceDate::fromIsoString('2026-01-01');
        
        // Assert
        $this->assertEquals('2026-01-01', $date->toIsoString());
        $this->assertEquals('20260101', $date->toAfipFormat());
    }

    /**
     * Data Provider: Fechas inválidas
     * 
     * @dataProvider invalidDateProvider
     */
    public function testFromIsoString_WithInvalidDates_ThrowsException(string $invalidDate): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);
        
        // Act
        InvoiceDate::fromIsoString($invalidDate);
    }

    public static function invalidDateProvider(): array
    {
        return [
            'string vacío' => [''],
            'formato incorrecto' => ['15-01-2026'],
            'fecha inexistente' => ['2026-02-30'],
            'solo números' => ['20260115'],
            'texto aleatorio' => ['fecha'],
        ];
    }
}
