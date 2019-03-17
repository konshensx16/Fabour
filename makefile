db:
	php bin/console doctrine:database:drop --force || php bin/console doctrine:database:create
schema:
	php bin/console doctrine:schema:update --force
	php bin/console doctrine:fixtures:load
routes:
	php bin/console fos:js-routing:dump --format=json --target=assets/js/routes.json
server:
	php bin/console server:run	
gos_server:
	bin/console gos:websocket:server
npm:
	npm run watch
router:
	bin/console debug:router
phpstorm:
	nohup phpstorm.sh & 
