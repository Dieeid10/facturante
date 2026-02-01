<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Money\Money;
use Money\Currency;

final class MoneyAmount
{
    private Money $money;

    private function __construct(Money $money)
    {
        $this->money = $money;
    }

    public static function fromFloat(float $amount, string $currency): self
    {
        return new self(
            new Money(
                (string) round($amount * 100),
                new Currency($currency)
            )
        );
    }

    public static function zero(string $currency): self
    {
        return new self(new Money(0, new Currency($currency)));
    }

    public function add(self $other): self
    {
        return new self($this->money->add($other->money));
    }

    public function equals(self $other): bool
    {
        return $this->money->equals($other->money);
    }

    public function toFloat(): float
    {
        return ((int) $this->money->getAmount()) / 100;
    }

    public function currency(): string
    {
        return $this->money->getCurrency()->getCode();
    }

    public function money(): Money
    {
        return $this->money;
    }
}
