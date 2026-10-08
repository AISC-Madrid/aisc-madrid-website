-- Contactos externos para difusión (directores de grado, profesores, personal UC3M...)
-- Se usan desde dashboard/send_test_email.php -> "Contactos" y se gestionan en dashboard/contacts/.
-- full_name se usa tal cual en el saludo ("Buenos días <full_name>,")
-- organization completa la frase "...interesante para los alumnos del <organization>..."

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  category VARCHAR(50) NOT NULL,
  organization VARCHAR(300) NULL,
  notes TEXT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_contacts_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_email_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  contact_id INT NOT NULL,
  template_name VARCHAR(255) NOT NULL,
  sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_contact_email_logs_contact (contact_id, template_name),
  CONSTRAINT fk_contact_email_logs_contact FOREIGN KEY (contact_id) REFERENCES contacts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Directores de titulación EPS UC3M (curso 2026-2027)
-- INSERT IGNORE: re-ejecutar el script no duplica contactos (email es UNIQUE)
INSERT IGNORE INTO contacts (full_name, email, category, organization, notes) VALUES
('Silvia Noemí Santalla Arribas', 'silvia.santalla@uc3m.es', 'degree_director', 'Grado en Ciencias 4U', NULL),
('Fernando Díaz de María', 'fernando.diaz@uc3m.es', 'degree_director', 'Grado en Ciencia e Ingeniería de Datos y del Doble Grado en Ciencia e Ingeniería de Datos e Ingeniería en Tecnologías de Telecomunicación', NULL),
('Gonzalo Sánchez Arriaga', 'gonzalo.sanchez@uc3m.es', 'degree_director', 'Grado en Ingeniería Aeroespacial', NULL),
('Gonzalo Ricardo Ríos Muñoz', 'gonzaloricardo.rios@uc3m.es', 'degree_director', 'Grado en Ingeniería Biomédica', NULL),
('Jorge Martínez Crespo', 'jorge.martinez@uc3m.es', 'degree_director', 'Grado en Ingeniería Eléctrica y del Grado en Ingeniería de la Energía', NULL),
('Almudena Lindoso Muñoz', 'almudena.lindoso@uc3m.es', 'degree_director', 'Grado en Ingeniería Electrónica Industrial y Automática', NULL),
('Eduardo Salas Colera', 'eduardo.salas@uc3m.es', 'degree_director', 'Grado en Ingeniería Física y del Doble Grado en Ingeniería Física e Ingeniería en Tecnologías Industriales', NULL),
('María Carmen Cámara Núñez', 'mariacarmen.camara@uc3m.es', 'degree_director', 'Grado en Ingeniería Informática (Leganés y Colmenarejo)', NULL),
('David Expósito Singh', 'david.exposito@uc3m.es', 'degree_director', 'Doble Grado en Ingeniería Informática y Administración de Empresas', NULL),
('Patricia Rubio Herrero', 'patricia.rubio@uc3m.es', 'degree_director', 'Grado en Ingeniería Mecánica', NULL),
('Álvaro Castro González', 'alvaro.castro@uc3m.es', 'degree_director', 'Grado en Ingeniería Robótica', NULL),
('Iván González Díaz', 'ivan.gonzalez@uc3m.es', 'degree_director', 'Grado en Ingeniería de Comunicaciones Móviles y Espaciales y del Grado en Ingeniería de Sonido e Imagen', NULL),
('Luis Miguel García Gutiérrez', 'luismiguel.garcia@uc3m.es', 'degree_director', 'Grado en Ingeniería en Tecnologías Industriales', NULL),
('José Alberto Hernández Gutiérrez', 'josealberto.hernandez@uc3m.es', 'degree_director', 'Grado en Ingeniería de Tecnologías de Telecomunicación y del Grado en Ingeniería de Internet', NULL),
('Pablo Álvarez Caudevilla', 'pablo.alvarez@uc3m.es', 'degree_director', 'Grado en Matemáticas y Computación', NULL),
('Fernando Fernández Rebollo', 'grado_inteligencia.artificial@uc3m.es', 'degree_director', 'Grado en Inteligencia Artificial', 'Buzón genérico del grado, no personal'),
('Víctor Bayona Revilla', 'victor.bayona@uc3m.es', 'degree_director', 'Grado en Matemática Aplicada', NULL),
('Javier García-Heras Carretero', 'javier.garcia-heras@uc3m.es', 'degree_director', 'Máster Universitario en Ingeniería Aeronáutica', NULL),
('Shirley Kalamis García Castillo', 'shirley.garcia@uc3m.es', 'degree_director', 'Máster Universitario en Ingeniería Industrial', NULL),
('Anabel Fraga Vázquez', 'anabel.fraga@uc3m.es', 'degree_director', 'Máster Universitario en Ingeniería Informática', NULL),
('Eva Rajo Iglesias', 'eva.rajo@uc3m.es', 'degree_director', 'Máster Universitario en Ingeniería de Telecomunicación', NULL);
