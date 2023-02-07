<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class Version20230207214827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'initial setup';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_config (id INT AUTO_INCREMENT NOT NULL, config_key VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_318942FC95D1CAA6 (config_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crontab (id INT AUTO_INCREMENT NOT NULL, expression VARCHAR(255) NOT NULL, last_execution DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', next_execution DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', command VARCHAR(255) NOT NULL, result LONGTEXT DEFAULT NULL, arguments VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE distribution (id INT AUTO_INCREMENT NOT NULL, active_from DATETIME NOT NULL, active_till DATETIME NOT NULL, text VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE slot (id INT AUTO_INCREMENT NOT NULL, distribution_id INT NOT NULL, user_id INT DEFAULT NULL, version INT DEFAULT 1 NOT NULL, text VARCHAR(255) NOT NULL, start_at TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', INDEX IDX_AC0E20676EB6DDB5 (distribution_id), INDEX IDX_AC0E2067A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, display_name VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(32) NOT NULL, active TINYINT(1) DEFAULT 0 NOT NULL, score INT DEFAULT 0 NOT NULL, last_login DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_visit DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', UNIQUE INDEX UNIQ_8D93D649D5499347 (display_name), UNIQUE INDEX UNIQ_8D93D649444F97DD (phone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE slot ADD CONSTRAINT FK_AC0E20676EB6DDB5 FOREIGN KEY (distribution_id) REFERENCES distribution (id)');
        $this->addSql('ALTER TABLE slot ADD CONSTRAINT FK_AC0E2067A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE slot DROP FOREIGN KEY FK_AC0E20676EB6DDB5');
        $this->addSql('ALTER TABLE slot DROP FOREIGN KEY FK_AC0E2067A76ED395');
        $this->addSql('DROP TABLE app_config');
        $this->addSql('DROP TABLE crontab');
        $this->addSql('DROP TABLE distribution');
        $this->addSql('DROP TABLE slot');
        $this->addSql('DROP TABLE user');
    }
}
