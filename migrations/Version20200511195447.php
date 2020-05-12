<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200511195447 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE event_consumed_queue DROP COLUMN uid');
        $this->addSql('ALTER TABLE event_consumed_queue ADD COLUMN uid INTEGER');
        $this->addSql('ALTER TABLE event_consumed_archive DROP COLUMN uid');
        $this->addSql('ALTER TABLE event_consumed_archive ADD COLUMN uid INTEGER');
    }

    public function down(Schema $schema) : void
    {
    }
}
