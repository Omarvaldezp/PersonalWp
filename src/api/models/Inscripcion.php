<?php
/**
 * Modelo: Inscripcion
 * Manejo de inscripciones (matrículas) de estudiantes en cursos
 */

class Inscripcion {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Inscribir estudiante en curso
     */
    public function create($estudianteId, $cursoInstanciaId) {
        // Verificar que no esté ya inscrito
        $existe = $this->db->selectOne(
            "SELECT id FROM inscripciones WHERE estudiante_id = :estudiante_id AND curso_instancia_id = :curso_id",
            [':estudiante_id' => $estudianteId, ':curso_id' => $cursoInstanciaId]
        );

        if ($existe) {
            throw new Exception('El estudiante ya está inscrito en este curso');
        }

        $sql = "INSERT INTO inscripciones (estudiante_id, curso_instancia_id)
                VALUES (:estudiante_id, :curso_id)
                RETURNING id";

        $result = $this->db->selectOne($sql, [
            ':estudiante_id' => $estudianteId,
            ':curso_id' => $cursoInstanciaId
        ]);

        return $result['id'];
    }

    /**
     * Inscribir múltiples estudiantes
     */
    public function createBatch($estudianteIds, $cursoInstanciaId) {
        $inscritos = 0;
        $errores = [];

        foreach ($estudianteIds as $estudianteId) {
            try {
                $this->create($estudianteId, $cursoInstanciaId);
                $inscritos++;
            } catch (Exception $e) {
                $errores[] = ['estudiante_id' => $estudianteId, 'error' => $e->getMessage()];
            }
        }

        return [
            'inscritos' => $inscritos,
            'errores' => $errores
        ];
    }

    /**
     * Actualizar estado de inscripción
     */
    public function updateEstado($id, $estado) {
        $sql = "UPDATE inscripciones SET estado = :estado WHERE id = :id";
        return $this->db->update($sql, [':id' => $id, ':estado' => $estado]);
    }

    /**
     * Actualizar calificación final
     */
    public function updateCalificacion($id, $calificacion, $aprobado) {
        $sql = "UPDATE inscripciones SET
                    calificacion_final = :calificacion,
                    aprobado = :aprobado
                WHERE id = :id";

        return $this->db->update($sql, [
            ':id' => $id,
            ':calificacion' => $calificacion,
            ':aprobado' => $aprobado
        ]);
    }

    /**
     * Obtener inscripción por ID
     */
    public function getById($id) {
        $sql = "SELECT i.*,
                       e.nombre as estudiante_nombre,
                       e.correo as estudiante_correo,
                       ci.nombre as curso_nombre,
                       ci.codigo as curso_codigo
                FROM inscripciones i
                INNER JOIN estudiantes e ON i.estudiante_id = e.id
                INNER JOIN cursos_instancias ci ON i.curso_instancia_id = ci.id
                WHERE i.id = :id";

        return $this->db->selectOne($sql, [':id' => $id]);
    }

    /**
     * Obtener inscripciones por curso
     */
    public function getByCurso($cursoInstanciaId) {
        $sql = "SELECT i.*,
                       e.nombre as estudiante_nombre,
                       e.correo as estudiante_correo,
                       e.matricula as estudiante_matricula
                FROM inscripciones i
                INNER JOIN estudiantes e ON i.estudiante_id = e.id
                WHERE i.curso_instancia_id = :curso_id
                ORDER BY e.nombre ASC";

        return $this->db->select($sql, [':curso_id' => $cursoInstanciaId]);
    }

    /**
     * Eliminar inscripción
     */
    public function delete($id) {
        $sql = "DELETE FROM inscripciones WHERE id = :id";
        return $this->db->delete($sql, [':id' => $id]);
    }

    /**
     * Obtener estadísticas de inscripción por curso
     */
    public function getStatsByCurso($cursoInstanciaId) {
        $stats = [];

        // Total de inscritos
        $stats['total'] = $this->db->selectOne("
            SELECT COUNT(*) as count
            FROM inscripciones
            WHERE curso_instancia_id = :curso_id
        ", [':curso_id' => $cursoInstanciaId])['count'];

        // Por estado
        $stats['por_estado'] = $this->db->select("
            SELECT estado, COUNT(*) as count
            FROM inscripciones
            WHERE curso_instancia_id = :curso_id
            GROUP BY estado
        ", [':curso_id' => $cursoInstanciaId]);

        // Promedio de calificaciones
        $avg = $this->db->selectOne("
            SELECT AVG(calificacion_final) as promedio
            FROM inscripciones
            WHERE curso_instancia_id = :curso_id
            AND calificacion_final IS NOT NULL
        ", [':curso_id' => $cursoInstanciaId]);

        $stats['promedio_calificaciones'] = $avg['promedio'] ? round($avg['promedio'], 2) : null;

        // Aprobados
        $stats['aprobados'] = $this->db->selectOne("
            SELECT COUNT(*) as count
            FROM inscripciones
            WHERE curso_instancia_id = :curso_id
            AND aprobado = true
        ", [':curso_id' => $cursoInstanciaId])['count'];

        return $stats;
    }
}
