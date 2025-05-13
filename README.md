# Tic-tac-toe application

Tic-tac-toe is an online game for two players who take turns marking the spaces in a three-by-three grid with X or O. The player who succeeds in placing three of their marks in a horizontal, vertical, or diagonal row first is the winner. 

## Requirements
* PHP 8.3 or higher
* Symfony CLI binary
* Docker Compose
* Node.js & NPM


## Installation
1. Clone the repository to your computer
```bash
git clone git@github.com:maxwellzp/php-tic-tac-toe.git
```
2. Change your current directory to the project directory
```bash
cd php-tic-tac-toe
```
3. Install PHP dependencies
```bash
composer install
```
4. Install node modules dependencies and build them in dev
```bash
npm install
npm run dev
```
5. Create .env.local for your local environment variables
```bash
cp .env .env.local
```
6. Set up your environment variables for Postgres in .env.local
```dotenv
POSTGRES_DB=yourdbname
POSTGRES_USER=youruser
POSTGRES_PASSWORD=yourpassword
```
7. Start database and mercure services via Docker compose
```bash
docker compose --env-file .env.local up -d
```
8. Migrate Doctrine migration files
```bash
symfony console doctrine:migrations:migrate 
```

9. Start Symfony development server
```bash
symfony server:start --no-tls -d
```

## Usage
* Access the application in any browser at the given URL http://127.0.0.1:8000/game

## Running Tests

To run tests, run the following command

```bash
composer test
```


## License

[MIT](https://choosealicense.com/licenses/mit/)

