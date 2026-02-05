-- ============================================
-- TABLAS BÁSICAS PARA GESTIÓN DE CAMPAÑAS Y RH
-- ============================================

-- =========================
-- CATÁLOGO DE PUESTOS
-- =========================
CREATE TABLE cat_puestos (
    id_puesto serial PRIMARY KEY,
    nombre_puesto varchar(100) NOT NULL UNIQUE
);

-- =========================
-- SOLICITUDES DE PERSONAL
-- =========================
CREATE TABLE solicitud (
    id_solicitud serial PRIMARY KEY,
    id_puesto int NOT NULL,
    nombre varchar(100),
    apellido_paterno varchar(100),
    apellido_materno varchar(100),
    fecha_nacimiento date,
    sexo varchar(20),
    estado_civil varchar(30),
    rfc varchar(13),
    curp varchar(18),
    imss varchar(15),
    grado_estudios varchar(100),
    celular varchar(15),
    correo varchar(150),
    salario_deseado numeric(10,2),
    estatus varchar(30),
    fecha_registro date DEFAULT current_date,
    autorizacion_datos boolean,
    FOREIGN KEY (id_puesto) REFERENCES cat_puestos(id_puesto)
);

-- =========================
-- DIRECCIONES
-- =========================
CREATE TABLE direcciones (
    id_direccion serial PRIMARY KEY,
    id_solicitud int UNIQUE,
    calle varchar(150),
    colonia varchar(150),
    ciudad varchar(100),
    municipio varchar(100),
    estado varchar(100),
    cp varchar(10),
    FOREIGN KEY (id_solicitud)
        REFERENCES solicitud(id_solicitud)
        ON DELETE CASCADE
);

-- =========================
-- DATOS FAMILIARES
-- =========================
CREATE TABLE datos_familiares (
    id_familia serial PRIMARY KEY,
    id_solicitud int UNIQUE,
    nombre_padre varchar(150),
    nombre_madre varchar(150),
    numero_hijos int,
    quien_los_cuida varchar(150),
    FOREIGN KEY (id_solicitud)
        REFERENCES solicitud(id_solicitud)
        ON DELETE CASCADE
);

-- =========================
-- REFERENCIAS
-- =========================
CREATE TABLE referencias (
    id_referencia serial PRIMARY KEY,
    id_solicitud int,
    nombre varchar(150),
    parentesco varchar(100),
    telefono varchar(20),
    FOREIGN KEY (id_solicitud)
        REFERENCES solicitud(id_solicitud)
        ON DELETE CASCADE
);

-- =========================
-- PERSONAL ACTIVO
-- =========================
CREATE TABLE personal (
    id_personal serial PRIMARY KEY,
    id_solicitud int UNIQUE,
    num_empleado varchar(20) UNIQUE,
    cuenta_nomina varchar(30),
    contrato_url text,
    fecha_alta date DEFAULT current_date,
    estatus_laboral varchar(30) DEFAULT 'activo',
    FOREIGN KEY (id_solicitud)
        REFERENCES solicitud(id_solicitud)
);

-- =========================
-- MARCAS
-- =========================
CREATE TABLE marcas (
    id_marca serial PRIMARY KEY,
    nombre varchar(100) NOT NULL UNIQUE,
    estado varchar(20) DEFAULT 'activa'
);

-- =========================
-- TIPOS DE CAMPAÑA
-- =========================
CREATE TABLE tipos_campaña (
    id_tipo serial PRIMARY KEY,
    nombre varchar(100) NOT NULL UNIQUE
);

-- =========================
-- RESPONSABLES / COORDINADORES
-- =========================
CREATE TABLE responsables (
    id_responsable serial PRIMARY KEY,
    nombre varchar(150) NOT NULL,
    puesto varchar(100),
    estado varchar(20) DEFAULT 'activo'
);

-- =========================
-- CAMPAÑAS
-- =========================
CREATE TABLE campañas (
    id_campaña serial PRIMARY KEY,
    marca_id int NOT NULL,
    tipo_campaña_id int NOT NULL,
    responsable_id int NOT NULL,
    nombre_campaña varchar(200),
    estatus varchar(30) DEFAULT 'pendiente',

    -- Fecha y hora exacta de registro
    fecha_registro timestamp DEFAULT now(),

    -- Fechas operativas
    fecha_inicio date,
    fecha_fin date,

    FOREIGN KEY (marca_id) REFERENCES marcas(id_marca),
    FOREIGN KEY (tipo_campaña_id) REFERENCES tipos_campaña(id_tipo),
    FOREIGN KEY (responsable_id) REFERENCES responsables(id_responsable)
);

-- =========================
-- ASIGNACIONES DE PERSONAL
-- =========================
CREATE TABLE asignaciones (
    id_asignacion serial PRIMARY KEY,
    id_personal int NOT NULL,
    id_campaña int NOT NULL,
    id_responsable int,
    rol varchar(50),

    fecha_asignacion date DEFAULT current_date,
    fecha_inicio date,
    fecha_fin date,

    estatus_asignacion varchar(30) DEFAULT 'activa',

    FOREIGN KEY (id_personal) REFERENCES personal(id_personal),
    FOREIGN KEY (id_campaña) REFERENCES campañas(id_campaña),
    FOREIGN KEY (id_responsable) REFERENCES responsables(id_responsable),

    UNIQUE (id_personal, id_campaña)
);
