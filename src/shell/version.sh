#!/usr/bin/env bash
##############################################################
# Script for quick update version string in related files.   #
#                                                            #
# $ bash version.sh 2.3.4-patch.2                            #
##############################################################

: <<'LCS'
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
LCS

SRC_DIR=$(cd `dirname "${BASH_SOURCE[0]}"`/.. && pwd)
readonly SRC_DIR
cd "${SRC_DIR}"

php_file=${SRC_DIR}/lib/PreCommit/Console/Application.php
xml_file=${SRC_DIR}/config/root.xml
readme_file=${SRC_DIR}/../README.md

version_regex='(([0-9]+\.[0-9]+\.[0-9]+(-(alpha|beta|patch)(\.[0-9]+)*)?)|([0-9]+(\.[0-9]+)?\.x-dev))(\+.+)?'

target_version=$1
if [ 'v' = "${target_version::1}" ]; then
    target_version=${target_version#'v'}
fi

current_version=$(cat ${xml_file} | grep -Eo '<version>[^<]+' | grep -Eo '[0-9][^<]+')

if [ -z "${target_version}" ]; then
    echo ${current_version}
    exit 0
fi

if [ ${target_version} == ${current_version} ]; then
    echo "error: Target version and current one is the same."
    exit 1
fi

if ! [[ "${target_version}" =~ ^${version_regex}$ ]]; then
    echo "error: Incorrect format. Please use format from semver.org."
    exit 1
fi

# Update version in XML file
sed -i.bak -e "s:<version>.*</version>:<version>${target_version}</version>:g" ${xml_file}
if [ $? != 0 ]; then echo "error: Can't update file '${xml_file}'."; exit 1; fi

# Update version in PHP file
match=$(grep -Eo "const VERSION [^;]+;" ${php_file})
if [ -z "${match}" ]; then echo "error: Cannot find version string in ${php_file}."; exit 1; fi
sed -i.bak "s|${match}|const VERSION = '${target_version}';|g" ${php_file}
if [ $? != 0 ]; then echo "error: Can't update file '${php_file}'."; exit 1; fi


# Update version in README file
if ! [[ "${target_version}" =~ dev ]]; then
    match=$(grep -Eo "Latest release is.*" ${readme_file})
    if [ -z "${match}" ]; then echo "error: Cannot find version string in ${readme_file}."; exit 1; fi
    sed -i.bak "s:${match}:Latest release is v\`${target_version}\`:g" ${xml_file}
fi

# Commit changes
output=$(cd ${SRC_DIR} && git reset \
    && git add ${xml_file} ${php_file} \
    && git commit -m '@@through Updated version to '${target_version}'.' 2>&1)

echo
echo '    '$(git log -1 --format=%B)

# Clean up .bak files
rm -f ${xml_file}.bak  ${php_file}.bak ${readme_file}.bak

# Add tag for beta or alpha version turn back to dev
if [[ "${target_version}" =~ (alpha|beta) ]] && [ $(git rev-parse --abbrev-ref HEAD) == 'develop' ] ; then
    git pull && git checkout master\
        && git pull && git merge develop \
        && git tag 'v'${target_version} \
        && git checkout develop \
        && ${SRC_DIR}/shell/version.sh 2.0.x-dev
fi

