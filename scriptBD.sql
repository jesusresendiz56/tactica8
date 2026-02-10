-- WARNING: This schema is for context only and is not meant to be run.
-- Table order and constraints may not be valid for execution.

CREATE TABLE public.asignaciones (
  id_asignacion integer NOT NULL DEFAULT nextval('asignaciones_id_asignacion_seq'::regclass),
  id_personal integer NOT NULL,
  id_campaña integer NOT NULL,
  id_responsable integer,
  rol character varying,
  fecha_asignacion date DEFAULT CURRENT_DATE,
  fecha_inicio date,
  fecha_fin date,
  estatus_asignacion character varying DEFAULT 'activa'::character varying,
  CONSTRAINT asignaciones_pkey PRIMARY KEY (id_asignacion),
  CONSTRAINT asignaciones_id_personal_fkey FOREIGN KEY (id_personal) REFERENCES public.personal(id_personal),
  CONSTRAINT asignaciones_id_campaña_fkey FOREIGN KEY (id_campaña) REFERENCES public.campañas(id_campaña),
  CONSTRAINT asignaciones_id_responsable_fkey FOREIGN KEY (id_responsable) REFERENCES public.responsables(id_responsable)
);
CREATE TABLE public.campañas (
  id_campaña integer NOT NULL DEFAULT nextval('"campañas_id_campaña_seq"'::regclass),
  marca_id integer NOT NULL,
  tipo_campaña_id integer NOT NULL,
  responsable_id integer NOT NULL,
  nombre_campaña character varying,
  estatus character varying DEFAULT 'pendiente'::character varying,
  fecha_registro timestamp without time zone DEFAULT now(),
  fecha_inicio date,
  fecha_fin date,
  CONSTRAINT campañas_pkey PRIMARY KEY (id_campaña),
  CONSTRAINT campañas_marca_id_fkey FOREIGN KEY (marca_id) REFERENCES public.marcas(id_marca),
  CONSTRAINT campañas_tipo_campaña_id_fkey FOREIGN KEY (tipo_campaña_id) REFERENCES public.tipos_campaña(id_tipo),
  CONSTRAINT campañas_responsable_id_fkey FOREIGN KEY (responsable_id) REFERENCES public.responsables(id_responsable)
);
CREATE TABLE public.cat_puestos (
  id_puesto integer NOT NULL DEFAULT nextval('cat_puestos_id_puesto_seq'::regclass),
  nombre_puesto character varying NOT NULL UNIQUE,
  CONSTRAINT cat_puestos_pkey PRIMARY KEY (id_puesto)
);
CREATE TABLE public.datos_familiares (
  id_familia integer NOT NULL DEFAULT nextval('datos_familiares_id_familia_seq'::regclass),
  id_solicitud integer UNIQUE,
  nombre_padre character varying,
  nombre_madre character varying,
  numero_hijos integer,
  quien_los_cuida character varying,
  CONSTRAINT datos_familiares_pkey PRIMARY KEY (id_familia),
  CONSTRAINT datos_familiares_id_solicitud_fkey FOREIGN KEY (id_solicitud) REFERENCES public.solicitud(id_solicitud)
);
CREATE TABLE public.direcciones (
  id_direccion integer NOT NULL DEFAULT nextval('direcciones_id_direccion_seq'::regclass),
  id_solicitud integer UNIQUE,
  calle character varying,
  colonia character varying,
  ciudad character varying,
  municipio character varying,
  estado character varying,
  cp character varying,
  CONSTRAINT direcciones_pkey PRIMARY KEY (id_direccion),
  CONSTRAINT direcciones_id_solicitud_fkey FOREIGN KEY (id_solicitud) REFERENCES public.solicitud(id_solicitud)
);
CREATE TABLE public.marcas (
  id_marca integer NOT NULL DEFAULT nextval('marcas_id_marca_seq'::regclass),
  nombre character varying NOT NULL UNIQUE,
  estado character varying DEFAULT 'activa'::character varying,
  CONSTRAINT marcas_pkey PRIMARY KEY (id_marca)
);
CREATE TABLE public.personal (
  id_personal integer NOT NULL DEFAULT nextval('personal_id_personal_seq'::regclass),
  id_solicitud integer UNIQUE,
  num_empleado character varying UNIQUE,
  cuenta_nomina character varying,
  contrato_url text,
  fecha_alta date DEFAULT CURRENT_DATE,
  estatus_laboral character varying DEFAULT 'activo'::character varying,
  CONSTRAINT personal_pkey PRIMARY KEY (id_personal),
  CONSTRAINT personal_id_solicitud_fkey FOREIGN KEY (id_solicitud) REFERENCES public.solicitud(id_solicitud)
);
CREATE TABLE public.referencias (
  id_referencia integer NOT NULL DEFAULT nextval('referencias_id_referencia_seq'::regclass),
  id_solicitud integer,
  nombre character varying,
  parentesco character varying,
  telefono character varying,
  CONSTRAINT referencias_pkey PRIMARY KEY (id_referencia),
  CONSTRAINT referencias_id_solicitud_fkey FOREIGN KEY (id_solicitud) REFERENCES public.solicitud(id_solicitud)
);
CREATE TABLE public.responsables (
  id_responsable integer NOT NULL DEFAULT nextval('responsables_id_responsable_seq'::regclass),
  nombre character varying NOT NULL,
  puesto character varying,
  estado character varying DEFAULT 'activo'::character varying,
  CONSTRAINT responsables_pkey PRIMARY KEY (id_responsable)
);
CREATE TABLE public.solicitud (
  id_solicitud integer NOT NULL DEFAULT nextval('solicitud_id_solicitud_seq'::regclass),
  id_puesto integer NOT NULL,
  nombre character varying,
  apellido_paterno character varying,
  apellido_materno character varying,
  fecha_nacimiento date,
  sexo character varying,
  estado_civil character varying,
  rfc character varying,
  curp character varying,
  imss character varying,
  grado_estudios character varying,
  celular character varying,
  correo character varying,
  salario_deseado numeric,
  estatus character varying,
  fecha_registro date DEFAULT CURRENT_DATE,
  autorizacion_datos boolean,
  telefono_casa character varying,
  telefono_recados character varying,
  lugar_nacimiento character varying,
  tipo_sangre character varying,
  credito_infonavit boolean DEFAULT false,
  credito_fonacot boolean DEFAULT false,
  CONSTRAINT solicitud_pkey PRIMARY KEY (id_solicitud),
  CONSTRAINT solicitud_id_puesto_fkey FOREIGN KEY (id_puesto) REFERENCES public.cat_puestos(id_puesto)
);
CREATE TABLE public.tipos_campaña (
  id_tipo integer NOT NULL DEFAULT nextval('"tipos_campaña_id_tipo_seq"'::regclass),
  nombre character varying NOT NULL UNIQUE,
  CONSTRAINT tipos_campaña_pkey PRIMARY KEY (id_tipo)
);
CREATE TABLE public.usuarios_rh (
  id_usuario integer NOT NULL DEFAULT nextval('usuarios_rh_id_usuario_seq'::regclass),
  correo character varying NOT NULL UNIQUE,
  password text NOT NULL,
  created_at timestamp without time zone DEFAULT now(),
  CONSTRAINT usuarios_rh_pkey PRIMARY KEY (id_usuario)
);