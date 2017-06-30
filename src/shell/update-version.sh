#!/usr/bin/env bash
: <<'LCS'
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
LCS

set -o pipefail
set -o errexit
set -o nounset
#set -o xtrace

VERSION_DRY_RUN=0

ERR_NO_CRITICAL=2
ERR_NO_INVALID_ARG=3
ERR_NO_LOGICAL=4
ERR_NO_USER_EXIT=6

# Set magic variables for current file & dir
__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
__file="${__dir}/$(basename "${BASH_SOURCE[0]}")"
readonly __dir __file

check_error () {
  local status=${1}
  shift
  if [ '0' != "${status}" ]; then
    echo "error: $@" > /dev/stderr
    exit ${status}
  fi
}

cd "${__dir}/.."

current_branch=$(git branch | grep '*' | grep -Eo '[^* ]+')
echo -n "Use '${current_branch}' branch? (y/n) "
read answer
if [ -z "$(echo "${answer}" | grep -i "^y")" ]; then
  # Answer is not YES
  check_error ${ERR_NO_USER_EXIT} 'Stop!'
fi

# check if a tag defined on HEAD
if ! git describe HEAD --tags --exact-match 1> /dev/null 2> /dev/null; then
  check_error ${ERR_NO_CRITICAL} 'There is no tag on HEAD commit.'
fi

# reset files with the version
git checkout -- config/root.xml && git checkout -- lib/PreCommit/Command/Application.php \
  || check_error ${ERR_NO_CRITICAL} "Can't reset files Application.php and root.xml."


# Read current version
current_version=$(grep -E '<version>[^<]' ${__dir}/../config/root.xml | grep -Eo '[0-9][^<]+') \
  || check_error ${ERR_NO_CRITICAL} "Can't get current version from config/root.xml."

echo "Current version: ${current_version}"

match=$(grep " = '${current_version}'" ${__dir}/../lib/PreCommit/Command/Application.php)
if [ -z "${match}" ]; then
  check_error ${ERR_NO_LOGICAL} "error: The same version '${current_version}' not found in PreCommit/Command/Application.php file."
fi

last_tag_name=$(git describe HEAD --tags --exact-match | head -n1)
last_version=$(echo "${last_tag_name}" | sed 's/^v\(.*\)/\1/')

# Ask about selected version
echo -n "Is it correct version to change? ${last_version} (y/n) "
read answer
if [ -z $(echo "${answer}" | grep -i "^y") ]; then
  # Answer is not YES
  check_error ${ERR_NO_USER_EXIT} 'Stop!'
fi

echo ${current_version} > ${__dir}/../../dev_version \
  && echo ${last_version} > ${__dir}/../../release_version

# replace version
sed 's|<version>'"${current_version}"'</version>|<version>'"${last_version}"'</version>|g' \
    ${__dir}/../config/root.xml \
    > ${__dir}/../config/root.xml.tmp \
  && mv ${__dir}/../config/root.xml.tmp ${__dir}/../config/root.xml \
  && sed "s| = '${current_version}';| = '${last_version}';|g" ${__dir}/../lib/PreCommit/Command/Application.php \
    > ${__dir}/../lib/PreCommit/Command/Application.php.tmp \
  && mv ${__dir}/../lib/PreCommit/Command/Application.php.tmp ${__dir}/../lib/PreCommit/Command/Application.php

# commit updated files
git add ${__dir}/../../src/lib/PreCommit/Command/Application.php && \
git add ${__dir}/../../src/config/root.xml && \
git add ${__dir}/../../dev_version && \
git add ${__dir}/../../release_version
git status

# Ask about selected version
echo -n "Are these files correct for commit? (y/n) "
read answer
if [ -z $(echo "${answer}" | grep -i "^y") ]; then
  # Answer is not YES
  check_error ${ERR_NO_USER_EXIT} 'Stop!'
fi

# git tag-move -- custom command
# Make commit, add tag, revert this commit
git commit -m "@@through Update version to ${last_tag_name}." \
  && git tag ${last_tag_name} \
  && git revert HEAD
