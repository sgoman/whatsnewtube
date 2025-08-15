# PHP REST API

This project is a simple REST API built with PHP 8 that allows users to retrieve video feeds from a specified YouTube channel using its channel ID.

## Project Structure

```
php-rest-api
├── public
│   └── index.php        # Entry point of the application
├── src
│   └── Router.php       # Router class for handling requests
├── composer.json         # Composer configuration file
└── README.md             # Project documentation
```

## Setup Instructions

1. **Clone the repository**:
   ```
   git clone <repository-url>
   cd php-rest-api
   ```

2. **Install dependencies**:
   Make sure you have Composer installed. Run the following command to install the project dependencies:
   ```
   composer install
   ```

3. **Run the application**:
   You can use the built-in PHP server to run the application:
   ```
   php -S localhost:8000 -t public
   ```

4. **Access the API**:
   You can access the API endpoint by navigating to:
   ```
   http://localhost:8000/?channel_id=<channel_id>
   ```
   Replace `<channel_id>` with the actual YouTube channel ID you want to query.

## Usage Example

To retrieve videos from a specific channel, make a GET request to the endpoint with the channel ID as a query parameter. For example:
```
GET http://localhost:8000/?channel_id=UC_x5XG1OV2P6uZZ5FSM9Ttw
```

This will return the XML data containing the latest videos from the specified YouTube channel.