db:
	php bin/console doctrine:database:drop --force || php bin/console doctrine:database:create
schema:
	php bin/console doctrine:schema:update --force
	php bin/console doctrine:fixtures:load
	