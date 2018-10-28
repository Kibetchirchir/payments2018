<?php

class D4M {

    private $DBHost = NULL;
    private $DBUser = NULL;
    private $DBPassword = NULL;
    private $DBName = NULL;
    private $mysqli = NULL;
    private $DB = NULL;

    public static function createInstance($host, $user, $password, $DB) {

        $DB = new self($host, $user, $password, $DB);
        return $DB;
    }

    private final function __construct($host, $user, $password, $DB) {

        $this->DBHost = $host;
        $this->DBUser = $user;
        $this->DBPassword = $password;
        $this->DBName = $DB;

        $this->mysqli = new mysqli("$this->DBHost", "$this->DBUser", "$this->DBPassword", "$this->DBName");
    }

    public function DoInsert($table, $values) {

        $strCollumns = "";
        $strInsterts = "";

        foreach ($values as $key => $item) {
            $strCollumns .= $key . ",";

            $strInsterts .= "'" . $item . "'" . ",";
        }
        $strCollumns = substr($strCollumns, 0, -1);
        $strInsterts = substr($strInsterts, 0, -1);
        //global $mysqli;
        //echo "This are the connection Parameters =>".$DBHost.$DBUser.$DBPassword.$DBName."\n";
        $query = "INSERT INTO $table($strCollumns) values($strInsterts)";
        //echo "\n$query\n";
        $stmt = $this->mysqli->prepare($query);
        if ($stmt) {

            $stmt->execute();
            //echo("insert Finalized!!");
           $this->mysqli->close();
            return $stmt->insert_id;
        } else {
             return $this->mysqli->error;
        }
    }

    public function DoUpdate($query = NULL, $params = NULL) {
        $stmt = $this->mysqli->prepare($query);
        if ($stmt) {
            if ($params != NULL) {
                call_user_func_array(array($stmt, "bind_param"), $params);
            }
            $stmt->execute();
            echo("update Finalized!!");
            $this->mysqli->close();
            return true;
        } else {
            return $this->mysqli->error;
        }
    }

    public function DoSelect($query = NULL, $params = NULL) {
        $pass = NULL;

        $stmt = $this->mysqli->stmt_init();
        if ($stmt = $this->mysqli->prepare($query)) {

            if ($params != NULL) {
                //$stmt->bind_param($params[0],$params[1]);

                call_user_func_array(array($stmt, "bind_param"), $params);
            }
            $stmt->execute();
           
            $result = array();
            $result = $stmt->get_result();

            //$stmt->close();

            return $result;
        } else {
            return $this->mysqli->error;
        }
        $this->mysqli->close();
    }
    
   
    

}

?>
