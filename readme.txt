git clone https://github.com/Efereank/biblioteca-visitas.git
cd biblioteca-visitas
docker-compose up -d
docker exec biblioteca_app composer install
docker exec biblioteca_app php artisan key:generate
docker exec biblioteca_app php artisan migrate:fresh --seed
docker exec biblioteca_node npm install
docker exec biblioteca_node npm run build
docker ps
