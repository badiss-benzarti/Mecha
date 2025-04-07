<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329022813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez-vous DROP FOREIGN KEY fk_entretien
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE annonces
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE entretien
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rendez-vous
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sav
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE voitures
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets MODIFY ticket_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets DROP FOREIGN KEY tickets_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX id ON tickets
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON tickets
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets DROP ticket_id, DROP title, DROP description, DROP status, DROP created_at, DROP updated_at, CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets ADD PRIMARY KEY (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE annonces (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, voiture_id INT DEFAULT NULL, INDEX voiture_id (voiture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE entretien (id_entretien INT AUTO_INCREMENT NOT NULL, type_entretien VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_planifiee DATE NOT NULL, cout DOUBLE PRECISION NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id_entretien)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messages (message_id INT AUTO_INCREMENT NOT NULL, ticket_id INT DEFAULT NULL, sender_id INT DEFAULT NULL, receiver_id INT NOT NULL, message TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX ticket_id (ticket_id), INDEX sender_id (sender_id), INDEX fk_receiver (receiver_id), PRIMARY KEY(message_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE rendez-vous (id_rdv INT AUTO_INCREMENT NOT NULL, date_rdv DATETIME NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, id_entretien INT NOT NULL, INDEX fk_entretien (id_entretien), PRIMARY KEY(id_rdv)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sav (id INT AUTO_INCREMENT NOT NULL, typeService VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, dateDemande DATE NOT NULL, statut ENUM('En cour', 'Terminé', 'annuleé', '') CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prenom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, cin CHAR(8) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, motdepasse VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, reputation DOUBLE PRECISION DEFAULT NULL, services TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, tarif DOUBLE PRECISION DEFAULT NULL, role ENUM('client', 'admin', 'prestataire') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, UNIQUE INDEX email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE voitures (id INT AUTO_INCREMENT NOT NULL, marque VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, modele VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, annee INT DEFAULT NULL, prix NUMERIC(10, 2) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendez-vous ADD CONSTRAINT fk_entretien FOREIGN KEY (id_entretien) REFERENCES entretien (id_entretien)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets ADD ticket_id INT AUTO_INCREMENT NOT NULL, ADD title VARCHAR(255) NOT NULL, ADD description TEXT NOT NULL, ADD status ENUM('ouvert', 'en cours', 'résolu', 'fermé') NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE id id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (ticket_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets ADD CONSTRAINT tickets_ibfk_1 FOREIGN KEY (id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX id ON tickets (id)
        SQL);
    }
}
