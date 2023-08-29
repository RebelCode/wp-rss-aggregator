.PHONY: build
build: clean vendor node_modules build/wp-rss-aggregator.zip

build/wp-rss-aggregator.zip:
	mkdir -p build
	rm -f build/wp-rss-aggregator.zip
	cd .. && zip -r -9 ./wp-rss-aggregator/build/wp-rss-aggregator.zip ./wp-rss-aggregator -x \
		"wp-rss-aggregator/.git/*" \
		"wp-rss-aggregator/.idea/*" \
		"wp-rss-aggregator/nbproject/*" \
		"wp-rss-aggregator/build/*" \
		"wp-rss-aggregator/docker/*" \
		"wp-rss-aggregator/js/src/*" \
		"wp-rss-aggregator/css/src/*" \
		"wp-rss-aggregator/node_modules/*" \
		"wp-rss-aggregator/test/*" \
		"wp-rss-aggregator/webpack/*" \
		"wp-rss-aggregator/.directory" \
		"wp-rss-aggregator/.babelrc" \
		"wp-rss-aggregator/.gitattributes" \
		"wp-rss-aggregator/.gitignore" \
		"wp-rss-aggregator/.php-cs-fixer.cache" \
		"wp-rss-aggregator/.phpactor.json" \
		"wp-rss-aggregator/.directory" \
		"wp-rss-aggregator/build.sh" \
		"wp-rss-aggregator/composer.json" \
		"wp-rss-aggregator/composer.lock" \
		"wp-rss-aggregator/docker-compose.yml" \
		"wp-rss-aggregator/Makefile" \
		"wp-rss-aggregator/package.json" \
		"wp-rss-aggregator/package-lock.json" \
		"wp-rss-aggregator/phpunit.xml" \
		"wp-rss-aggregator/README.md" \
		"wp-rss-aggregator/webpack.config.js" \
		"wp-rss-aggregator/yarn.lock" \
		"wp-rss-aggregator/src/V5/*"

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
