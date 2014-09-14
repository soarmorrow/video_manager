<?php
/*****************************************
 *  DB Class script
 * ***************************************
 * @file   : ajax/db.class.php
 * @author : Vineeth N K(me@vineethkrisknan.in)
 */
// database configuraton
require_once "config.class.php";
require_once "driver.class.php";

class DB {

    private $config;
    private $query;
    protected $db;
    protected $settings;

    public function getConnection() {
        $this->config = new Config();
        $this->connectDB();
    }

    private function connectDB() {
        $this->settings = $this->config->getDBAccess();
        $settings = $this->settings;
        try {
            $this->db = new PDO(Driver::loadDriver($settings['db']['type'], $settings['db']['host'], $settings['db']['db_name'], $settings['db']['path']), $settings['db']['username'], $settings['db']['password']);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $ex) {
            echo $ex->getMessage();
            exit;
        }
    }
    public function runQuery($query){
        $this->query = $query;
        return $this->excecuteQuery();
    }
    public function query($query){
        return $this->db->query($query);
    }
    private function excecuteQuery(){
        return $this->db->exec($this->query);
    }
    public function lastInsertedId(){
        return $this->db->lastInsertId();
    }
    public function setAssoc(){
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
}
?>
