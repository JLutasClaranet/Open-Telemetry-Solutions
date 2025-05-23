# Variables
DOCKER_COMPOSE_FILE=docker-compose.yml
INSTALL_DIR=/opt/observabilityApp
DOCKER_CMD=docker compose
APP_NAME=observabilityApp

.PHONY: check install start stop restart status logs uninstall

check:
	@command -v docker >/dev/null || (echo "Docker is not installed. Please install Docker and try again." && exit 1)
	@command -v docker-compose >/dev/null || command -v docker compose >/dev/null || (echo "Docker Compose is not installed. Please install Docker Compose and try again." && exit 1)

install: check
	@echo "Installing $(APP_NAME)..."
	@test -f $(DOCKER_COMPOSE_FILE) || (echo "Error: $(DOCKER_COMPOSE_FILE) not found!" && exit 1)
	sudo mkdir -p $(INSTALL_DIR)
	sudo cp -r ./* $(INSTALL_DIR)
	@echo "All necessary files copied to $(INSTALL_DIR)."
	@echo "Installation complete. Use 'make start' to launch the application."

start:
	@echo "Starting $(APP_NAME) services..."
	sudo $(DOCKER_CMD) -f $(INSTALL_DIR)/$(DOCKER_COMPOSE_FILE) up -d
	@echo "$(APP_NAME) services started."

stop:
	@echo "Stopping $(APP_NAME) services..."
	sudo $(DOCKER_CMD) -f $(INSTALL_DIR)/$(DOCKER_COMPOSE_FILE) down
	@echo "$(APP_NAME) services stopped."

restart: stop start

status:
	@echo "Checking status of $(APP_NAME) services..."
	sudo $(DOCKER_CMD) -f $(INSTALL_DIR)/$(DOCKER_COMPOSE_FILE) ps

logs:
	@echo "Viewing logs for $(APP_NAME) services..."
	sudo $(DOCKER_CMD) -f $(INSTALL_DIR)/$(DOCKER_COMPOSE_FILE) logs --tail=100 -f

uninstall:
	@echo "Uninstalling $(APP_NAME)..."
	sudo rm -rf $(INSTALL_DIR)
	@echo "$(APP_NAME) uninstalled."
