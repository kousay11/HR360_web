<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327142332 extends AbstractMigration
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
            ALTER TABLE candidature DROP FOREIGN KEY candidature_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY candidature_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY fk_candidature_offre
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD date_modification DATETIME NOT NULL, DROP dateModification, CHANGE id_candidature id_candidature INT NOT NULL, CHANGE id_offre id_offre INT DEFAULT NULL, CHANGE id_user id_user INT DEFAULT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE dateCandidature date_candidature DATETIME NOT NULL, CHANGE lettreMotivation lettre_motivation VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B86B3CA4B FOREIGN KEY (id_user) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_candidature_offre ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B84103C75F ON candidature (id_offre)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX candidature_user ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B86B3CA4B ON candidature (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT candidature_user FOREIGN KEY (id_user) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT fk_candidature_offre FOREIGN KEY (id_offre) REFERENCES offre (id_offre) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien MODIFY idEntretien INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien DROP FOREIGN KEY entretein_candidature
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON entretien
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien DROP FOREIGN KEY entretein_candidature
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien ADD id_entretien INT NOT NULL, DROP idEntretien, CHANGE heure heure VARCHAR(255) NOT NULL, CHANGE lien_meet lien_meet VARCHAR(255) NOT NULL, CHANGE localisation localisation VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien ADD CONSTRAINT FK_2B58D6DAA3662CC0 FOREIGN KEY (idCandidature) REFERENCES candidature (id_candidature) ON DELETE CASCADE
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
            ALTER TABLE entretien ADD CONSTRAINT entretein_candidature FOREIGN KEY (idCandidature) REFERENCES candidature (id_candidature) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe CHANGE id id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe_employe DROP FOREIGN KEY employe_id_fk
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe_employe DROP FOREIGN KEY equipe_employe_ibfk_2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe_employe DROP FOREIGN KEY employe_id_fk
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX employe_id_fk ON equipe_employe
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2004CF0526997AC9 ON equipe_employe (id_employe)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe_employe ADD CONSTRAINT equipe_employe_ibfk_2 FOREIGN KEY (id_employe) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe_employe ADD CONSTRAINT employe_id_fk FOREIGN KEY (id_employe) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation MODIFY idEvaluation INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation DROP FOREIGN KEY EntretienEval
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON evaluation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evaluation DROP FOREIGN KEY EntretienEval
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
            ALTER TABLE evaluation ADD CONSTRAINT EntretienEval FOREIGN KEY (idEntretien) REFERENCES entretien (idEntretien) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formation ADD is_favorite TINYINT(1) NOT NULL, DROP isFavorite, CHANGE id id INT NOT NULL, CHANGE dateFormation date_formation VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY user_not
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY user_not
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification CHANGE id id INT NOT NULL, CHANGE reservation_id reservation_id INT NOT NULL, CHANGE message message LONGTEXT NOT NULL, CHANGE date date DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA5E5C27E9 FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX user_not ON notification
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BF5476CA5E5C27E9 ON notification (iduser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT user_not FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre ADD date_publication DATETIME NOT NULL, ADD date_expiration DATETIME NOT NULL, DROP datePublication, DROP dateExpiration, CHANGE id_offre id_offre INT NOT NULL, CHANGE description description LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY fk_participation_formation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY fk_participation_user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY fk_participation_formation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY fk_participation_user
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
            DROP INDEX fk_participation_formation ON participation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AB55E24FBCAA0AE9 ON participation (idFormation)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_participation_user ON participation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AB55E24FFE6E88D7 ON participation (idUser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT fk_participation_formation FOREIGN KEY (idFormation) REFERENCES formation (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT fk_participation_user FOREIGN KEY (idUser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet ADD date_debut DATE NOT NULL, ADD date_fin DATE NOT NULL, DROP dateDebut, DROP dateFin, CHANGE id id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet_equipe DROP FOREIGN KEY projet_equipe_ibfk_2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet_equipe CHANGE id_equipe id_equipe INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX id_equipe ON projet_equipe
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6E00A4327E0FF8 ON projet_equipe (id_equipe)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet_equipe ADD CONSTRAINT projet_equipe_ibfk_2 FOREIGN KEY (id_equipe) REFERENCES equipe (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY reservation_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY user_reservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY reservation_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY user_reservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation CHANGE id id INT NOT NULL, CHANGE id_ressource id_ressource INT DEFAULT NULL, CHANGE iduser iduser INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C8495513AAF963 FOREIGN KEY (id_ressource) REFERENCES ressource (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C849555E5C27E9 FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX id_ressource ON reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C8495513AAF963 ON reservation (id_ressource)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX user_reservation ON reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_42C849555E5C27E9 ON reservation (iduser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT reservation_ibfk_1 FOREIGN KEY (id_ressource) REFERENCES ressource (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT user_reservation FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
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
            ALTER TABLE tache ADD date_debut DATE NOT NULL, ADD date_fin DATE NOT NULL, DROP dateDebut, DROP dateFin, CHANGE id id INT NOT NULL, CHANGE idProjet idProjet INT DEFAULT NULL, CHANGE trello_board_id trello_board_id VARCHAR(255) NOT NULL
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
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B86B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B84103C75F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B86B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD dateCandidature DATETIME NOT NULL, ADD dateModification DATETIME DEFAULT NULL, DROP date_candidature, DROP date_modification, CHANGE id_candidature id_candidature INT AUTO_INCREMENT NOT NULL, CHANGE id_offre id_offre INT NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE lettre_motivation lettreMotivation VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT candidature_user FOREIGN KEY (id_user) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_e33bd3b86b3ca4b ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX candidature_user ON candidature (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_e33bd3b84103c75f ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_candidature_offre ON candidature (id_offre)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B84103C75F FOREIGN KEY (id_offre) REFERENCES offre (id_offre) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B86B3CA4B FOREIGN KEY (id_user) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien DROP FOREIGN KEY FK_2B58D6DAA3662CC0
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON entretien
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien DROP FOREIGN KEY FK_2B58D6DAA3662CC0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien ADD idEntretien INT AUTO_INCREMENT NOT NULL, DROP id_entretien, CHANGE heure heure TIME NOT NULL, CHANGE lien_meet lien_meet VARCHAR(255) DEFAULT NULL, CHANGE localisation localisation VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entretien ADD CONSTRAINT entretein_candidature FOREIGN KEY (idCandidature) REFERENCES candidature (id_candidature) ON UPDATE CASCADE ON DELETE CASCADE
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
            ALTER TABLE entretien ADD CONSTRAINT FK_2B58D6DAA3662CC0 FOREIGN KEY (idCandidature) REFERENCES candidature (id_candidature) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe_employe DROP FOREIGN KEY FK_2004CF0526997AC9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe_employe ADD CONSTRAINT employe_id_fk FOREIGN KEY (id_employe) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_2004cf0526997ac9 ON equipe_employe
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX employe_id_fk ON equipe_employe (id_employe)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipe_employe ADD CONSTRAINT FK_2004CF0526997AC9 FOREIGN KEY (id_employe) REFERENCES utilisateur (id) ON DELETE CASCADE
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
            ALTER TABLE evaluation ADD CONSTRAINT EntretienEval FOREIGN KEY (idEntretien) REFERENCES entretien (idEntretien) ON UPDATE CASCADE ON DELETE CASCADE
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
            ALTER TABLE notification CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE reservation_id reservation_id INT DEFAULT NULL, CHANGE message message TEXT DEFAULT NULL, CHANGE date date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT user_not FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_bf5476ca5e5c27e9 ON notification
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_not ON notification (iduser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA5E5C27E9 FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre ADD datePublication DATETIME NOT NULL, ADD dateExpiration DATETIME NOT NULL, DROP date_publication, DROP date_expiration, CHANGE id_offre id_offre INT AUTO_INCREMENT NOT NULL, CHANGE description description TEXT NOT NULL
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
            ALTER TABLE participation ADD CONSTRAINT fk_participation_formation FOREIGN KEY (idFormation) REFERENCES formation (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT fk_participation_user FOREIGN KEY (idUser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_ab55e24ffe6e88d7 ON participation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_participation_user ON participation (idUser)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_ab55e24fbcaa0ae9 ON participation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_participation_formation ON participation (idFormation)
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
            ALTER TABLE projet_equipe DROP FOREIGN KEY FK_6E00A4327E0FF8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet_equipe CHANGE id_equipe id_equipe INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_6e00a4327e0ff8 ON projet_equipe
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX id_equipe ON projet_equipe (id_equipe)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE projet_equipe ADD CONSTRAINT FK_6E00A4327E0FF8 FOREIGN KEY (id_equipe) REFERENCES equipe (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495513AAF963
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C849555E5C27E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495513AAF963
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C849555E5C27E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE id_ressource id_ressource INT NOT NULL, CHANGE iduser iduser INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT reservation_ibfk_1 FOREIGN KEY (id_ressource) REFERENCES ressource (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT user_reservation FOREIGN KEY (iduser) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_42c8495513aaf963 ON reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX id_ressource ON reservation (id_ressource)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_42c849555e5c27e9 ON reservation
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_reservation ON reservation (iduser)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C8495513AAF963 FOREIGN KEY (id_ressource) REFERENCES ressource (id) ON DELETE CASCADE
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
            ALTER TABLE tache ADD dateDebut DATE NOT NULL, ADD dateFin DATE NOT NULL, DROP date_debut, DROP date_fin, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE trello_board_id trello_board_id VARCHAR(255) DEFAULT NULL, CHANGE idProjet idProjet INT NOT NULL
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
