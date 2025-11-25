DROP DATABASE linkdin_emi;
GO

IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'linkdin_emi')
BEGIN
    CREATE DATABASE linkdin_emi;
END
GO

USE linkdin_emi;
GO
CREATE TABLE [roles] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [nombre] varchar(50) UNIQUE NOT NULL,
  [descripcion] nvarchar(max),
  [creado_en] datetime
)
GO

CREATE TABLE [permisos] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [codigo] varchar(100) UNIQUE NOT NULL,
  [descripcion] nvarchar(max)
)
GO

CREATE TABLE [roles_permisos] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [rol_id] int NOT NULL,
  [permiso_id] int NOT NULL
)
GO

CREATE TABLE usuarios (
    id                  INT IDENTITY(1,1) PRIMARY KEY,

    -- Rol simple (admin, empresa, postulante)
    rol_id              INT NOT NULL,     -- FK a tabla roles (1=admin, 2=empresa, 3=postulante)

    -- 🔹 Datos básicos
    nombre              VARCHAR(100)  NOT NULL,  -- lo que ingresas en el formulario
    apellidos           VARCHAR(150)  NULL,      -- OPCIONAL para que no falle el INSERT
    correo              VARCHAR(150)  NOT NULL UNIQUE,
    password            VARCHAR(255)  NOT NULL,  -- aquí guardas el hash

    telefono            VARCHAR(30)   NULL,
    fecha_nacimiento    DATE          NULL,

    -- 🌍 Ubicación (para que las empresas filtren por ciudad/país)
    ubicacion_ciudad    VARCHAR(100)  NULL,
    ubicacion_pais      VARCHAR(100)  NULL,

    -- 🎓 Datos EMI / académicos
    codigo_emi          VARCHAR(50)   NULL,       -- RU / matrícula / código interno
    carrera             VARCHAR(150)  NULL,       -- Ing. de Sistemas, Ambiental, etc.
    semestre_actual     TINYINT       NULL,
    anio_egreso         SMALLINT      NULL,       -- útil para egresados

    -- 💼 Perfil profesional (lo que ve el reclutador)
    titulo_perfil       VARCHAR(150)  NULL,       -- ej: "Estudiante de Ing. de Sistemas en la EMI"
    resumen             NVARCHAR(MAX) NULL,       -- resumen corto tipo LinkedIn
    experiencia_actual  NVARCHAR(MAX) NULL,       -- puesto o prácticas actuales
    habilidades         NVARCHAR(MAX) NULL,       -- lista de skills
    intereses           NVARCHAR(MAX) NULL,       -- áreas de interés laboral

    -- 🔗 Enlaces externos
    url_linkedin        VARCHAR(255)  NULL,
    url_github          VARCHAR(255)  NULL,
    url_portafolio      VARCHAR(255)  NULL,

    -- 🖼️ Archivos
    foto                VARCHAR(255)  NULL,       -- foto de perfil
    cv                  VARCHAR(255)  NULL,       -- ruta del CV en PDF

    -- Estado simple y fechas
    estado              VARCHAR(20)   NOT NULL DEFAULT 'activo',
    creado_en           DATETIME      NOT NULL DEFAULT GETDATE(),
    actualizado_en      DATETIME      NULL     DEFAULT GETDATE()
);
GO

CREATE TABLE [parametros_plataforma] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [nombre] varchar(150),
  [correo_contacto] varchar(150),
  [zona_horaria] varchar(50),
  [creado_por] int,
  [creado_en] datetime
)
GO

CREATE TABLE [secciones_plataforma] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [nombre] varchar(100) NOT NULL,
  [slug] varchar(100) UNIQUE NOT NULL,
  habilitada bit DEFAULT 1,
  [actualizado_por] int,
  [actualizado_en] datetime
)
GO

CREATE TABLE [contenidos_estaticos] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [seccion_id] int NOT NULL,
  [titulo] varchar(150),
  [contenido] nvarchar(max),
  [actualizado_por] int,
  [actualizado_en] datetime
)
GO

CREATE TABLE [categorias] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [nombre] varchar(100) NOT NULL,
  [descripcion] nvarchar(max),
  [creada_por] int,
  [creado_en] datetime
)
GO

CREATE TABLE [subcategorias] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [categoria_id] int NOT NULL,
  [nombre] varchar(100) NOT NULL,
  [descripcion] nvarchar(max),
  [creado_en] datetime
)
GO

CREATE TABLE [ofertas] (
    [id] int PRIMARY KEY IDENTITY(1,1),
    [usuario_id] int NOT NULL,               -- quien publica la oferta
    [categoria_id] int,                       -- área principal (Tecnología, Administración, etc.)
    [subcategoria_id] int,                    -- sub-área (Desarrollo Web, Soporte/TI, etc.)
    [titulo] varchar(150) NOT NULL,          -- nombre del puesto
    [descripcion] nvarchar(max),             -- descripción completa del puesto
    [ubicacion] varchar(100),                -- ciudad/país o "remoto"
    [tipo_jornada] varchar(50),              -- completa, media, remoto
    [modalidad] varchar(50),                 -- presencial, remoto, híbrido
    [experiencia_min] int,                    -- años mínimos de experiencia
    [salario_min] decimal(12,2),             -- salario mínimo (opcional)
    [salario_max] decimal(12,2),             -- salario máximo (opcional)
    [beneficios] nvarchar(max),              -- lista de beneficios
    [documento_adj] varchar(255),            -- archivo adjunto (PDF, etc.)
    [estado] varchar(20) DEFAULT 'en_revision', -- estado de la oferta
    [publicado_en] datetime,                 -- fecha de publicación
    [actualizado_en] datetime,               -- fecha de actualización
    [contacto_reclutador] varchar(150),      -- email o teléfono del reclutador
    [imagen_empresa] varchar(255)            -- logo o imagen del anunciante
);
GO

CREATE TABLE [postulaciones] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [oferta_id] int NOT NULL,
  [usuario_id] int NOT NULL,
  [estado] varchar(20) DEFAULT 'en_revision',  -- lo maneja el reclutador (en_revision, aceptado, rechazado, etc.)
  [calificacion] int,
  [mensaje] nvarchar(max),
  [respuesta_postulante] varchar(20) NULL,     -- NUEVO: 'aceptada', 'rechazada', NULL (pendiente)
  [fecha_respuesta] datetime NULL,             -- NUEVO: cuándo respondió el postulante
  [notificado] bit NOT NULL DEFAULT 0,         -- NUEVO: 0 = aún no se le mostró aviso / 1 = ya se le mostró
  [creado_en] datetime
);
GO

-- Opcional, pero muy útil: que un usuario NO pueda postular dos veces a la misma oferta
CREATE UNIQUE INDEX UQ_postulaciones_usuario_oferta
ON postulaciones (usuario_id, oferta_id);
GO


CREATE TABLE [publicaciones] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [usuario_id] int NOT NULL,
  [contenido] nvarchar(max),
  [imagen] varchar(255),
  [creado_en] datetime
)
GO

CREATE TABLE [comentarios] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [publicacion_id] int NOT NULL,
  [usuario_id] int NOT NULL,
  [comentario] nvarchar(max) NOT NULL,
  [creado_en] datetime
)
GO

CREATE TABLE [reacciones] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [publicacion_id] int NOT NULL,
  [usuario_id] int NOT NULL,
  [tipo] varchar(30) DEFAULT 'like',
  [creado_en] datetime
)
GO

CREATE TABLE [actividades] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [usuario_id] int NOT NULL,
  [accion] varchar(100) NOT NULL,
  [descripcion] nvarchar(max),
  [ip] varchar(50),
  [creado_en] datetime
)
GO

CREATE TABLE [alertas_seguridad] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [usuario_id] int NOT NULL,
  [tipo] varchar(100) NOT NULL,
  [detalle] nvarchar(max),
  atendido bit DEFAULT 0,
  [creado_en] datetime
)
GO

CREATE TABLE [notificaciones] (
  [id] int IDENTITY(1,1) PRIMARY KEY,
  [usuario_id] int NOT NULL,
  [titulo] nvarchar(150) NOT NULL,
  [mensaje] nvarchar(max) NULL,
  [leido] bit NOT NULL DEFAULT 0,
  [creado_en] datetime NOT NULL DEFAULT GETDATE()
);
GO

ALTER TABLE [notificaciones] 
ADD FOREIGN KEY ([usuario_id]) REFERENCES [usuarios]([id]);
GO


---------------------------------------------------
-- TABLA DE SOLICITUDES DE AMISTAD
---------------------------------------------------
CREATE TABLE [solicitudes_amistad] (
    [id] int PRIMARY KEY IDENTITY(1,1),
    [solicitante_id] int NOT NULL,          -- quién envía la solicitud
    [destinatario_id] int NOT NULL,         -- quién la recibe
    [estado] varchar(20) NOT NULL DEFAULT 'pendiente',  
    -- estados: 'pendiente', 'aceptada', 'rechazada'

    [creado_en] datetime NOT NULL DEFAULT GETDATE(),
    [respondido_en] datetime NULL
);
GO

-- Clave foránea a usuarios (quien envía)
ALTER TABLE [solicitudes_amistad] 
ADD CONSTRAINT FK_solicitudes_amistad_solicitante
FOREIGN KEY ([solicitante_id]) REFERENCES [usuarios]([id]);
GO

-- Clave foránea a usuarios (quien recibe)
ALTER TABLE [solicitudes_amistad] 
ADD CONSTRAINT FK_solicitudes_amistad_destinatario
FOREIGN KEY ([destinatario_id]) REFERENCES [usuarios]([id]);
GO

-- (Opcional) Evitar que un usuario se envíe solicitud a sí mismo
ALTER TABLE [solicitudes_amistad]
ADD CONSTRAINT CK_solicitudes_amistad_no_self
CHECK (solicitante_id <> destinatario_id);
GO

-- (Opcional pero útil) Evitar duplicar solicitudes en la misma dirección
CREATE UNIQUE INDEX UQ_solicitudes_amistad_par
ON [solicitudes_amistad] (solicitante_id, destinatario_id);
GO

CREATE UNIQUE INDEX [reacciones_index_0] 
ON [reacciones] (publicacion_id, usuario_id)
GO

ALTER TABLE [roles_permisos] ADD FOREIGN KEY ([rol_id]) REFERENCES [roles] ([id])
GO

ALTER TABLE [roles_permisos] ADD FOREIGN KEY ([permiso_id]) REFERENCES [permisos] ([id])
GO

ALTER TABLE [usuarios] ADD FOREIGN KEY ([rol_id]) REFERENCES [roles] ([id])
GO

ALTER TABLE [parametros_plataforma] ADD FOREIGN KEY ([creado_por]) REFERENCES [usuarios] ([id])
GO

ALTER TABLE [secciones_plataforma] ADD FOREIGN KEY ([actualizado_por]) REFERENCES [usuarios] ([id])
GO

ALTER TABLE [contenidos_estaticos] ADD FOREIGN KEY ([seccion_id]) REFERENCES [secciones_plataforma] ([id])
GO

ALTER TABLE [contenidos_estaticos] ADD FOREIGN KEY ([actualizado_por]) REFERENCES [usuarios] ([id])
GO

ALTER TABLE [categorias] ADD FOREIGN KEY ([creada_por]) REFERENCES [usuarios] ([id])
GO

ALTER TABLE [subcategorias] ADD FOREIGN KEY ([categoria_id]) REFERENCES [categorias] ([id])
GO

ALTER TABLE [ofertas] ADD FOREIGN KEY ([usuario_id]) REFERENCES [usuarios] ([id])
GO

ALTER TABLE [ofertas] ADD FOREIGN KEY ([categoria_id]) REFERENCES [categorias] ([id])
GO

ALTER TABLE [ofertas] ADD FOREIGN KEY ([subcategoria_id]) REFERENCES [subcategorias] ([id])
GO

ALTER TABLE [postulaciones] ADD FOREIGN KEY ([oferta_id]) REFERENCES [ofertas] ([id])
GO

ALTER TABLE [postulaciones] ADD FOREIGN KEY ([usuario_id]) REFERENCES [usuarios] ([id])
GO

ALTER TABLE [publicaciones] ADD FOREIGN KEY ([usuario_id]) REFERENCES [usuarios] ([id])
GO

ALTER TABLE [comentarios] ADD FOREIGN KEY ([publicacion_id]) REFERENCES [publicaciones] ([id])
GO

ALTER TABLE [comentarios] ADD FOREIGN KEY ([usuario_id]) REFERENCES [usuarios] ([id])
GO

ALTER TABLE [reacciones] ADD FOREIGN KEY ([publicacion_id]) REFERENCES [publicaciones] ([id])
GO

ALTER TABLE [reacciones] ADD FOREIGN KEY ([usuario_id]) REFERENCES [usuarios] ([id])
GO

ALTER TABLE [actividades] ADD FOREIGN KEY ([usuario_id]) REFERENCES [usuarios] ([id])
GO

ALTER TABLE [alertas_seguridad] ADD FOREIGN KEY ([usuario_id]) REFERENCES [usuarios] ([id])
GO
---------------------------------------------------------------
-- DATOS POR DEFECTO (SQL SERVER)
---------------------------------------------------------------

---------------------------------------------------------------
-- DATOS POR DEFECTO (SQL SERVER)
---------------------------------------------------------------

-- ROLES
INSERT INTO roles (nombre, descripcion, creado_en) VALUES
('admin', 'Administrador del sistema', GETDATE()),
('empresa', 'Publicador de ofertas / reclutador', GETDATE()),
('postulante', 'Usuario que postula a una oferta', GETDATE());
GO

-- PERMISOS BÁSICOS
INSERT INTO permisos (codigo, descripcion) VALUES
('GESTION_USUARIOS', 'Puede administrar usuarios'),
('PUBLICAR_OFERTA', 'Puede crear y editar ofertas'),
('POSTULAR', 'Puede postular a una oferta'),
('GESTION_CONTENIDO', 'Puede editar contenido estático'),
('VER_SEGURIDAD', 'Puede ver el registro de actividades');
GO

-- Asignar TODOS los permisos al rol admin (id=1)
INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 1, id FROM permisos;
GO

---------------------------------------------------------------
-- USUARIO ADMIN POR DEFECTO (ID = 1)
---------------------------------------------------------------
INSERT INTO usuarios
(
    rol_id,
    nombre,
    apellidos,
    correo,
    password,
    telefono,
    fecha_nacimiento,
    ubicacion_ciudad,
    ubicacion_pais,
    carrera,
    titulo_perfil,
    resumen,
    estado,
    creado_en
)
VALUES
(
    1,
    'Administrador',
    'General',
    'admin@emi.edu.bo',
    CONVERT(VARCHAR(64), HASHBYTES('SHA2_256', 'admin123'), 2),
    '+59170000000',
    '2000-01-01',
    N'La Paz',
    N'Bolivia',
    N'Escuela Militar de Ingeniería "Mcal. Antonio José de Sucre"',
    N'Administrador de la plataforma LinkedIn EMI',
    N'Cuenta administrativa para la gestión de usuarios, ofertas y seguridad.',
    'activo',
    GETDATE()
);
GO

---------------------------------------------------------------
-- PARÁMETROS DE LA PLATAFORMA
---------------------------------------------------------------
INSERT INTO parametros_plataforma (nombre, correo_contacto, zona_horaria, creado_por, creado_en)
VALUES
(N'LinkedIn EMI', N'contacto@emi.edu.bo', N'GMT-4', 1, GETDATE());
GO

---------------------------------------------------------------
-- SECCIONES HABILITABLES
---------------------------------------------------------------
INSERT INTO secciones_plataforma (nombre, slug, habilitada, actualizado_por, actualizado_en) VALUES
(N'Gestión de Usuarios', N'gestion-usuarios', 1, 1, GETDATE()),
(N'Convocatorias',       N'convocatorias',    1, 1, GETDATE()),
(N'Publicaciones',       N'publicaciones',    1, 1, GETDATE()),
(N'Seguridad',           N'seguridad',        1, 1, GETDATE());
GO

---------------------------------------------------------------
-- CONTENIDO ESTÁTICO INICIAL
---------------------------------------------------------------
INSERT INTO contenidos_estaticos (seccion_id, titulo, contenido, actualizado_por, actualizado_en)
VALUES
(
    3,
    N'Bienvenido a LinkedIn EMI',
    N'Plataforma de conexión profesional para estudiantes y egresados de la EMI.',
    1,
    GETDATE()
);
GO

---------------------------------------------------------------
-- CATEGORÍAS
---------------------------------------------------------------
INSERT INTO categorias (nombre, descripcion, creada_por, creado_en) VALUES
(N'Tecnología',      N'Empleos del área de informática y sistemas', 1, GETDATE()),
(N'Administración',  N'Cargos administrativos y contables',         1, GETDATE()),
(N'Ingeniería',      N'Convocatorias del área técnica y proyectos', 1, GETDATE());
GO

---------------------------------------------------------------
-- SUBCATEGORÍAS
---------------------------------------------------------------
INSERT INTO subcategorias (categoria_id, nombre, descripcion, creado_en) VALUES
(1, N'Desarrollo Web',        N'Frontend, Backend y Fullstack',       GETDATE()),
(1, N'Soporte/TI',            N'Soporte técnico, redes, help desk',  GETDATE()),
(2, N'Gestión de Proyectos',  N'Administración y planificación',     GETDATE()),
(3, N'Electromecánica',       N'Mantenimiento y control industrial', GETDATE());
GO

---------------------------------------------------------------
-- USUARIO POSTULANTE DEMO (ID = 2)
---------------------------------------------------------------
INSERT INTO usuarios
(
    rol_id,
    nombre,
    apellidos,
    correo,
    password,
    estado,
    creado_en
)
VALUES
(
    3,
    N'Postulante',
    N'Demo',
    N'postulante@emi.edu.bo',
    CONVERT(VARCHAR(64), HASHBYTES('SHA2_256', 'postu123'), 2),
    'activo',
    GETDATE()
);
GO

---------------------------------------------------------------
-- ALERTA DE SEGURIDAD DE EJEMPLO
---------------------------------------------------------------
INSERT INTO alertas_seguridad (usuario_id, tipo, detalle, atendido, creado_en)
VALUES
(1, N'acceso_sospechoso', N'Se detectó un acceso desde IP desconocida', 0, GETDATE());
GO

---------------------------------------------------
-- 10 USUARIOS POSTULANTES DE PRUEBA (IDs 3..12)
---------------------------------------------------
INSERT INTO usuarios (
    rol_id,
    nombre,
    apellidos,
    correo,
    password,
    estado,
    creado_en,
    foto,
    titulo_perfil,
    carrera,
    ubicacion_ciudad,
    ubicacion_pais
)
VALUES
(3, 'Alejandro', 'Torres',   'atorres@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
 'activo', GETDATE(), '/public/img/main.png',
 N'Estudiante de Ing. de Sistemas',
 N'Ingeniería de Sistemas', N'La Paz',      N'Bolivia'),

(3, 'Daniela',   'Rivera',   'drivera@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
 'activo', GETDATE(), '/public/img/main.png',
 N'Estudiante de Ingeniería Industrial',
 N'Ingeniería Industrial', N'Cochabamba',  N'Bolivia'),

(3, 'Gabriel',   'Soto',     'gsoto@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
 'activo', GETDATE(), '/public/img/main.png',
 N'Estudiante de Ingeniería Electrónica',
 N'Ingeniería Electrónica', N'La Paz',     N'Bolivia'),

(3, 'Camila',    'Herrera',  'cherrera@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
 'activo', GETDATE(), '/public/img/main.png',
 N'Estudiante de Administración de Empresas',
 N'Administración de Empresas', N'Santa Cruz', N'Bolivia'),

(3, 'Martín',    'Salazar',  'msalazar@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
 'activo', GETDATE(), '/public/img/main.png',
 N'Estudiante de Ingeniería Civil',
 N'Ingeniería Civil', N'Oruro',      N'Bolivia'),

(3, 'Sofía',     'Delgado',  'sdelgado@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
 'activo', GETDATE(), '/public/img/main.png',
 N'Estudiante de Ingeniería Ambiental',
 N'Ingeniería Ambiental', N'La Paz',   N'Bolivia'),

(3, 'Diego',     'Campos',   'dcampos@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
 'activo', GETDATE(), '/public/img/main.png',
 N'Estudiante de Telecomunicaciones',
 N'Ingeniería en Telecomunicaciones', N'Tarija', N'Bolivia'),

(3, 'Valeria',   'Méndez',   'vmendez@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
 'activo', GETDATE(), '/public/img/main.png',
 N'Estudiante de Ingeniería Comercial',
 N'Ingeniería Comercial', N'Chuquisaca', N'Bolivia'),

(3, 'Andrés',    'Peralta',  'aperalta@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
 'activo', GETDATE(), '/public/img/main.png',
 N'Estudiante de Ingeniería Mecánica',
 N'Ingeniería Mecánica', N'Potosí',   N'Bolivia'),

(3, 'Laura',     'Cárdenas', 'lcardenas@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
 'activo', GETDATE(), '/public/img/main.png',
 N'Estudiante de Informática',
 N'Ingeniería de Sistemas', N'Beni',   N'Bolivia');
GO

---------------------------------------------------
-- 2️⃣ Insertar 2 publicaciones por cada usuario (IDs 2..11)
---------------------------------------------------
INSERT INTO publicaciones (usuario_id, contenido, imagen, creado_en)
VALUES
-- Usuario 2 (Postulante Demo)
(2,  N'Empezando mi carrera en ciberseguridad en la EMI.', '/uploads/publicaciones/main.png', GETDATE()),
(2,  N'Nuevo proyecto de seguridad web completado con éxito.', '/uploads/publicaciones/main.png', GETDATE()),

-- Usuario 3
(3,  N'Participé en un curso de redes Cisco, aprendí mucho.', '/uploads/publicaciones/main.png', GETDATE()),
(3,  N'Trabajando en mi portafolio como desarrollador backend.', '/uploads/publicaciones/main.png', GETDATE()),

-- Usuario 4
(4,  N'Primer día en prácticas profesionales en la EMI.', '/uploads/publicaciones/main.png', GETDATE()),
(4,  N'Super contenta con mi equipo de trabajo, aprendiendo día a día.', '/uploads/publicaciones/main.png', GETDATE()),

-- Usuario 5
(5,  N'Iniciando un nuevo proyecto de desarrollo en Python.', '/uploads/publicaciones/main.png', GETDATE()),
(5,  N'Probando herramientas de automatización con Selenium.', '/uploads/publicaciones/main.png', GETDATE()),

-- Usuario 6
(6,  N'Explorando la administración de servidores Linux.', '/uploads/publicaciones/main.png', GETDATE()),
(6,  N'Configuré un entorno de pruebas en Docker, muy útil.', '/uploads/publicaciones/main.png', GETDATE()),

-- Usuario 7
(7,  N'Trabajando en un análisis de vulnerabilidades con Nmap.', '/uploads/publicaciones/main.png', GETDATE()),
(7,  N'Primera experiencia usando Metasploit en entornos controlados.', '/uploads/publicaciones/main.png', GETDATE()),

-- Usuario 8
(8,  N'Estudiando seguridad ofensiva con S4vitar.', '/uploads/publicaciones/main.png', GETDATE()),
(8,  N'Mi primer laboratorio en Hack The Box completado.', '/uploads/publicaciones/main.png', GETDATE()),

-- Usuario 9
(9,  N'Aprendiendo más sobre administración de bases de datos SQL Server.', '/uploads/publicaciones/main.png', GETDATE()),
(9,  N'Implementando triggers y stored procedures en proyectos.', '/uploads/publicaciones/main.png', GETDATE()),

-- Usuario 10
(10, N'Trabajando en un dashboard en Power BI para mi práctica.', '/uploads/publicaciones/main.png', GETDATE()),
(10, N'Integrando datos desde API REST hacia mi sistema.', '/uploads/publicaciones/main.png', GETDATE()),

-- Usuario 11
(11, N'Practicando desarrollo web con PHP y MySQL.', '/uploads/publicaciones/main.png', GETDATE()),
(11, N'Construyendo mi primer sistema de gestión estudiantil.', '/uploads/publicaciones/main.png', GETDATE());
GO



/* =========================================================
   1) 30 USUARIOS ADICIONALES (ROL POSTULANTE = 3)
   ========================================================= */
DECLARE @i INT = 1;

WHILE @i <= 30
BEGIN
    INSERT INTO usuarios (
        rol_id,
        nombre,
        apellidos,
        correo,
        password,
        estado,
        creado_en,
        foto,
        titulo_perfil,
        carrera,
        ubicacion_ciudad,
        ubicacion_pais
    )
    VALUES (
        3,  -- postulante
        CONCAT('Usuario Demo ', @i),
        'Apellidos',
        CONCAT('usuario_demo', @i, '@emi.edu.bo'),
        CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','usuario123'), 2),
        'activo',
        GETDATE(),
        '/public/img/main.png',
        N'Estudiante de la EMI',
        N'Carrera EMI',
        N'La Paz',
        N'Bolivia'
    );

    SET @i += 1;
END;
GO

/* =========================================================
   2) RELLENAR OFERTAS HASTA TENER 50 TRABAJOS EN TOTAL
   ========================================================= */
DECLARE @ofertasActuales INT = (SELECT COUNT(*) FROM ofertas);
DECLARE @faltan          INT = 8 - ISNULL(@ofertasActuales, 0);

IF @faltan > 0
BEGIN
    DECLARE @j          INT = 1;
    DECLARE @cat        INT;
    DECLARE @sub        INT;
    DECLARE @empresaId  INT;
    DECLARE @salMin     DECIMAL(12,2);
    DECLARE @salMax     DECIMAL(12,2);

    -- Tomamos una empresa (usuario con rol_id = 2) para publicar
    SET @empresaId = (SELECT TOP 1 id FROM usuarios WHERE rol_id = 2 ORDER BY id);

    -- Si no hubiera empresas, usamos el admin (id = 1) para evitar error de FK
    IF @empresaId IS NULL
        SET @empresaId = 1;

    WHILE @j <= @faltan
    BEGIN
        -- Categoría 1..3
        SET @cat = ((@j - 1) % 3) + 1;

        -- Subcategoría según categoría
        IF @cat = 1       -- Tecnología
            SET @sub = CASE WHEN (@j % 2) = 0 THEN 1 ELSE 2 END;   -- Desarrollo Web / Soporte TI
        ELSE IF @cat = 2  -- Administración
            SET @sub = 3;                                         -- Gestión de Proyectos
        ELSE               -- Ingeniería
            SET @sub = 4;                                         -- Electromecánica

        -- Sueldos de ejemplo
        SET @salMin = CAST(2000 + (@cat * 400) + (@j * 10) AS DECIMAL(12,2));
        SET @salMax = @salMin + 1000;

        INSERT INTO ofertas (
            usuario_id,
            categoria_id,
            subcategoria_id,
            titulo,
            descripcion,
            ubicacion,
            tipo_jornada,
            modalidad,
            experiencia_min,
            salario_min,
            salario_max,
            beneficios,
            documento_adj,
            estado,
            publicado_en,
            actualizado_en,
            contacto_reclutador,
            imagen_empresa
        )
        VALUES (
            @empresaId,
            @cat,
            @sub,
            CONCAT('Puesto Demo ', @ofertasActuales + @j),
            N'Oferta de trabajo de prueba generada automáticamente para probar la plataforma LinkedIn EMI. Ideal para tests de listados, filtros y postulaciones.',
            CASE 
                WHEN @cat = 1 THEN N'La Paz, Bolivia'
                WHEN @cat = 2 THEN N'Santa Cruz, Bolivia'
                ELSE N'Cochabamba, Bolivia'
            END,
            CASE 
                WHEN (@j % 3) = 0 THEN N'Medio tiempo'
                WHEN (@j % 3) = 1 THEN N'Tiempo completo'
                ELSE N'Prácticas'
            END,
            CASE 
                WHEN (@j % 2) = 0 THEN N'Presencial'
                ELSE N'Híbrido'
            END,
            (@j % 3),     -- años de experiencia (0,1,2)
            @salMin,
            @salMax,
            N'Seguro básico; Posibilidad de crecimiento; Ambiente colaborativo.',
            NULL,
            'activa',
            DATEADD(DAY, -(@faltan - @j), GETDATE()),
            GETDATE(),
            CONCAT('reclutador', @j, '@empresa-demo.com'),
            'main.png'  -- <<< solo nombre de archivo, para usar /public/img/main.png
        );

        SET @j += 1;
    END;
END;
GO



/* =========================================================
   3) PUBLICACIONES PARA TODOS LOS USUARIOS QUE NO TENGAN
      (USA /uploads/publicaciones/main.png)
   ========================================================= */
INSERT INTO publicaciones (usuario_id, contenido, imagen, creado_en)
SELECT 
    u.id,
    CONCAT(
        N'Publicación demo automática del usuario ',
        u.nombre, ' ', ISNULL(u.apellidos, '')
    ),
    '/uploads/publicaciones/main.png',
    DATEADD(MINUTE, -u.id, GETDATE())
FROM usuarios u
WHERE u.id NOT IN (
    SELECT DISTINCT usuario_id FROM publicaciones
);
GO

INSERT INTO usuarios (
    rol_id,
    nombre,
    apellidos,
    correo,
    password,
    telefono,
    ubicacion_ciudad,
    ubicacion_pais,
    titulo_perfil,
    resumen,
    foto,
    estado,
    creado_en
)
VALUES
(2, 'EMI Jobs', 'Bolivia', 'empresa1@emi.edu.bo',
 CONVERT(VARCHAR(64), HASHBYTES('SHA2_256','empresa123'), 2),
 '+59172000001', N'La Paz', N'Bolivia',
 N'Bolsa de trabajo EMI',
 N'Cuenta de empresa para publicar oportunidades laborales.',
 '/public/img/main.png',
 'activo', GETDATE());
GO
