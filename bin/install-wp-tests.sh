#!/usr/bin/env bash
#
# Installs a WordPress core, its PHPUnit test library and The Events Calendar
# for the integration tests, without Docker.
#
#   bin/install-wp-tests.sh [wp-version] [db-name] [db-user] [db-pass] [db-host] [tec-version]
#
# wp-version is "latest" (default) or a release such as "7.0" or "7.0.2".
# tec-version is "latest" (default) or a release of The Events Calendar from
# wordpress.org, such as "6.18.3.1".
#
# Prints the environment variables to export for `composer test`, so CI can
# append the output to $GITHUB_ENV.
set -euo pipefail

WP_VERSION=${1:-latest}
DB_NAME=${2:-wordpress_tests}
DB_USER=${3:-root}
DB_PASS=${4:-}
DB_HOST=${5:-127.0.0.1}
TEC_VERSION=${6:-latest}
TARGET=${WP_TESTS_INSTALL_DIR:-${TMPDIR:-/tmp}/events-calendar-live-viewer-wp-tests}

# Resolve to an absolute path; the test library breaks on relative ones.
mkdir -p "$TARGET"
TARGET=$(cd "$TARGET" && pwd -P)

# Keep stdout for the variables; progress goes to stderr.
log() { echo "$@" >&2; }

if [ "$WP_VERSION" = "latest" ]; then
	WP_VERSION=$(curl -fsSL https://api.wordpress.org/core/version-check/1.7/ | php -r 'echo json_decode(stream_get_contents(STDIN))->offers[0]->version;')
fi

# The test library is published per release; use the newest patch of the
# same minor so a fresh core point release never blocks the run.
WP_MINOR=$(echo "$WP_VERSION" | cut -d. -f1,2)
DIR="$TARGET/$WP_VERSION"

mkdir -p "$DIR"

if [ ! -d "$DIR/wordpress" ]; then
	log "Downloading WordPress $WP_VERSION"
	curl -fsSL -o "$DIR/wordpress.zip" "https://wordpress.org/wordpress-$WP_VERSION.zip"
	unzip -q "$DIR/wordpress.zip" -d "$DIR"
	rm "$DIR/wordpress.zip"
fi

if [ ! -d "$DIR/vendor/wp-phpunit/wp-phpunit" ]; then
	log "Installing the WordPress $WP_MINOR test library"
	composer --working-dir="$DIR" require --quiet --no-interaction "wp-phpunit/wp-phpunit:~$WP_MINOR.0" >&2
fi

# The Events Calendar is a hard dependency, so the suite runs against it.
if [ "$TEC_VERSION" = "latest" ]; then
	TEC_ZIP="the-events-calendar.zip"
else
	TEC_ZIP="the-events-calendar.$TEC_VERSION.zip"
fi

TEC_DIR="$DIR/plugins/${TEC_ZIP%.zip}"

if [ ! -f "$TEC_DIR/the-events-calendar/the-events-calendar.php" ]; then
	log "Downloading The Events Calendar ($TEC_VERSION)"
	mkdir -p "$TEC_DIR"
	curl -fsSL -o "$TEC_DIR/tec.zip" "https://downloads.wordpress.org/plugin/$TEC_ZIP"
	unzip -q "$TEC_DIR/tec.zip" -d "$TEC_DIR"
	rm "$TEC_DIR/tec.zip"
fi

cat > "$DIR/wp-tests-config.php" <<CONFIG
<?php
define( 'ABSPATH', '$DIR/wordpress/' );
define( 'WP_DEFAULT_THEME', 'default' );
define( 'WP_DEBUG', true );
define( 'DB_NAME', '$DB_NAME' );
define( 'DB_USER', '$DB_USER' );
define( 'DB_PASSWORD', '$DB_PASS' );
define( 'DB_HOST', '$DB_HOST' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
\$table_prefix = 'wptests_';
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
CONFIG

echo "WP_PHPUNIT__DIR=$DIR/vendor/wp-phpunit/wp-phpunit"
echo "WP_PHPUNIT__TESTS_CONFIG=$DIR/wp-tests-config.php"
echo "TEC_PLUGIN_DIR=$TEC_DIR/the-events-calendar"
