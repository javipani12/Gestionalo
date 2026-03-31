<?php
    class DefaultDataModel {
        private $db;

        private const TABLE_MAP = [
            'categorias' => [
                'id' => 'id_categoria',
                'name' => 'nombre_categoria'
            ],
            'subcategorias' => [
                'id' => 'id_subcategoria',
                'name' => 'nombre_subcategoria'
            ],
            'metodos_pago' => [
                'id' => 'id_metodo',
                'name' => 'nombre'
            ],
            'estados_objetivo' => [
                'id' => 'id_estado',
                'name' => 'nombre'
            ],
            'roles' => [
                'id' => 'id_rol',
                'name' => 'nombre'
            ],
            'tipos_informe' => [
                'id' => 'id_tipo_informe',
                'name' => 'nombre'
            ],
            'tipos_movimiento' => [
                'id' => 'id_tipo',
                'name' => 'nombre'
            ]
        ];

        public function __construct() {
            $database = new Database();
            $this->db = $database->conectar();
        }

        /**
         * Obtiene todos los elementos de una tabla maestra permitida.
         */
        public function obtenerTodos($tabla) {
            $config = $this->obtenerConfiguracionTabla($tabla);

            $sql = "SELECT {$config['id']} AS id, {$config['name']} AS nombre
                FROM {$tabla}
                ORDER BY {$config['name']} ASC
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Obtiene subcategorías junto a su categoría para filtrado en cliente.
         */
        public function obtenerSubcategoriasConCategoria() {
            $sql = "SELECT id_subcategoria AS id, id_categoria, nombre_subcategoria AS nombre
                FROM subcategorias
                ORDER BY nombre_subcategoria ASC
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Comprueba si existe un ID en una tabla maestra permitida.
         */
        public function existeId($tabla, $id) {
            $config = $this->obtenerConfiguracionTabla($tabla);
            $id = (int)$id;

            if($id <= 0) {
                return false;
            }

            $sql = "SELECT 1
                FROM {$tabla}
                WHERE {$config['id']} = :id
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return (bool)$stmt->fetchColumn();
        }

        /**
         * Comprueba si una subcategoría pertenece a una categoría.
         */
        public function validarSubcategoriaDeCategoria($id_subcategoria, $id_categoria) {
            $id_subcategoria = (int)$id_subcategoria;
            $id_categoria = (int)$id_categoria;

            if($id_subcategoria <= 0 || $id_categoria <= 0) {
                return false;
            }

            $sql = "SELECT 1
                FROM subcategorias
                WHERE id_subcategoria = :id_subcategoria
                AND id_categoria = :id_categoria
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_subcategoria', $id_subcategoria, PDO::PARAM_INT);
            $stmt->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
            $stmt->execute();

            return (bool)$stmt->fetchColumn();
        }

        /**
         * Devuelve la configuración de una tabla maestra o lanza excepción si no está permitida.
         */
        private function obtenerConfiguracionTabla($tabla) {
            if(!isset(self::TABLE_MAP[$tabla])) {
                throw new InvalidArgumentException("Tabla no permitida: {$tabla}");
            }

            return self::TABLE_MAP[$tabla];
        }
    }
?>