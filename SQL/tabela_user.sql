-- Table: public.usuarios

-- DROP TABLE IF EXISTS public.usuarios;

CREATE TABLE IF NOT EXISTS public.usuarios
(
    id integer NOT NULL DEFAULT nextval('usuarios_id_seq'::regclass),
    nome character varying(100) COLLATE pg_catalog."default" NOT NULL,
    matricula character varying(10) COLLATE pg_catalog."default" NOT NULL,
    senha character varying(100) COLLATE pg_catalog."default" NOT NULL,
    data_criacao timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usuario character varying(100) COLLATE pg_catalog."default" NOT NULL,
    tipoconta tipologin NOT NULL DEFAULT 'normal'::tipologin,
    unidade_id integer,
    CONSTRAINT usuarios_pkey PRIMARY KEY (id),
    CONSTRAINT usuarios_matricula_key UNIQUE (matricula),
    CONSTRAINT "usuarios_unidade_id _fkey" FOREIGN KEY (unidade_id)
        REFERENCES public.unidadehemopa (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        DEFERRABLE INITIALLY DEFERRED
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public.usuarios
    OWNER to postgres;