DIR_NAME = $(shell basename $(shell pwd))

.PHONY: build
build: clean vendor node_modules build/wp-rss-aggregator.zip

build/wp-rss-aggregator.zip:
	mkdir -p build
	rm -f "$@"
	cd .. && zip -r -9 ./$(DIR_NAME)/build/wp-rss-aggregator.zip ./$(DIR_NAME) -x \
		"$(DIR_NAME)/.git/*" \
		"$(DIR_NAME)/.idea/*" \
		"$(DIR_NAME)/nbproject/*" \
		"$(DIR_NAME)/build/*" \
		"$(DIR_NAME)/docker/*" \
		"$(DIR_NAME)/js/src/*" \
		"$(DIR_NAME)/css/src/*" \
		"$(DIR_NAME)/node_modules/*" \
		"$(DIR_NAME)/test/*" \
		"$(DIR_NAME)/webpack/*" \
		"$(DIR_NAME)/.directory" \
		"$(DIR_NAME)/.babelrc" \
		"$(DIR_NAME)/.gitattributes" \
		"$(DIR_NAME)/.gitignore" \
		"$(DIR_NAME)/.php-cs-fixer.cache" \
		"$(DIR_NAME)/.phpactor.json" \
		"$(DIR_NAME)/.directory" \
		"$(DIR_NAME)/build.sh" \
		"$(DIR_NAME)/composer.json" \
		"$(DIR_NAME)/composer.lock" \
		"$(DIR_NAME)/docker-compose.yml" \
		"$(DIR_NAME)/Makefile" \
		"$(DIR_NAME)/package.json" \
		"$(DIR_NAME)/package-lock.json" \
		"$(DIR_NAME)/phpunit.xml" \
		"$(DIR_NAME)/README.md" \
		"$(DIR_NAME)/webpack.config.js" \
		"$(DIR_NAME)/yarn.lock" \
		"$(DIR_NAME)/src/V5/*"

.PHONY: clean
clean:
	rm -rf ./vendor
	rm -rf ./node_modules
	rm -rf build

vendor:
	composer install --no-dev --optimize-autoloader --prefer-dist --ignore-platform-reqs

node_modules:
	yarn install
	yarn run build
