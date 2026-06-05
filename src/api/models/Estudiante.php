<?php
/**
 * Modelo: Estudiante
 * Manejo de perfiles de estudiantes
 */

class Estudiante {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtener todos los estudiantes con filtros y paginación
     */
    public function getAll($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = ['activo = true'];
        $params = [];

        // Filtro por búsqueda (nombre o correo)
        if (!empty($filters['search'])) {
            $search = '%' . strtolower($filters['search']) . '%';
            $where[] = "(LOWER(nombre) LIKE :search OR LOWER(correo) LIKE :search)";
            $params[':search'] = $search;
        }

        // Filtro por grado académico
        if (!empty($filters['grado'])) {
            $where[] = "grado_academico = :grado";
            $params[':grado'] = $filters['grado'];
        }

        // Filtro por curso inscrito
        if (!empty($filters['curso_instancia_id'])) {
            $where[] = "id IN (SELECT estudiante_id FROM inscripciones WHERE curso_instancia_id = :curso_id AND estado = 'activo')";
            $params[':curso_id'] = $filters['curso_instancia_id'];
        }

        $whereClause = implode(' AND ', $where);

        // Contar total
        $total = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM estudiantes WHERE $whereClause",
            $params
        )['count'];

        // Obtener registros
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $sql = "SELECT id, nombre, correo, foto, matricula, telefono,
                       grado_academico, carrera_licenciatura, area_formacion,
                       created_at, updated_at
                FROM estudiantes
                WHERE $whereClause
                ORDER BY nombre ASC
                LIMIT :limit OFFSET :offset";

        $estudiantes = $this->db->select($sql, $params);

        return [
            'items' => $estudiantes,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * Obtener estudiante por ID con información completa
     */
    public function getById($id) {
        $sql = "SELECT e.*,
                       d.conocimiento_total as diagnostico_puntaje,
                       d.created_at as diagnostico_fecha
                FROM estudiantes e
                LEFT JOIN diagnostico_disde_respuestas d ON e.diagnostico_respuesta_id = d.id
                WHERE e.id = :id";

        $estudiante = $this->db->selectOne($sql, [':id' => $id]);

        if (!$estudiante) {
            return null;
        }

        // Obtener cursos inscritos
        $estudiante['cursos'] = $this->getCursos($id);

        // Obtener cuestionarios respondidos
        $estudiante['cuestionarios'] = $this->getCuestionarios($id);

        return $estudiante;
    }

    /**
     * Obtener cursos del estudiante
     */
    private function getCursos($estudianteId) {
        $sql = "SELECT ci.id, ci.nombre, ci.codigo, ci.periodo,
                       ci.fecha_inicio, ci.fecha_fin,
                       i.estado as estado_inscripcion,
                       i.calificacion_final, i.aprobado,
                       i.fecha_inscripcion
                FROM inscripciones i
                INNER JOIN cursos_instancias ci ON i.curso_instancia_id = ci.id
                WHERE i.estudiante_id = :estudiante_id
                ORDER BY ci.fecha_inicio DESC";

        return $this->db->select($sql, [':estudiante_id' => $estudianteId]);
    }

    /**
     * Obtener cuestionarios respondidos
     */
    private function getCuestionarios($estudianteId) {
        // Por ahora solo el diagnóstico, más adelante incluirá todos los cuestionarios
        $sql = "SELECT 'Diagnóstico DISDE' as nombre,
                       conocimiento_total as puntaje,
                       created_at as fecha
                FROM diagnostico_disde_respuestas
                WHERE id = (SELECT diagnostico_respuesta_id FROM estudiantes WHERE id = :estudiante_id)";

        return $this->db->select($sql, [':estudiante_id' => $estudianteId]);
    }

    /**
     * Crear estudiante
     */
    public function create($data) {
        $sql = "INSERT INTO estudiantes (
                    nombre, correo, foto, matricula, telefono,
                    grado_academico, carrera_licenciatura, area_formacion,
                    institucion_licenciatura, institucion_maestria, notas
                ) VALUES (
                    :nombre, :correo, :foto, :matricula, :telefono,
                    :grado, :carrera, :area,
                    :inst_lic, :inst_maestria, :notas
                ) RETURNING id";

        $result = $this->db->selectOne($sql, [
            ':nombre' => $data['nombre'],
            ':correo' => $data['correo'],
            ':foto' => $data['foto'] ?? null,
            ':matricula' => $data['matricula'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':grado' => $data['grado_academico'] ?? null,
            ':carrera' => $data['carrera_licenciatura'] ?? null,
            ':area' => $data['area_formacion'] ?? null,
            ':inst_lic' => $data['institucion_licenciatura'] ?? null,
            ':inst_maestria' => $data['institucion_maestria'] ?? null,
            ':notas' => $data['notas'] ?? null
        ]);

        return $result['id'];
    }

    /**
     * Actualizar estudiante
     */
    public function update($id, $data) {
        $sql = "UPDATE estudiantes SET
                    nombre = :nombre,
                    correo = :correo,
                    foto = :foto,
                    matricula = :matricula,
                    telefono = :telefono,
                    grado_academico = :grado,
                    carrera_licenciatura = :carrera,
                    area_formacion = :area,
                    institucion_licenciatura = :inst_lic,
                    institucion_maestria = :inst_maestria,
                    notas = :notas
                WHERE id = :id";

        return $this->db->update($sql, [
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':correo' => $data['correo'],
            ':foto' => $data['foto'] ?? null,
            ':matricula' => $data['matricula'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':grado' => $data['grado_academico'] ?? null,
            ':carrera' => $data['carrera_licenciatura'] ?? null,
            ':area' => $data['area_formacion'] ?? null,
            ':inst_lic' => $data['institucion_licenciatura'] ?? null,
            ':inst_maestria' => $data['institucion_maestria'] ?? null,
            ':notas' => $data['notas'] ?? null
        ]);
    }

    /**
     * Eliminar (soft delete)
     */
    public function delete($id) {
        $sql = "UPDATE estudiantes SET activo = false WHERE id = :id";
        return $this->db->update($sql, [':id' => $id]);
    }

    /**
     * Obtener estadísticas generales
     */
    public function getStats() {
        $stats = [];

        // Total de estudiantes activos
        $stats['total'] = $this->db->selectOne("SELECT COUNT(*) as count FROM estudiantes WHERE activo = true")['count'];

        // Por grado académico
        $stats['por_grado'] = $this->db->select("
            SELECT grado_academico, COUNT(*) as count
            FROM estudiantes
            WHERE activo = true AND grado_academico IS NOT NULL
            GROUP BY grado_academico
            ORDER BY count DESC
        ");

        // Inscritos en cursos activos
        $stats['inscritos_cursos_activos'] = $this->db->selectOne("
            SELECT COUNT(DISTINCT e.id) as count
            FROM estudiantes e
            INNER JOIN inscripciones i ON e.id = i.estudiante_id
            INNER JOIN cursos_instancias ci ON i.curso_instancia_id = ci.id
            WHERE e.activo = true
            AND i.estado = 'activo'
            AND ci.estado = 'activo'
        ")['count'];

        return $stats;
    }
}
