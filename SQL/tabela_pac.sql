-- Table: public.pacotes

-- DROP TABLE IF EXISTS public.pacotes;

CREATE TABLE IF NOT EXISTS public.pacotes
(
    id integer NOT NULL DEFAULT nextval('pacotes_id_seq'::regclass),
    descricao character varying(255) COLLATE pg_catalog."default" NOT NULL,
    status character varying(50) COLLATE pg_catalog."default" NOT NULL DEFAULT 'cadastrado'::character varying,
    data_envio timestamp without time zone,
    data_recebimento timestamp without time zone,
    unidade_envio_id integer,
    usuario_envio_id integer,
    usuario_recebimento_id integer,
    codigobarras character varying(254) COLLATE pg_catalog."default" NOT NULL DEFAULT 1,
    unidade_cadastro_id integer,
    usuario_cadastro_id integer,
    data_cadastro timestamp with time zone NOT NULL DEFAULT now(),
    lab_id integer,
    CONSTRAINT pacotes_pkey PRIMARY KEY (id),
    CONSTRAINT "pacotes_lab_id _fkey" FOREIGN KEY (lab_id)
        REFERENCES public.laboratorio (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        DEFERRABLE INITIALLY DEFERRED,
    CONSTRAINT "pacotes_unidade_cadastro_id _fkey" FOREIGN KEY (unidade_cadastro_id)
        REFERENCES public.unidadehemopa (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        DEFERRABLE INITIALLY DEFERRED,
    CONSTRAINT pacotes_unidade_envio_id_fkey FOREIGN KEY (unidade_envio_id)
        REFERENCES public.unidadehemopa (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        DEFERRABLE INITIALLY DEFERRED,
    CONSTRAINT "pacotes_usuario_cadastro_id _fkey" FOREIGN KEY (usuario_cadastro_id)
        REFERENCES public.usuarios (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        DEFERRABLE INITIALLY DEFERRED,
    CONSTRAINT pacotes_usuario_envio_id_fkey FOREIGN KEY (usuario_envio_id)
        REFERENCES public.usuarios (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        DEFERRABLE INITIALLY DEFERRED,
    CONSTRAINT pacotes_usuario_recevimento_id_fkey FOREIGN KEY (usuario_recebimento_id)
        REFERENCES public.usuarios (id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
        DEFERRABLE INITIALLY DEFERRED
)

TABLESPACE pg_default;

ALTER TABLE IF EXISTS public.pacotes
    OWNER to postgres;