<?php
/**
 * Modelo Research - Manejo de investigaciones y publicaciones académicas
 */

require_once __DIR__ . '/../config/Database.php';

class Research {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todas las investigaciones con filtros
     */
    public function getAll($options = []) {
        $tipo = $options['tipo'] ?? null;
        $categoria = $options['categoria'] ?? null;
        $destacado = $options['destacado'] ?? null;
        $ano = $options['ano'] ?? null;
        $orderBy = $options['order_by'] ?? 'fecha_publicacion';
        $orderDir = $options['order_dir'] ?? 'DESC';

        $whereClauses = [];
        $params = [];

        if ($tipo) {
            $whereClauses[] = "tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        if ($categoria) {
            $whereClauses[] = ":categoria = ANY(categorias)";
            $params[':categoria'] = $categoria;
        }

        if ($destacado !== null) {
            $whereClauses[] = "destacado = :destacado";
            $params[':destacado'] = $destacado ? 'true' : 'false';
        }

        if ($ano) {
            $whereClauses[] = "ano_publicacion = :ano";
            $params[':ano'] = $ano;
        }

        $whereSQL = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);

        $sql = "SELECT
                    id, titulo, slug, autores, abstract, tipo, categorias,
                    palabras_clave, revista, editorial, doi, url_publicacion,
                    pdf_url, fecha_publicacion, ano_publicacion, citaciones,
                    destacado, created_at
                FROM investigaciones
                $whereSQL
                ORDER BY $orderBy $orderDir";

        $investigaciones = $this->db->select($sql, $params);

        foreach ($investigaciones as &$inv) {
            $inv['autores'] = $this->pgArrayToPhp($inv['autores']);
            $inv['categorias'] = $this->pgArrayToPhp($inv['categorias']);
            $inv['palabras_clave'] = $this->pgArrayToPhp($inv['palabras_clave']);
        }

        return $investigaciones;
    }

    /**
     * Obtener investigación por ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM investigaciones WHERE id = :id";
        $inv = $this->db->selectOne($sql, [':id' => $id]);

        if ($inv) {
            $inv['autores'] = $this->pgArrayToPhp($inv['autores']);
            $inv['categorias'] = $this->pgArrayToPhp($inv['categorias']);
            $inv['palabras_clave'] = $this->pgArrayToPhp($inv['palabras_clave']);
            $inv['metadata'] = json_decode($inv['metadata'], true);
        }

        return $inv;
    }

    /**
     * Obtener por slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT * FROM investigaciones WHERE slug = :slug";
        $inv = $this->db->selectOne($sql, [':slug' => $slug]);

        if ($inv) {
            $inv['autores'] = $this->pgArrayToPhp($inv['autores']);
            $inv['categorias'] = $this->pgArrayToPhp($inv['categorias']);
            $inv['palabras_clave'] = $this->pgArrayToPhp($inv['palabras_clave']);
            $inv['metadata'] = json_decode($inv['metadata'], true);
        }

        return $inv;
    }

    /**
     * Crear investigación
     */
    public function create($data) {
        $sql = "INSERT INTO investigaciones (
                    titulo, slug, autores, abstract, contenido_completo, tipo,
                    categorias, palabras_clave, revista, editorial, isbn, doi,
                    url_publicacion, pdf_url, fecha_publicacion, ano_publicacion,
                    volumen, numero, paginas, idioma, acceso, destacado
                ) VALUES (
                    :titulo, :slug, :autores, :abstract, :contenido_completo, :tipo,
                    :categorias, :palabras_clave, :revista, :editorial, :isbn, :doi,
                    :url_publicacion, :pdf_url, :fecha_publicacion, :ano_publicacion,
                    :volumen, :numero, :paginas, :idioma, :acceso, :destacado
                ) RETURNING id";

        $params = [
            ':titulo' => $data['titulo'],
            ':slug' => $data['slug'],
            ':autores' => $this->phpArrayToPg($data['autores']),
            ':abstract' => $data['abstract'] ?? null,
            ':contenido_completo' => $data['contenido_completo'] ?? null,
            ':tipo' => $data['tipo'] ?? 'articulo',
            ':categorias' => $this->phpArrayToPg($data['categorias'] ?? []),
            ':palabras_clave' => $this->phpArrayToPg($data['palabras_clave'] ?? []),
            ':revista' => $data['revista'] ?? null,
            ':editorial' => $data['editorial'] ?? null,
            ':isbn' => $data['isbn'] ?? null,
            ':doi' => $data['doi'] ?? null,
            ':url_publicacion' => $data['url_publicacion'] ?? null,
            ':pdf_url' => $data['pdf_url'] ?? null,
            ':fecha_publicacion' => $data['fecha_publicacion'] ?? null,
            ':ano_publicacion' => $data['ano_publicacion'] ?? null,
            ':volumen' => $data['volumen'] ?? null,
            ':numero' => $data['numero'] ?? null,
            ':paginas' => $data['paginas'] ?? null,
            ':idioma' => $data['idioma'] ?? 'es',
            ':acceso' => $data['acceso'] ?? 'abierto',
            ':destacado' => $data['destacado'] ?? false,
        ];

        $result = $this->db->selectOne($sql, $params);
        return $result['id'];
    }

    /**
     * Actualizar investigación
     */
    public function update($id, $data) {
        $sql = "UPDATE investigaciones SET
                    titulo = :titulo, slug = :slug, autores = :autores,
                    abstract = :abstract, contenido_completo = :contenido_completo,
                    tipo = :tipo, categorias = :categorias, palabras_clave = :palabras_clave,
                    revista = :revista, editorial = :editorial, isbn = :isbn, doi = :doi,
                    url_publicacion = :url_publicacion, pdf_url = :pdf_url,
                    fecha_publicacion = :fecha_publicacion, ano_publicacion = :ano_publicacion,
                    volumen = :volumen, numero = :numero, paginas = :paginas,
                    idioma = :idioma, acceso = :acceso, destacado = :destacado,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $params = [
            ':id' => $id,
            ':titulo' => $data['titulo'],
            ':slug' => $data['slug'],
            ':autores' => $this->phpArrayToPg($data['autores']),
            ':abstract' => $data['abstract'],
            ':contenido_completo' => $data['contenido_completo'],
            ':tipo' => $data['tipo'],
            ':categorias' => $this->phpArrayToPg($data['categorias']),
            ':palabras_clave' => $this->phpArrayToPg($data['palabras_clave']),
            ':revista' => $data['revista'],
            ':editorial' => $data['editorial'],
            ':isbn' => $data['isbn'],
            ':doi' => $data['doi'],
            ':url_publicacion' => $data['url_publicacion'],
            ':pdf_url' => $data['pdf_url'],
            ':fecha_publicacion' => $data['fecha_publicacion'],
            ':ano_publicacion' => $data['ano_publicacion'],
            ':volumen' => $data['volumen'],
            ':numero' => $data['numero'],
            ':paginas' => $data['paginas'],
            ':idioma' => $data['idioma'],
            ':acceso' => $data['acceso'],
            ':destacado' => $data['destacado'],
        ];

        return $this->db->update($sql, $params);
    }

    /**
     * Eliminar investigación
     */
    public function delete($id) {
        $sql = "DELETE FROM investigaciones WHERE id = :id";
        return $this->db->delete($sql, [':id' => $id]);
    }

    /**
     * Obtener categorías
     */
    public function getCategories() {
        $sql = "SELECT DISTINCT unnest(categorias) as categoria
                FROM investigaciones
                ORDER BY categoria";

        $result = $this->db->select($sql);
        return array_column($result, 'categoria');
    }

    /**
     * Búsqueda full-text
     */
    public function search($query, $limit = 20) {
        $sql = "SELECT
                    id, titulo, slug, autores, abstract, tipo, categorias,
                    fecha_publicacion, ano_publicacion,
                    ts_rank(search_vector, plainto_tsquery('spanish', :query)) as rank
                FROM investigaciones
                WHERE search_vector @@ plainto_tsquery('spanish', :query)
                ORDER BY rank DESC, fecha_publicacion DESC
                LIMIT :limit";

        $result = $this->db->select($sql, [':query' => $query, ':limit' => $limit]);

        foreach ($result as &$inv) {
            $inv['autores'] = $this->pgArrayToPhp($inv['autores']);
            $inv['categorias'] = $this->pgArrayToPhp($inv['categorias']);
        }

        return $result;
    }

    // Métodos auxiliares de conversión
    private function phpArrayToPg($array) {
        if (empty($array)) return '{}';
        return '{' . implode(',', array_map(function($item) {
            return '"' . str_replace('"', '\\"', $item) . '"';
        }, $array)) . '}';
    }

    private function pgArrayToPhp($pgArray) {
        if (!$pgArray || $pgArray === '{}') return [];
        $pgArray = trim($pgArray, '{}');
        if (empty($pgArray)) return [];
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
