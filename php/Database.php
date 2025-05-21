<?php

require_once '../vendor/autoload.php';
// Load the .env file
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__) . '/');
$dotenv->load();

/**
 * The Database class is resposible to create the database connection and return the connection string.
 */
class Database {

  /**
   * The database connection string.
   * 
   * @var 
   */
  protected $conn;

  /**
   * The construcor will initilize the database connection.
   */
  function __construct() {
    try {
      $this->conn = new PDO(
        "mysql:host={$_ENV['SERVERNAME']};dbname={$_ENV['USERDATABASE']}",
        $_ENV['USERNAME'],
        $_ENV['PASSWORD']
      );
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      return $this->conn;
    }
    catch (PDOException $e) {
      return NULL;
    }
  }
}
?>
