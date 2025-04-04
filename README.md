# LeoContent

A personal media server for organizing and streaming your movie / show collection.

## Overview

LeoContent is a web application that helps you organize and stream your media files. It provides a clean interface for browsing your content and offers automatic metadata fetching.

## Features

- Movie organization with metadata
- Directory management
- Media streaming
- Automatic indexing
- User authentication

## Installation

1. Clone the repository
   ```
   git clone https://github.com/lplaat/LeoContent.git
   cd LeoContent
   ```

2. Configure environment
   ```
   cp .env.example .env
   ```
   Edit the .env file with your database credentials and media directories.

3. Set up the database
   ```
   # Import the database schema
   mysql -u your_username -p your_database_name < database.sql
   ```
   This will create all necessary tables and the default admin user (username: admin).

4. Start the application using Docker
   ```
   docker-compose up -d
   ```

5. Access the application at http://localhost
   Login with the default admin account:
   - Username: admin
   - Password: admin

## Requirements

- Docker and Docker Compose
- PHP 8.2+
- MySQL 8.0

## Development

To build and run the project locally:
```
docker-compose up --build -d
```
