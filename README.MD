# AccelaSearch Prestashop Module

AccelaSearch Prestashop Module is a Prestashop module that allows you to add a powerful search engine to your store.

## Compatibility

Minimum Prestashop version: 1.7.0 or higher
Minimum PHP version: 7.1 or higher

## Start a local tunnel

You can use localtunnel to expose your local Prestashop installation to the internet.

```bash
npx localtunnel --port 8199
```

## Make Commands

All commands needs to be run inside the container.

### Lint view

```bash
make lint
```

### Lint fix

```bash
make lint-fix
```

### PHP Lint

```bash
make php-lint
```

### PHP Stan (static analysis)

```bash
make phpstan
```

### Header check

```bash
make header-check
```

### Header fix

```bash
make header-fix
```

### Run phpunit and e2e tests

```bash
make test
```

### Run autoindex file generation

```bash
make autoindex
```

## Write new tests

Create a file in the `tests/Unit` folder with the name of the class you want to test and add the suffix `Test.php`.

Take a look at the `tests/Unit/ManagerTest.php` file for an example.

## Backoffice login

- URL: <http://localhost:8199/adminPS>
- Username: <demo@prestashop.com>
- Password: prestashop_demo
