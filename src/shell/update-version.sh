#!/usr/bin/env bash
: <<'LCS'
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
LCS
SRC_DIR=$(cd `dirname "${BASH_SOURCE[0]}"`/../src && pwd)
readonly SRC_DIR
cd "${SRC_DIR}"

current_branch=$(git branch | grep '*' | grep -Eo '[^* ]+')
echo -n "Use '${current_branch}' branch? (y/n) "
read answer
if [ -z $(echo "${answer}" | grep -i "^y") ]; then
    # Answer is not YES
    echo Stop!
    exit 1
fi

if [ $? != 0 ]; then echo "error: Can't go to master branch."; exit 1; fi

# Read current version
current_version=$(grep -E '<version>[^<]' ${SRC_DIR}/config/root.xml | grep -Eo '[0-9][^<]+')
if [ $? != 0 ]; then echo "error: Can't get current version."; exit 1; fi
echo "Current version: ${current_version}"

match=$(grep " = '${current_version}'" ${SRC_DIR}/lib/PreCommit/Command/Application.php)
if [ -z "${match}" ]; then
    echo "error: The same version '${current_version}' not found in PreCommit/Command/Application.php file."
    exit 1
fi

last_tag_name=$(git tag -l | sort -V | tail -1)
last_version=$(echo "${last_version}" | sed 's/^v\(.*\)/\1/')

# Ask about selected version
echo -n "Is it correct version to change? ${last_version} (y/n) "
read answer
if [ -z $(echo "${answer}" | grep -i "^y") ]; then
    # Answer is not YES
    echo Stop!
    exit 1
fi

echo ${current_version} > ${SRC_DIR}/../dev_version \
    && echo ${last_version} > ${SRC_DIR}/../release_version
if [ $? != 0 ]; then echo "error: Can't create version files."; exit 1; fi

# replace version
sed 's|<version>'"${current_version}"'</version>|<version>'"${last_version}"'</version>|g' ${SRC_DIR}/config/root.xml \
        > ${SRC_DIR}/config/root.xml.tmp \
        && mv ${SRC_DIR}/config/root.xml.tmp ${SRC_DIR}/config/root.xml \
&& sed "s| = '${current_version}';| = '${last_version}';|g" ${SRC_DIR}/lib/PreCommit/Command/Application.php \
    > ${SRC_DIR}/lib/PreCommit/Command/Application.php.tmp \
    && mv ${SRC_DIR}/lib/PreCommit/Command/Application.php.tmp ${SRC_DIR}/lib/PreCommit/Command/Application.php
if [ $? != 0 ]; then echo "error: Can't update version in files."; exit 1; fi

# commit updated files
git add ${SRC_DIR}/../src/lib/PreCommit/Command/Application.php && \
git add ${SRC_DIR}/../src/config/root.xml && \
git add ${SRC_DIR}/../dev_version && \
git add ${SRC_DIR}/../release_version
git status

# Ask about selected version
echo -n "Correct files for commit? (y/n) "
read answer
if [ -z $(echo "${answer}" | grep -i "^y") ]; then
    # Answer is not YES
    echo Stop!
    exit 1
fi

# git tag-move -- custom command
# Make commit, add tag, revert this commit
git commit -m "@@through Update version to ${last_tag_name}." \
    && git tag-move ${last_tag_name} \
    && git revert HEAD
if [ $? != 0 ]; then echo "error: Can't make commit."; exit 1; fi

