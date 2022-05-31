# yii2-beter-logging: Development end testing

## Launch it

To make testing and development simple project contains `php.dev.Dockerfile` and `docker-compose.dev.yml`.

You are not required to install PHP, Apache or nginx.

The whole pipeline for the first setup is the following:
1. You need to build docker image with a command `docker build -f php.dev.Dockerfile -t yii2-beter-logging-php:latest .`.
2. `composer`, `xdebug` and all needed extensions are installed during the Docker build phase.
3. `composer` installs `yii` boilerplate project.
4. Next step in the build pipeline copies custom files from the repo
`yii2-beter-logging/deploy/data/php/root/var/www/html` to the image.
5. Custom files contains php scripts and updated `composer.json`, so `composer update` will be executed.
6. Custom `composer.json` specifies `"beter/yii2-beter-logging": "dev-main"` and symlinks `/yii2-beter-logging` folder
in the container with the `beter/yii2-beter-logging` package. This requires to mount volume later, but gives possibility
to change source files right in the `src/` folder on the host machine and immediately see that changes inside running
container.
7. After that you need to check `docker ps -a` output and assure that image `yii2-beter-logging-php:latest` was built.
8. Create docker container with the command
`docker create --name yii2-beter-logging-php-tmp-for-copy yii2-beter-logging-php:latest`.
*But don't launch docker container!* You need the container only for file copying.
9. Copy the content of the `/var/www/html` folder from the container to the host machine. Run it from the root path
of this repository - `docker cp -L yii2-beter-logging-php-tmp-for-copy:/var/www/html yii-test-app-src`.
10. Folder `yii-test-app-src` is included to `.gitignore`, so it must not bother you.
11. Run containers after that. `docker-compose -f docker-compose.dev.yml -p yii2-beter-logging up`.

> By default, nginx listens port 8080. You may change this port by specifying environment variable `HOST_NGINX_PORT`.
> For example, to run nginx on port 8280 run `docker-compose -f docker-compose.dev.yml -p yii2-beter-logging up`

`docker-compose` will run in foreground, so you will see `stderr` logs and may test this application.
If you want you may run containers in a background mode (add `-d` option after `docker-compose up`).

## Tips

If you want to test specific version of the package you have 2 options:
* just edit `composer.json` before the docker build and replace `"beter/yii2-beter-logging": "dev-main"` to specific version.
* after the build phase change `"beter/yii2-beter-logging": "dev-main"` inside running container and run `composer update`.

## Development and testng pipeline

You may use `xdebug` to test the application,
check settings (`deploy/data/php/root/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini`).

`nginx` and `php` write their logs directly to `stderr` of the container. The project is about logging, so you definitely
need to read `stderr`. But yii's default configuration writes logs to files, so you may also check
`var/www/html/runtime/logs/app.log` file on the container.
