# ToyLab • UFOPA – Portfólio (PHP 8 + MySQL + Vanilla JS)

## Stack
- HTML5, CSS3 (custom properties), JS vanilla
- PHP 8+, PDO + prepared statements
- MySQL (utf8mb4)
- XAMPP/Linux Mint

## Features
- Home com **filtros por categoria/subcategoria**, **busca**, **paginação infinita**, **placeholders esqueléticos**, **lightbox acessível**
- **Dark mode** persistente (localStorage)
- Seção **Em Desenvolvimento**
- Páginas: **Produto**, **Equipe**, **História (timeline)**, **Notícias** e **Notícia**
- **Contato** com validação, **CSRF**, **honeypot**, **rate-limit** (5/10min), persistência no MySQL
- **Admin** com login seguro (bcrypt), CRUDs: **Produtos** (+ upload múltiplo seguro), **Categorias/Sub**, **Equipe**, **Posts**, **Milestones**, **Parcerias**, **Mensagens**
- Segurança: CSP, no-sniff, sessões httponly, .htaccess bloqueando execução em uploads, whitelist MIME, tamanho máx, `getimagesize`, `htmlspecialchars`

## Deploy rápido
1. Copie a pasta `toylab` para `/opt/lampp/htdocs/`
2. Inicie XAMPP: `sudo /opt/lampp/lampp start`
3. Crie BD `toylab` e importe `database/schema.sql` + `database/seed.sql`
4. Gere o **hash** da senha admin:
   ```bash
   php -r 'echo password_hash("Admin@123", PASSWORD_DEFAULT), PHP_EOL;'


admin@toylab.ufopa.br
12345678