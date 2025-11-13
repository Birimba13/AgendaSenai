# Guia de Configuração .htaccess

## 📋 Visão Geral

Este projeto usa arquivos `.htaccess` para:
1. Permitir acesso aos arquivos PHP sem precisar digitar `/public/` na URL
2. Proteger diretórios sensíveis (`app/` e `config/`)
3. Manter a estrutura organizada para deploy

## 🗂️ Estrutura de Arquivos

```
2.0/
├── .htaccess              ← Redireciona URLs para public/
├── app/
│   └── .htaccess          ← Bloqueia acesso direto
├── config/
│   └── .htaccess          ← Bloqueia acesso direto
└── public/
    └── *.php              ← Arquivos principais
```

## ✅ Como Funciona

### 1. Arquivo Principal (2.0/.htaccess)

**Redirecionamentos:**
- `http://localhost/AgendaSenai/2.0/` → `public/index.php`
- `http://localhost/AgendaSenai/2.0/login.php` → `public/login.php`
- `http://localhost/AgendaSenai/2.0/professores.php` → `public/professores.php`

**Permite acesso:**
- ✅ Arquivos em `/assets/` (CSS, JS, imagens)
- ✅ Arquivos em `/api/` (endpoints da API)

**Bloqueia acesso:**
- ❌ Acesso direto a `/app/`
- ❌ Acesso direto a `/config/`

### 2. Proteção de Diretórios

**app/.htaccess e config/.htaccess:**
- Bloqueia 100% o acesso direto via browser
- Impede que alguém acesse `http://localhost/AgendaSenai/2.0/config/database.php`

## 🚀 Configuração para Deploy

### Desenvolvimento Local (XAMPP)

1. Certifique-se de que o módulo `mod_rewrite` está habilitado no Apache
2. No arquivo `httpd.conf`, verifique se existe:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
3. E que `AllowOverride` está configurado:
   ```apache
   <Directory "C:/xampp/htdocs">
       AllowOverride All
   </Directory>
   ```

### Deploy em Servidor

**Opção 1: Hospedagem compartilhada**
- Faça upload de toda a pasta `2.0/` para `public_html/`
- Os arquivos `.htaccess` funcionarão automaticamente

**Opção 2: VPS/Servidor dedicado**
- Configure o DocumentRoot para apontar para `2.0/`
- Os `.htaccess` gerenciarão o resto

**Opção 3: Subdomain/Subpasta**
- Ajuste o `RewriteBase` no arquivo `2.0/.htaccess`:
  ```apache
  # Para subdomain: site.com
  RewriteBase /

  # Para subpasta: site.com/agenda
  RewriteBase /agenda/
  ```

## 🧪 Testando

### URLs que devem funcionar:

✅ `http://localhost/AgendaSenai/2.0/` (index)
✅ `http://localhost/AgendaSenai/2.0/login.php`
✅ `http://localhost/AgendaSenai/2.0/professores.php`
✅ `http://localhost/AgendaSenai/2.0/assets/css/style.css`

### URLs que devem ser bloqueadas:

❌ `http://localhost/AgendaSenai/2.0/app/conexao.php` (403 Forbidden)
❌ `http://localhost/AgendaSenai/2.0/config/database.php` (403 Forbidden)

## 🔒 Segurança

Os arquivos `.htaccess` adicionam camadas de segurança:

1. **Isolamento de código sensível**: Arquivos em `app/` e `config/` não podem ser acessados diretamente
2. **Proteção de credenciais**: O arquivo `database.php` fica inacessível via web
3. **Controle de rotas**: Apenas arquivos em `public/` são servidos

## 🛠️ Troubleshooting

### Erro 500 Internal Server Error
- Verifique se `mod_rewrite` está habilitado
- Verifique se `AllowOverride All` está configurado
- Confira os logs do Apache em `C:\xampp\apache\logs\error.log`

### Redirecionamento não funciona
- Ajuste o `RewriteBase` para corresponder ao seu caminho
- Limpe o cache do navegador (Ctrl + Shift + Delete)

### 403 Forbidden em tudo
- Verifique permissões dos arquivos
- Remova temporariamente os `.htaccess` de `app/` e `config/` para testar

## 📚 Referências

- [Apache mod_rewrite](https://httpd.apache.org/docs/current/mod/mod_rewrite.html)
- [.htaccess Tutorial](https://httpd.apache.org/docs/current/howto/htaccess.html)
