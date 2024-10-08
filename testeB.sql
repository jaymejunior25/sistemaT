PGDMP          
            |            SBSENVIO    16.3    16.3 \    �           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                      false            �           0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                      false            �           0    0 
   SEARCHPATH 
   SEARCHPATH     8   SELECT pg_catalog.set_config('search_path', '', false);
                      false            �           1262    16555    SBSENVIO    DATABASE     �   CREATE DATABASE "SBSENVIO" WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'Portuguese_Brazil.1252';
    DROP DATABASE "SBSENVIO";
                postgres    false                        2615    16396    pgagent    SCHEMA        CREATE SCHEMA pgagent;
    DROP SCHEMA pgagent;
                postgres    false            �           0    0    SCHEMA pgagent    COMMENT     6   COMMENT ON SCHEMA pgagent IS 'pgAgent system tables';
                   postgres    false    8                        3079    16384 	   adminpack 	   EXTENSION     A   CREATE EXTENSION IF NOT EXISTS adminpack WITH SCHEMA pg_catalog;
    DROP EXTENSION adminpack;
                   false            �           0    0    EXTENSION adminpack    COMMENT     M   COMMENT ON EXTENSION adminpack IS 'administrative functions for PostgreSQL';
                        false    2                        3079    16397    pgagent 	   EXTENSION     <   CREATE EXTENSION IF NOT EXISTS pgagent WITH SCHEMA pgagent;
    DROP EXTENSION pgagent;
                   false    8            �           0    0    EXTENSION pgagent    COMMENT     >   COMMENT ON EXTENSION pgagent IS 'A PostgreSQL job scheduler';
                        false    3            �           1247    16621 	   tipologin    TYPE     D   CREATE TYPE public.tipologin AS ENUM (
    'admin',
    'normal'
);
    DROP TYPE public.tipologin;
       public          postgres    false            �            1259    24840    laboratorio    TABLE     �   CREATE TABLE public.laboratorio (
    digito character varying(5) DEFAULT 0 NOT NULL,
    nome character varying(100) NOT NULL,
    id integer NOT NULL
);
    DROP TABLE public.laboratorio;
       public         heap    postgres    false            �            1259    24839    laboratorio_id_seq    SEQUENCE     �   CREATE SEQUENCE public.laboratorio_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 )   DROP SEQUENCE public.laboratorio_id_seq;
       public          postgres    false    242            �           0    0    laboratorio_id_seq    SEQUENCE OWNED BY     I   ALTER SEQUENCE public.laboratorio_id_seq OWNED BY public.laboratorio.id;
          public          postgres    false    241            �            1259    24894    lotes    TABLE     O  CREATE TABLE public.lotes (
    id integer NOT NULL,
    protocolo character varying(50) NOT NULL,
    cadastrado_por integer NOT NULL,
    unidade_cadastro_id integer NOT NULL,
    data_cadastro timestamp without time zone DEFAULT now() NOT NULL,
    amostras_doador boolean NOT NULL,
    amostras_paciente boolean NOT NULL,
    amostras_transplante boolean NOT NULL,
    amostras_outros boolean NOT NULL,
    observacoes text,
    data_envio timestamp without time zone,
    usuario_envio_id integer,
    data_recebimento timestamp without time zone,
    usuario_recebimento_id integer
);
    DROP TABLE public.lotes;
       public         heap    postgres    false            �            1259    24893    lotes_id_seq    SEQUENCE     �   CREATE SEQUENCE public.lotes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 #   DROP SEQUENCE public.lotes_id_seq;
       public          postgres    false    245            �           0    0    lotes_id_seq    SEQUENCE OWNED BY     =   ALTER SEQUENCE public.lotes_id_seq OWNED BY public.lotes.id;
          public          postgres    false    244            �            1259    24916    lotes_laboratorios    TABLE     �   CREATE TABLE public.lotes_laboratorios (
    id integer NOT NULL,
    lote_id integer NOT NULL,
    laboratorio_id integer NOT NULL,
    numero_amostras integer NOT NULL
);
 &   DROP TABLE public.lotes_laboratorios;
       public         heap    postgres    false            �            1259    24915    lotes_laboratorios_id_seq    SEQUENCE     �   CREATE SEQUENCE public.lotes_laboratorios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 0   DROP SEQUENCE public.lotes_laboratorios_id_seq;
       public          postgres    false    247            �           0    0    lotes_laboratorios_id_seq    SEQUENCE OWNED BY     W   ALTER SEQUENCE public.lotes_laboratorios_id_seq OWNED BY public.lotes_laboratorios.id;
          public          postgres    false    246            �            1259    16591    pacotes    TABLE     N  CREATE TABLE public.pacotes (
    id integer NOT NULL,
    descricao character varying(255) NOT NULL,
    status character varying(50) DEFAULT 'cadastrado'::character varying NOT NULL,
    data_envio timestamp without time zone,
    data_recebimento timestamp without time zone,
    unidade_envio_id integer,
    usuario_envio_id integer,
    usuario_recebimento_id integer,
    codigobarras character varying(254) DEFAULT 1 NOT NULL,
    unidade_cadastro_id integer,
    usuario_cadastro_id integer,
    data_cadastro timestamp with time zone DEFAULT now() NOT NULL,
    lab_id integer
);
    DROP TABLE public.pacotes;
       public         heap    postgres    false            �            1259    16590    pacotes_id_seq    SEQUENCE     �   CREATE SEQUENCE public.pacotes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 %   DROP SEQUENCE public.pacotes_id_seq;
       public          postgres    false    240            �           0    0    pacotes_id_seq    SEQUENCE OWNED BY     A   ALTER SEQUENCE public.pacotes_id_seq OWNED BY public.pacotes.id;
          public          postgres    false    239            �            1259    24855    remessas    TABLE     4  CREATE TABLE public.remessas (
    id integer DEFAULT nextval('public.pacotes_id_seq'::regclass) NOT NULL,
    codigo character varying(8) NOT NULL,
    data_envio timestamp without time zone,
    data_recebimento timestamp without time zone,
    unidade_envio_id integer,
    usuario_envio_id integer,
    usuario_recebimento_id integer,
    unidade_cadastro_id integer,
    usuario_cadastro_id integer,
    data_cadastro timestamp with time zone DEFAULT now() NOT NULL,
    tipos_amostras json NOT NULL,
    numero_tubos integer NOT NULL,
    observacao text
);
    DROP TABLE public.remessas;
       public         heap    postgres    false    239            �            1259    16567    unidade    TABLE     �   CREATE TABLE public.unidade (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    data_criacao timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);
    DROP TABLE public.unidade;
       public         heap    postgres    false            �            1259    16566    unidade_id_seq    SEQUENCE     �   CREATE SEQUENCE public.unidade_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 %   DROP SEQUENCE public.unidade_id_seq;
       public          postgres    false    236            �           0    0    unidade_id_seq    SEQUENCE OWNED BY     A   ALTER SEQUENCE public.unidade_id_seq OWNED BY public.unidade.id;
          public          postgres    false    235            �            1259    16578    unidadehemopa    TABLE     �   CREATE TABLE public.unidadehemopa (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    data_criacao timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);
 !   DROP TABLE public.unidadehemopa;
       public         heap    postgres    false            �            1259    16577    unidadehemopa_id_seq    SEQUENCE     �   CREATE SEQUENCE public.unidadehemopa_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 +   DROP SEQUENCE public.unidadehemopa_id_seq;
       public          postgres    false    238            �           0    0    unidadehemopa_id_seq    SEQUENCE OWNED BY     M   ALTER SEQUENCE public.unidadehemopa_id_seq OWNED BY public.unidadehemopa.id;
          public          postgres    false    237            �            1259    16557    usuarios    TABLE     �  CREATE TABLE public.usuarios (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    matricula character varying(10) NOT NULL,
    senha character varying(100) NOT NULL,
    data_criacao timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    usuario character varying(100) NOT NULL,
    tipoconta public.tipologin DEFAULT 'normal'::public.tipologin NOT NULL,
    unidade_id integer
);
    DROP TABLE public.usuarios;
       public         heap    postgres    false    914    914            �            1259    16556    usuarios_id_seq    SEQUENCE     �   CREATE SEQUENCE public.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 &   DROP SEQUENCE public.usuarios_id_seq;
       public          postgres    false    234            �           0    0    usuarios_id_seq    SEQUENCE OWNED BY     C   ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;
          public          postgres    false    233            �           2604    24843    laboratorio id    DEFAULT     p   ALTER TABLE ONLY public.laboratorio ALTER COLUMN id SET DEFAULT nextval('public.laboratorio_id_seq'::regclass);
 =   ALTER TABLE public.laboratorio ALTER COLUMN id DROP DEFAULT;
       public          postgres    false    242    241    242            �           2604    24897    lotes id    DEFAULT     d   ALTER TABLE ONLY public.lotes ALTER COLUMN id SET DEFAULT nextval('public.lotes_id_seq'::regclass);
 7   ALTER TABLE public.lotes ALTER COLUMN id DROP DEFAULT;
       public          postgres    false    245    244    245            �           2604    24919    lotes_laboratorios id    DEFAULT     ~   ALTER TABLE ONLY public.lotes_laboratorios ALTER COLUMN id SET DEFAULT nextval('public.lotes_laboratorios_id_seq'::regclass);
 D   ALTER TABLE public.lotes_laboratorios ALTER COLUMN id DROP DEFAULT;
       public          postgres    false    246    247    247            �           2604    16594 
   pacotes id    DEFAULT     h   ALTER TABLE ONLY public.pacotes ALTER COLUMN id SET DEFAULT nextval('public.pacotes_id_seq'::regclass);
 9   ALTER TABLE public.pacotes ALTER COLUMN id DROP DEFAULT;
       public          postgres    false    240    239    240            �           2604    16570 
   unidade id    DEFAULT     h   ALTER TABLE ONLY public.unidade ALTER COLUMN id SET DEFAULT nextval('public.unidade_id_seq'::regclass);
 9   ALTER TABLE public.unidade ALTER COLUMN id DROP DEFAULT;
       public          postgres    false    235    236    236            �           2604    16581    unidadehemopa id    DEFAULT     t   ALTER TABLE ONLY public.unidadehemopa ALTER COLUMN id SET DEFAULT nextval('public.unidadehemopa_id_seq'::regclass);
 ?   ALTER TABLE public.unidadehemopa ALTER COLUMN id DROP DEFAULT;
       public          postgres    false    238    237    238            �           2604    16560    usuarios id    DEFAULT     j   ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);
 :   ALTER TABLE public.usuarios ALTER COLUMN id DROP DEFAULT;
       public          postgres    false    233    234    234            o          0    16398    pga_jobagent 
   TABLE DATA           I   COPY pgagent.pga_jobagent (jagpid, jaglogintime, jagstation) FROM stdin;
    pgagent          postgres    false    218   �t       p          0    16407    pga_jobclass 
   TABLE DATA           7   COPY pgagent.pga_jobclass (jclid, jclname) FROM stdin;
    pgagent          postgres    false    220   �t       q          0    16417    pga_job 
   TABLE DATA           �   COPY pgagent.pga_job (jobid, jobjclid, jobname, jobdesc, jobhostagent, jobenabled, jobcreated, jobchanged, jobagentid, jobnextrun, joblastrun) FROM stdin;
    pgagent          postgres    false    222   �t       s          0    16465    pga_schedule 
   TABLE DATA           �   COPY pgagent.pga_schedule (jscid, jscjobid, jscname, jscdesc, jscenabled, jscstart, jscend, jscminutes, jschours, jscweekdays, jscmonthdays, jscmonths) FROM stdin;
    pgagent          postgres    false    226   �t       t          0    16493    pga_exception 
   TABLE DATA           J   COPY pgagent.pga_exception (jexid, jexscid, jexdate, jextime) FROM stdin;
    pgagent          postgres    false    228   �t       u          0    16507 
   pga_joblog 
   TABLE DATA           X   COPY pgagent.pga_joblog (jlgid, jlgjobid, jlgstatus, jlgstart, jlgduration) FROM stdin;
    pgagent          postgres    false    230   u       r          0    16441    pga_jobstep 
   TABLE DATA           �   COPY pgagent.pga_jobstep (jstid, jstjobid, jstname, jstdesc, jstenabled, jstkind, jstcode, jstconnstr, jstdbname, jstonerror, jscnextrun) FROM stdin;
    pgagent          postgres    false    224   1u       v          0    16523    pga_jobsteplog 
   TABLE DATA           |   COPY pgagent.pga_jobsteplog (jslid, jsljlgid, jsljstid, jslstatus, jslresult, jslstart, jslduration, jsloutput) FROM stdin;
    pgagent          postgres    false    232   Nu       �          0    24840    laboratorio 
   TABLE DATA           7   COPY public.laboratorio (digito, nome, id) FROM stdin;
    public          postgres    false    242   ku       �          0    24894    lotes 
   TABLE DATA             COPY public.lotes (id, protocolo, cadastrado_por, unidade_cadastro_id, data_cadastro, amostras_doador, amostras_paciente, amostras_transplante, amostras_outros, observacoes, data_envio, usuario_envio_id, data_recebimento, usuario_recebimento_id) FROM stdin;
    public          postgres    false    245   �u       �          0    24916    lotes_laboratorios 
   TABLE DATA           Z   COPY public.lotes_laboratorios (id, lote_id, laboratorio_id, numero_amostras) FROM stdin;
    public          postgres    false    247   �x       �          0    16591    pacotes 
   TABLE DATA           �   COPY public.pacotes (id, descricao, status, data_envio, data_recebimento, unidade_envio_id, usuario_envio_id, usuario_recebimento_id, codigobarras, unidade_cadastro_id, usuario_cadastro_id, data_cadastro, lab_id) FROM stdin;
    public          postgres    false    240   z       �          0    24855    remessas 
   TABLE DATA           �   COPY public.remessas (id, codigo, data_envio, data_recebimento, unidade_envio_id, usuario_envio_id, usuario_recebimento_id, unidade_cadastro_id, usuario_cadastro_id, data_cadastro, tipos_amostras, numero_tubos, observacao) FROM stdin;
    public          postgres    false    243   �       �          0    16567    unidade 
   TABLE DATA           9   COPY public.unidade (id, nome, data_criacao) FROM stdin;
    public          postgres    false    236   ��       �          0    16578    unidadehemopa 
   TABLE DATA           ?   COPY public.unidadehemopa (id, nome, data_criacao) FROM stdin;
    public          postgres    false    238   ��       �          0    16557    usuarios 
   TABLE DATA           l   COPY public.usuarios (id, nome, matricula, senha, data_criacao, usuario, tipoconta, unidade_id) FROM stdin;
    public          postgres    false    234   ؘ       �           0    0    laboratorio_id_seq    SEQUENCE SET     A   SELECT pg_catalog.setval('public.laboratorio_id_seq', 17, true);
          public          postgres    false    241            �           0    0    lotes_id_seq    SEQUENCE SET     ;   SELECT pg_catalog.setval('public.lotes_id_seq', 35, true);
          public          postgres    false    244            �           0    0    lotes_laboratorios_id_seq    SEQUENCE SET     H   SELECT pg_catalog.setval('public.lotes_laboratorios_id_seq', 80, true);
          public          postgres    false    246            �           0    0    pacotes_id_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('public.pacotes_id_seq', 402, true);
          public          postgres    false    239            �           0    0    unidade_id_seq    SEQUENCE SET     =   SELECT pg_catalog.setval('public.unidade_id_seq', 1, false);
          public          postgres    false    235            �           0    0    unidadehemopa_id_seq    SEQUENCE SET     C   SELECT pg_catalog.setval('public.unidadehemopa_id_seq', 15, true);
          public          postgres    false    237            �           0    0    usuarios_id_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('public.usuarios_id_seq', 37, true);
          public          postgres    false    233            �           2606    24845    laboratorio laboratorio_pkey 
   CONSTRAINT     Z   ALTER TABLE ONLY public.laboratorio
    ADD CONSTRAINT laboratorio_pkey PRIMARY KEY (id);
 F   ALTER TABLE ONLY public.laboratorio DROP CONSTRAINT laboratorio_pkey;
       public            postgres    false    242            �           2606    24921 *   lotes_laboratorios lotes_laboratorios_pkey 
   CONSTRAINT     h   ALTER TABLE ONLY public.lotes_laboratorios
    ADD CONSTRAINT lotes_laboratorios_pkey PRIMARY KEY (id);
 T   ALTER TABLE ONLY public.lotes_laboratorios DROP CONSTRAINT lotes_laboratorios_pkey;
       public            postgres    false    247            �           2606    24902    lotes lotes_pkey 
   CONSTRAINT     N   ALTER TABLE ONLY public.lotes
    ADD CONSTRAINT lotes_pkey PRIMARY KEY (id);
 :   ALTER TABLE ONLY public.lotes DROP CONSTRAINT lotes_pkey;
       public            postgres    false    245            �           2606    24904    lotes lotes_protocolo_key 
   CONSTRAINT     Y   ALTER TABLE ONLY public.lotes
    ADD CONSTRAINT lotes_protocolo_key UNIQUE (protocolo);
 C   ALTER TABLE ONLY public.lotes DROP CONSTRAINT lotes_protocolo_key;
       public            postgres    false    245            �           2606    16597    pacotes pacotes_pkey 
   CONSTRAINT     R   ALTER TABLE ONLY public.pacotes
    ADD CONSTRAINT pacotes_pkey PRIMARY KEY (id);
 >   ALTER TABLE ONLY public.pacotes DROP CONSTRAINT pacotes_pkey;
       public            postgres    false    240            �           2606    24865    remessas remessas_codigo_key 
   CONSTRAINT     Y   ALTER TABLE ONLY public.remessas
    ADD CONSTRAINT remessas_codigo_key UNIQUE (codigo);
 F   ALTER TABLE ONLY public.remessas DROP CONSTRAINT remessas_codigo_key;
       public            postgres    false    243            �           2606    24863    remessas remessas_pkey 
   CONSTRAINT     T   ALTER TABLE ONLY public.remessas
    ADD CONSTRAINT remessas_pkey PRIMARY KEY (id);
 @   ALTER TABLE ONLY public.remessas DROP CONSTRAINT remessas_pkey;
       public            postgres    false    243            �           2606    16573    unidade unidade_pkey 
   CONSTRAINT     R   ALTER TABLE ONLY public.unidade
    ADD CONSTRAINT unidade_pkey PRIMARY KEY (id);
 >   ALTER TABLE ONLY public.unidade DROP CONSTRAINT unidade_pkey;
       public            postgres    false    236            �           2606    16584     unidadehemopa unidadehemopa_pkey 
   CONSTRAINT     ^   ALTER TABLE ONLY public.unidadehemopa
    ADD CONSTRAINT unidadehemopa_pkey PRIMARY KEY (id);
 J   ALTER TABLE ONLY public.unidadehemopa DROP CONSTRAINT unidadehemopa_pkey;
       public            postgres    false    238            �           2606    16565    usuarios usuarios_matricula_key 
   CONSTRAINT     _   ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_matricula_key UNIQUE (matricula);
 I   ALTER TABLE ONLY public.usuarios DROP CONSTRAINT usuarios_matricula_key;
       public            postgres    false    234            �           2606    16563    usuarios usuarios_pkey 
   CONSTRAINT     T   ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);
 @   ALTER TABLE ONLY public.usuarios DROP CONSTRAINT usuarios_pkey;
       public            postgres    false    234            �           2606    24905    lotes lotes_cadastrado_por_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.lotes
    ADD CONSTRAINT lotes_cadastrado_por_fkey FOREIGN KEY (cadastrado_por) REFERENCES public.usuarios(id);
 I   ALTER TABLE ONLY public.lotes DROP CONSTRAINT lotes_cadastrado_por_fkey;
       public          postgres    false    234    245    4812            �           2606    24927 9   lotes_laboratorios lotes_laboratorios_laboratorio_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.lotes_laboratorios
    ADD CONSTRAINT lotes_laboratorios_laboratorio_id_fkey FOREIGN KEY (laboratorio_id) REFERENCES public.laboratorio(id);
 c   ALTER TABLE ONLY public.lotes_laboratorios DROP CONSTRAINT lotes_laboratorios_laboratorio_id_fkey;
       public          postgres    false    242    4820    247            �           2606    24922 2   lotes_laboratorios lotes_laboratorios_lote_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.lotes_laboratorios
    ADD CONSTRAINT lotes_laboratorios_lote_id_fkey FOREIGN KEY (lote_id) REFERENCES public.lotes(id);
 \   ALTER TABLE ONLY public.lotes_laboratorios DROP CONSTRAINT lotes_laboratorios_lote_id_fkey;
       public          postgres    false    245    247    4826            �           2606    24910 $   lotes lotes_unidade_cadastro_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.lotes
    ADD CONSTRAINT lotes_unidade_cadastro_id_fkey FOREIGN KEY (unidade_cadastro_id) REFERENCES public.unidadehemopa(id);
 N   ALTER TABLE ONLY public.lotes DROP CONSTRAINT lotes_unidade_cadastro_id_fkey;
       public          postgres    false    4816    245    238            �           2606    24942 !   lotes lotes_usuario_envio_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.lotes
    ADD CONSTRAINT lotes_usuario_envio_id_fkey FOREIGN KEY (usuario_envio_id) REFERENCES public.usuarios(id);
 K   ALTER TABLE ONLY public.lotes DROP CONSTRAINT lotes_usuario_envio_id_fkey;
       public          postgres    false    4812    234    245            �           2606    24947 '   lotes lotes_usuario_recebimento_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.lotes
    ADD CONSTRAINT lotes_usuario_recebimento_id_fkey FOREIGN KEY (usuario_recebimento_id) REFERENCES public.usuarios(id);
 Q   ALTER TABLE ONLY public.lotes DROP CONSTRAINT lotes_usuario_recebimento_id_fkey;
       public          postgres    false    4812    234    245            �           2606    24846    pacotes pacotes_lab_id _fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.pacotes
    ADD CONSTRAINT "pacotes_lab_id _fkey" FOREIGN KEY (lab_id) REFERENCES public.laboratorio(id) DEFERRABLE INITIALLY DEFERRED;
 H   ALTER TABLE ONLY public.pacotes DROP CONSTRAINT "pacotes_lab_id _fkey";
       public          postgres    false    240    242    4820            �           2606    16654 )   pacotes pacotes_unidade_cadastro_id _fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.pacotes
    ADD CONSTRAINT "pacotes_unidade_cadastro_id _fkey" FOREIGN KEY (unidade_cadastro_id) REFERENCES public.unidadehemopa(id) DEFERRABLE INITIALLY DEFERRED;
 U   ALTER TABLE ONLY public.pacotes DROP CONSTRAINT "pacotes_unidade_cadastro_id _fkey";
       public          postgres    false    238    240    4816            �           2606    16631 %   pacotes pacotes_unidade_envio_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.pacotes
    ADD CONSTRAINT pacotes_unidade_envio_id_fkey FOREIGN KEY (unidade_envio_id) REFERENCES public.unidadehemopa(id) DEFERRABLE INITIALLY DEFERRED;
 O   ALTER TABLE ONLY public.pacotes DROP CONSTRAINT pacotes_unidade_envio_id_fkey;
       public          postgres    false    240    238    4816            �           2606    16659 )   pacotes pacotes_usuario_cadastro_id _fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.pacotes
    ADD CONSTRAINT "pacotes_usuario_cadastro_id _fkey" FOREIGN KEY (usuario_cadastro_id) REFERENCES public.usuarios(id) DEFERRABLE INITIALLY DEFERRED;
 U   ALTER TABLE ONLY public.pacotes DROP CONSTRAINT "pacotes_usuario_cadastro_id _fkey";
       public          postgres    false    4812    240    234            �           2606    16636 %   pacotes pacotes_usuario_envio_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.pacotes
    ADD CONSTRAINT pacotes_usuario_envio_id_fkey FOREIGN KEY (usuario_envio_id) REFERENCES public.usuarios(id) DEFERRABLE INITIALLY DEFERRED;
 O   ALTER TABLE ONLY public.pacotes DROP CONSTRAINT pacotes_usuario_envio_id_fkey;
       public          postgres    false    4812    234    240            �           2606    16641 +   pacotes pacotes_usuario_recevimento_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.pacotes
    ADD CONSTRAINT pacotes_usuario_recevimento_id_fkey FOREIGN KEY (usuario_recebimento_id) REFERENCES public.usuarios(id) DEFERRABLE INITIALLY DEFERRED;
 U   ALTER TABLE ONLY public.pacotes DROP CONSTRAINT pacotes_usuario_recevimento_id_fkey;
       public          postgres    false    234    4812    240            �           2606    24866 +   remessas remessas_unidade_cadastro_id _fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.remessas
    ADD CONSTRAINT "remessas_unidade_cadastro_id _fkey" FOREIGN KEY (unidade_cadastro_id) REFERENCES public.unidadehemopa(id) DEFERRABLE INITIALLY DEFERRED;
 W   ALTER TABLE ONLY public.remessas DROP CONSTRAINT "remessas_unidade_cadastro_id _fkey";
       public          postgres    false    4816    238    243            �           2606    24871 '   remessas remessas_unidade_envio_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.remessas
    ADD CONSTRAINT remessas_unidade_envio_id_fkey FOREIGN KEY (unidade_envio_id) REFERENCES public.unidadehemopa(id) DEFERRABLE INITIALLY DEFERRED;
 Q   ALTER TABLE ONLY public.remessas DROP CONSTRAINT remessas_unidade_envio_id_fkey;
       public          postgres    false    238    4816    243            �           2606    24876 +   remessas remessas_usuario_cadastro_id _fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.remessas
    ADD CONSTRAINT "remessas_usuario_cadastro_id _fkey" FOREIGN KEY (usuario_cadastro_id) REFERENCES public.usuarios(id) DEFERRABLE INITIALLY DEFERRED;
 W   ALTER TABLE ONLY public.remessas DROP CONSTRAINT "remessas_usuario_cadastro_id _fkey";
       public          postgres    false    234    4812    243            �           2606    24881 '   remessas remessas_usuario_envio_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.remessas
    ADD CONSTRAINT remessas_usuario_envio_id_fkey FOREIGN KEY (usuario_envio_id) REFERENCES public.usuarios(id) DEFERRABLE INITIALLY DEFERRED;
 Q   ALTER TABLE ONLY public.remessas DROP CONSTRAINT remessas_usuario_envio_id_fkey;
       public          postgres    false    4812    243    234            �           2606    24886 -   remessas remessas_usuario_recevimento_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.remessas
    ADD CONSTRAINT remessas_usuario_recevimento_id_fkey FOREIGN KEY (usuario_recebimento_id) REFERENCES public.usuarios(id) DEFERRABLE INITIALLY DEFERRED;
 W   ALTER TABLE ONLY public.remessas DROP CONSTRAINT remessas_usuario_recevimento_id_fkey;
       public          postgres    false    243    234    4812            �           2606    16626 "   usuarios usuarios_unidade_id _fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT "usuarios_unidade_id _fkey" FOREIGN KEY (unidade_id) REFERENCES public.unidadehemopa(id) DEFERRABLE INITIALLY DEFERRED;
 N   ALTER TABLE ONLY public.usuarios DROP CONSTRAINT "usuarios_unidade_id _fkey";
       public          postgres    false    238    4816    234            o      x������ � �      p      x������ � �      q      x������ � �      s      x������ � �      t      x������ � �      u      x������ � �      r      x������ � �      v      x������ � �      �   |   x�5�1� ���D�q@(��bK!Ve����E�)fg�����&F�Tr%��ԶF
�����,��R#/�P۵=b��a��@͟-��3-%}��2��Vb���-�:8������S#�      �   �  x���͎�8 ��)��+�����,=���(�L�H��b�=mf�(����E�������������;g��Y�H�K����{G�C��{(Iȥ{��������w?����vN�;]�4+s�W�5'���a�|k���l��^!c�n~h�@=x��\����ݙg�4��qI/j��EV�0�ow&��l����5�Ƚ`C�=�g���µ���S��8S9�~AW�"\���Iۍ�jRF9���~��]�p=�H�_s�Q��.�]mT��tIz�d��yB��s����ڮ���FE��+k��8)O�t��x�r۽�ZF%�����1��v/��R'�z�H�-j�&{�lu?��^IC�Y0��f/:�|���U&փFG-n���.����S�y�1�n���" �:�V�rݞsr�,�#����2��8Lc��I-���#�7��*���3BO�"Y�e~��A��손q�b�䍰�`����v20���)�����\�Y.�H;�@I��/>?=>�����U1�ْ"R�V��M�����X譁�XJ	A�i�*�K�O�bzy�&�4�S�f�bH�D��,^z@K;��3㩒7��[H�J�Ê{(��L/��ښ6aE�q�,Be��c��>F�+,�G��t-=���zn,�pR��ڢ��'4��y�5����E�����~h�uL      �   8  x�E�˵�0�(�9 �ry��1�6\�\ݧ,�	qY��8 ͑��d�RQ�9֔@�h����c�V7Z�|~C�r���2X��M�Iy�yďV�N��+*�^��C��;�z��}�����}����޾M����{%Wo�<�99��Gt�&�X���6�1�T��5|�i�"�����0�c8�L�6���T&����KƮv�ؓ!t!bn���ܞ1|���6��`�<H�^�ۻ?/��[^,ry���������f��<Zy��^2|�<��I.�,��w�֞��s���6�� �w>���8�\^N����o��{���      �      x��]]�9n~V�B�
@����	�E��+bgd����>�/f�?Y��deUeK���t*�� �?�H������7���O�?}����_?�C���� ||u��B��W�����5��������K���_���/XH�>	p�>^H$xz�e4�����RH�(�W��.��$ �P��WNĞ�� w��JB�>D#ă����J$D����~ْ zE�pH��!�JÀF�D��C4�� ]�?����˗�b�����U��L�~�����I�ם��_�_<F��#҆�ç߿� |\�˺5꿙ux
m�o����%r����'p{��	r^������o_�vy�G�l��;���qtB��08��'��7����Ni��\g5�;}T=����%EN�7R:8M����#�~���@��� q�G�+/�AVʄ*��<q��DdǴ~D� �;�+��x�%��������_?oq����_���Y��o����{t'�s�yq6���j;|Z?a5�JT�۴�xp�1NR��c���n@X�J8H��r�:�B��r�IU�B�G񇗪��F���-!}Oղ��ޫ�F@ٚ	�?ܜN�a!a�����!(�s�	%�E[Z!��麀��-��f5$
�v�Wr	� W�l���ݢ�������F*DT���u�*	���Q�W��tK�]�^RZ`�ى�EP{�f�&��+�\eK}L�fvB�FA}�&�%H,�G��!O.�OR;* ��k����X���Y<6jc�_>�}���_>ݻ=���nS��>�f�;��� ����(s�푲V����7��?ݤ��s��Mɛ"��s1���#�ީ��X�F���#^���B���lQ��h��Y4R��Ez(>n�}Ű%V%ʨ�)��u��>^ e���#�������������c�U������??�c���8���Kb�iǆ�b8��ީ/�A��ʯ�L
@tC��fD�Rv`h��p�����9���0.ta�k(�8Z��D��.Tc��=y��,Nzr'���9D�݂Z��Z���9H��P�֑��L�qA �HT�?FĿ�,��,���ۊ-s��������yP~���!����iN�4%�A����2z�5���v���DXl�b��~��%���)�8.i��t'1���JR���CL\��|<HVk։&O��(u��7(�AJ~E	ߍ�(�C�3(�R�Q8��jgi��N��֞�:^�9$����W��4T���Wg�e$��S��vT7�U�O1V0x���`�h`P�Z<b�a�1�ؠx�΂���|Kb�H�H��Y��25yae�	_��� �(&~�D��֤֮��
ZN	�1�Foed�rn��
�;>��^O�[�p2=�.�a���53�O�]ɽ�
�H���pQ'9��K/�fC��;4�W�l�M�%�l葷:;�m[��qY^��F�QL�<r��u����j>̆�Uv��4b��%<^I	!���8#C��r"n=~4�c�-��Չ����(C{SB����Mb����:��l�j�y���4��i-�C{Y�'IX�'�&��^�`�@����%����$/�\�~?��%�>�k����>~���۟|�r�lR.�#���T�i�!�J�������_.=֥�'�_,IHn�1�j(�RuM�]�w�Se�|�J�.B6yΛ@uҏ^���ZL?p��=�P׀߅~�A?q����}�'
�<��ރ>���Pl�
�{Ч���3U�C�w��
@{P�K�*������$��Å���"1T�q'˞R�����$�!:��N������Z��>���-jS�����m�00�b�ciKRl4�ġ~~�d&�hRd.�\Ws�ɥ\x�3�}�"� 9�>$+U��EJ6� uu��H�[>+YAJ�-�z��s!�C�A�!$�T�����)p�Ka	](2�U�O BJ������)+��.�I��L��n�������1""��%@ו>��*x-��+�XX�MH���� <x7 �J&��߮�����mL�R4j���-IiTku��R��Vw/����w+(:J������y�������$�gd#��;�}��5!�!d +r��b�a�OO�!�K<���]����1����L,�q5Q#��v��^j[٘Qct�M-�(� G�%�VZ�䂞���Ty�h��"Kv'ӛy"�d�r��>o�|��'�mc����?�>Bj�y�672����SN#��^��F�h���)�4�����}Η�=��i��̏�g}>�$7,��\"W^f臅9K��z�c����Ğ��%�3vJ	�q[���mK�\-�Qj�
��^7�u@��: �������3�C��rը�Ʀ48� ��9�p�mW� �&��Y�8�r�A.�)��tî��U����T~ � �(� K�M�;Jp_꒺6�s$+j�.e��!A��RC6?�; D�Ӆ�����#e��~Ad��88����(r�XCwd  䳞�b�Hc�rR2���n��+JOh��27���
�S�t��G��_\s=��IN/� ��@/Ԍ솉�P�պ�h�����!�:w`�蜜am�P���Ƭ�e�tǸ�qڏ�Ґ�l��*w��z{�2�Κ��g��=�H���o�g}����Ҩ1�y�J�����I�d��kt��CXp'g�0��K��4d9G-����[P�"uYє���@���	Ņ�QoJ�Dc!Qm�|<����zu�\��X9��<�<�`Q�$vv����j�[��C�)@K�Ã�~q@�cv��YCc�v�Sf'��x{>Y�����S�iȌ��4�͆%���@����lT�����������I~���e�a ��.AY����r�5�s�US[[��x���bW���C���þ�9�o�:�j\�z�SzEU|)W �����}��K9��o7R����Ifg���"��ya�r��`l��Y��]P$�%}$�H0WVQ��FU_��}m�v��ꯖS�l�=�/8w�(ИX���@`^��t��a�σ�sG.t���
?u^J�6w1��6{��w���L^��w����O������>����%X�i���&u�5i�����G��R�|�נ=6�`Id����%\�����d���y=���ޫ�g*T���ϙ��G�P�?��q��Xj�0´
����*X��[�ܺ��%�2�.�]�w�BAv�U��n�d�ss̖��`q��O���?�
����͘�B,Pp�6��2���2�&�,Q��	�O�Tx1��m�B���>t�1p�A�}��)�#�1Uك�ڑ+$�m����HI��&���2?x� �1`h����F$"�с'W/DA�;)ޓ��Rԗ��.��"��S3��릀�ߖ���.6VH�A~Uk�����x'��;�n���Ë���/���hzF%�#�y�0t�Z�vm��y�m3��؎옼A	?J��o۠������B����x(Ѯ�MP�σb��m�%��&�DOx���gM�^ΝkѫpJ��Ati��z�r��}�G;�l]s�Y�Wڄ���% c��%�F���Y�2��%P�;�z
$xJ{Ĭ�c��W(�3�KE� ���j��4�L�)ݶ����WAT7J��(.X��a������f��ڕ)�v���A��Q�H �\�0�=�X v]�G��F�&���Ȉ�0����?�B.�z��ˏ���$*!o�����t��|�M/���4�L.�u�����%'���u~�;1�5h�����W�EiWj�%л�O��Z����<?�M�Ͽ�z�6S�I����ڥR����qA
�7.����%n704$�6&�I��qN���mEH�z�����H�ٶ9�գ`=��q�mq �j��<Y�P�CCb��E��y:�/������MXҢ�|%�D�zc+��N���ZZ�+,��Yk!� ��^��6O$w��b��� ne�Y_�}�Rf]h��V:V�    ����+������s��٩/G"�phg���c+��n�KX�)�z"��JѬ���9\�}���ŕT\}� �����oQ������5ӈ���y@%�l�>a�{\6@�!�m��=P;t����ϯ/�Ic�q� �2dzVA����h�{
r,M��&i77G�mv6rnA�X���!����e�$c�g�J�=�QA�lȟ�Ǭ�$SL��du���A���#�~���&'��IyyS���A��Nd8�7V��獿!v��81��к��1J�QQ�g�Cg��;��*љ��
k�!=��\.W���y�,��}�h��|(9G��r?������Vk��Ox��ۜ��Ʉ5)�ӗHΐ��E���1��m�r�8w���=��-&�l�� ��t(�x��&�w�:HdJ�ػ�W���ǰ4�P�	<��	)��4B��m�e��Jp�Y笠K�m�[�)�n�H��Uq8�O�y���'Mk�r/ɣG�K�Ү*!�����ڲT_ r4Pkf����t���:�v
I���ᝂ*��;0�O|^uw�6m��lo����V!��c�x�5�e���;9H��0�4�g���&
n�IW ܯ��@�M��4�q?��V��ҧ�j��텞a�]8���.�#�
1��(��c��Mc�H�h�q'���%H��ٗj�$��`���#���X��:����I�X�IEb�`��:,��%�
6e���y��mu�=��:�<�W�WU �9�2�.���c���v�����_�H�:t�Ļ�D���	^���z��Kc��^�*�8�vK<H�M�\��=Z=w2$~�o^lQ��S�9��7E#Iv5���.�̚�XPz������#>U^�O�r��dŶ|�I���P��
�%���P�c���;��N�x'bj1R'�I��2�9���
%��r��"��N;.�H�����&�"�'�
׏J>O���f�d� k�����օ�F�s߭�������ͳ���̨�8���܀v2����SPJ�-�0�BL���YXJ?�-�8�BR����8tݓ����S�m/���c�L�/VJ�f6"����P��Ij\|��)����H7f�A�'@�u��Bًg*��y�i�+�r:���i߾дzT*+\���య�uPrV���Vs��?	�}�<�Z��g�	>�������r�?�x��B��h+77i�/իU,p�d]&v4
m�. HmU��y@��d��qo�v�Uu*[\���	x�>����=�[�<����YP���)m�[,8}�S:�te8ܐa��^=�呢���Iע�O£Ʀ�Lrc����L�HR�h�|���Q��x���h�q<7�5T�Z=f<) �vst'���
N�7|-��q�MĞ��Ӄ�<e��$��%��%���ok��y~���n�y����]�U��-���fz��O��9�5�쁉C0��������yEL=0a&Ֆ�`�i`8v#�U�_��S�[��̈́�g<�<��Sc��L]?�e���#�Ż��nt�+�����Iʕvò�����\�j���R|�Ⱦ�Y��b�RK��,Lé%T�j���ݶu�c� �b�Ҟg+k�g7!7T}���TjD��2���|�Ӯ�����EK�;�jx,�����Uy�v���];猤�K��~PvВ\���6K��,�u�X���rc�8�>Z$-�WLg�m�)Y�x��a�b�T��_n�3�+ �z��Owi��Ϸ_����o},�c�/�Z���Z���&�p�ȗYcp�WR�_B6�\�Pkf=lo�B�d����q'sZ?R�R%�uuN��2�����*���&7�b�� ��"�Y�� ,��o��
���:Y���n�~���O��s�ͽ�^�SP� �S���Ps�Q�&��yǚ9�S-��$yD15���)|�����t�똫�T��}`p��|�X�hf����u!^�L�*���Qp��K�%����呙D�3w��+�Ѓ�r�HQ�%I�w<4={����nҜY�b�ݔ���|i;	���h��^+�����皊�S����hL,M$�w�&*����j^�n��=Ltc;D!��q[�p�8$ja5�+���q���\�O�ҟ�����6�[���ݚ������#3N�Δ
]��|�KM��!y�B��IC4�C�~Ҁ�Os<�Ƕ����֧Vy���=Z�n�7�$L�6��\���%/#�La�$�_{�����±5��#�wn�!����bR��`����ݩ{�Ӫ!UG��?Ir�U(a7vu$A=�S/��'��$C���N�w���Au��O���ۿ�S�o��A
U���^y�AA�1%hlÝ��!H��#�'98h"n�抦Ú�#|e&�m���܏�y;�%r��i��v�c����{<h����=<2�O.3��wg��H]<a�'A�a�^��� ��'���j~��	�<a��#WL=�7�ƻyJr�D�ָ:pL�G9R�jT����.LI���f� 7��c��;�[�+-�,%F�N�D����S�X�F�.N'$>ܞ����F�)�Dn�s�nĻ+�[�s�a	��d�-X�Z�!O��
,����8�Ro��e�T"`"�'�^�^�[ٓ���^Q���iG�XQJH��%��+�=�rb��j�6����y�i�v�q8�0ݕ�n۴��Gw��fG>���7i��|w>���S��b�vm�ө��`hǕ����RK_����ݓ9��Ui?��?2�t���\��&�����!�#�㰐;Iͥ���F6B>�~�1 �O���L�zOJ����� i��]��<����3�vX;�-x��A�C}dɆy��j���� �Pj�{�1�<k��c�5iRin�2��4(jߨ%��t��H0���,�s�"C(�6�W��|���\��
��J�<�iP������_Ö���o���/߾������Q#�����7%R6L64���M���J���{x�aB��'f�W$�4!�ٹ�f����P�_)�=��D#VӰE�I:/x�������)h u�l��4*�m��&�7�h6GK4�#�},x&��"t�l���eΧ{�I 4��e�Fj�W��ii�z�o��Xa�}$m�$X�Ѿ��!E��q�"�G��J������pL�0����$��S6�
��$g%�U�$<	��bU[��!�Uκ��?	j��A�H�V��Nt�=���~�-��,$��B	��DhAX���9�ABC$:*[�o������s�      �   b   x�360�47qr4q�t��CA�Ɯ��FF&�溆�
�FV�V�FzƆ�ƺƜ�J)��)�EJ��F������
�W*��*����*p��qqq ]�      �      x������ � �      �     x�e�=n�0�g��@�"��-�ԡK۱��h ��@E�$�<	j@��==���c1�,;lw�qZ_k�:�l�<�EX�#�^���<����Q�1	%�6�:�?�2�y9<�IC��+1x�9O�|��!JR�h]��yʗ<��CV�R�	Wg�H���-�r�B�}��-�y-ñ�����%���U���z���YN�F\�Ec�X3�$e���ؗ-'޶���܍u�o0$
I�"GR���|�K�CC.�����+�����zO�I���/�hs      �   �  x�m�ɚ�����jZ�;EDl�:AL|�3�r^솙v�u������{��n���^���(c��U� �E���r�i�#VrXtr+6m��@���S}���|˪�{�QW@�o@~#\��)+M$�SIQ�%�c���A�5��[�a=p�Z��ݺ �������$<_|/�8����4�m�w���	Ί~gvκ�z��������ܔ�(�L����	",�AX���SݿL!�x�yG�Z�n����j�!�U���7X'n<�au�x��^Mϲ�8=j�L��"�[�[�^���K�M(��)ܦQzퟪ�>ւ��r�pwi8Gàc�3u�KO-?��p��ۡ�u:�CXj�Q�CxW�k���9��Sw=7<���s�8��$���c�W�U���ƾ[���'�����.�@��Na�,�MՋ�|5�4% ,	ob�������>~O�s=s�%�S�y\�Ħ���ms�M�2�+���%)cj���Co��A/��A�X�( �6|��AP*���n�~�Cyk�r����i4�����>ۋS�����N�G���|����i����f6%$r#PF���]_�!��_?~�Q�=������#{gY�Q���1��Kb���@4���p"Xjq�)�&�hRi��JW�?X�&�n���X��A�Ԑ$y�fQR������� �L�sP'�;]��2���j���9/�|��l5ޟ�`KZA���)Xqxx*�P(M�M���!����(�������s���! �^N~�;�ة�f��sO��$Nˎx�i�F\^̃�z+է�)��I�!�PEA�K�ěE~ �.7�qF�{C�<��LV�sm���4�����"c �K��1\X质��%*��j���T�w0ɸ��(!�Yx�{P��?��0�����AQB��<-(3f'��[�φc@�K�EE��CZ���@-ﰀ�_��T�~�&""�eȭ���5���R�Ø�,�<�>Ǩ:��dgً�8�p��S�g\����f�����J�:�lv�<�X��MDJ�)t�a5��<$�>I��>3
�ܽ���t����h�*���"�Vm7���|�vE�b$k�ݶ���»�+��ϭ�}M|}P�${�s|�h���n4���؁ s���>��Z=}���j3I�V1ZQ���n�@^!>>RE��]�\�<kyڸ�m�xc��k�q�O����x�pзDi�F��]�Y�������Xs�q7x�[���P#�1Y�o*�!�5	��þ3�7�APL��t.��4N�Ҟ��y�ib~ܫ��WVط�V�K���j��� �����%	3��i���W^�٧�Ϳ���k�G'��N�x�#���O]/
�9.�K4�fN�3�mgɚAˬ� ]�(p��$�6��!�IZ������ƚ޹Sw��A�B���Ud��6��pg�W՗���bj(�e2w�xm_�O��(3��$xI���{<�Y������)���z8M*cҠ��8l_�tŚ<E��)Qƭ.lQ%z�H�#ȷ^ �O��z^�s%�g��:����S%�w�>̯K�g..���I?��-� 4��E��؄���Y�ы�[AnB�	��b�0��j��$*h����]�yl>+pj,��@�s���Z���|U��S����q��.�m�SU(p����nF��;�1z�z��Z��?%�[�     