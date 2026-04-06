-- insights_setup.sql
-- Einmalig auf dem MSSQL-Server ausführen

IF NOT EXISTS (
    SELECT * FROM sysobjects WHERE name = 'insights' AND xtype = 'U'
)
CREATE TABLE insights (
    id           INT IDENTITY(1,1) PRIMARY KEY,
    slug         NVARCHAR(120)  NOT NULL,
    title_de     NVARCHAR(200)  NOT NULL,
    title_en     NVARCHAR(200)  NULL,
    body_de      NVARCHAR(MAX)  NOT NULL,
    body_en      NVARCHAR(MAX)  NULL,
    category     NVARCHAR(60)   NULL,
    attachments  NVARCHAR(MAX)  NULL,   -- JSON: [{type,url,caption}]
    published_at DATETIME2      NULL,   -- NULL = Entwurf
    created_at   DATETIME2      NOT NULL DEFAULT GETDATE(),
    CONSTRAINT UQ_insights_slug UNIQUE (slug)
);
