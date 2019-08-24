#!/bin/sh

TEST_SERVER_LOCATION=/srv/http/api/
REQUIRES_SUDO=1
# ^ 0 = false, 1 = true

if [[ "$REQUIRES_SUDO" == "0" ]]; then

  cp -r src/* $TEST_SERVER_LOCATION

else

  sudo cp -r src/* $TEST_SERVER_LOCATION

fi

echo -e "\e[32mMoved! New directory listing for\e[1m $TEST_SERVER_LOCATION\e[0m\e[32m:\e[0m"
ls -lh $TEST_SERVER_LOCATION

