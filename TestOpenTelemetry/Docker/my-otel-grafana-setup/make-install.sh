#!/bin/bash

# Variables
DOCKER_COMPOSE_FILE="docker-compose.yml"
INSTALL_DIR="/opt/observabilityApp"
DOCKER_CMD="docker compose"
APP_NAME="observabilityApp"

# Function to check prerequisites
check_prerequisites() {
    if ! command -v docker &> /dev/null; then
        echo "Docker is not installed. Please install Docker and try again."
        exit 1
    fi

    if ! command -v docker-compose &> /dev/null && ! command -v docker compose &> /dev/null; then
        echo "Docker Compose is not installed. Please install Docker Compose and try again."
        exit 1
    fi
}

# Function to install the Docker Compose configuration
install() {
    echo "Installing $APP_NAME..."

    if [ ! -f "$DOCKER_COMPOSE_FILE" ]; then
        echo "Error: $DOCKER_COMPOSE_FILE not found!"
        exit 1
    fi

    sudo mkdir -p "$INSTALL_DIR"

    # Copy everything from the current folder to the install directory
    sudo cp -r ./* "$INSTALL_DIR"

    echo "All necessary files copied to $INSTALL_DIR."
    echo "Installation complete. Use './make-install.sh start' to launch the application."
}

# Function to start services
start() {
    echo "Starting $APP_NAME services..."
    sudo $DOCKER_CMD -f "$INSTALL_DIR/$DOCKER_COMPOSE_FILE" up -d
    echo "$APP_NAME services started."
}

# Function to stop services
stop() {
    echo "Stopping $APP_NAME services..."
    sudo $DOCKER_CMD -f "$INSTALL_DIR/$DOCKER_COMPOSE_FILE" down
    echo "$APP_NAME services stopped."
}

# Function to restart services
restart() {
    stop
    start
}

# Function to check status of services
status() {
    echo "Checking status of $APP_NAME services..."
    sudo $DOCKER_CMD -f "$INSTALL_DIR/$DOCKER_COMPOSE_FILE" ps
}

# Function to view logs
logs() {
    echo "Viewing logs for $APP_NAME services..."
    sudo $DOCKER_CMD -f "$INSTALL_DIR/$DOCKER_COMPOSE_FILE" logs --tail=100 -f
}

# Function to uninstall the Docker Compose configuration
uninstall() {
    echo "Uninstalling $APP_NAME..."
    sudo rm -rf "$INSTALL_DIR"
    echo "$APP_NAME uninstalled."
}

# Main script logic
case "$1" in
    install)
        check_prerequisites
        install
        ;;
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart
        ;;
    status)
        status
        ;;
    logs)
        logs
        ;;
    uninstall)
        uninstall
        ;;
    *)
        echo "Usage: $0 {install|start|stop|restart|status|logs|uninstall}"
        ;;
esac