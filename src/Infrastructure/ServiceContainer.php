<?php

namespace App\Infrastructure;

use App\Application\Port\AfipServiceInterface;
use App\Application\Port\EventQueueInterface;
use App\Application\Port\InvoiceRepositoryInterface;
use App\Application\Port\ProcessingLockInterface;
use App\Application\UseCase\CreateInvoiceUseCase;
use App\Application\UseCase\ProcessInvoiceUseCase;
use App\Application\UseCase\ValidateInvoiceUseCase;
use App\Infrastructure\Adapter\AfipServiceAdapter;
use App\Infrastructure\Factory\AfipFactory;
use App\Infrastructure\Queue\InMemoryEventQueue;
use App\Infrastructure\Repository\InMemoryInvoiceRepository;
use App\Infrastructure\Lock\FileProcessingLock;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

class ServiceContainer
{
    private static ?ServiceContainer $instance = null;

    private ?AfipServiceInterface $afipService = null;
    private ?EventQueueInterface $eventQueue = null;
    private ?InvoiceRepositoryInterface $invoiceRepository = null;
    private ?CreateInvoiceUseCase $createInvoiceUseCase = null;
    private ?ProcessInvoiceUseCase $processInvoiceUseCase = null;
    private ?ValidateInvoiceUseCase $validateInvoiceUseCase = null;
    private ?ProcessingLockInterface $processingLock = null;
    private ?LoggerInterface $logger = null;

    private function __construct()
    {
    }

    public static function getInstance(): ServiceContainer
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function initializeAfip(string $cuit, bool $production, string $cert, string $key, ?string $accessToken = null): void
    {
        $afip = AfipFactory::create($cuit, $production, $cert, $key, $accessToken);
        $this->afipService = new AfipServiceAdapter($afip);
    }

    public function getAfipService(): AfipServiceInterface
    {
        if ($this->afipService === null) {
            throw new \RuntimeException('AFIP service no inicializado. Llama a initializeAfip() primero.');
        }
        return $this->afipService;
    }

    public function getEventQueue(): EventQueueInterface
    {
        if ($this->eventQueue === null) {
            $this->eventQueue = new InMemoryEventQueue();
        }
        return $this->eventQueue;
    }

    public function getInvoiceRepository(): InvoiceRepositoryInterface
    {
        if ($this->invoiceRepository === null) {
            $this->invoiceRepository = new InMemoryInvoiceRepository();
        }
        return $this->invoiceRepository;
    }

    public function getProcessingLock(): ProcessingLockInterface
    {
        if ($this->processingLock === null) {
            $this->processingLock = new FileProcessingLock();
        }
        return $this->processingLock;
    }

    public function getLogger(): LoggerInterface
    {
        if ($this->logger === null) {
            $logger = new Logger('facturante');

            // Handler para logs en archivo
            $logFile = __DIR__ . '/../../logs/app.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));

            // Handler para errores en stderr (útil para producción)
            $logger->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));

            $this->logger = $logger;
        }
        return $this->logger;
    }

    public function getCreateInvoiceUseCase(): CreateInvoiceUseCase
    {
        if ($this->createInvoiceUseCase === null) {
            $this->createInvoiceUseCase = new CreateInvoiceUseCase(
                $this->getAfipService(),
                $this->getInvoiceRepository(),
                $this->getEventQueue()
            );
        }
        return $this->createInvoiceUseCase;
    }

    public function getProcessInvoiceUseCase(): ProcessInvoiceUseCase
    {
        if ($this->processInvoiceUseCase === null) {
            $this->processInvoiceUseCase = new ProcessInvoiceUseCase(
                $this->getAfipService(),
                $this->getInvoiceRepository(),
                $this->getEventQueue(),
                $this->getProcessingLock(),
                $this->getLogger()
            );
        }
        return $this->processInvoiceUseCase;
    }

    public function getValidateInvoiceUseCase(): ValidateInvoiceUseCase
    {
        if ($this->validateInvoiceUseCase === null) {
            $this->validateInvoiceUseCase = new ValidateInvoiceUseCase(
                $this->getAfipService()
            );
        }
        return $this->validateInvoiceUseCase;
    }
}
