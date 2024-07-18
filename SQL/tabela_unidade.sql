-- Table: public.unidadehemopa

-- DROP TABLE IF EXISTS public.unidadehemopa;

CREATE TABLE IF NOT EXISTS public.unidadehemopa
(
    id integer NOT NULL DEFAULT nextval('unidadehemopa_id_seq'::regclass),
    nome character varying(100) COLLATE pg_catalog."default" NOT NULL,
    data_criacao timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unidadehemopa_pkey PRIMARY KEY (id)
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public.unidadehemopa
    OWNER to postgres;