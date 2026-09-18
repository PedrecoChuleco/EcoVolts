# EcoVolts

## Instalação e execução

### Pré-requisitos

Antes de começar, certifique-se de ter instalado em sua máquina:

- [PHP](https://www.php.net/) >= 8.1
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) >= 18 e npm
- [SQLite](https://www.sqlite.org/)
- Git

### 1. Clonar o repositório

```bash
git clone https://github.com/PedrecoChuleco/EcoVolts.git
cd ecovolts
```

### 2. Instalar as dependências do backend (PHP)

```bash
composer install
```

### 3. Instalar as dependências do frontend (React)

```bash
npm install
```

### 4. Configurar as variáveis de ambiente

Copie o arquivo de exemplo `.env.example` para `.env`:

```bash
cp .env.example .env
```

### 5. Gerar a chave da aplicação

```bash
php artisan key:generate
```

### 6. Executar as migrations (e seeders, se houver)

```bash
php artisan migrate
```

### 7. Rodar o projeto em modo de desenvolvimento

O Laravel já disponibiliza um script que sobe o backend e o frontend juntos em modo de desenvolvimento:

```bash
composer run dev
```

### 8. Acessar a aplicação

Com o processo rodando, acesse no navegador:

```
http://localhost:8000
```

