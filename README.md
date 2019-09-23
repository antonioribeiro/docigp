# DOCIGP - Descentralização Orçamentária de Custeio Individualizado para Gabinete Parlamentar 

## [https://docigp.alerj.rj.gov.br/](https://docigp.alerj.rj.gov.br/)

## Características da aplicação

- [Git](https://git-scm.com/docs/user-manual.html)
- [PHP 7.2 ou superior](http://php.net/)
- [Composer](https://getcomposer.org/)
- [Redis](https://redis.io/topics/quickstart)
- [PostgreSQL](https://www.postgresql.org/)

### Instalação 

#### Guia genérico de uma aplicação desenvolvida em PHP pelo Projetos Especiais

- Clonar o repositório (branch: staging [homologação] or production [produção])
- Configurar servidor web para apontar para a **`<pasta-aonde-o-site-foi-instalado>`/public**
- Instalar certificado SSL (precisamos que a página seja acessível **via https apenas**)
- Criar o banco do dados
- Entrar na `<pasta-aonde-o-site-foi-instalado>`
- Copiar o arquivo `.env.example` para `.env`
- Editar o arquivo `.env` e configurar todos dados do sistema
- Ainda no arquivo `.env`, alterar a variável `APP_ENV` para o ambiente correto (testing, staging, production)
- Ainda no arquivo `.env`, configurar banco de dados
- Executar o comando `composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev` para instalar todas as dependências da aplicação
- Banco de dados
    - Caso **não** haja backup: executar o comando `php artisan migrate` para **criar** a estrutura do banco de dados
    - Caso haja backup: restaurar o banco e executar o comando `php artisan migrate` para **atualizar** a estrutura do banco de dados
- Linkar a pasta storage
```
php artisan storage:link
```

### Atualizando a aplicação

- Entrar na `<pasta-aonde-o-site-foi-instalado>`
- Baixar as atualizações de código fonte usando Git (git pull ou git fetch + git merge, isso depende de como operador prefere trabalhar com Git)
- Executar os comandos em sequência:
```
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force
php artisan docigp:sync:roles
```
- Reiniciar o Horizon

#### Passos extras específicos desta aplicação

##### Configurar [scheduler](https://laravel.com/docs/5.8/scheduling)
Colocar no cron a seguinte linha de comando, respeitando o path da aplicação:
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

##### Configurar o [Laravel Horizon](https://laravel.com/docs/5.8/horizon)
Configurar o Supervisor para manter o Horizon rodando o seguinte deamon
```
php artisan horizon
```

##### Configurar [Pusher](https://pusher.com/)
As configurações ficam no .env:

```
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
```

### Comandos disponíveis

```
php artisan docigp:sync:parties 
php artisan docigp:sync:congressmen
php artisan docigp:sync:departments
php artisan docigp:sync:roles
php artisan docigp:budget:generate
php artisan docigp:role:assign administrator afaria@alerj.rj.gov.br 
php artisan storage:link
```

### Para debugar
 
```
- Budget de Fevereiro e Março
a migrate:fresh -vvv --force; a docigp:sync:parties -vvv; a docigp:sync:congressmen -vvv; a docigp:sync:departments; a docigp:sync:roles; a docigp:budget:generate 2019-02-01; a docigp:budget:generate 2019-03-01;                                a db:seed -vvv --force; a docigp:budget:generate -vvv; 
- Budget de Fevereiro, Março e Abril
a migrate:fresh -vvv --force; a docigp:sync:parties -vvv; a docigp:sync:congressmen -vvv; a docigp:sync:departments; a docigp:sync:roles; a docigp:budget:generate 2019-02-01; a docigp:budget:generate 2019-03-01; a docigp:budget:generate -vvv; a db:seed -vvv --force;

a migrate:fresh -vvv --force; a docigp:sync:roles -vvv; a docigp:budget:generate 2019-04-01 -vvv; a docigp:budget:generate -vvv
```

### Perfis de permissionamento
- As permissões são guardadas em `config/roles.php` e são atualizadas a partir do comando `php artisan docigp:sync:roles`.

#### Administrador 
    - Todas as permissões
#### Deputado
    - Lançamentos
        - Verificar
        - Criar / Editar / Apagar
    - Documentos
        - Criar / Apagar
        - Verificar
        - Publicar
    - Mês
        - Fechar
        - Porcentagem
        - Depositar
#### ACI
    - Ver todos os botões
    - Lançamentos
        - Analisar
        - Publicar
    - Fornecedores
        - Criar / Editar
    - Documentos
        - Analisar
    - Mês
        - Reabrir
        - Analisar
        - Publicar