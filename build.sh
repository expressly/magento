#!/bin/bash
# fail on error
set -e

# find the source dir
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

# make a target dir so we can have a clean distribution
echo "[Copying extension files to target]"
mkdir -p ${DIR}/target
rsync -a --exclude='build' --exclude='target' --exclude='.*' --exclude='*.sh' --exclude='*.iml'  ${DIR}/* ${DIR}/target

# install dependencies
echo "[Installing dependencies]"
pushd "${DIR}/target/app/code/community/Expressly/Expressly"
composer install --no-dev
popd

# TODO: rebuild package.xml also replace version with tag in all the required files

# build the distribution
echo "[Packaging extension]"
php ${DIR}/build/pack.php ${DIR}/target $1

# TODO: repackage for long file names issue on Magento 1.7

