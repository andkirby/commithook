#!/usr/bin/env sh
: <<'LCS'
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
LCS

SRC_DIR=$(cd `dirname "${BASH_SOURCE[0]}"`/.. && pwd)
readonly SRC_DIR

${SRC_DIR}/../vendor/bin/phpunit -c ${SRC_DIR}/tests/phpunit.xml $@
