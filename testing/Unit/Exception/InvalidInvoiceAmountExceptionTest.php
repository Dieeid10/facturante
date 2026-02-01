<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use App\Domain\Exception\InvalidInvoiceAmountException;
use PHPUnit\Framework\TestCase;

/**
 * Test Unitario: InvalidInvoiceAmountException
 * 
 * Este test demuestra:
 * - Testing de excepciones personalizadas
 * - Testing de mensajes de error
 * - Testing de herencia de excepciones
 */
class InvalidInvoiceAmountExceptionTest extends TestCase
{
    /**
     * Test: Crear excepción con mensaje
     * 
     * Verifica que la excepción se crea correctamente
     * y hereda de Exception.
     */
    public function testConstructor_CreatesException_WithMessage(): void
    {
        // Arrange & Act
        $exception = new InvalidInvoiceAmountException('Importe total inválido');
        
        // Assert
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Importe total inválido', $exception->getMessage());
    }

    /**
     * Test: Excepción puede tener código
     */
    public function testConstructor_WithCode_SetsCode(): void
    {
        // Arrange & Act
        $exception = new InvalidInvoiceAmountException('Error', 400);
        
        // Assert
        $this->assertEquals(400, $exception->getCode());
    }
}
