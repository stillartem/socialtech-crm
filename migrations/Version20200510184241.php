<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200510184241 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE auth_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE event_consumed_queue_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE event_consumed_archive_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE auth_token (id INT NOT NULL, token VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, nick_name VARCHAR(255) NOT NULL, expire_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE event_consumed_queue (id INT NOT NULL, uid UUID NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, routing_key VARCHAR(255) DEFAULT NULL, event_body JSON DEFAULT NULL, w_status VARCHAR(255) DEFAULT NULL, w_next_exec_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, w_last_error TEXT DEFAULT NULL, w_worker_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE event_consumed_archive (id INT NOT NULL, uid UUID NOT NULL, timestamp TIMESTAMP(0) WITH TIME ZONE NOT NULL, routing_key VARCHAR(255) DEFAULT NULL, event_body JSON DEFAULT NULL, w_status VARCHAR(255) DEFAULT NULL, w_next_exec_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, w_last_error TEXT DEFAULT NULL, w_worker_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE auth_token_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE event_consumed_queue_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE event_consumed_archive_id_seq CASCADE');
        $this->addSql('DROP TABLE auth_token');
        $this->addSql('DROP TABLE event_consumed_queue');
        $this->addSql('DROP TABLE event_consumed_archive');
    }
}
