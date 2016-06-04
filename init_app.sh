#!/bin/bash

./yii migrate/up 1
./yii migrate --migrationPath=@yii/rbac/migrations
./yii migrate/up

./yii rbac/init

exit 0
