CREATE TABLE [roles] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [nombre] varchar(50) UNIQUE NOT NULL,
  [descripcion] text,
  [creado_en] datetime
)
GO

CREATE TABLE [permisos] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [codigo] varchar(100) UNIQUE NOT NULL,
  [descripcion] text
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
  [habilitada] bool DEFAULT (true),
  [actualizado_por] int,
  [actualizado_en] datetime
)
GO

CREATE TABLE [contenidos_estaticos] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [seccion_id] int NOT NULL,
  [titulo] varchar(150),
  [contenido] text,
  [actualizado_por] int,
  [actualizado_en] datetime
)
GO

CREATE TABLE [categorias] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [nombre] varchar(100) NOT NULL,
  [descripcion] text,
  [creada_por] int,
  [creado_en] datetime
)
GO

CREATE TABLE [subcategorias] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [categoria_id] int NOT NULL,
  [nombre] varchar(100) NOT NULL,
  [descripcion] text,
  [creado_en] datetime
)
GO

CREATE TABLE [ofertas] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [usuario_id] int NOT NULL,
  [categoria_id] int,
  [subcategoria_id] int,
  [titulo] varchar(150) NOT NULL,
  [descripcion] text,
  [ubicacion] varchar(100),
  [tipo_jornada] varchar(50),
  [modalidad] varchar(50),
  [documento_adj] varchar(255),
  [estado] varchar(20) DEFAULT 'en_revision',
  [publicado_en] datetime,
  [actualizado_en] datetime
)
GO

CREATE TABLE [postulaciones] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [oferta_id] int NOT NULL,
  [usuario_id] int NOT NULL,
  [estado] varchar(20) DEFAULT 'en_revision',
  [calificacion] int,
  [mensaje] text,
  [creado_en] datetime
)
GO

CREATE TABLE [publicaciones] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [usuario_id] int NOT NULL,
  [contenido] text,
  [imagen] varchar(255),
  [creado_en] datetime
)
GO

CREATE TABLE [comentarios] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [publicacion_id] int NOT NULL,
  [usuario_id] int NOT NULL,
  [comentario] text NOT NULL,
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
  [descripcion] text,
  [ip] varchar(50),
  [creado_en] datetime
)
GO

CREATE TABLE [alertas_seguridad] (
  [id] int PRIMARY KEY IDENTITY(1, 1),
  [usuario_id] int NOT NULL,
  [tipo] varchar(100) NOT NULL,
  [detalle] text,
  [atendido] bool DEFAULT (false),
  [creado_en] datetime
)
GO

CREATE UNIQUE INDEX [reacciones_index_0] ON [reacciones] ("publicacion_id", "usuario_id")
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
