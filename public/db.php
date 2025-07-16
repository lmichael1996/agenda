<?php

class ConnectionDB {
    private static $instance = null;
    private $mysqli;
    private $localhost = 'localhost';
    private $dbUser = 'dbuser';
    private $dbPass = 'dbpass';
    private $dbName = 'dbname';

    private function __construct() {
        $this->mysqli = new mysqli(
            $this->localhost, 
            $this->dbUser, 
            $this->dbPass, 
            $this->dbName
        );
        if ($this->mysqli->connect_errno) {
            throw new Exception('Errore di connessione al database.');
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new ConnectionDB();
        }
        return self::$instance;
    }

    public function check_login($username, $password) {
        $stmt = $this->mysqli->prepare('SELECT password FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        $result = false;

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($hash);
            $stmt->fetch();
            if (password_verify($password, $hash)) {
                $result = true;
            }
        }

        $stmt->close();
        return $result;
    }

    public function __destruct() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }
}

?>