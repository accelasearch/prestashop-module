-include .env
export
.PHONY: help zip-me version release merge lint lint-fix php-lint header-check header-fix phpstan run-tests autoindex deploy translate
ZIP_FILES := $(shell cat ./.zip_files)
MODULE_NAME=accelasearch
REACT_DIR = ./react
OUTPUT_TRANSLATION_FILE = ./views/templates/admin/configure.tpl

# target: merge - Merge develop into master
merge:
	git checkout master
	git merge development
	git push
	git checkout development

# target: release - Create a release to github
release:
	git tag -a $(VERSION) -m "Release $(VERSION)"
	git push --tags

# target: compose - Run docker-compose
compose:
	docker-compose up -d

# target: zip-me - Create a local zip archive
zip-me: 
	@mkdir -p ./temp
	@mkdir -p ./temp/$(MODULE_NAME)
	@mkdir -p ./temp/$(MODULE_NAME)/react
	@mkdir -p ./releases

	@for file in $(ZIP_FILES); do \
		cp -R $$file ./temp/$(MODULE_NAME); \
	done

	@cp -R ./react/dist ./temp/$(MODULE_NAME)/react
	@cp -R ./react/public ./temp/$(MODULE_NAME)/react
	
	@rm -rf ./releases/$(MODULE_NAME).zip
	@cd temp && zip -rq ../releases/$(MODULE_NAME).zip $(MODULE_NAME) && cd ..
	@rm -rf ./temp

# target: version - Replace version in files
version:
	@echo "...$(VERSION)..."
	@sed -i.bak -e "s/\(VERSION = \).*/\1\'${VERSION}\';/" $(MODULE_NAME).php
	@sed -i.bak -e "s/\($this->version = \).*/\1\'${VERSION}\';/" $(MODULE_NAME).php
	@rm -f $(MODULE_NAME).php.bak config.xml.bak

# target: help - Get help on this file
help:
	@egrep "^#" Makefile

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

# target: header 							   - Check if header is present
header-check:
	@echo "Checking header..."
	@vendor/bin/header-stamp --license=vendor/prestashop/header-stamp/assets/afl.txt --exclude=vendor,tests,_dev,react --dry-run

# target: header-fix 							   - Fix header
header-fix:
	@echo "Fixing header..."
	@vendor/bin/header-stamp --license=vendor/prestashop/header-stamp/assets/afl.txt --exclude=vendor,tests,_dev,react

# target: phpstan                                - Run phpstan
phpstan:
	_PS_ROOT_DIR_=/var/www/html vendor/bin/phpstan analyse --configuration=tests/phpstan/phpstan.neon

# target: phpunit-docker						 - Run phpunit in docker
phpunit-docker:
	@echo "Running tests..."
	@docker exec -it accelasearch-module php /var/www/html/modules/accelasearch/vendor/bin/phpunit -c /var/www/html/modules/accelasearch/tests/Unit/phpunit.xml

# target: phpunit                                - Run phpunit
phpunit:
	@echo "Running tests..."
	@php vendor/bin/phpunit -c tests/Unit/phpunit.xml

# target: e2e									- Run e2e tests
e2e:
	@echo "Running e2e tests..."
	@npx playwright test

# target: test - Run tests
test:
	@echo "Running tests..."
	@$(MAKE) phpunit
	@$(MAKE) e2e

# target: autoindex - Generate index.php files recursively
autoindex:
	@echo "Generating index.php files..."
	@vendor/bin/autoindex prestashop:add:index

deploy:
	@echo "Deploying $(MODULE_NAME)"
	@scp -i $(SSH_KEY_PATH) -r ./releases/$(MODULE_NAME).zip $(LIVE_USER)@$(LIVE_HOST):$(REMOTE_MODULE_PATH)/$(MODULE_NAME).zip
	@ssh -i $(SSH_KEY_PATH) $(LIVE_USER)@$(LIVE_HOST) "cd $(REMOTE_MODULE_PATH) && unzip -o $(MODULE_NAME).zip && rm -rf $(MODULE_NAME).zip"



