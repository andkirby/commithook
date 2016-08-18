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

version_regex='(([0-9]+\.[0-9]+\.[0-9]+(-(alpha|beta|patch)(\.[0-9]+)*)?)|([0-9]+(\.[0-9]+)?\.x-dev))(\+.+)?'

target_version=$1
if [ 'v' = "${target_version::1}" ]; then
    target_version=${target_version#'v'}
fi

if ! [[ "${target_version}" =~ ^${version_regex}$ ]]; then
    echo "error: Version '${target_version}' is not valid."
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

# Commit changes
output=$(cd ${SRC_DIR} && git reset \
    && git add ${xml_file} ${php_file} \
    && git commit -m '@@through Updated version to '${target_version}'.' 2>&1)

echo
echo '    '$(git log -1 --format=%B)

# Clean up .bak file
rm -f ${xml_file}.bak ${php_file}.bak
