.PHONY: help build version zip zip-inte zip-preprod zip-prod build test composer-validate lint php-lint lint-fix phpunit phpstan phpstan-baseline docker-test docker-lint docker-lint docker-phpunit docker-phpstan
PHP = $(shell command -v php >/dev/null 2>&1 || { echo >&2 "PHP is not installed."; exit 1; } && which php)
VERSION ?= $(shell git describe --tags 2> /dev/null || echo "0.0.0")
SEM_VERSION ?= $(shell echo ${VERSION} | sed 's/^v//')
PACKAGE ?= accelasearch-${VERSION}
BUILDPLATFORM ?= linux/amd64
TESTING_DOCKER_IMAGE ?= accelasearch-testing:latest 
TESTING_DOCKER_BASE_IMAGE ?= phpdockerio/php80-cli
PHP_VERSION ?= 8.1
PS_VERSION ?= 8.0.1
PS_ROOT_DIR ?= $(shell pwd)/prestashop/prestashop-${PS_VERSION}

# target: default                                - Calling build by default
default: build

# target: help                                   - Get help on this file
help:
	@egrep "^#" Makefile

# target: build                                  - Clean up the repository
clean:
	git -c core.excludesfile=/dev/null clean -X -d -f

# target: version                                - Replace version in files
version:
	@echo "...$(VERSION)..."
	@sed -i.bak -e "s/\(VERSION = \).*/\1\'${SEM_VERSION}\';/" accelasearch.php
	@sed -i.bak -e "s/\($this->version = \).*/\1\'${SEM_VERSION}\';/" accelasearch.php
	@sed -i.bak -e "s|\(<version><!\[CDATA\[\)[0-9a-z.-]\{1,\}]]></version>|\1${SEM_VERSION}]]></version>|" config.xml
	@rm -f accelasearch.php.bak config.xml.bak

#target: zip-me									 - Create a local zip arhicve
zip-me: local_vendor build
	@mkdir -p ./temp
	@mkdir -p ./temp/accelasearch
	@mkdir -p ./releases

	@cp -R ./src temp/accelasearch
	@cp -R ./controllers temp/accelasearch
	@cp -R ./vendor temp/accelasearch
	@cp -R ./sql temp/accelasearch
	@cp -R ./views temp/accelasearch
	@cp -R ./accelasearch.php temp/accelasearch && sed -i 's/"DEBUG_MODE" => true/"DEBUG_MODE" => false/g' temp/accelasearch/accelasearch.php
	@cp -R ./cron.php temp/accelasearch
	@cp -R ./logo.png temp/accelasearch
	@cp -R ./*.pdf temp/accelasearch
	@rm -rf ./releases/accelasearch.zip
	@cd temp && zip -rq ../releases/accelasearch.zip accelasearch && cd ..
	@rm -rf ./temp

#target: zip-me-prod									 - Create a prod zip arhicve
zip-me-prod:
	@mkdir -p ./temp
	@mkdir -p ./temp/accelasearch
	@mkdir -p ./releases

	@cp -R ./src temp/accelasearch
	@cp -R ./controllers temp/accelasearch
	@cp -R ./vendor temp/accelasearch
	@cp -R ./sql temp/accelasearch
	@cp -R ./views temp/accelasearch
	@cp -R ./accelasearch.php temp/accelasearch && sed -i 's/"DEBUG_MODE" => true/"DEBUG_MODE" => false/g' temp/accelasearch/accelasearch.php
	@cp -R ./cron.php temp/accelasearch
	@cp -R ./logo.png temp/accelasearch
	@cp -R ./*.pdf temp/accelasearch
	@rm -rf ./releases/accelasearch.zip
	@cd temp && zip -rq ../releases/accelasearch.zip accelasearch && cd ..
	@rm -rf ./temp

# target: build                                  - Setup PHP & Node.js locally
build: vendor

local_vendor:
	./composer.phar dump-autoload -o --no-dev

composer.phar:
	@php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');";
	@php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;";
	@php composer-setup.php;
	@php -r "unlink('composer-setup.php');";

vendor: composer.phar
	./composer.phar install --no-dev -o;

vendor/bin/php-cs-fixer: composer.phar
	./composer.phar install --ignore-platform-reqs

vendor/bin/phpunit: composer.phar
	./composer.phar install --ignore-platform-reqs

vendor/bin/phpstan: composer.phar
	./composer.phar install --ignore-platform-reqs

prestashop:
	@mkdir -p ./prestashop

prestashop/prestashop-${PS_VERSION}: prestashop composer.phar
	@git clone --depth 1 --branch ${PS_VERSION} https://github.com/PrestaShop/PrestaShop.git prestashop/prestashop-${PS_VERSION};
	@./composer.phar -d ./prestashop/prestashop-${PS_VERSION} install

# target: test                                   - Static and unit testing
test: composer-validate lint php-lint phpstan phpunit translation-validate

# target: composer-validate                      - Validates composer.json and composer.lock
composer-validate: vendor
	@./composer.phar validate --no-check-publish

# target: translation-validate                   - Validates the translation files in translations/ directory
translation-validate: 
	php tests/translation.test.php

# target: lint                                   - Lint the code and expose errors
lint: vendor/bin/php-cs-fixer
	@vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no;

# target: lint-fix                               - Lint the code and fix it
lint-fix: vendor/bin/php-cs-fixer
	@vendor/bin/php-cs-fixer fix --using-cache=no;

# target: php-lint                               - Use php linter to check the code
php-lint:
	@git ls-files | grep -E '.*\.(php)' | xargs -n1 php -l -n | (! grep -v "No syntax errors" );
	@echo "php $(shell php -r 'echo PHP_VERSION;') lint passed";

# target: phpstan                                - Run phpstan
phpstan: vendor/bin/phpstan prestashop/prestashop-${PS_VERSION}
	_PS_ROOT_DIR_=${PS_ROOT_DIR} vendor/bin/phpstan analyse --memory-limit=256M --configuration=./tests/phpstan/phpstan.neon;

# target: docker-test                            - Static and unit testing in docker
docker-test: docker-lint docker-phpstan docker-phpunit

# target: docker-lint                            - Lint the code in docker
docker-lint:
	docker build --build-arg BUILDPLATFORM=${BUILDPLATFORM} --build-arg PHP_VERSION=${PHP_VERSION} -t ${TESTING_DOCKER_IMAGE} -f dev-tools.Dockerfile .;
	docker run --rm -v $(shell pwd):/src ${TESTING_DOCKER_IMAGE} lint;

# target: docker-lint-fix                        - Lint and fix the code in docker
docker-lint-fix:
	docker build --build-arg BUILDPLATFORM=${BUILDPLATFORM} --build-arg PHP_VERSION=${PHP_VERSION} -t ${TESTING_DOCKER_IMAGE} -f dev-tools.Dockerfile .;
	docker run --rm -v $(shell pwd):/src ${TESTING_DOCKER_IMAGE} lint-fix;

# target: docker-lint                            - Lint the code with php in docker
docker-php-lint:
	docker build --build-arg BUILDPLATFORM=${BUILDPLATFORM} --build-arg PHP_VERSION=${PHP_VERSION} -t ${TESTING_DOCKER_IMAGE} -f dev-tools.Dockerfile .;
	docker run --rm -v $(shell pwd):/src ${TESTING_DOCKER_IMAGE} php-lint;

# target: docker-phpstan                         - Run phpstan in docker
docker-phpstan: prestashop/prestashop-${PS_VERSION}
	docker build --build-arg BUILDPLATFORM=${BUILDPLATFORM} --build-arg PHP_VERSION=${PHP_VERSION} -t ${TESTING_DOCKER_IMAGE} -f dev-tools.Dockerfile .;
	docker run --rm -e _PS_ROOT_DIR_=/src/prestashop/prestashop-${PS_VERSION} -v $(shell pwd):/src ${TESTING_DOCKER_IMAGE} phpstan;
