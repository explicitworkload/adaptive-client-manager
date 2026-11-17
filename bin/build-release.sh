#!/usr/bin/env bash
# Build an upload-ready package for hosting without Composer or SSH.
#
#   bin/build-release.sh            -> build/acm5-release.zip         (first install)
#   bin/build-release.sh --update   -> build/acm5-release-update.zip  (no config/app_local.php,
#                                      so extracting it over a live install keeps your settings)
#
# The package includes vendor/ (production dependencies only), empty tmp/ and
# logs/ folders, and config/app_local.php created from
# config/app_local.production.php. Build once per code version: the same zip
# works for any number of sites (each generates its own security salt on first run).
# Needs Composer and PHP 8.3+ on the machine that runs it.
set -euo pipefail

SRC="$(cd "$(dirname "$0")/.." && pwd)"
UPDATE=0; [ "${1:-}" = "--update" ] && UPDATE=1
SUFFIX=""; [ $UPDATE = 1 ] && SUFFIX="-update"
OUT="$SRC/build/acm5-release$SUFFIX"
ZIP="$SRC/build/acm5-release$SUFFIX.zip"

rm -rf "$OUT" "$ZIP"
mkdir -p "$OUT"

rsync -a \
  --exclude '/build' --exclude '/private' --exclude '/vendor' --exclude '/tests' --exclude '/tmp' --exclude '/logs' \
  --exclude '/.git' --exclude '/.github' --exclude '/config/app_local.php' \
  --exclude 'phpunit.xml.dist' --exclude 'phpcs.xml' --exclude 'phpstan.neon' --exclude 'psalm.xml' \
  --exclude '*.mtx.php' --exclude '*.cw.dat' --exclude '*.cw127.php' \
  --exclude '/webroot/css/dead_pixel.css' \
  --exclude '.editorconfig' --exclude '.gitattributes' --exclude '.DS_Store' \
  "$SRC/" "$OUT/"

# The logo belongs to the site: ship it on first install only, never in updates.
[ $UPDATE = 1 ] && rm -f "$OUT/webroot/img/logo.png"

(cd "$OUT" && composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --quiet)

mkdir -p "$OUT"/tmp/cache/{models,persistent,views} "$OUT"/tmp/sessions "$OUT"/logs
touch "$OUT"/tmp/cache/models/empty "$OUT"/tmp/cache/persistent/empty "$OUT"/tmp/cache/views/empty \
  "$OUT"/tmp/sessions/empty "$OUT"/logs/empty

if [ $UPDATE = 0 ]; then
  # Keeps the '__SALT__' placeholder: each installation generates its own salt
  # on first run (App\Service\InstallSalt), so one zip can be used for any site.
  cp "$OUT/config/app_local.production.php" "$OUT/config/app_local.php"
fi

(cd "$OUT" && zip -qr "$ZIP" .)
echo "Built $ZIP ($(du -h "$ZIP" | cut -f1), $(cd "$OUT" && find . -type f | wc -l | tr -d ' ') files)"
echo "Unzipped copy for inspection: $OUT"
