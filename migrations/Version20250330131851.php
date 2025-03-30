<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250330131851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY candidatureuser
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY candidatureuser
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY fkcandidatureoffre
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD date_candidature DATETIME NOT NULL, ADD date_modification DATETIME NOT NULL, DROP dateCandidature, DROP dateModification, CHANGE iduser iduser INT DEFAULT NULL, CHANGE idCandidature id_candidature INT NOT NULL, CHANGE lettreMotivation lettre_motivation VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B85E5C27E9 FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD PRIMARY KEY (id_candidature)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fkcandidatureoffre ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B87983EA76 ON candidature (idoffre)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX candidatureuser ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B85E5C27E9 ON candidature (iduser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT candidatureuser FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT fkcandidatureoffre FOREIGN KEY (idoffre) REFERENCES offre (idoffre) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien MODIFY idEntretien INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON entretien
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien DROP FOREIGN KEY Etrangère3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien ADD id_entretien INT NOT NULL, DROP idEntretien
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien ADD PRIMARY KEY (id_entretien)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX etrangère3 ON entretien
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2B58D6DAA3662CC0 ON entretien (idCandidature)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien ADD CONSTRAINT Etrangère3 FOREIGN KEY (idCandidature) REFERENCES candidature (idCandidature) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipeemploye DROP FOREIGN KEY employeidfk
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipeemploye DROP FOREIGN KEY employeidfk
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipeemploye ADD CONSTRAINT FK_C005292270081D7 FOREIGN KEY (idemploye) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX employeidfk ON equipeemploye
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C005292270081D7 ON equipeemploye (idemploye)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipeemploye ADD CONSTRAINT employeidfk FOREIGN KEY (idemploye) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation MODIFY idEvaluation INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation DROP FOREIGN KEY Etrangère
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON evaluation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation DROP FOREIGN KEY Etrangère
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation ADD id_evaluation INT NOT NULL, ADD note_technique DOUBLE PRECISION NOT NULL, ADD note_soft_skills DOUBLE PRECISION NOT NULL, ADD date_evaluation DATETIME NOT NULL, DROP idEvaluation, DROP noteTechnique, DROP noteSoftSkills, DROP dateEvaluation, CHANGE scorequiz scorequiz INT NOT NULL, CHANGE questions questions LONGTEXT NOT NULL, CHANGE titreEva titre_eva VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575D7B2FD8C FOREIGN KEY (idEntretien) REFERENCES entretien (idEntretien) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation ADD PRIMARY KEY (id_evaluation)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX etrangère ON evaluation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1323A575D7B2FD8C ON evaluation (idEntretien)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation ADD CONSTRAINT Etrangère FOREIGN KEY (idEntretien) REFERENCES entretien (idEntretien) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation ADD is_favorite TINYINT(1) NOT NULL, DROP isFavorite, CHANGE id id INT NOT NULL, CHANGE dateFormation date_formation VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY usernot
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY usernot
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification CHANGE id id INT NOT NULL, CHANGE reservationid reservationid INT NOT NULL, CHANGE message message LONGTEXT NOT NULL, CHANGE date date DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA5E5C27E9 FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX usernot ON notification
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BF5476CA5E5C27E9 ON notification (iduser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT usernot FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre ADD date_publication DATETIME NOT NULL, ADD date_expiration DATETIME NOT NULL, DROP datePublication, DROP dateExpiration, CHANGE idoffre idoffre INT NOT NULL, CHANGE description description LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY fkparticipationformation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY fkparticipationuser
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY fkparticipationformation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY fkparticipationuser
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation CHANGE id id INT NOT NULL, CHANGE idFormation idFormation INT DEFAULT NULL, CHANGE idUser idUser INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FBCAA0AE9 FOREIGN KEY (idFormation) REFERENCES formation (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FFE6E88D7 FOREIGN KEY (idUser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fkparticipationformation ON participation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AB55E24FBCAA0AE9 ON participation (idFormation)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fkparticipationuser ON participation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AB55E24FFE6E88D7 ON participation (idUser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT fkparticipationformation FOREIGN KEY (idFormation) REFERENCES formation (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT fkparticipationuser FOREIGN KEY (idUser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet ADD date_debut DATE NOT NULL, ADD date_fin DATE NOT NULL, DROP dateDebut, DROP dateFin, CHANGE id id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idequipe ON projetequipe
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY idressource
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY userreservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY idressource
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY userreservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation CHANGE id id INT NOT NULL, CHANGE idressource idressource INT DEFAULT NULL, CHANGE iduser iduser INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955ED84ECB1 FOREIGN KEY (idressource) REFERENCES ressource (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C849555E5C27E9 FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idressource ON reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C84955ED84ECB1 ON reservation (idressource)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX userreservation ON reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C849555E5C27E9 ON reservation (iduser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT idressource FOREIGN KEY (idressource) REFERENCES ressource (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT userreservation FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ressource CHANGE id id INT NOT NULL, CHANGE prix prix DOUBLE PRECISION NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache DROP FOREIGN KEY fktache
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache DROP FOREIGN KEY fktache
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache ADD date_debut DATE NOT NULL, ADD date_fin DATE NOT NULL, DROP dateDebut, DROP dateFin, CHANGE id id INT NOT NULL, CHANGE idProjet idProjet INT DEFAULT NULL, CHANGE trelloboardid trelloboardid VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache ADD CONSTRAINT FK_9387207533043433 FOREIGN KEY (idProjet) REFERENCES projet (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fktache ON tache
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9387207533043433 ON tache (idProjet)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache ADD CONSTRAINT fktache FOREIGN KEY (idProjet) REFERENCES projet (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur CHANGE id id INT NOT NULL, CHANGE salaire salaire DOUBLE PRECISION NOT NULL, CHANGE poste poste VARCHAR(255) NOT NULL, CHANGE competence competence VARCHAR(255) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B85E5C27E9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B87983EA76
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B85E5C27E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD dateCandidature DATETIME NOT NULL, ADD dateModification DATETIME NOT NULL, DROP date_candidature, DROP date_modification, CHANGE iduser iduser INT NOT NULL, CHANGE id_candidature idCandidature INT NOT NULL, CHANGE lettre_motivation lettreMotivation VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT candidatureuser FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD PRIMARY KEY (idCandidature)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_e33bd3b87983ea76 ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fkcandidatureoffre ON candidature (idoffre)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_e33bd3b85e5c27e9 ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX candidatureuser ON candidature (iduser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B87983EA76 FOREIGN KEY (idoffre) REFERENCES offre (idoffre) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B85E5C27E9 FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON entretien
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien DROP FOREIGN KEY FK_2B58D6DAA3662CC0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien ADD idEntretien INT AUTO_INCREMENT NOT NULL, DROP id_entretien
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien ADD PRIMARY KEY (idEntretien)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_2b58d6daa3662cc0 ON entretien
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX Etrangère3 ON entretien (idCandidature)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien ADD CONSTRAINT FK_2B58D6DAA3662CC0 FOREIGN KEY (idCandidature) REFERENCES candidature (idCandidature) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipeemploye DROP FOREIGN KEY FK_C005292270081D7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipeemploye DROP FOREIGN KEY FK_C005292270081D7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipeemploye ADD CONSTRAINT employeidfk FOREIGN KEY (idemploye) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_c005292270081d7 ON equipeemploye
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX employeidfk ON equipeemploye (idemploye)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipeemploye ADD CONSTRAINT FK_C005292270081D7 FOREIGN KEY (idemploye) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575D7B2FD8C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON evaluation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation DROP FOREIGN KEY FK_1323A575D7B2FD8C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation ADD idEvaluation INT AUTO_INCREMENT NOT NULL, ADD noteTechnique DOUBLE PRECISION NOT NULL, ADD noteSoftSkills DOUBLE PRECISION NOT NULL, ADD dateEvaluation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, DROP id_evaluation, DROP note_technique, DROP note_soft_skills, DROP date_evaluation, CHANGE scorequiz scorequiz INT DEFAULT NULL, CHANGE questions questions LONGTEXT DEFAULT NULL, CHANGE titre_eva titreEva VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation ADD CONSTRAINT Etrangère FOREIGN KEY (idEntretien) REFERENCES entretien (idEntretien) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation ADD PRIMARY KEY (idEvaluation)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_1323a575d7b2fd8c ON evaluation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX Etrangère ON evaluation (idEntretien)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation ADD CONSTRAINT FK_1323A575D7B2FD8C FOREIGN KEY (idEntretien) REFERENCES entretien (idEntretien) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation ADD isFavorite TINYINT(1) DEFAULT 0 NOT NULL, DROP is_favorite, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE date_formation dateFormation VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA5E5C27E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA5E5C27E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE reservationid reservationid INT DEFAULT NULL, CHANGE message message TEXT DEFAULT NULL, CHANGE date date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT usernot FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_bf5476ca5e5c27e9 ON notification
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX usernot ON notification (iduser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA5E5C27E9 FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre ADD datePublication DATETIME NOT NULL, ADD dateExpiration DATETIME NOT NULL, DROP date_publication, DROP date_expiration, CHANGE idoffre idoffre INT AUTO_INCREMENT NOT NULL, CHANGE description description TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FBCAA0AE9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FFE6E88D7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FBCAA0AE9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FFE6E88D7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE idFormation idFormation INT NOT NULL, CHANGE idUser idUser INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT fkparticipationformation FOREIGN KEY (idFormation) REFERENCES formation (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT fkparticipationuser FOREIGN KEY (idUser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_ab55e24fbcaa0ae9 ON participation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fkparticipationformation ON participation (idFormation)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_ab55e24ffe6e88d7 ON participation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fkparticipationuser ON participation (idUser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FBCAA0AE9 FOREIGN KEY (idFormation) REFERENCES formation (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FFE6E88D7 FOREIGN KEY (idUser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet ADD dateDebut DATE NOT NULL, ADD dateFin DATE NOT NULL, DROP date_debut, DROP date_fin, CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idequipe ON projetequipe (idequipe)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955ED84ECB1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C849555E5C27E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955ED84ECB1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C849555E5C27E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE idressource idressource INT NOT NULL, CHANGE iduser iduser INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT idressource FOREIGN KEY (idressource) REFERENCES ressource (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT userreservation FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_42c84955ed84ecb1 ON reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idressource ON reservation (idressource)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_42c849555e5c27e9 ON reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX userreservation ON reservation (iduser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955ED84ECB1 FOREIGN KEY (idressource) REFERENCES ressource (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C849555E5C27E9 FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ressource CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE prix prix DOUBLE PRECISION DEFAULT '0' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache DROP FOREIGN KEY FK_9387207533043433
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache DROP FOREIGN KEY FK_9387207533043433
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache ADD dateDebut DATE NOT NULL, ADD dateFin DATE NOT NULL, DROP date_debut, DROP date_fin, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE trelloboardid trelloboardid VARCHAR(255) DEFAULT NULL, CHANGE idProjet idProjet INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache ADD CONSTRAINT fktache FOREIGN KEY (idProjet) REFERENCES projet (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_9387207533043433 ON tache
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fktache ON tache (idProjet)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache ADD CONSTRAINT FK_9387207533043433 FOREIGN KEY (idProjet) REFERENCES projet (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE salaire salaire DOUBLE PRECISION DEFAULT NULL, CHANGE poste poste VARCHAR(255) DEFAULT NULL, CHANGE competence competence VARCHAR(255) DEFAULT NULL
        SQL);
    }
}
