-- =========================================================
-- Esquema de base de datos - Bufete de abogados
-- =========================================================

CREATE TABLE clientes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula          VARCHAR(20)  NOT NULL UNIQUE,
    nombre          VARCHAR(150) NOT NULL,
    telefono        VARCHAR(30)  NULL,
    email           VARCHAR(150) NULL,
    direccion       VARCHAR(255) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      DATETIME NULL,
    INDEX idx_clientes_cedula (cedula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE abogados (
    id                     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula                 VARCHAR(20)  NOT NULL UNIQUE,
    nombre                 VARCHAR(150) NOT NULL,
    telefono               VARCHAR(30)  NULL,
    email                  VARCHAR(150) NULL,
    tarjeta_profesional    VARCHAR(50)  NULL,
    created_at             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at             DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE casos (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_expediente   VARCHAR(50) NOT NULL UNIQUE,
    cliente_id          BIGINT UNSIGNED NOT NULL,
    fecha_inicio        DATE NOT NULL,
    fecha_archivo       DATE NULL,
    estado              ENUM('en_tramite', 'archivado', 'suspendido', 'finalizado')
                         NOT NULL DEFAULT 'en_tramite',
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          DATETIME NULL,
    CONSTRAINT fk_casos_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    INDEX idx_casos_expediente (numero_expediente),
    INDEX idx_casos_cliente (cliente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE caso_abogado (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    caso_id     BIGINT UNSIGNED NOT NULL,
    abogado_id  BIGINT UNSIGNED NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ca_caso
        FOREIGN KEY (caso_id) REFERENCES casos(id),
    CONSTRAINT fk_ca_abogado
        FOREIGN KEY (abogado_id) REFERENCES abogados(id),
    UNIQUE KEY uq_caso_abogado (caso_id, abogado_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;