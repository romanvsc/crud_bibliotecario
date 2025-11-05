-- Datos de prueba para el Sistema de Biblioteca
USE biblioteca;

-- Insertar usuarios del sistema (para login)
INSERT INTO usuarios_sistema (usuario, password, nombre, email, rol) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'admin@biblioteca.com', 'admin');

-- Nota: La contraseña para todos los usuarios es "password"

-- Insertar usuarios (lectores/socios de la biblioteca)
INSERT INTO usuarios (nombre_completo, email, telefono, direccion, dni, fecha_registro, estado) VALUES
('Juan Pérez García', 'juan.perez@email.com', '555-0101', 'Calle Principal 123, Madrid', '12345678A', '2024-01-15', 'activo'),
('Ana Martínez López', 'ana.martinez@email.com', '555-0102', 'Avenida Central 456, Barcelona', '23456789B', '2024-02-20', 'activo'),
('Luis Fernández Ruiz', 'luis.fernandez@email.com', '555-0103', 'Plaza Mayor 789, Valencia', '34567890C', '2024-03-10', 'activo'),
('Carmen Sánchez Díaz', 'carmen.sanchez@email.com', '555-0104', 'Calle Nueva 321, Sevilla', '45678901D', '2024-04-05', 'activo'),
('Miguel Torres Vega', 'miguel.torres@email.com', '555-0105', 'Avenida España 654, Bilbao', '56789012E', '2024-05-12', 'suspendido'),
('Laura Ramírez Castro', 'laura.ramirez@email.com', '555-0106', 'Calle Real 987, Málaga', '67890123F', '2024-06-18', 'activo'),
('David Jiménez Moreno', 'david.jimenez@email.com', '555-0107', 'Plaza España 147, Zaragoza', '78901234G', '2024-07-22', 'activo'),
('Elena Romero Gil', 'elena.romero@email.com', '555-0108', 'Avenida Libertad 258, Murcia', '89012345H', '2024-08-30', 'activo');

-- Insertar libros de prueba
INSERT INTO libros (titulo, autor, isbn, editorial, anio, categoria, descripcion, estado) VALUES
('Cien años de soledad', 'Gabriel García Márquez', '978-84-376-0494-7', 'Sudamericana', 1967, 'Realismo mágico', 'Obra maestra del realismo mágico latinoamericano', 'disponible'),
('Don Quijote de la Mancha', 'Miguel de Cervantes', '978-84-08-04782-2', 'Planeta', 1605, 'Clásico', 'Obra cumbre de la literatura española', 'disponible'),
('1984', 'George Orwell', '978-84-663-0001-1', 'Debolsillo', 1949, 'Ciencia ficción', 'Distopía totalitaria sobre el control social', 'prestado'),
('El amor en los tiempos del cólera', 'Gabriel García Márquez', '978-84-376-2576-7', 'Oveja Negra', 1985, 'Romance', 'Historia de amor épica que trasciende el tiempo', 'disponible'),
('La sombra del viento', 'Carlos Ruiz Zafón', '978-84-9838-288-5', 'Planeta', 2001, 'Misterio', 'Misterio en la Barcelona de posguerra', 'prestado'),
('Harry Potter y la Piedra Filosofal', 'J.K. Rowling', '978-0-7475-3269-9', 'Salamandra', 1997, 'Fantasía', 'Primera entrega de la saga Harry Potter', 'disponible'),
('El Hobbit', 'J.R.R. Tolkien', '978-0-439-13959-5', 'Minotauro', 1937, 'Fantasía', 'La precuela de El Señor de los Anillos', 'disponible'),
('Matar a un ruiseñor', 'Harper Lee', '978-0-06-112008-4', 'HarperCollins', 1960, 'Drama', 'Clásico de la literatura estadounidense sobre racismo', 'prestado'),
('El Código Da Vinci', 'Dan Brown', '978-0-7432-7356-5', 'Umbriel', 2003, 'Thriller', 'Thriller de misterio sobre conspiración religiosa', 'disponible'),
('El guardián entre el centeno', 'J.D. Salinger', '978-0-316-76948-0', 'Alianza', 1951, 'Ficción', 'Novela de iniciación adolescente', 'disponible'),
('Orgullo y prejuicio', 'Jane Austen', '978-0-452-28423-4', 'Penguin', 1813, 'Romance', 'Clásico de la literatura inglesa romántica', 'disponible'),
('El gran Gatsby', 'F. Scott Fitzgerald', '978-0-7432-4722-5', 'Scribner', 1925, 'Ficción', 'Novela sobre la era del jazz estadounidense', 'disponible'),
('Crimen y castigo', 'Fiódor Dostoyevski', '978-0-06-112241-5', 'Alianza', 1866, 'Drama psicológico', 'Exploración profunda de la psicología criminal', 'disponible'),
('Ángeles y demonios', 'Dan Brown', '978-0-7432-7357-2', 'Umbriel', 2000, 'Thriller', 'Precuela de El Código Da Vinci sobre ciencia y religión', 'prestado'),
('El señor de las moscas', 'William Golding', '978-0-141-18902-7', 'Alianza', 1954, 'Ficción', 'Alegoría sobre la naturaleza humana y la civilización', 'disponible'),
('La casa de los espíritus', 'Isabel Allende', '978-84-204-2676-5', 'Plaza & Janés', 1982, 'Realismo mágico', 'Saga familiar chilena con elementos mágicos', 'disponible'),
('El túnel', 'Ernesto Sábato', '978-84-322-0044-7', 'Seix Barral', 1948, 'Ficción psicológica', 'Novela existencialista sobre obsesión y locura', 'disponible'),
('Rayuela', 'Julio Cortázar', '978-84-204-0114-7', 'Alfaguara', 1963, 'Experimental', 'Novela experimental del boom latinoamericano', 'disponible');

-- Insertar préstamos de prueba
-- Préstamos activos (fecha_dev_real es NULL)
INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, fecha_devolucion, fecha_dev_real, estado, observaciones) VALUES
(3, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 5 DAY), INTERVAL 14 DAY), NULL, 'activo', 'Primer préstamo del usuario'),
(5, 2, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 10 DAY), INTERVAL 14 DAY), NULL, 'activo', 'Usuario frecuente'),
(8, 3, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 20 DAY), INTERVAL 14 DAY), NULL, 'vencido', 'Préstamo vencido - contactar al usuario'),
(14, 4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 3 DAY), INTERVAL 14 DAY), NULL, 'activo', 'Préstamo reciente');

-- Préstamos devueltos (fecha_dev_real completada)
INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, fecha_devolucion, fecha_dev_real, estado, observaciones) VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 45 DAY), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 32 DAY), 'devuelto', 'Devuelto en buen estado'),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 60 DAY), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 47 DAY), 'devuelto', 'Devuelto con ligero retraso'),
(6, 3, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 30 DAY), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 18 DAY), 'devuelto', 'Usuario solicitó renovación previa'),
(7, 6, DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 25 DAY), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 13 DAY), 'devuelto', 'Lectura rápida - devuelto antes'),
(9, 7, DATE_SUB(CURDATE(), INTERVAL 50 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 50 DAY), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 37 DAY), 'devuelto', 'Sin problemas'),
(10, 8, DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 40 DAY), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 28 DAY), 'devuelto', 'Excelente estado del libro');
