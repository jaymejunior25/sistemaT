-- Type: tipologin

-- DROP TYPE IF EXISTS public.tipologin;

CREATE TYPE public.tipologin AS ENUM
    ('admin', 'normal');

ALTER TYPE public.tipologin
    OWNER TO postgres;
