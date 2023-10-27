.PHONY: help zip-me version release merge
ZIP_FILES := $(shell cat ./.zip_files)

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

# target: zip-me - Create a local zip archive
zip-me: 
	@mkdir -p ./temp
	@mkdir -p ./temp/$(accelasearch)
	# START REACT #
	@mkdir -p ./temp/$(accelasearch)/react
	# END REACT #
	@mkdir -p ./releases

	@for file in $(ZIP_FILES); do \
		cp -R $$file ./temp/$(accelasearch); \
	done
	
	@rm -rf ./releases/$(accelasearch).zip
	@cd temp && zip -rq ../releases/$(accelasearch).zip $(accelasearch) && cd ..
	@rm -rf ./temp

# target: version - Replace version in files
version:
	@echo "...$(VERSION)..."
	@sed -i.bak -e "s/\(VERSION = \).*/\1\'${VERSION}\';/" $(ordermargins).php
	@sed -i.bak -e "s/\($this->version = \).*/\1\'${VERSION}\';/" $(ordermargins).php
	@rm -f $(ordermargins).php.bak config.xml.bak

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

# target: run-tests - Run tests
run-tests:
	@echo "Running tests..."
	@vendor/bin/phpunit tests

# target: autoindex - Generate index.php files recursively
autoindex:
	@echo "Generating index.php files..."
	@vendor/bin/autoindex prestashop:add:index