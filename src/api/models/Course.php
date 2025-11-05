<?php
/**
 * Modelo Course - Manejo de cursos
 */

require_once __DIR__ . '/../config/Database.php';

class Course {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todos los cursos con filtros
     */
    public function getAll($options = []) {
        $nivel = $options['nivel'] ?? null;
        $modalidad = $options['modalidad'] ?? null;
        $categoria = $options['categoria'] ?? null;
        $destacado = $options['destacado'] ?? null;
        $activo = $options['activo'] ?? true;
        $orderBy = $options['order_by'] ?? 'created_at';
        $orderDir = $options['order_dir'] ?? 'DESC';

        $whereClauses = [];
        $params = [];

        if ($activo !== null) {
            $whereClauses[] = "activo = :activo";
            $params[':activo'] = $activo ? 'true' : 'false';
        }

        if ($nivel) {
            $whereClauses[] = "nivel = :nivel";
            $params[':nivel'] = $nivel;
        }

        if ($modalidad) {
            $whereClauses[] = "modalidad = :modalidad";
            $params[':modalidad'] = $modalidad;
        }

        if ($categoria) {
            $whereClauses[] = ":categoria = ANY(categorias)";
            $params[':categoria'] = $categoria;
        }

        if ($destacado !== null) {
            $whereClauses[] = "destacado = :destacado";
            $params[':destacado'] = $destacado ? 'true' : 'false';
        }

        $whereSQL = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);

        $sql = "SELECT
                    id, titulo, slug, descripcion_corta, imagen_portada,
                    nivel, duracion_horas, precio, moneda, modalidad,
                    categorias, destacado, activo, cupo_maximo, inscritos,
                    calificacion, numero_resenas, fecha_inicio, fecha_fin
                FROM cursos
                $whereSQL
                ORDER BY $orderBy $orderDir";

        $cursos = $this->db->select($sql, $params);

        foreach ($cursos as &$curso) {
            $curso['categorias'] = $this->pgArrayToPhp($curso['categorias']);
        }

        return $cursos;
    }

    /**
     * Obtener un curso por ID
     */
    public function getById($id) {
        $sql = "SELECT
                    c.*,
                    u.nombre_completo as instructor_nombre
                FROM cursos c
                LEFT JOIN usuarios u ON c.instructor_id = u.id
                WHERE c.id = :id";

        $curso = $this->db->selectOne($sql, [':id' => $id]);

        if ($curso) {
            $curso['categorias'] = $this->pgArrayToPhp($curso['categorias']);
            $curso['habilidades_aprendidas'] = $this->pgArrayToPhp($curso['habilidades_aprendidas']);
            $curso['requisitos'] = $this->pgArrayToPhp($curso['requisitos']);
            $curso['temario'] = json_decode($curso['temario'], true);
            $curso['metadata'] = json_decode($curso['metadata'], true);
        }

        return $curso;
    }

    /**
     * Obtener curso por slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT
                    c.*,
                    u.nombre_completo as instructor_nombre
                FROM cursos c
                LEFT JOIN usuarios u ON c.instructor_id = u.id
                WHERE c.slug = :slug";

        $curso = $this->db->selectOne($sql, [':slug' => $slug]);

        if ($curso) {
            $curso['categorias'] = $this->pgArrayToPhp($curso['categorias']);
            $curso['habilidades_aprendidas'] = $this->pgArrayToPhp($curso['habilidades_aprendidas']);
            $curso['requisitos'] = $this->pgArrayToPhp($curso['requisitos']);
            $curso['temario'] = json_decode($curso['temario'], true);
            $curso['metadata'] = json_decode($curso['metadata'], true);
        }

        return $curso;
    }

    /**
     * Crear nuevo curso
     */
    public function create($data) {
        $sql = "INSERT INTO cursos (
                    titulo, slug, descripcion_corta, descripcion_completa,
                    imagen_portada, nivel, duracion_horas, precio, moneda,
                    modalidad, categorias, habilidades_aprendidas, requisitos,
                    temario, instructor_id, destacado, activo, cupo_maximo,
                    fecha_inicio, fecha_fin
                ) VALUES (
                    :titulo, :slug, :descripcion_corta, :descripcion_completa,
                    :imagen_portada, :nivel, :duracion_horas, :precio, :moneda,
                    :modalidad, :categorias, :habilidades_aprendidas, :requisitos,
                    :temario, :instructor_id, :destacado, :activo, :cupo_maximo,
                    :fecha_inicio, :fecha_fin
                ) RETURNING id";

        $params = [
            ':titulo' => $data['titulo'],
            ':slug' => $data['slug'],
            ':descripcion_corta' => $data['descripcion_corta'] ?? null,
            ':descripcion_completa' => $data['descripcion_completa'] ?? null,
            ':imagen_portada' => $data['imagen_portada'] ?? null,
            ':nivel' => $data['nivel'] ?? 'principiante',
            ':duracion_horas' => $data['duracion_horas'] ?? null,
            ':precio' => $data['precio'] ?? 0,
            ':moneda' => $data['moneda'] ?? 'MXN',
            ':modalidad' => $data['modalidad'] ?? 'online',
            ':categorias' => $this->phpArrayToPg($data['categorias'] ?? []),
            ':habilidades_aprendidas' => $this->phpArrayToPg($data['habilidades_aprendidas'] ?? []),
            ':requisitos' => $this->phpArrayToPg($data['requisitos'] ?? []),
            ':temario' => json_encode($data['temario'] ?? []),
            ':instructor_id' => $data['instructor_id'] ?? 1,
            ':destacado' => $data['destacado'] ?? false,
            ':activo' => $data['activo'] ?? true,
            ':cupo_maximo' => $data['cupo_maximo'] ?? null,
            ':fecha_inicio' => $data['fecha_inicio'] ?? null,
            ':fecha_fin' => $data['fecha_fin'] ?? null,
        ];

        $result = $this->db->selectOne($sql, $params);
        return $result['id'];
    }

    /**
     * Actualizar curso
     */
    public function update($id, $data) {
        $sql = "UPDATE cursos SET
                    titulo = :titulo,
                    slug = :slug,
                    descripcion_corta = :descripcion_corta,
                    descripcion_completa = :descripcion_completa,
                    imagen_portada = :imagen_portada,
                    nivel = :nivel,
                    duracion_horas = :duracion_horas,
                    precio = :precio,
                    moneda = :moneda,
                    modalidad = :modalidad,
                    categorias = :categorias,
                    habilidades_aprendidas = :habilidades_aprendidas,
                    requisitos = :requisitos,
                    temario = :temario,
                    destacado = :destacado,
                    activo = :activo,
                    cupo_maximo = :cupo_maximo,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $params = [
            ':id' => $id,
            ':titulo' => $data['titulo'],
            ':slug' => $data['slug'],
            ':descripcion_corta' => $data['descripcion_corta'],
            ':descripcion_completa' => $data['descripcion_completa'],
            ':imagen_portada' => $data['imagen_portada'],
            ':nivel' => $data['nivel'],
            ':duracion_horas' => $data['duracion_horas'],
            ':precio' => $data['precio'],
            ':moneda' => $data['moneda'],
            ':modalidad' => $data['modalidad'],
            ':categorias' => $this->phpArrayToPg($data['categorias']),
            ':habilidades_aprendidas' => $this->phpArrayToPg($data['habilidades_aprendidas']),
            ':requisitos' => $this->phpArrayToPg($data['requisitos']),
            ':temario' => json_encode($data['temario']),
            ':destacado' => $data['destacado'],
            ':activo' => $data['activo'],
            ':cupo_maximo' => $data['cupo_maximo'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
        ];

        return $this->db->update($sql, $params);
    }

    /**
     * Eliminar curso
     */
    public function delete($id) {
        $sql = "DELETE FROM cursos WHERE id = :id";
        return $this->db->delete($sql, [':id' => $id]);
    }

    /**
     * Obtener categorías de cursos
     */
    public function getCategories() {
        $sql = "SELECT DISTINCT unnest(categorias) as categoria
                FROM cursos
                WHERE activo = true
                ORDER BY categoria";

        $result = $this->db->select($sql);
        return array_column($result, 'categoria');
    }

    /**
     * Obtener cursos destacados
     */
    public function getFeatured($limit = 5) {
        return $this->getAll([
            'destacado' => true,
            'activo' => true,
            'order_by' => 'calificacion',
            'order_dir' => 'DESC'
        ]);
    }

    /**
     * Convertir array PHP a formato PostgreSQL
     */
    private function phpArrayToPg($array) {
        if (empty($array)) {
            return '{}';
        }
        return '{' . implode(',', array_map(function($item) {
            return '"' . str_replace('"', '\\"', $item) . '"';
        }, $array)) . '}';
    }

    /**
     * Convertir array PostgreSQL a PHP
     */
    private function pgArrayToPhp($pgArray) {
        if (!$pgArray || $pgArray === '{}') {
            return [];
        }

        $pgArray = trim($pgArray, '{}');
        if (empty($pgArray)) {
            return [];
        }

        $elements = [];
        $current = '';
        $inQuotes = false;

        for ($i = 0; $i < strlen($pgArray); $i++) {
            $char = $pgArray[$i];

            if ($char === '"') {
                $inQuotes = !$inQuotes;
            } elseif ($char === ',' && !$inQuotes) {
                $elements[] = trim($current, '"');
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if ($current !== '') {
            $elements[] = trim($current, '"');
        }

        return $elements;
    }
}
