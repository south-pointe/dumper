DOCKER_COMPOSE_COMMAND=cd docker && docker compose -p $(shell basename $(CURDIR))

.PHONY: build
build:
	$(DOCKER_COMPOSE_COMMAND) build --pull app

.PHONY: up
up:
	$(DOCKER_COMPOSE_COMMAND) up -d

.PHONY: down
down:
	$(DOCKER_COMPOSE_COMMAND) down --remove-orphans

.PHONY: ps
ps:
	$(DOCKER_COMPOSE_COMMAND) ps -a

.PHONY: logs
logs:
	$(DOCKER_COMPOSE_COMMAND) logs

.PHONY: test
test:
	$(DOCKER_COMPOSE_COMMAND) run --rm app phpunit

.PHONY: analyse
analyze:
	$(DOCKER_COMPOSE_COMMAND) run --rm app phpstan analyse src --memory-limit 1G

.PHONY: bash
bash:
	$(DOCKER_COMPOSE_COMMAND) run --rm app bash --login

.PHONY: update
update:
	$(DOCKER_COMPOSE_COMMAND) run --rm app composer update
