<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240131092922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE anlage (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE anlage_string_assignment (id INT AUTO_INCREMENT NOT NULL, anl_id_id INT NOT NULL, station_nr VARCHAR(255) NOT NULL, inverter_nr VARCHAR(255) NOT NULL, string_nr VARCHAR(255) NOT NULL, channel_nr VARCHAR(255) NOT NULL, string_active VARCHAR(255) NOT NULL, channel_cat VARCHAR(255) DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, tilt VARCHAR(255) DEFAULT NULL, azimut VARCHAR(255) DEFAULT NULL, panel_type VARCHAR(255) DEFAULT NULL, inverter_type VARCHAR(255) DEFAULT NULL, INDEX IDX_929B8EED60D8C639 (anl_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE anlage_string_assignment ADD CONSTRAINT FK_929B8EED60D8C639 FOREIGN KEY (anl_id_id) REFERENCES anlage (id)');
        $this->addSql('DROP TABLE db__pv_dcist_ax102');
        $this->addSql('DROP TABLE db__pv_dcist_ax106');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE db__pv_dcist_ax102 (db_id BIGINT AUTO_INCREMENT NOT NULL, anl_id INT NOT NULL, stamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, wr_group INT NOT NULL, group_ac INT NOT NULL, wr_num INT NOT NULL, wr_idc VARCHAR(20) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, wr_udc VARCHAR(20) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, wr_pdc VARCHAR(20) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, wr_temp VARCHAR(20) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, wr_mpp_current JSON NOT NULL COMMENT \'(DC2Type:json)\', wr_mpp_voltage JSON NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(db_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE db__pv_dcist_ax106 (db_id BIGINT AUTO_INCREMENT NOT NULL, anl_id INT NOT NULL, stamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, wr_group INT NOT NULL, group_ac INT NOT NULL, wr_num INT NOT NULL, wr_idc VARCHAR(20) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, wr_udc VARCHAR(20) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, wr_pdc VARCHAR(20) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, wr_temp VARCHAR(20) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, wr_mpp_current JSON NOT NULL COMMENT \'(DC2Type:json)\', wr_mpp_voltage JSON NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(db_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE anlage_string_assignment DROP FOREIGN KEY FK_929B8EED60D8C639');
        $this->addSql('DROP TABLE anlage');
        $this->addSql('DROP TABLE anlage_string_assignment');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
