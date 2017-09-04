#!/usr/bin/env bash
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

if commithook 2>&1 > /dev/null; then
  commithook_bin=$(type commithook | awk '{print $NF}' | tr -d "'\`")
else
  commithook_bin=${__dir}/../../bin/commithook
fi

current_pwd=$(pwd)

submodule_paths() {
  cat ${current_pwd}/.gitmodules \
    | grep 'path = ' | awk '{print $3}'
}

while read module_path; do
  cd ${module_path}
  ${commithook_bin} install
done < <(submodule_paths)
