#!/bin/bash

DIR="$(cd "$(dirname "$0")" && pwd)"

FLAG_COVERAGE=false

for arg in "$@"
do
  if [ "$arg" == "--coverage" ]; then
    FLAG_COVERAGE=true
  fi
done

export APP_ENV=test

RETURN=0

if [ "$FLAG_COVERAGE" == true ]; then
  COVERAGE_PATH_HTML="$DIR/../code-coverage-html"
  COVERAGE_PATH_COBERTURA="$DIR/../artifacts/code-coverage-cobertura.xml"
  COVERAGE_PATH_CLOVER="$DIR/../artifacts/code-coverage-clover.xml"
  COVERAGE_PATH_TAR="$DIR/../code-coverage.tar.gz"
  php -d pcov.directory=. ./vendor/bin/phpunit --coverage-html "$COVERAGE_PATH_HTML" --coverage-cobertura "$COVERAGE_PATH_COBERTURA" --coverage-clover "$COVERAGE_PATH_CLOVER"
  UNIT=$?
  tar zcf "$COVERAGE_PATH_TAR" "$COVERAGE_PATH_HTML"
  rm -rf "$COVERAGE_PATH_HTML"
  if [ -n "$CODE_COV_PATH_CORRECTION" ]; then
    sed -i "s%/var/www/src%$CODE_COV_PATH_CORRECTION/src%" "$COVERAGE_PATH_COBERTURA" # this fixes the source list in the xml file
  fi

  [ ! -d "$DIR/../artifacts" ] && mkdir "$DIR/../artifacts"
  mv "$COVERAGE_PATH_TAR" "$DIR/../artifacts/code-coverage.tar.gz"

  php "$DIR/../scripts/coverage-check.php" "$COVERAGE_PATH_CLOVER" "$CODE_COVERAGE_MIN"
  COV=$?

  if [ "$UNIT" -ne 0 ] || [ "$COV" -ne 0 ]; then
    RETURN=-1
  fi
else
  vendor/bin/phpunit
  RETURN=$?
fi

if [ $RETURN -ne 0 ]; then
  echo "Test or coverage failed."
  exit 1 || return 1
else
  exit 0 || return 0
fi