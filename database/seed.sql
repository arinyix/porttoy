-- database/seed.sql


USE `toylab`;

-- Admin (Atualize o hash no README)
INSERT INTO users (name, email, password_hash, created_at) VALUES
('Administrador', 'admin@toylab.ufopa.br', '__REPLACE_WITH_BCRYPT_HASH__', NOW());

-- Categorias principais
INSERT INTO categories (name, parent_id) VALUES
('Brinquedos Educativos', NULL),
('Jogos Digitais', NULL),
('Protótipos', NULL),
('Impressão 3D', NULL),
('Corte a Laser', NULL);

-- Subcategorias Impressão 3D
INSERT INTO categories (name, parent_id)
SELECT 'PLA', id FROM categories WHERE name='Impressão 3D' LIMIT 1;
INSERT INTO categories (name, parent_id)
SELECT 'ABS', id FROM categories WHERE name='Impressão 3D' LIMIT 1;
INSERT INTO categories (name, parent_id)
SELECT 'Resina', id FROM categories WHERE name='Impressão 3D' LIMIT 1;

-- Subcategorias Corte a Laser
INSERT INTO categories (name, parent_id)
SELECT 'MDF', id FROM categories WHERE name='Corte a Laser' LIMIT 1;
INSERT INTO categories (name, parent_id)
SELECT 'Acrílico', id FROM categories WHERE name='Corte a Laser' LIMIT 1;

-- Produtos demo
INSERT INTO products (title, slug, description, category_id, subcategory_id, status, featured, created_at, updated_at)
VALUES
('Quebra-cabeça Amazônico', CONCAT('quebra-cabeca-amazonico-', FLOOR(RAND()*10000)), 'Puzzle educativo com fauna/flora amazônica.', (SELECT id FROM categories WHERE name='Brinquedos Educativos' LIMIT 1), NULL, 'disponivel', 1, NOW(), NOW()),
('Jogo Digital Tapajós Runner', CONCAT('tapajos-runner-', FLOOR(RAND()*10000)), 'Infinite runner tematizado no Tapajós.', (SELECT id FROM categories WHERE name='Jogos Digitais' LIMIT 1), NULL, 'disponivel', 0, NOW(), NOW()),
('Suporte de Ferramentas (PLA)', CONCAT('suporte-ferramentas-pla-', FLOOR(RAND()*10000)), 'Suporte impresso em 3D para bancada.', (SELECT id FROM categories WHERE name='Impressão 3D' LIMIT 1), (SELECT id FROM categories WHERE name='PLA' LIMIT 1), 'disponivel', 0, NOW(), NOW()),
('Chaveiro Boto (Acrílico)', CONCAT('chaveiro-boto-acrilico-', FLOOR(RAND()*10000)), 'Chaveiro cortado a laser em acrílico.', (SELECT id FROM categories WHERE name='Corte a Laser' LIMIT 1), (SELECT id FROM categories WHERE name='Acrílico' LIMIT 1), 'disponivel', 1, NOW(), NOW()),
('Miniatura Palafita', CONCAT('miniatura-palafita-', FLOOR(RAND()*10000)), 'Protótipo de palafita amazônica.', (SELECT id FROM categories WHERE name='Protótipos' LIMIT 1), NULL, 'em_desenvolvimento', 0, NOW(), NOW()),
('Case de Sensor ABS', CONCAT('case-sensor-abs-', FLOOR(RAND()*10000)), 'Case resistente para eletrônica.', (SELECT id FROM categories WHERE name='Impressão 3D' LIMIT 1), (SELECT id FROM categories WHERE name='ABS' LIMIT 1), 'em_desenvolvimento', 0, NOW(), NOW());

-- Imagens (placeholder)
INSERT INTO product_images (product_id, path, alt, created_at)VALUES
(1, 'public/uploads/quebra_cabeca.png', 'Quebra-cabeça Amazônico', NOW()),
(2, 'public/uploads/jogo.png', 'Jogo Digital Tapajós Runner', NOW()),
(3, 'public/uploads/holder.png', 'Suporte de Ferramentas (PLA)', NOW()),
(4, 'public/uploads/cha_boto.png', 'Chaveiro Boto (Acrílico)', NOW()),
(5, 'public/uploads/palafita.png', 'Miniatura Palafita', NOW()),
(6, 'public/uploads/case.png', 'Case de Sensor ABS', NOW());
SELECT id, 'public/uploads/placeholder.png', title, NOW() FROM products;

-- Equipe
INSERT INTO team (name, role, photo_path, lattes_url, created_at) VALUES
('Professor', 'Coordenador', 'public/uploads/9cbf5fddaf78d669293fee2cf14e2c99.jpg', NULL, NOW()),
('Maria', 'Bolsista', 'public/uploads/9cbf5fddaf78d669293fee2cf14e2c99.jpg', NULL, NOW()),
('Danilo', 'Bolsista', 'public/uploads/9cbf5fddaf78d669293fee2cf14e2c99.jpg', NULL, NOW()),
('Joanna', 'Colaboradora', 'public/uploads/9cbf5fddaf78d669293fee2cf14e2c99.jpg', NULL, NOW());

-- Posts
INSERT INTO posts (title, slug, excerpt, content, cover_path, published_at, created_at) VALUES
('TOYLab participa do JINU 2025', 'toylab-participa-jinu-2025', 'Laboratório apresentará projetos em bioeconomia e educação.', 'Conteúdo completo do post...', 'public/uploads/bioec.png', NOW(), NOW()),
('Nova oficina de impressão 3D', 'nova-oficina-impressao-3d', 'Inscrições abertas para capacitação prática.', 'Conteúdo completo do post...', 'public/uploads/impressao3d.png', NOW(), NOW());

-- Timeline
INSERT INTO milestones (title, description, event_date, created_at) VALUES
('Criação do Laboratório', 'Início das atividades do TOYLab.', '2019-08-01', NOW()),
('Primeiro prêmio', 'Reconhecimento em competição regional.', '2021-11-20', NOW()),
('Parceria com escolas', 'Programa “Mais Ciência nas Escolas”.', '2024-03-05', NOW());

-- Parcerias
INSERT INTO partners (name, logo_path, url, created_at) VALUES
('UFOPA', 'public/uploads/ufopa.png', 'https://www.ufopa.edu.br', NOW()),
('InTap', 'public/uploads/e6999f6412421cf458642581dced511a.webp', 'https://example.com/intap', NOW());

