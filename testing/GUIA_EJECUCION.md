# Guía de Ejecución de Tests

## 🚀 Comandos Básicos

### Ejecutar todos los tests
```bash
vendor/bin/phpunit
```

### Ejecutar un test suite específico
```bash
# Solo unit tests
vendor/bin/phpunit --testsuite Unit

# Solo integration tests
vendor/bin/phpunit --testsuite Integration

# Solo infrastructure tests
vendor/bin/phpunit --testsuite Infrastructure
```

### Ejecutar un test específico
```bash
vendor/bin/phpunit testing/Unit/ValueObject/MoneyAmountTest.php
```

### Ejecutar un método de test específico
```bash
vendor/bin/phpunit --filter testFromFloat_CreatesMoneyAmount_WithCorrectAmount
```

## 📊 Generar Reporte de Coverage

### HTML Coverage Report
```bash
vendor/bin/phpunit --coverage-html coverage/
```
Luego abre `coverage/index.html` en tu navegador.

### Text Coverage Report
```bash
vendor/bin/phpunit --coverage-text
```

## 🎯 Ejemplos de Salida

### Test Exitoso
```
PHPUnit 12.5.6 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.0
Configuration: /ruta/al/proyecto/phpunit.xml

.........                                                     9 / 9 (100%)

Time: 00:00.123, Memory: 6.00 MB

OK (9 tests, 15 assertions)
```

### Test Fallido
```
PHPUnit 12.5.6 by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: 00:00.045, Memory: 4.00 MB

There was 1 failure:

1) Tests\Unit\ValueObject\MoneyAmountTest::testAdd_WithTwoAmounts_ReturnsNewAmountWithSum
Failed asserting that 150.0 matches expected 200.0.

/ruta/al/test/MoneyAmountTest.php:45

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
```

## 📝 Estructura de Tests Creada

```
testing/
├── Unit/                          # Tests unitarios
│   ├── ValueObject/
│   │   ├── MoneyAmountTest.php   # Tests de Value Objects
│   │   └── InvoiceDateTest.php
│   ├── DTO/
│   │   └── CreateInvoiceRequestTest.php
│   ├── Entity/
│   │   └── InvoiceTest.php       # Tests de entidades
│   └── Exception/
│       └── InvalidInvoiceAmountExceptionTest.php
│
├── Integration/                   # Tests de integración
│   ├── UseCase/
│   │   ├── CreateInvoiceUseCaseTest.php
│   │   └── ProcessInvoiceUseCaseTest.php
│   └── Repository/
│       └── InMemoryInvoiceRepositoryTest.php
│
├── Infrastructure/                # Tests de infraestructura
│   ├── Adapter/
│   │   └── AfipServiceAdapterTest.php
│   └── Queue/
│       └── InMemoryEventQueueTest.php
│
├── Advanced/                      # Técnicas avanzadas
│   └── AdvancedTestingTechniquesTest.php
│
└── Helpers/                       # Helpers para tests
    └── InvoiceFactory.php
```

## 🎓 Conceptos Aprendidos

### 1. Unit Tests
- **Qué son**: Tests de unidades individuales de código
- **Cuándo usar**: Para Value Objects, DTOs, lógica de negocio pura
- **Características**: Rápidos, aislados, sin dependencias externas

### 2. Integration Tests
- **Qué son**: Tests que verifican interacción entre componentes
- **Cuándo usar**: Para Use Cases, Repositories, flujos completos
- **Características**: Más lentos, pueden usar mocks

### 3. Infrastructure Tests
- **Qué son**: Tests de componentes de infraestructura
- **Cuándo usar**: Para Adapters, Repositories, Queues
- **Características**: Pueden requerir servicios externos

### 4. Mocks y Stubs
- **Mock**: Verifica interacciones (expects, method calls)
- **Stub**: Devuelve valores predefinidos (willReturn)
- **Spy**: Registra llamadas para verificación posterior

### 5. Data Providers
- Permiten ejecutar el mismo test con diferentes datos
- Útiles para probar múltiples casos de uso
- Reducen duplicación de código

### 6. Test Fixtures
- Datos de prueba reutilizables
- Helpers y Factories para crear objetos de test
- Mantienen consistencia entre tests

## 🔍 Debugging Tests

### Ver output detallado
```bash
vendor/bin/phpunit --verbose
```

### Detener en el primer fallo
```bash
vendor/bin/phpunit --stop-on-failure
```

### Ejecutar solo tests que fallaron
```bash
vendor/bin/phpunit --only-failed
```

## 📚 Próximos Pasos

1. **Agregar más tests**: Cubrir más casos edge
2. **Mejorar coverage**: Alcanzar >80% de cobertura
3. **Tests de performance**: Para operaciones críticas
4. **Tests de seguridad**: Validar inputs maliciosos
5. **Tests de regresión**: Para bugs encontrados

## 🎯 Buenas Prácticas Aplicadas

✅ Un test, una aserción (cuando es posible)  
✅ Nombres descriptivos de tests  
✅ Tests independientes  
✅ Uso de setUp/tearDown  
✅ Data Providers para múltiples casos  
✅ Mocks para dependencias externas  
✅ Helpers para reducir duplicación  
✅ Comentarios explicativos en tests complejos  
