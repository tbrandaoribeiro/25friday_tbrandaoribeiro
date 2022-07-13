<?php

//Defining the Database class to be used each time a query is to be used.
class Database
{
    
    /*
    user: 'xwouyqkk',
    host: 'manny.db.elephantsql.com',
    database: 'xwouyqkk',
    password: 'O8I4xrd1hdqCgGh8t1SBCKlSf9gkbL8g',
    port: 5432,
    rejectUnauthorized: false
    */ 

    private $db_host = 'manny.db.elephantsql.com';
    private $db_name = 'xwouyqkk';
    private $db_username = 'xwouyqkk';
    private $db_password = 'O8I4xrd1hdqCgGh8t1SBCKlSf9gkbL8g';


    public function dbConnection()
    {

        try {
            
            $conn = new PDO('pgsql:host=' . $this->db_host . ';dbname=' . $this->db_name, $this->db_username, $this->db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            echo "Connection error " . $e->getMessage();
            exit;
        }
    }
}