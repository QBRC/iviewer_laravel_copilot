# set up front end
docker compose exec app ls -l
#cp .env laravel_smp/
# docker compose exec app rm /var/www/composer.lock
#docker compose exec app composer install # for dev
docker compose exec app composer install --no-dev # for prod

docker compose exec app php artisan key:generate
# create seed db
docker compose exec app php artisan migrate:refresh --seed

# set up default slides
#mkdir -p laravel_smp/public/images/{csv,mask,original,thumbnail}

# build seperate app if necessay
#docker compose build deepzoom
#docker compose build annotation
