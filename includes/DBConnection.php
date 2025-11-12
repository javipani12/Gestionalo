<?php
    //Clase para la conexión a la base de datos
    class DBConnection {
        //Recibe en un array con los settings de acceso a la BBDD
        protected $_config; // Propiedad que contiene los settings de la BBDD
        public $dbc;        // Propiedad que contiene la conexión a la BBDD
        
        /* 
         * Función constructor de la clase
         * Abre una conexión con la BBDD
         * @param $_config array Parámetros de la conexión con la BBDD
         * //una línea por cada parámetro; el nombre, el tipo y para qué se usa
         */
        public function __construct(array $_config){
            //Asignamos los valores del array que pasamos en la instanciación
            //($dbsetting pasa a ser $config);
            $this->_config = $_config;
            //Intentamos conseguir una conexión válida;
            $this->getPDOConnection();
        }
        
        
        /* 
         * Función destructora de la clase
         * Cierra la conexión con la BBDD
         */
        public function __destruct(){
            $this->dbc = NULL;
        }
        
        private function getPDOConnection(){
            //Comprobar si ya tenemos una conexión previa
            if($this->dbc == NULL){ //No tenemos conexión previa
                
                //Creamos el DSN
                $dsn = "".
                        $this->_config['driver'] . 
                        ":host=" . $this->_config['host'] . 
                        ";dbname=" . $this->_config['dbname'];
                
                //Hacemos la conexión persistente y activar el lanzamiento de
                //excepciones propias de PDO
                $options = array (
                    PDO::ATTR_PERSISTENT    => true,
                    PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION,
                );
                
                try{
                    $this->dbc = new PDO($dsn, $this->_config['user'], $this->_config['password'], $options);
                } catch(PDOException $e) {
                    echo __LINE__.$e->getMessage();
                }
            }
        }
        
        /* 
         * Función que devuelve un resultset de una consulta
         * Ejecuta una consulta SELECT
         * @param sql string Sentencia de la consulta
         * @returns resulset
         */
        public function getQuery($sql){
            try{
                $resultset = $this->dbc->query($sql);           //Hacemos la consulta propiamente dicha que llega a través de $sql
                $resultset->setFetchMode(PDO::FETCH_ASSOC);     //Nos devuelve el resultset como un array asociativo
            } catch(PDOException $e) {
                echo __LINE__.$e->getMessage();
            }
            
            return $resultset;
        }
        
        /* 
         * Función que devuelve el número de tuplas afectadas
         * Ejecuta una consulta INSERT, UPDATE, DELETE
         * @param sql string Sentencia de la consulta
         * @returns int nuúmero de tuplas afectadas
         */
        public function runQuery($sql){
            try{
                $count = $this->dbc->exec($sql);
            } catch(PDOException $e) {
                echo __LINE__.$e->getMessage();
            }
            
            return $count;
        }
    }
?>