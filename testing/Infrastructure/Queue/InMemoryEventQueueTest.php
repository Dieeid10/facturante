<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Queue;

use App\Domain\Event\InvoicePendingEvent;
use App\Infrastructure\Queue\InMemoryEventQueue;
use PHPUnit\Framework\TestCase;

/**
 * Test de Infraestructura: InMemoryEventQueue
 * 
 * Este test demuestra:
 * - Testing de colas (FIFO - First In First Out)
 * - Testing de estado de cola (vacía, con elementos)
 * - Testing de operaciones de cola (enqueue, dequeue)
 */
class InMemoryEventQueueTest extends TestCase
{
    private InMemoryEventQueue $queue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queue = new InMemoryEventQueue();
    }

    /**
     * Test: Cola vacía inicialmente
     */
    public function testIsEmpty_Initially_ReturnsTrue(): void
    {
        // Assert
        $this->assertTrue($this->queue->isEmpty());
        $this->assertEquals(0, $this->queue->count());
    }

    /**
     * Test: Encolar evento
     */
    public function testEnqueue_AddsEvent_ToQueue(): void
    {
        // Arrange
        $event = new InvoicePendingEvent(1);
        
        // Act
        $this->queue->enqueue($event);
        
        // Assert
        $this->assertFalse($this->queue->isEmpty());
        $this->assertEquals(1, $this->queue->count());
    }

    /**
     * Test: Desencolar evento (FIFO)
     * 
     * Verifica que se desencola en orden FIFO
     * (First In First Out).
     */
    public function testDequeue_ReturnsEvents_InFifoOrder(): void
    {
        // Arrange
        $event1 = new InvoicePendingEvent(1);
        $event2 = new InvoicePendingEvent(2);
        $event3 = new InvoicePendingEvent(3);
        
        $this->queue->enqueue($event1);
        $this->queue->enqueue($event2);
        $this->queue->enqueue($event3);
        
        // Act & Assert
        $this->assertEquals($event1, $this->queue->dequeue());
        $this->assertEquals($event2, $this->queue->dequeue());
        $this->assertEquals($event3, $this->queue->dequeue());
    }

    /**
     * Test: Desencolar de cola vacía retorna null
     */
    public function testDequeue_FromEmptyQueue_ReturnsNull(): void
    {
        // Act
        $result = $this->queue->dequeue();
        
        // Assert
        $this->assertNull($result);
    }

    /**
     * Test: Contar eventos en cola
     */
    public function testCount_WithMultipleEvents_ReturnsCorrectCount(): void
    {
        // Arrange
        $this->queue->enqueue(new InvoicePendingEvent(1));
        $this->queue->enqueue(new InvoicePendingEvent(2));
        $this->queue->enqueue(new InvoicePendingEvent(3));
        
        // Act & Assert
        $this->assertEquals(3, $this->queue->count());
        
        // Desencolar uno
        $this->queue->dequeue();
        $this->assertEquals(2, $this->queue->count());
    }
}
