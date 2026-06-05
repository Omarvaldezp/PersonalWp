<?php
/**
 * Modelo: CursoInstancia
 * Manejo de instancias específicas de cursos
 */

class CursoInstancia {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtener todas las instancias con filtros
     */
    public function getAll($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = ['1=1'];
        $params = [];

        // Filtro por estado
        if (!empty($filters['estado'])) {
            $where[] = "estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        // Filtro por periodo
        if (!empty($filters['periodo'])) {
            $where[] = "periodo = :periodo";
            $params[':periodo'] = $filters['periodo'];
        }

        // Filtro por búsqueda
        if (!empty($filters['search'])) {
            $search = '%' . strtolower($filters['search']) . '%';
            $where[] = "(LOWER(nombre) LIKE :search OR LOWER(codigo) LIKE :search)";
            $params[':search'] = $search;
        }

        $whereClause = implode(' AND ', $where);

        // Contar total
        $total = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM cursos_instancias WHERE $whereClause",
            $params
        )['count'];

        // Obtener registros
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $sql = "SELECT ci.*,
                       c.name as curso_base_nombre,
                       (SELECT COUNT(*) FROM inscripciones WHERE curso_instancia_id = ci.id AND estado = 'activo') as total_inscritos
                FROM cursos_instancias ci
                LEFT JOIN cursos c ON ci.curso_base_id = c.id
                WHERE $whereClause
                ORDER BY ci.fecha_inicio DESC
                LIMIT :limit OFFSET :offset";

        $cursos = $this->db->select($sql, $params);

        return [
            'items' => $cursos,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * Obtener instancia por ID
     */
    public function getById($id) {
        $sql = "SELECT ci.*,
                       c.name as curso_base_nombre,
                       (SELECT COUNT(*) FROM inscripciones WHERE curso_instancia_id = ci.id AND estado = 'activo') as total_inscritos
                FROM cursos_instancias ci
                LEFT JOIN cursos c ON ci.curso_base_id = c.id
                WHERE ci.id = :id";

        return $this->db->selectOne($sql, [':id' => $id]);
    }

    /**
     * Obtener estudiantes inscritos
     */
    public function getEstudiantes($cursoInstanciaId) {
        $sql = "SELECT e.id, e.nombre, e.correo, e.matricula,
                       i.estado as estado_inscripcion,
                       i.calificacion_final, i.aprobado,
                       i.fecha_inscripcion
                FROM inscripciones i
                INNER JOIN estudiantes e ON i.estudiante_id = e.id
                WHERE i.curso_instancia_id = :curso_id
                ORDER BY e.nombre ASC";

        return $this->db->select($sql, [':curso_id' => $cursoInstanciaId]);
    }

    /**
     * Crear instancia de curso
     */
    public function create($data) {
        $sql = "INSERT INTO cursos_instancias (
                    curso_base_id, nombre, codigo, descripcion,
                    fecha_inicio, fecha_fin, periodo,
                    cupo_maximo, instructores, estado
                ) VALUES (
                    :curso_base_id, :nombre, :codigo, :descripcion,
                    :fecha_inicio, :fecha_fin, :periodo,
                    :cupo_maximo, :instructores, :estado
                ) RETURNING id";

        $result = $this->db->selectOne($sql, [
            ':curso_base_id' => $data['curso_base_id'] ?? null,
            ':nombre' => $data['nombre'],
            ':codigo' => $data['codigo'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':periodo' => $data['periodo'] ?? null,
            ':cupo_maximo' => $data['cupo_maximo'] ?? null,
            ':instructores' => isset($data['instructores']) ? '{' . implode(',', $data['instructores']) . '}' : '{}',
            ':estado' => $data['estado'] ?? 'activo'
        ]);

        return $result['id'];
    }

    /**
     * Actualizar instancia
     */
    public function update($id, $data) {
        $sql = "UPDATE cursos_instancias SET
                    nombre = :nombre,
                    codigo = :codigo,
                    descripcion = :descripcion,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    periodo = :periodo,
                    cupo_maximo = :cupo_maximo,
                    instructores = :instructores,
                    estado = :estado
                WHERE id = :id";

        return $this->db->update($sql, [
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':codigo' => $data['codigo'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':periodo' => $data['periodo'] ?? null,
            ':cupo_maximo' => $data['cupo_maximo'] ?? null,
            ':instructores' => isset($data['instructores']) ? '{' . implode(',', $data['instructores']) . '}' : '{}',
            ':estado' => $data['estado']
        ]);
    }

    /**
     * Eliminar instancia
     */
    public function delete($id) {
        $sql = "DELETE FROM cursos_instancias WHERE id = :id";
        return $this->db->delete($sql, [':id' => $id]);
    }

    /**
     * Obtener períodos disponibles
     */
    public function getPeriodos() {
        $sql = "SELECT DISTINCT periodo
                FROM cursos_instancias
                WHERE periodo IS NOT NULL
                ORDER BY periodo DESC";

        return $this->db->select($sql);
    }
}
