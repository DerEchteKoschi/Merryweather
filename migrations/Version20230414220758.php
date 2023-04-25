<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230414220758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add amountPaid to slot';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slot ADD amount_paid INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slot DROP amount_paid');
    }
}
