<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230510130822 extends AbstractMigration
{
    private string $sql;

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
            $this->update('UPDATE ' . $table . ' SET id = ' . $newId . ' WHERE id = "' . $record['id'] . '"');
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
            $newId = $this->genUUID((int)$record['id']);
            $this->update('UPDATE ' . $table . ' SET id = "' . $newId . '" WHERE id = ' . $record['id']);
        }
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slot DROP FOREIGN KEY FK_AC0E20676EB6DDB5');
        $this->addSql('ALTER TABLE slot DROP FOREIGN KEY FK_AC0E2067A76ED395');
        $this->addSql('ALTER TABLE app_config CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE crontab CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');

        $this->addSql('ALTER TABLE distribution CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE slot CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE distribution_id distribution_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE user_id user_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE slot ADD CONSTRAINT FK_AC0E20676EB6DDB5 FOREIGN KEY (distribution_id) REFERENCES distribution (id)');
        $this->addSql('ALTER TABLE slot ADD CONSTRAINT FK_AC0E2067A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slot DROP FOREIGN KEY FK_AC0E20676EB6DDB5');
        $this->addSql('ALTER TABLE slot DROP FOREIGN KEY FK_AC0E2067A76ED395');
        $this->addSql('ALTER TABLE app_config CHANGE id id INT AUTO_INCREMENT NOT NULL');

        $this->addSql('ALTER TABLE slot CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE distribution_id distribution_id INT NOT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE distribution CHANGE id id INT AUTO_INCREMENT NOT NULL');

        $this->addSql('ALTER TABLE crontab CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE slot ADD CONSTRAINT FK_AC0E20676EB6DDB5 FOREIGN KEY (distribution_id) REFERENCES distribution (id)');
        $this->addSql('ALTER TABLE slot ADD CONSTRAINT FK_AC0E2067A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');

    }

    public function postUp(Schema $schema): void
    {
        $this->sql ='';
        foreach (['app_config', 'crontab'] as $table) {
            $this->toUUID($table);
        }
        $userIds = $this->connection->executeQuery('SELECT id FROM user')->fetchAllAssociative();
        foreach ($userIds as $userId) {
            $slotIds = $this->connection->executeQuery('SELECT id FROM slot WHERE user_id = ' . $userId['id'])->fetchAllAssociative();
            $newId = $this->genUUID((int)$userId['id']);
            $this->update('UPDATE slot SET user_id = NULL WHERE user_id= "' . $userId['id'] . '"');
            $this->update('UPDATE user SET id = "' . $newId . '" WHERE id= "' . $userId['id'] . '"');
            foreach ($slotIds as $slotId) {
                $this->update('UPDATE slot SET user_id = "' . $newId . '" WHERE id= "' . $slotId['id'] . '"');
            }
        }
        $slotIds = $this->connection->executeQuery('SELECT id FROM slot')->fetchAllAssociative();
        foreach ($slotIds as $slotId) {
            $newId = $this->genUUID((int)$slotId['id']);
            $this->update('UPDATE slot SET id = "' . $newId . '" WHERE id= "' . $slotId['id'] . '"');
        }
        $distIds = $this->connection->executeQuery('SELECT id FROM distribution')->fetchAllAssociative();
        foreach ($distIds as $distId) {
            $newId = $this->genUUID((int)$distId['id']);
            $this->update('UPDATE distribution SET id = "' . $newId . '" WHERE id= "' . $distId['id'] . '"');
            $this->update('UPDATE slot SET distribution_id = "' . $newId . '" WHERE distribution_id= "' . $distId['id'] . '"');
        }
        echo $this->sql;
    }

    public function preDown(Schema $schema): void
    {
        foreach (['app_config', 'crontab'] as $table) {
            $this->fromUUID($table);
        }
        $userIds = $this->connection->executeQuery('SELECT id FROM user')->fetchAllAssociative();
        $newId = 0;
        foreach ($userIds as $userId) {
            $slotIds = $this->connection->executeQuery('SELECT id FROM slot WHERE user_id = ' . $userId['id'])->fetchAllAssociative();
            $newId++;
            $this->update('UPDATE slot SET user_id = NULL WHERE user_id= "' . $userId['id'] . '"');
            $this->update('UPDATE user SET id = "' . $newId . '" WHERE id= "' . $userId['id'] . '"');
            foreach ($slotIds as $slotId) {
                $this->update('UPDATE slot SET user_id = "' . $newId . '" WHERE id= "' . $slotId['id'] .'"');
            }
        }
        $slotIds = $this->connection->executeQuery('SELECT id FROM slot')->fetchAllAssociative();
        $newId =0;
        foreach ($slotIds as $slotId) {
            $newId++;
            $this->update('UPDATE slot SET id = "' . $newId . '" WHERE id= "' . $slotId['id'] . '"');
        }
        $distIds = $this->connection->executeQuery('SELECT id FROM distribution')->fetchAllAssociative();
        $newId = 0;
        foreach ($distIds as $distId) {
            $newId++;
            $this->update('UPDATE distribution SET id = "' . $newId . '" WHERE id= "' . $distId['id'] . '"');
            $this->update('UPDATE slot SET distribution_id = "' . $newId . '" WHERE distribution_id= "' . $distId['id'] . '"');
        }
    }

    private function genUUID(int $offset = 0): string
    {
        return (string)Uuid::uuid7(new \DateTimeImmutable('yesterday + ' . $offset . ' minute'));
    }

    private function update(string $string)
    {
        $this->sql .= $string . ';' . PHP_EOL;
    }
}
