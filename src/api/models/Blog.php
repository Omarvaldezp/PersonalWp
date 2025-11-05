<?php
/**
 * Modelo Blog - Manejo de blog posts
 */

require_once __DIR__ . '/../config/Database.php';

class Blog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todos los posts con paginación y filtros
     *
     * @param array $options Opciones de consulta
     * @return array
     */
    public function getAll($options = []) {
        $page = $options['page'] ?? 1;
        $perPage = $options['per_page'] ?? 12;
        $categoria = $options['categoria'] ?? null;
        $estado = $options['estado'] ?? 'publicado';
        $search = $options['search'] ?? null;
        $orderBy = $options['order_by'] ?? 'fecha_publicacion';
        $orderDir = $options['order_dir'] ?? 'DESC';

        $offset = ($page - 1) * $perPage;

        // Construir WHERE clause
        $whereClauses = ["estado = :estado"];
        $params = [':estado' => $estado];

        if ($categoria) {
            $whereClauses[] = ":categoria = ANY(categorias)";
            $params[':categoria'] = $categoria;
        }

        if ($search) {
            $whereClauses[] = "search_vector @@ plainto_tsquery('spanish', :search)";
            $params[':search'] = $search;
        }

        $whereSQL = implode(' AND ', $whereClauses);

        // Consulta principal
        $sql = "SELECT
                    id, titulo, slug, extracto, imagen_portada,
                    categorias, etiquetas, fecha_publicacion,
                    visitas, likes,
                    created_at, updated_at
                FROM blog_posts
                WHERE $whereSQL
                ORDER BY $orderBy $orderDir
                LIMIT :limit OFFSET :offset";

        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $posts = $this->db->select($sql, $params);

        // Convertir arrays PostgreSQL a PHP arrays
        foreach ($posts as &$post) {
            $post['categorias'] = $this->pgArrayToPhp($post['categorias']);
            $post['etiquetas'] = $this->pgArrayToPhp($post['etiquetas']);
        }

        // Obtener total para paginación
        $countSQL = "SELECT COUNT(*) as total FROM blog_posts WHERE $whereSQL";
        $countParams = array_diff_key($params, [':limit' => '', ':offset' => '']);
        $totalResult = $this->db->selectOne($countSQL, $countParams);
        $total = $totalResult['total'];

        return [
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Obtener un post por ID
     */
    public function getById($id) {
        $sql = "SELECT
                    bp.*,
                    u.nombre_completo as autor_nombre
                FROM blog_posts bp
                LEFT JOIN usuarios u ON bp.autor_id = u.id
                WHERE bp.id = :id";

        $post = $this->db->selectOne($sql, [':id' => $id]);

        if ($post) {
            $post['categorias'] = $this->pgArrayToPhp($post['categorias']);
            $post['etiquetas'] = $this->pgArrayToPhp($post['etiquetas']);
            $post['metadata'] = json_decode($post['metadata'], true);
        }

        return $post;
    }

    /**
     * Obtener un post por slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT
                    bp.*,
                    u.nombre_completo as autor_nombre
                FROM blog_posts bp
                LEFT JOIN usuarios u ON bp.autor_id = u.id
                WHERE bp.slug = :slug";

        $post = $this->db->selectOne($sql, [':slug' => $slug]);

        if ($post) {
            $post['categorias'] = $this->pgArrayToPhp($post['categorias']);
            $post['etiquetas'] = $this->pgArrayToPhp($post['etiquetas']);
            $post['metadata'] = json_decode($post['metadata'], true);

            // Incrementar visitas
            $this->incrementViews($post['id']);
        }

        return $post;
    }

    /**
     * Crear un nuevo post
     */
    public function create($data) {
        $sql = "INSERT INTO blog_posts (
                    titulo, slug, extracto, contenido, imagen_portada,
                    autor_id, categorias, etiquetas, metadata, estado, fecha_publicacion
                ) VALUES (
                    :titulo, :slug, :extracto, :contenido, :imagen_portada,
                    :autor_id, :categorias, :etiquetas, :metadata, :estado, :fecha_publicacion
                ) RETURNING id";

        $params = [
            ':titulo' => $data['titulo'],
            ':slug' => $data['slug'],
            ':extracto' => $data['extracto'] ?? null,
            ':contenido' => $data['contenido'],
            ':imagen_portada' => $data['imagen_portada'] ?? null,
            ':autor_id' => $data['autor_id'],
            ':categorias' => $this->phpArrayToPg($data['categorias'] ?? []),
            ':etiquetas' => $this->phpArrayToPg($data['etiquetas'] ?? []),
            ':metadata' => json_encode($data['metadata'] ?? []),
            ':estado' => $data['estado'] ?? 'borrador',
            ':fecha_publicacion' => $data['fecha_publicacion'] ?? null,
        ];

        $result = $this->db->selectOne($sql, $params);
        return $result['id'];
    }

    /**
     * Actualizar un post
     */
    public function update($id, $data) {
        $sql = "UPDATE blog_posts SET
                    titulo = :titulo,
                    slug = :slug,
                    extracto = :extracto,
                    contenido = :contenido,
                    imagen_portada = :imagen_portada,
                    categorias = :categorias,
                    etiquetas = :etiquetas,
                    metadata = :metadata,
                    estado = :estado,
                    fecha_publicacion = :fecha_publicacion,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $params = [
            ':id' => $id,
            ':titulo' => $data['titulo'],
            ':slug' => $data['slug'],
            ':extracto' => $data['extracto'] ?? null,
            ':contenido' => $data['contenido'],
            ':imagen_portada' => $data['imagen_portada'] ?? null,
            ':categorias' => $this->phpArrayToPg($data['categorias'] ?? []),
            ':etiquetas' => $this->phpArrayToPg($data['etiquetas'] ?? []),
            ':metadata' => json_encode($data['metadata'] ?? []),
            ':estado' => $data['estado'],
            ':fecha_publicacion' => $data['fecha_publicacion'] ?? null,
        ];

        return $this->db->update($sql, $params);
    }

    /**
     * Eliminar un post
     */
    public function delete($id) {
        $sql = "DELETE FROM blog_posts WHERE id = :id";
        return $this->db->delete($sql, [':id' => $id]);
    }

    /**
     * Incrementar visitas de un post
     */
    public function incrementViews($id) {
        $sql = "UPDATE blog_posts SET visitas = visitas + 1 WHERE id = :id";
        return $this->db->update($sql, [':id' => $id]);
    }

    /**
     * Incrementar likes de un post
     */
    public function incrementLikes($id) {
        $sql = "UPDATE blog_posts SET likes = likes + 1 WHERE id = :id";
        return $this->db->update($sql, [':id' => $id]);
    }

    /**
     * Obtener categorías únicas
     */
    public function getCategories() {
        $sql = "SELECT DISTINCT unnest(categorias) as categoria
                FROM blog_posts
                WHERE estado = 'publicado'
                ORDER BY categoria";

        $result = $this->db->select($sql);
        return array_column($result, 'categoria');
    }

    /**
     * Obtener posts relacionados
     */
    public function getRelated($id, $limit = 3) {
        $sql = "SELECT
                    bp2.id, bp2.titulo, bp2.slug, bp2.extracto,
                    bp2.imagen_portada, bp2.fecha_publicacion
                FROM blog_posts bp1
                CROSS JOIN blog_posts bp2
                WHERE bp1.id = :id
                    AND bp2.id != :id
                    AND bp2.estado = 'publicado'
                    AND bp1.categorias && bp2.categorias
                ORDER BY
                    RANDOM()
                LIMIT :limit";

        return $this->db->select($sql, [
            ':id' => $id,
            ':limit' => $limit
        ]);
    }

    /**
     * Búsqueda full-text
     */
    public function search($query, $limit = 10) {
        $sql = "SELECT
                    id, titulo, slug, extracto, imagen_portada, fecha_publicacion,
                    ts_rank(search_vector, plainto_tsquery('spanish', :query)) as rank
                FROM blog_posts
                WHERE search_vector @@ plainto_tsquery('spanish', :query)
                    AND estado = 'publicado'
                ORDER BY rank DESC, fecha_publicacion DESC
                LIMIT :limit";

        return $this->db->select($sql, [
            ':query' => $query,
            ':limit' => $limit
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

        // Remover llaves y parsear
        $pgArray = trim($pgArray, '{}');
        if (empty($pgArray)) {
            return [];
        }

        // Split considerando elementos con comillas
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
