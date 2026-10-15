<?php

namespace App\Tests\Entity;

use App\Entity\Loans;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires du statut calculé des emprunts.
 * Lancer avec : php bin/phpunit tests/Entity/LoansTest.php
 */
class LoansTest extends TestCase
{
    private function loan(string $due, ?string $returned = null): Loans
    {
        $loan = (new Loans())
            ->setBorrowerName('Test')
            ->setBorrowedAt(new \DateTime('-10 days'))
            ->setDueDate(new \DateTime($due));
        if (null !== $returned) {
            $loan->setReturnedAt(new \DateTime($returned));
        }

        return $loan;
    }

    public function testActiveLoan(): void
    {
        $loan = $this->loan('+3 days');
        self::assertFalse($loan->isOverdue());
        self::assertSame('En cours', $loan->getStatusLabel());
    }

    public function testOverdueLoan(): void
    {
        $loan = $this->loan('-1 day');
        self::assertTrue($loan->isOverdue());
        self::assertSame('En retard', $loan->getStatusLabel());
    }

    public function testReturnedLoanIsNeverOverdue(): void
    {
        $loan = $this->loan('-5 days', '-1 day');
        self::assertTrue($loan->isReturned());
        self::assertFalse($loan->isOverdue());
        self::assertSame('Retourné', $loan->getStatusLabel());
    }
}
