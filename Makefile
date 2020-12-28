.PHONY: build
build:
	docker-compose build --pull
	docker-compose up -d

.PHONY: start
start:
	docker-compose up -d

.PHONY: down
down:
	docker-compose down

.PHONY: shell
shell:
	docker exec -it tombstone-redis sh

.PHONY: logs
logs:
	docker-compose logs -f --tail=100
