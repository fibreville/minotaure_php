
.RECIPEPREFIX = >

up:
> docker-compose -f docker/docker-compose.yml --project-directory . up -d
.PHONY: up

down:
> docker-compose -f docker/docker-compose.yml --project-directory . down
.PHONY: down

logs:
> docker-compose -f docker/docker-compose.yml --project-directory . logs -f
.PHONY: up

build:
> docker build -t atrpg:latest -f docker/Dockerfile .
.PHONY: build

reset: down
> sudo rm -fr data
.PHONY: reset

