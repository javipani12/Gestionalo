<?php 
    class User{
        protected $nombre;
        protected $apellido1;
        protected $apellido2;
        protected $email;
        protected $password;
        protected $localidad;
        protected $fecha_nacimiento;
        protected $edad;

        public function __construct($data){
            foreach($this as $field=>$value){
                if(property_exists($this, $field)){
                    $this->$field = $data[$field];
                }
            }
        }

        public function getNombre(){
            return $this->nombre;
        }

        public function getApellido1(){
            return $this->apellido1;
        }
        
        public function getApellido2(){
            return $this->apellido2;
        }

        public function getEmail(){
            return $this->email;
        }

        public function getPassword(){
            return $this->password;
        }

        public function getLocalidad(){
            return $this->localidad;
        }

        public function getFechaNacimiento(){
            return $this->fecha_nacimiento;
        }

        public function getEdad(){
            return $this->edad;
        }

        public function getFields(){
            $fields = array(); 
            foreach($this as $field=>$value){
                $fields[$field] = "'" . $value . "'";
            }
            return $fields;
        }
    }
?>