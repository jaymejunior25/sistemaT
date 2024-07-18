-- Table: public.laboratorio

-- DROP TABLE IF EXISTS public.laboratorio;

CREATE TABLE IF NOT EXISTS public.laboratorio
(
    digito character varying(5) COLLATE pg_catalog."default" NOT NULL DEFAULT 0,
    nome character varying(100) COLLATE pg_catalog."default" NOT NULL,
    id integer NOT NULL DEFAULT nextval('laboratorio_id_seq'::regclass),
    CONSTRAINT laboratorio_pkey PRIMARY KEY (id)
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public.laboratorio
    OWNER to postgres;