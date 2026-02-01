<?php

namespace App\Application\UseCase;

use App\Application\Port\AfipServiceInterface;

class ValidateInvoiceUseCase
{
    private AfipServiceInterface $afipService;

    public function __construct(AfipServiceInterface $afipService)
    {
        $this->afipService = $afipService;
    }

    /**
     * Valida una factura obteniendo su información de AFIP
     *
     * @param int $voucherNumber
     * @param int $pointOfSale
     * @param int $voucherType
     * @return array{
     *   CAE: string,
     *   CAEFchVto: string,
     *   CbteDesde: int,
     *   Resultado: string
     * }
     * @throws \Exception
     */
    public function execute(int $voucherNumber, int $pointOfSale, int $voucherType): array
    {
        return $this->afipService->getInformationInvoice($voucherNumber, $pointOfSale, $voucherType);
    }
}
