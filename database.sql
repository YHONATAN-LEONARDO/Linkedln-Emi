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

CREATE TABLE [usuarios] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [rol_id] int NOT NULL,
  [nombre] varchar(150) NOT NULL,
  [correo] varchar(150) UNIQUE NOT NULL,
  [password] varchar(255) NOT NULL,
  [educacion] varchar(200),
  [ubicacion] varchar(150),
  [telefono] varchar(30),
  [fecha_nacimiento] date,
  [foto] varchar(255),
  [cv] varchar(255),
  [estado] varchar(20) DEFAULT 'activo',
  [creado_en] datetime,
  [actualizado_en] datetime
)
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
  [estado] varchar(20) DEFAULT 'en_revision',
  [calificacion] int,
  [mensaje] nvarchar(max),
  [creado_en] datetime
)
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

-- ROLES
INSERT INTO roles (nombre, descripcion, creado_en) VALUES
('admin', 'Administrador del sistema', GETDATE()),
('empresa', 'Publicador de ofertas / reclutador', GETDATE()),
('postulante', 'Usuario que postula a ofertas', GETDATE());

-- PERMISOS BÁSICOS
INSERT INTO permisos (codigo, descripcion) VALUES
('GESTION_USUARIOS', 'Puede administrar usuarios'),
('PUBLICAR_OFERTA', 'Puede crear y editar ofertas'),
('POSTULAR', 'Puede postular a una oferta'),
('GESTION_CONTENIDO', 'Puede editar contenido estático'),
('VER_SEGURIDAD', 'Puede ver el registro de actividades');

-- Asignar TODOS los permisos al rol admin (id=1)
INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 1, id FROM permisos;

-- USUARIO ADMIN POR DEFECTO
INSERT INTO usuarios
(rol_id, nombre, correo, password, educacion, ubicacion, telefono, fecha_nacimiento, estado, creado_en)
VALUES
(1, 'Administrador General', 'admin@emi.edu.bo',
 HASHBYTES('SHA2_256', 'admin123'),
 N'Escuela Militar de Ingeniería "Mcal. Antonio José de Sucre"',
 N'La Paz, La Paz, Bolivia',
 N'+59170000000',
 '2000-01-01',
 'activo',
 GETDATE());

-- PARÁMETROS DE LA PLATAFORMA
INSERT INTO parametros_plataforma (nombre, correo_contacto, zona_horaria, creado_por, creado_en)
VALUES
(N'LinkedIn EMI', N'contacto@emi.edu.bo', N'GMT-4', 1, GETDATE());

-- SECCIONES HABILITABLES
INSERT INTO secciones_plataforma (nombre, slug, habilitada, actualizado_por, actualizado_en) VALUES
(N'Gestión de Usuarios', N'gestion-usuarios', 1, 1, GETDATE()),
(N'Convocatorias', N'convocatorias', 1, 1, GETDATE()),
(N'Publicaciones', N'publicaciones', 1, 1, GETDATE()),
(N'Seguridad', N'seguridad', 1, 1, GETDATE());

-- CONTENIDO ESTÁTICO INICIAL
INSERT INTO contenidos_estaticos (seccion_id, titulo, contenido, actualizado_por, actualizado_en)
VALUES
(3, N'Bienvenido a LinkedIn EMI',
 N'Plataforma de conexión profesional para estudiantes y egresados de la EMI.',
 1, GETDATE());

-- CATEGORÍAS
INSERT INTO categorias (nombre, descripcion, creada_por, creado_en) VALUES
(N'Tecnología', N'Empleos del área de informática y sistemas', 1, GETDATE()),
(N'Administración', N'Cargos administrativos y contables', 1, GETDATE()),
(N'Ingeniería', N'Convocatorias del área técnica y proyectos', 1, GETDATE());

-- SUBCATEGORÍAS
INSERT INTO subcategorias (categoria_id, nombre, descripcion, creado_en) VALUES
(1, N'Desarrollo Web', N'Frontend, Backend y Fullstack', GETDATE()),
(1, N'Soporte/TI', N'Soporte técnico, redes, help desk', GETDATE()),
(2, N'Gestión de Proyectos', N'Administración y planificación', GETDATE()),
(3, N'Electromecánica', N'Mantenimiento y control industrial', GETDATE());

-- OFERTA DE EJEMPLO
INSERT INTO ofertas
(usuario_id, categoria_id, subcategoria_id, titulo, descripcion, ubicacion, tipo_jornada, modalidad, documento_adj, estado, publicado_en, actualizado_en)
VALUES
(1, 1, 1,
 N'Asistente Virtual (The Link Housing)',
 N'Apoyo administrativo remoto. Uso de Google Sheets. Inglés intermedio.',
 N'Bolivia (remoto)',
 N'completa',
 N'remoto',
 N'CV_Asistente.pdf',
 N'aprobado',
 GETDATE(),
 GETDATE());

-- USUARIO POSTULANTE DEMO
INSERT INTO usuarios
(rol_id, nombre, correo, password, estado, creado_en)
VALUES
(3, N'Postulante Demo', N'postulante@emi.edu.bo',
 HASHBYTES('SHA2_256', 'postu123'),
 'activo',
 GETDATE());

-- POSTULACIÓN DE EJEMPLO
INSERT INTO postulaciones (oferta_id, usuario_id, estado, calificacion, mensaje, creado_en)
VALUES
(1, 2, 'en_revision', NULL, N'Me interesa la vacante', GETDATE());

-- PUBLICACIÓN DE EJEMPLO
INSERT INTO publicaciones (usuario_id, contenido, imagen, creado_en)
VALUES
(1, N'¡Bienvenidos a la nueva red profesional de la EMI!', N'public/img/image.png', GETDATE());

-- COMENTARIO DE EJEMPLO
INSERT INTO comentarios (publicacion_id, usuario_id, comentario, creado_en)
VALUES
(1, 1, N'Este es un comentario de prueba.', GETDATE());

-- REACCIÓN DE EJEMPLO
INSERT INTO reacciones (publicacion_id, usuario_id, tipo, creado_en)
VALUES
(1, 1, N'like', GETDATE());

-- ACTIVIDAD / LOG DE EJEMPLO
INSERT INTO actividades (usuario_id, accion, descripcion, ip, creado_en)
VALUES
(1, N'login', N'Inicio de sesión exitoso', N'127.0.0.1', GETDATE());

-- ALERTA DE SEGURIDAD DE EJEMPLO
INSERT INTO alertas_seguridad (usuario_id, tipo, detalle, atendido, creado_en)
VALUES
(1, N'acceso_sospechoso', N'Se detectó un acceso desde IP desconocida', 0, GETDATE());