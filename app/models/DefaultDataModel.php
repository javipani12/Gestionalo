<?php
    class DefaultDataModel {
        private $db;

        private const TABLE_MAP = [
            'categorias' => [
                'id' => 'id_categoria',
                'name' => 'nombre_categoria',
                'label' => 'Categorias'
            ],
            'subcategorias' => [
                'id' => 'id_subcategoria',
                'name' => 'nombre_subcategoria',
                'label' => 'Subcategorias'
            ],
            'metodos_pago' => [
                'id' => 'id_metodo',
                'name' => 'nombre',
                'label' => 'Metodos de pago'
            ],
            'estados_objetivo' => [
                'id' => 'id_estado',
                'name' => 'nombre',
                'label' => 'Estados de objetivo'
            ],
            'estados_consulta' => [
                'id' => 'id_estado',
                'name' => 'nombre',
                'label' => 'Estados de consulta'
            ],
            'asuntos' => [
                'id' => 'id_asunto',
                'name' => 'nombre',
                'label' => 'Asuntos'
            ],
            'roles' => [
                'id' => 'id_rol',
                'name' => 'nombre',
                'label' => 'Roles'
            ],
            'tipos_informe' => [
                'id' => 'id_tipo_informe',
                'name' => 'nombre',
                'label' => 'Tipos de informe'
            ],
            'tipos_movimiento' => [
                'id' => 'id_tipo',
                'name' => 'nombre',
                'label' => 'Tipos de movimiento'
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
         * Devuelve las tablas maestras disponibles para administración.
         */
        public function obtenerDefinicionesTablasMaestras() {
            $definiciones = [];

            foreach (self::TABLE_MAP as $tabla => $config) {
                $definiciones[$tabla] = [
                    'tabla' => $tabla,
                    'id' => $config['id'],
                    'name' => $config['name'],
                    'label' => $config['label'] ?? ucfirst(str_replace('_', ' ', $tabla))
                ];
            }

            return $definiciones;
        }

        /**
         * Devuelve el resumen de cada tabla maestra con número de registros.
         */
        public function obtenerResumenTablasMaestras() {
            $resumen = [];

            foreach ($this->obtenerDefinicionesTablasMaestras() as $tabla => $definicion) {
                $sql = "SELECT COUNT(*) FROM {$tabla}";
                $total = (int)$this->db->query($sql)->fetchColumn();

                $resumen[] = [
                    'tabla' => $tabla,
                    'label' => $definicion['label'],
                    'total' => $total
                ];
            }

            return $resumen;
        }

        /**
         * Cuenta registros de una tabla maestra con búsqueda opcional por nombre.
         */
        public function contarRegistrosTablaMaestra($tabla, $buscar = '') {
            $config = $this->obtenerConfiguracionTabla($tabla);
            $buscar = trim((string)$buscar);

            if ($tabla === 'subcategorias') {
                $sql = "SELECT COUNT(*)
                    FROM subcategorias s
                    INNER JOIN categorias c ON s.id_categoria = c.id_categoria
                    WHERE 1=1";

                if ($buscar !== '') {
                    $sql .= " AND (s.nombre_subcategoria LIKE :buscar OR c.nombre_categoria LIKE :buscar)";
                }

                $stmt = $this->db->prepare($sql);
                if ($buscar !== '') {
                    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
                }
                $stmt->execute();

                return (int)$stmt->fetchColumn();
            }

            $sql = "SELECT COUNT(*)
                FROM {$tabla}
                WHERE 1=1";

            if ($buscar !== '') {
                $sql .= " AND {$config['name']} LIKE :buscar";
            }

            $stmt = $this->db->prepare($sql);

            if ($buscar !== '') {
                $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
            }

            $stmt->execute();
            return (int)$stmt->fetchColumn();
        }

        /**
         * Obtiene registros paginados de una tabla maestra con búsqueda opcional.
         */
        public function obtenerRegistrosTablaMaestraPaginados($tabla, $limite, $offset, $buscar = '') {
            $config = $this->obtenerConfiguracionTabla($tabla);
            $buscar = trim((string)$buscar);

            if ($tabla === 'subcategorias') {
                $sql = "SELECT
                        s.id_subcategoria AS id,
                        s.nombre_subcategoria AS nombre,
                        c.nombre_categoria AS categoria
                    FROM subcategorias s
                    INNER JOIN categorias c ON s.id_categoria = c.id_categoria
                    WHERE 1=1";

                if ($buscar !== '') {
                    $sql .= " AND (s.nombre_subcategoria LIKE :buscar OR c.nombre_categoria LIKE :buscar)";
                }

                $sql .= " ORDER BY s.nombre_subcategoria ASC
                    LIMIT :limite OFFSET :offset";

                $stmt = $this->db->prepare($sql);

                if ($buscar !== '') {
                    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
                }

                $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $sql = "SELECT {$config['id']} AS id, {$config['name']} AS nombre
                FROM {$tabla}
                WHERE 1=1";

            if ($buscar !== '') {
                $sql .= " AND {$config['name']} LIKE :buscar";
            }

            $sql .= " ORDER BY {$config['name']} ASC
                LIMIT :limite OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            if ($buscar !== '') {
                $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
            }

            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Obtiene un registro de una tabla maestra por su ID.
         */
        public function obtenerRegistroTablaMaestraPorId($tabla, $id) {
            $config = $this->obtenerConfiguracionTabla($tabla);

            if ($tabla === 'subcategorias') {
                $sql = "SELECT
                        s.id_subcategoria AS id,
                        s.nombre_subcategoria AS nombre,
                        s.id_categoria
                    FROM subcategorias s
                    WHERE s.id_subcategoria = :id
                    LIMIT 1";

                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetch(PDO::FETCH_ASSOC);
            }

            $sql = "SELECT {$config['id']} AS id, {$config['name']} AS nombre
                FROM {$tabla}
                WHERE {$config['id']} = :id
                LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * Crea un registro en una tabla maestra permitida.
         */
        public function crearRegistroTablaMaestra($tabla, $datos) {
            $config = $this->obtenerConfiguracionTabla($tabla);

            if ($tabla === 'subcategorias') {
                $sql = "INSERT INTO subcategorias (id_categoria, nombre_subcategoria)
                    VALUES (:id_categoria, :nombre)";

                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id_categoria', (int)($datos['id_categoria'] ?? 0), PDO::PARAM_INT);
                $stmt->bindValue(':nombre', trim((string)($datos['nombre'] ?? '')), PDO::PARAM_STR);
                return $stmt->execute();
            }

            $sql = "INSERT INTO {$tabla} ({$config['name']})
                VALUES (:nombre)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', trim((string)($datos['nombre'] ?? '')), PDO::PARAM_STR);
            return $stmt->execute();
        }

        /**
         * Actualiza un registro de una tabla maestra permitida.
         */
        public function actualizarRegistroTablaMaestra($tabla, $id, $datos) {
            $config = $this->obtenerConfiguracionTabla($tabla);

            if ($tabla === 'subcategorias') {
                $sql = "UPDATE subcategorias
                    SET id_categoria = :id_categoria,
                        nombre_subcategoria = :nombre
                    WHERE id_subcategoria = :id";

                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id_categoria', (int)($datos['id_categoria'] ?? 0), PDO::PARAM_INT);
                $stmt->bindValue(':nombre', trim((string)($datos['nombre'] ?? '')), PDO::PARAM_STR);
                $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
                return $stmt->execute();
            }

            $sql = "UPDATE {$tabla}
                SET {$config['name']} = :nombre
                WHERE {$config['id']} = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', trim((string)($datos['nombre'] ?? '')), PDO::PARAM_STR);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            return $stmt->execute();
        }

        /**
         * Elimina un registro de una tabla maestra permitida.
         */
        public function eliminarRegistroTablaMaestra($tabla, $id) {
            $config = $this->obtenerConfiguracionTabla($tabla);

            $sql = "DELETE FROM {$tabla}
                WHERE {$config['id']} = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            return $stmt->execute();
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