# Facturante2 - Sistema de Facturación Electrónica

Sistema de facturación electrónica con arquitectura hexagonal que integra el SDK de AFIP para crear y validar facturas electrónicas.

## Arquitectura

El proyecto sigue una arquitectura hexagonal (puertos y adaptadores) con las siguientes capas:

- **Domain**: Entidades y eventos del dominio
- **Application**: Casos de uso y puertos (interfaces)
- **Infrastructure**: Adaptadores (AFIP SDK, cola de eventos, repositorios)

## Estructura de Directorios

```
src/
├── Domain/
│   ├── Entity/
│   │   └── Factura.php
│   └── Event/
│       ├── FacturaCreadaEvent.php
│       └── FacturaPendienteEvent.php
├── Application/
│   ├── Port/
│   │   ├── AfipServiceInterface.php
│   │   ├── EventQueueInterface.php
│   │   └── FacturaRepositoryInterface.php
│   └── UseCase/
│       ├── CrearFacturaUseCase.php
│       ├── ProcesarFacturaUseCase.php
│       └── ValidarFacturaUseCase.php
└── Infrastructure/
    ├── Adapter/
    │   └── AfipServiceAdapter.php
    ├── Factory/
    │   └── AfipFactory.php
    ├── Queue/
    │   └── InMemoryEventQueue.php
    ├── Repository/
    │   └── InMemoryFacturaRepository.php
    └── ServiceContainer.php
```

## Características

1. **Integración con AFIP SDK**: Adaptador que encapsula las llamadas al SDK de AFIP
2. **Cola de Eventos**: Sistema de cola en memoria para procesar facturas de forma asíncrona
3. **Arquitectura Hexagonal**: Separación clara entre dominio, aplicación e infraestructura
4. **Simple y Extensible**: Fácil de extender con base de datos u otros adaptadores

## Instalación

1. Instalar dependencias (cuando agregues el SDK de AFIP):
```bash
composer install
```

2. Configurar `bootstrap.php` con tus credenciales de AFIP (CUIT, certificados, etc.)

## Uso

### Crear una Factura

```php
php example_crear_factura.php
```

Este ejemplo:
- Crea una entidad Factura
- La guarda en el repositorio
- La encola para procesamiento
- Procesa la cola y crea la factura en AFIP

### Validar una Factura

```php
php example_validar_factura.php
```

Este ejemplo obtiene la información de una factura desde AFIP.

## Casos de Uso

- **CrearFacturaUseCase**: Crea una factura y la encola para procesamiento
- **ProcesarFacturaUseCase**: Procesa facturas pendientes de la cola y las crea en AFIP
- **ValidarFacturaUseCase**: Valida una factura obteniendo su información de AFIP
