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

set -o errexit
set -o pipefail
set -o nounset
#set -o xtrace

# Set magic variables for current file & dir
__dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
__file="${__dir}/$(basename "${BASH_SOURCE[0]}")"
readonly __dir __file

cd "${__dir}"

# t_color MESSAGE COLOR_NO [TAIL_MESSAGE]
t_color () {
  local style message tail
  message=${1}; shift
  style=${1}; shift
  tail="$@"
  printf '\e[%sm''%s''\e[0m''%s' "${style}" "${message}" "${tail}"
}
t_error () {
  local message=${1}; shift
  t_color ${message} 31 "$@"
}
t_warning () {
  local message=${1}; shift
  t_color ${message} 33 "$@"
}
t_question() {
  local message=${1}; shift
  t_color "${message}" 36 "$@"
}
t_ok () {
  local message=${1}; shift
  t_color ${message} 32 "$@"
}

validate_version () {
  local version_regex
  version_regex='v?(([0-9]+\.[0-9]+\.[0-9]+(-(alpha|beta|patch)(\.[0-9]+)*)?)|([0-9]+(\.[0-9]+)?\.x-dev))(\+.+)?'

  if [[ "${1}" =~ ^${version_regex}$ ]]; then
    echo 'ok'
  fi
}

target_version=$1
shift
force_opt=''
#################################
# Init options
while getopts "hf" opt; do
  case "${opt}" in
  h)
    show_help
    exit 0
    ;;
  \?)
    show_help
    exit 1
    ;;
  f)  force_opt='-f'
    ;;
  esac
done
#################################

increment_tag () {
  local last_tag last_no target_version
  last_tag=$(echo $1 | sed -re 's|(\+.*)$||' || true)

  # using semver
  echo $(semver ${last_tag} --preid "$(echo ${last_tag} | grep -Eo '-[a-z]+' | tr -d '-')" -i prerelease)
  return

  if [ "$(validate_version ${last_tag})" != 'ok' ]; then
    return
  fi

  last_no=$(echo "${last_tag}" | grep -oP '[0-9]+$' || true)

  if [ -n "${last_no}" ]; then
    target_version=$(echo "${last_tag}" | sed -re 's|[0-9]+$|'$((${last_no} + 1))'|')
  else
    t_error 'error:'
    echo " Cannot increment last version '${last_tag}'."
    exit 1
  fi
  echo ${target_version}
}

# Increment tag
if [ 'i' == "${target_version}" ] || [ 'increment' == "${target_version}" ]; then
  # fetch last tag and increment it
  target_version=$(increment_tag "$(tag=$(git tags 2>&1 | tail -1 || true) && echo ${tag})")

  if [ -z "${target_version}" ]; then
    t_error 'error:'
    echo " Cannot use last version tag."
  fi

  t_ok ${target_version}; echo
  printf 'Correct? y/n [y]: '
  read answer
  if [ "${answer}" != "y" ] && [ -n "${answer}" ]; then
     t_error 'Exit.'
     exit 1
  fi
fi

# remove "v" in the beginning
target_version=${target_version#'v'}

if [ -n "${target_version}" ] && [ "$(validate_version ${target_version})" != 'ok' ]; then
  echo "error: Incorrect format. Please use format from semver.org."
  exit 1
fi

###########################
# Update version in files #
###########################

php_file=${__dir}/../lib/PreCommit/Console/Application.php
xml_file=${__dir}/../config/root.xml
readme_file=${__dir}/../../README.md

current_version=$(cat ${xml_file} | grep -Eo '<version>[^<]+' | grep -Eo '[0-9][^<]+')

if [ -z "${target_version}" ]; then
  echo ${current_version}
  exit 0
fi

if [ ${target_version} == ${current_version} ]; then
  t_error 'error: '
  echo ' Target version and current one is the same.'
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
output=$(cd ${__dir} && git reset \
  && git add ${xml_file} ${php_file} \
  && git commit -m '@@through Updated version to '${target_version}'.' 2>&1)

echo
echo '  '$(git log -1 --format=%B)

# Clean up .bak files
rm -f ${xml_file}.bak  ${php_file}.bak ${readme_file}.bak

# Add tag for beta or alpha version turn back to dev
if [[ "${target_version}" =~ (alpha|beta) ]] && [[ $(git rev-parse --abbrev-ref HEAD) =~ ^(develop|master)$ ]] ; then
  git pull && git checkout master\
    && git pull && git merge develop \
    && git tag 'v'${target_version} ${force_opt} \
    && git push origin 'v'${target_version} ${force_opt} \
    && git checkout develop \
    && bash ${__file} 2.0.x-dev
fi

