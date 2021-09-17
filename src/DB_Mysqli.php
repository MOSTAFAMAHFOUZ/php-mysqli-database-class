<?php 

namespace Src;
use mysqli;


class DB_Mysqli{


    private static $conn;
    // hold name of table
    private  $table;
    // hold query 
    private  $query;
    // hold result of query
    private  $result;
    // store any results of query
    private  $data = [];
    // hold all conditions 
    private  $where = [];
    // for order by or limit or 
    private  $additiinalParts = [];
    // define static value for sorting
    const  SORTING = "DESC";


    // connection details
    private $serverName;
    private $user;
    private $db_name;
    private $password;


    public function __construct($serverName,$user,$password,$db_name){
        $this->serverName = $serverName;
        $this->user = $user;
        $this->db_name = $db_name;
        $this->password = $password;
        $this->connection();
    }


    // connection 
    public function connection(){

        if(!self::$conn){
            // echo "new <br>";
            self::$conn = new mysqli($this->serverName,$this->user,$this->password,$this->db_name);
            self::$conn->set_charset("utf8");
        }

        return self::$conn;
    }


    // define the table 
    public  function table($table){
        $this->table = $table;
        return $this;
    } 

    // define first condition
    public function where($name,$op,$value){
        $value = self::$conn->real_escape_string($value);
        $this->where[] = " WHERE  `{$name}` {$op} '{$value}' ";
        return $this;
    }

    // add new condition with AND key 
    public function whereAnd($name,$op,$value){
        $value = self::$conn->real_escape_string($value);
        $this->where[] = " AND `{$name}` {$op} '{$value}' ";
        return $this;
    }

    // add new condition claus with (OR) key
    public function whereOr($name,$op,$value){
        $value = self::$conn->real_escape_string($value);
        $this->where[] = " OR `{$name}` {$op} '{$value}' ";
        return $this;
    }

    // extract  conditions 
    private function whereSentence() : string{
        $where = '';
        foreach($this->where as $val){
            $where .= $val; 
        }
        return $where;
    }

    // extract additional statements like ( order by or limit or another ...)
    public function additionalParts(){
        $additional = '';
        foreach($this->additiinalParts as $val){
            $additional .= $val; 
        }
        return $additional;
    }



    // insert new record
    public function insert($data){
        $fields ="";
        $values ="";
        foreach($data as $key => $value){
            if(array_key_last($data)){
                $fields .= "`$key`";
                $value = self::$conn->real_escape_string($value);
                $values .= "'$value'";
            }else{
                $fields .= "`$key`,";
                $value = self::$conn->real_escape_string($value);
                $values .= "\'$value\',";
            }
        }

        $this->query = "INSERT INTO {$this->table} ({$fields}) VALUES({$values}) ";
        return $this;
    }


     // update record
    public function update($data){
        $fields ="";
        foreach($data as $key => $value){
            if(array_key_last($data)){
                $value = self::$conn->real_escape_string($value);
                $fields .= " `{$key}`='$value'";
            }else{
                $value = self::$conn->real_escape_string($value);
                $fields .= " `{$key}`='$value',";
            }
        }

        $this->query = "UPDATE {$this->table} SET {$fields} {$this->whereSentence()} ";
        return $this;
    }


    // get all data 
    public function getAll(){
        // extract where 
        $this->query = "SELECT * FROM {$this->table} {$this->whereSentence()} {$this->additionalParts()} ";
        // die($this->query);
        if($this->save()){
            // die("sdfsadf");
            while($row = $this->result->fetch_object()){
                $this->data[] = $row;
            }
            $this->result->free();
            return $this->data;
        }else{
            return $this->queryError();
        }

    }


    // get one row
    public function getRow(array | int | null  $data=null){
        if(is_int($data) && empty($this->where)){
            $this->where = [" WHERE `id`='$data' "];
        }
        if(is_array($data) && empty($this->where) ){
            $this->where[] = [" WHERE `$data[0]` $data[1] '$data[2]'"];
        }
        $this->query = "SELECT * FROM {$this->table}   {$this->whereSentence()} ";
        if($this->save()){
            while($row = $this->result->fetch_object()){
                $this->data = $row;
            }
            return $this->data;
        }else{
            return $this->queryError();
        }

    }


    // delete from database 
    public function delete($id){
        $this->query = "DELETE FROM {$this->table} WHERE `id`='$id' ";
        if($this->save()){
            true;
        }else{
            return $this->queryError();
        }
    }


    public function save(){

        $this->result = self::$conn->query($this->query);

        if(self::$conn->affected_rows > 0){
            return true;
        }
        return false;
    }


    // get error of query 
    public function queryError(){
        return self::$conn->error;
    }


    // get count 
    public function getNumRows(){
        return $this->result->num_rows;
    }

    

    // order by 
    public function orderBy($field,$sort=self::SORTING){
        $this->additiinalParts[] = " ORDER BY `{$field}` {$sort} ";
        return $this;
    }

    // limit
    public function limit($count,$offset=0){
        $this->additiinalParts[] = " LIMIT {$offset} ,  {$count} ";
        return $this;
    }

    
    // get last inserted id 
    public function getLastId(){
        return self::$conn->insert_id;
    }



    // close the connection
    public function __destruct()
    {
        self::$conn->close();
    }




}



