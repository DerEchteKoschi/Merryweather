<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Shared\UUID;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230510130822 extends AbstractMigration
{
    /**
     * @param string $table
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function fromUUID(string $table): void
    {
        $records = $this->connection->executeQuery('SELECT id FROM ' . $table)->fetchAllAssociative();
        $newId = 0;
        foreach ($records as $record) {
            $newId++;
            $this->connection->executeStatement('UPDATE ' . $table . ' SET id = ' . $newId . ' WHERE id = "' . $record['id'] . '"');
        }
    }

    /**
     * @param string $table
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function toUUID(string $table): void
    {
        $records = $this->connection->executeQuery('SELECT id FROM ' . $table)->fetchAllAssociative();
        foreach ($records as $record) {
            $newId = \Ramsey\Uuid\Uuid::uuid7(new \DateTimeImmutable('yesterday + ' . $record['id'] . ' minute'));
            $this->connection->executeStatement('UPDATE ' . $table . ' SET id = "' . $newId . '" WHERE id = ' . $record['id']);
        }
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_config CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE crontab CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_config CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE crontab CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        foreach (['app_config', 'crontab'] as $table) {
            $this->toUUID($table);
        }
    }

    public function preDown(Schema $schema): void
    {
        foreach (['app_config', 'crontab'] as $table) {
            $this->fromUUID($table);
        }
    }
}
