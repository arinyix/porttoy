# ToyLab • UFOPA – Portfólio (PHP 8 + MySQL + Vanilla JS)

Portfólio do Laboratório **ToyLab/UFOPA** com site público e **área administrativa** completa (CRUDs).  
O projeto prioriza acessibilidade, segurança (PDO/CSRF/Uploads seguros) e UX (dark mode, filtros dinâmicos e paginação infinita).

Programaçao WEB
BACHARELADO EM CIÊNCIA DA COMPUTAÇÃO
Maria de Fatima Mota da Silva

---

## ✅ O que o sistema faz

- **Home**: filtros por **categoria/subcategoria**, **busca por título**, **scroll infinito** com **skeletons**, seção **“Em Desenvolvimento”**.
- **Produtos**: cards e página de detalhe com **lightbox** acessível.
- **Equipe**: cards de membros (foto, função, Lattes).
- **História (Timeline)**: marcos carregados do MySQL (layout vertical).
- **Notícias/Blog**: listagem e página do post com capa.
- **Contato**: validação client/server, **CSRF**, **honeypot**, **rate-limit (5 envios/10min)**, mensagens salvas no MySQL.
- **Admin (/admin)**:
  - Login seguro (bcrypt + sessões `httponly`).
  - CRUDs: **Produtos** (+ **upload múltiplo seguro**), **Categorias/Subcategorias**, **Equipe**, **Notícias**, **Marcos (Timeline)**, **Parcerias**, **Mensagens**.
  - Dashboard com contadores e atalhos.
- **Segurança**: PDO prepared, `htmlspecialchars` em saídas, **CSRF** por formulário, bloqueio de execução em `/public/uploads`, headers (CSP/No-Sniff/Frame-Options).

---

## 🗂️ Estrutura (resumo)

htdocs/
  toylab/                     # (pasta do projeto)
    admin/
      categories/            # CRUD
      messages/
      milestones/
      partners/
      posts/
      products/
      team/
      partials/              # header/footer do admin
      index.php              # Dashboard
      login.php | logout.php
    config/
      auth.php               # middleware sessão
      config.php             # credenciais, PDO, headers de segurança
      csrf.php               # helpers CSRF
      functions.php          # helpers (db, uploads, utils, media_url, etc.)
    database/
      schema.sql             # criação das tabelas + índices/FKs
      seed.sql               # dados de exemplo
    public/
      css/styles.css
      js/main.js
      js/darkmode.js
      uploads/               # destino dos uploads (bloqueado p/ execução)
      .htaccess
    templates/
      header.php | footer.php
      components/            # product-card, filter-bar, timeline-item, lightbox
    tools/
      set_admin.php          # utilitário para criar/ajustar admin
    index.php                # Home
    produto.php
    equipe.php
    historia.php
    noticias.php
    noticia.php
    contato.php
    README.md



---

## 🧭 Como executar (Windows + XAMPP)

> Requisitos: **XAMPP para Windows** com Apache + MySQL ativos.

1. **Copie** a pasta do projeto para:
2. **Inicie** o Apache e o MySQL no **XAMPP Control Panel**.
3. **Crie o banco** de dados **toylab** no phpMyAdmin:
- Acesse: `http://localhost/phpmyadmin`
- Crie o BD com nome **toylab**
- Importe **em ordem**:
  - `database/schema.sql`
  - `database/seed.sql`
4. **Configurar conexão** (se necessário):
- Arquivo: `toylab\config\config.php`
- Em instalações padrão do XAMPP/Windows, os valores costumam ser:
  ```php
  $dbHost = 'localhost';
  $dbName = 'toylab';
  $dbUser = 'root';
  $dbPass = ''; // senha vazia por padrão no Windows
  ```
5. **Acessos**:
- **Site público**: `http://localhost/porttoy/`
- **Admin**: `http://localhost/porttoy/admin/`

> Observação: as funções `base_url()`/`asset()`/`media_url()` calculam o caminho automaticamente — não é preciso fixar `/toylab` no código.

---

## 🔐 Login da área administrativa

- **URL do admin:** `http://localhost/porttoy/admin/`
- **Credenciais (demo):**
- E-mail: `admin@toylab.ufopa.br`
- Senha:  `12345678`

> Se quiser **recriar/alterar** o usuário admin, acesse:  
> `http://localhost/porttoy/tools/set_admin.php`  
> Preencha e salve — o sistema grava o **hash** da senha no banco com `password_hash(...)`.

---

## 🧑‍🏫 Como navegar (professor)

### Site público
- **Home** (`/toylab/`): veja filtros por **categoria** e **subcategoria**, a busca, e a listagem infinita de produtos.  
- **Produto** (`/toylab/produto.php?id=...`): detalhe + imagens em lightbox.  
- **Equipe** (`/toylab/equipe.php`): cards da equipe.  
- **História** (`/toylab/historia.php`): timeline com marcos do laboratório.  
- **Notícias** (`/toylab/noticias.php`) e **Notícia** (`/toylab/noticia.php?id=...`).  
- **Contato** (`/toylab/contato.php`): formulário com CSRF + honeypot + rate limit.

### Admin (CRUDs)
- **Entrar no admin**: `http://localhost/toylab/admin/`
- **Dashboard**: cartões com contagens e atalhos.
- **Produtos**: `Admin → Produtos`
- Criar/editar produto, escolher categoria/subcategoria, status e destaque.
- **Upload de imagens** no item “Imagens” (JPG/PNG/WebP, até 3MB cada).
- **Categorias/Subcategorias**: `Admin → Categorias`
- **Equipe**: `Admin → Equipe` (foto é opcional).
- **Notícias**: `Admin → Notícias` (capa do post via upload).
- **Timeline**: `Admin → Timeline`
- **Parcerias**: `Admin → Parcerias` (logo + URL; aparecem no rodapé público).
- **Mensagens**: `Admin → Mensagens` (formulário do “Fale Conosco”).
- **Sair**: `Admin → Logout`.

---

## 🔒 Segurança aplicada

- **PDO + prepared statements** em todas as consultas.
- **CSRF** em formulários (`csrf_field()` + `csrf_validate()`).
- **Escapes** com `htmlspecialchars` via helper `e()`.
- **Sessões** c/ `httponly` e regeneração após login.
- **Uploads**:
- Extensões e **MIME real** validados (finfo).
- Tamanho máx 3MB, `getimagesize` para verificar imagem.
- Nome aleatório e salvamento em `public/uploads/` (execução **bloqueada**).
- **Headers**: CSP, `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`.

---

## 🔎 Dicas & Solução de problemas

- **CSS/JS não carregam?**  
Acesse `http://localhost/toylab/public/css/styles.css`. Se abrir, a rota está correta.  
Confira se `config/config.php` está apontando para o BD **toylab**.

- **Imagens do site não aparecem (mas no admin aparecem):**  
O caminho salvo no BD deve ser do tipo `public/uploads/arquivo.ext`.  
No template público use **`media_url($path)`** para montar a URL exibível.

- **Erro de CSRF ao enviar formulários:**  
Verifique se o `<form>` tem `<?= csrf_field(); ?>` e se o `POST` chama `csrf_validate(...)`.

---

## 📌 Observações finais

- Nome do banco **DEVE SER**: `toylab`.  
- Qualquer conteúdo inicial (categorias, produtos de exemplo, equipe etc.) já vem em **`database/seed.sql`**.  
- O tema utiliza **dark mode** persistente, foco visível e contraste AA.

---

### Contatos (demo) / Admin
- **Admin:** `admin@toylab.ufopa.br` / `12345678`
- **Público:** `http://localhost/porttoy/`
- **Admin:** `http://localhost/porttoy/admin/`

Recomenda-se trocar após o primeiro login.
Ou use tools/set_admin.php para definir um novo hash.

> Este projeto é acadêmico, voltado à apresentação do ToyLab/UFOPA.  
> Tecnologias: PHP 8, MySQL, HTML/CSS/JS (vanilla).

---

## ✉️ E-mail / SMTP (.env)

O projeto envia:
- Notificações do **Fale Conosco** para o e-mail do laboratório (admin).
- **Auto-reply** opcional para quem preenche o formulário.
- E-mails a partir do **Admin → Mensagens** (responder/compôr).

Para **não versionar credenciais**, usamos variáveis em `.env`.

### Passo a passo

1. **Copie o exemplo** e edite:
   ```bash
   cp .env.example .env

porttoyy@gmail.com
porttoy123$
menu jrno dwmu ptqj
