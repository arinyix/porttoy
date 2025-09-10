-- database/seed.sql

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
INSERT INTO product_images (product_id, path, alt, created_at)
SELECT id, 'public/img/placeholder.png', title, NOW() FROM products;

-- Equipe
INSERT INTO team (name, role, photo_path, lattes_url, created_at) VALUES
('Professor', 'Coordenador', 'public/img/placeholder.png', NULL, NOW()),
('Maria', 'Bolsista', 'public/img/placeholder.png', NULL, NOW()),
('Danilo', 'Bolsista', 'public/img/placeholder.png', NULL, NOW()),
('Joanna', 'Colaboradora', 'public/img/placeholder.png', NULL, NOW());

-- Posts
INSERT INTO posts (title, slug, excerpt, content, cover_path, published_at, created_at) VALUES
('TOYLab participa do JINU 2025', 'toylab-participa-jinu-2025', 'Laboratório apresentará projetos em bioeconomia e educação.', 'Conteúdo completo do post...', 'public/img/placeholder.png', NOW(), NOW()),
('Nova oficina de impressão 3D', 'nova-oficina-impressao-3d', 'Inscrições abertas para capacitação prática.', 'Conteúdo completo do post...', 'public/img/placeholder.png', NOW(), NOW());

-- Timeline
INSERT INTO milestones (title, description, event_date, created_at) VALUES
('Criação do Laboratório', 'Início das atividades do TOYLab.', '2019-08-01', NOW()),
('Primeiro prêmio', 'Reconhecimento em competição regional.', '2021-11-20', NOW()),
('Parceria com escolas', 'Programa “Mais Ciência nas Escolas”.', '2024-03-05', NOW());

-- Parcerias
INSERT INTO partners (name, logo_path, url, created_at) VALUES
('UFOPA', 'public/img/placeholder.png', 'https://www.ufopa.edu.br', NOW()),
('InTap', 'public/img/placeholder.png', 'https://example.com/intap', NOW()),
('Edital X', 'public/img/placeholder.png', 'https://example.com/editalx', NOW());
