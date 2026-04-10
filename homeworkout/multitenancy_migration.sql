CREATE TABLE IF NOT EXISTS tenants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    stato ENUM('active', 'suspended') DEFAULT 'active',
    logo_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO tenants (id, nome, slug, stato)
VALUES (1, 'HomeWorkout Demo', 'demo-homeworkout', 'active');

ALTER TABLE utenti ADD COLUMN tenant_id INT NULL;
ALTER TABLE utenti ADD COLUMN refresh_token VARCHAR(128) NULL;
ALTER TABLE utenti ADD COLUMN refresh_token_scadenza DATETIME NULL;
ALTER TABLE utenti ADD COLUMN creato_il TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
UPDATE utenti SET tenant_id = 1 WHERE tenant_id IS NULL;
ALTER TABLE utenti ADD CONSTRAINT fk_utenti_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL;

ALTER TABLE quiz_risposte ADD COLUMN tenant_id INT NULL;
ALTER TABLE quiz_risposte ADD UNIQUE KEY uniq_quiz_tenant_utente (tenant_id, utente_id);
ALTER TABLE quiz_risposte ADD CONSTRAINT fk_quiz_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL;

ALTER TABLE piani_allenamento ADD COLUMN tenant_id INT NULL;
ALTER TABLE piani_allenamento ADD KEY idx_piani_tenant_utente (tenant_id, utente_id);
ALTER TABLE piani_allenamento ADD CONSTRAINT fk_piani_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL;

ALTER TABLE esercizi_piano ADD COLUMN tenant_id INT NULL;
ALTER TABLE esercizi_piano ADD CONSTRAINT fk_esercizi_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL;

ALTER TABLE progressi_dettaglio ADD COLUMN tenant_id INT NULL;
ALTER TABLE progressi_dettaglio ADD CONSTRAINT fk_progressi_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL;

ALTER TABLE statistiche_esercizio ADD COLUMN tenant_id INT NULL;
ALTER TABLE statistiche_esercizio ADD UNIQUE KEY uniq_stat_tenant_utente_nome (tenant_id, utente_id, nome_esercizio);
ALTER TABLE statistiche_esercizio ADD CONSTRAINT fk_stat_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL;

ALTER TABLE amicizie ADD COLUMN tenant_id INT NULL;
ALTER TABLE amicizie ADD CONSTRAINT fk_amicizie_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL;

ALTER TABLE periodi_riposo ADD COLUMN tenant_id INT NULL;
ALTER TABLE periodi_riposo ADD CONSTRAINT fk_riposo_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL;

ALTER TABLE feedback_allenamento ADD COLUMN tenant_id INT NULL;
ALTER TABLE feedback_allenamento ADD CONSTRAINT fk_feedback_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL;

INSERT INTO ruoli (id, nome_ruolo, descrizione)
VALUES (4, 'super_admin', 'Super amministratore multi-tenant')
ON DUPLICATE KEY UPDATE nome_ruolo = VALUES(nome_ruolo), descrizione = VALUES(descrizione);
