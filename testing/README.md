# Guía de Testing - Facturante

Esta carpeta contiene ejemplos educativos de diferentes tipos de tests en PHP usando PHPUnit.

## 📚 Tipos de Tests Incluidos

### 1. **Unit Tests** (`Unit/`)
Tests que verifican unidades individuales de código en aislamiento.

- **Value Objects**: Tests de objetos inmutables (MoneyAmount, InvoiceDate)
- **DTOs**: Tests de objetos de transferencia de datos
- **Entities**: Tests de entidades de dominio con sus invariantes
- **Exceptions**: Tests de excepciones personalizadas

**Características:**
- Rápidos de ejecutar
- No dependen de servicios externos
- Verifican lógica de negocio pura

### 2. **Integration Tests** (`Integration/`)
Tests que verifican la interacción entre múltiples componentes.

- **Use Cases**: Tests de casos de uso completos
- **Repositories + Entities**: Tests de persistencia
- **Event Queue**: Tests de cola de eventos

**Características:**
- Verifican flujos completos
- Pueden usar mocks para servicios externos
- Más lentos que unit tests

### 3. **Infrastructure Tests** (`Infrastructure/`)
Tests de componentes de infraestructura.

- **Adapters**: Tests de adaptadores (AFIP, etc.)
- **Repositories**: Tests de repositorios
- **Queues**: Tests de colas

**Características:**
- Pueden requerir servicios externos
- Tests de integración con sistemas externos

## 🎯 Conceptos de Testing Aprendidos

### Mocks y Stubs
- **Mock**: Objeto que simula comportamiento y verifica interacciones
- **Stub**: Objeto que devuelve valores predefinidos
- **Spy**: Objeto que registra llamadas para verificación posterior

### Test Doubles
```php
// Mock: Verifica que se llame un método
$mock->expects($this->once())
     ->method('save')
     ->with($invoice);

// Stub: Devuelve un valor predefinido
$stub->method('findById')
     ->willReturn($invoice);
```

### Test Fixtures
Datos de prueba reutilizables para mantener consistencia.

### Test Coverage
- **Line Coverage**: Porcentaje de líneas ejecutadas
- **Branch Coverage**: Porcentaje de ramas (if/else) ejecutadas
- **Path Coverage**: Porcentaje de caminos de ejecución

## 🚀 Ejecutar Tests

```bash
# Todos los tests
vendor/bin/phpunit

# Solo unit tests
vendor/bin/phpunit --testsuite Unit

# Solo integration tests
vendor/bin/phpunit --testsuite Integration

# Con coverage
vendor/bin/phpunit --coverage-html coverage/

# Test específico
vendor/bin/phpunit testing/Unit/ValueObject/MoneyAmountTest.php
```

## 📖 Patrones de Testing

### AAA Pattern (Arrange-Act-Assert)
```php
public function testSomething()
{
    // Arrange: Preparar datos
    $amount = MoneyAmount::fromFloat(100.0, 'ARS');
    
    // Act: Ejecutar acción
    $result = $amount->add($amount);
    
    // Assert: Verificar resultado
    $this->assertEquals(200.0, $result->toFloat());
}
```

### Test Naming Convention
- `testShould_When_Then()`: Describe comportamiento esperado
- `testMethodName_WithCondition_ReturnsExpected()`: Describe método y condición

## 🔍 Buenas Prácticas

1. **Un test, una aserción**: Cada test debe verificar una cosa
2. **Tests independientes**: No deben depender de otros tests
3. **Nombres descriptivos**: El nombre debe explicar qué se prueba
4. **Fast**: Tests deben ejecutarse rápidamente
5. **Isolated**: Tests no deben compartir estado
6. **Repeatable**: Mismo resultado siempre
7. **Self-validating**: Deben pasar o fallar claramente

## 📝 Estructura de un Test

```php
class ExampleTest extends TestCase
{
    // Setup: Se ejecuta antes de cada test
    protected function setUp(): void
    {
        parent::setUp();
        // Preparar datos comunes
    }
    
    // Teardown: Se ejecuta después de cada test
    protected function tearDown(): void
    {
        // Limpiar recursos
        parent::tearDown();
    }
    
    // Test individual
    public function testSomething(): void
    {
        // Arrange, Act, Assert
    }
    
    // Data Provider: Múltiples casos de prueba
    /**
     * @dataProvider amountProvider
     */
    public function testWithDataProvider(float $amount): void
    {
        // Test con diferentes datos
    }
    
    public static function amountProvider(): array
    {
        return [
            [100.0],
            [200.0],
            [0.0],
        ];
    }
}
```

## 🎓 Recursos de Aprendizaje

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Test-Driven Development (TDD)](https://en.wikipedia.org/wiki/Test-driven_development)
- [Behavior-Driven Development (BDD)](https://en.wikipedia.org/wiki/Behavior-driven_development)
