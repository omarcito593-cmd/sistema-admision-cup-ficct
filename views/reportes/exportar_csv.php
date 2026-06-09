<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2, 3]);

require_once __DIR__ . "/../../config/database.php";


$tipo = $_GET['tipo'] ?? '';

$reportes = [
    "aprobados" => [
        "archivo" => "reporte_aprobados.csv",
        "columnas" => ["CI", "Postulante", "Materia", "Examen 1", "Examen 2", "Examen 3", "Promedio", "Resultado"],
        "sql" => "
            SELECT 
                p.ci,
                TRIM(p.nombre || ' ' || COALESCE(NULLIF(p.apellido_paterno, ''), p.apellido, '') || ' ' || COALESCE(p.apellido_materno, '')) AS postulante,
                m.nombre_materia,
                n.examen1,
                n.examen2,
                n.examen3,
                n.promedio_final,
                n.resultado
            FROM notas n
            INNER JOIN postulantes p ON n.id_postulante = p.id_postulante
            INNER JOIN materias m ON n.id_materia = m.id_materia
            WHERE n.resultado = 'APROBADO'
            ORDER BY p.nombre ASC
        "
    ],

    "reprobados" => [
        "archivo" => "reporte_reprobados.csv",
        "columnas" => ["CI", "Postulante", "Materia", "Examen 1", "Examen 2", "Examen 3", "Promedio", "Resultado"],
        "sql" => "
            SELECT 
                p.ci,
                TRIM(p.nombre || ' ' || COALESCE(NULLIF(p.apellido_paterno, ''), p.apellido, '') || ' ' || COALESCE(p.apellido_materno, '')) AS postulante,
                m.nombre_materia,
                n.examen1,
                n.examen2,
                n.examen3,
                n.promedio_final,
                n.resultado
            FROM notas n
            INNER JOIN postulantes p ON n.id_postulante = p.id_postulante
            INNER JOIN materias m ON n.id_materia = m.id_materia
            WHERE n.resultado = 'REPROBADO'
            ORDER BY p.nombre ASC
        "
    ],

    "grupo_mas_aprobados" => [
        "archivo" => "grupo_mas_aprobados.csv",
        "columnas" => ["Grupo", "Turno", "Aula", "Cantidad de aprobados"],
        "sql" => "
            WITH conteo AS (
                SELECT 
                    g.nombre_grupo,
                    g.turno,
                    a.nombre_aula,
                    COUNT(DISTINCT CASE WHEN n.resultado = 'APROBADO' THEN p.id_postulante END) AS cantidad_aprobados
                FROM grupos g
                INNER JOIN aulas a ON g.id_aula = a.id_aula
                LEFT JOIN postulante_grupo pg ON g.id_grupo = pg.id_grupo
                LEFT JOIN postulantes p ON pg.id_postulante = p.id_postulante
                LEFT JOIN notas n ON p.id_postulante = n.id_postulante
                GROUP BY g.id_grupo, g.nombre_grupo, g.turno, a.nombre_aula
            )
            SELECT *
            FROM conteo
            WHERE cantidad_aprobados = (SELECT MAX(cantidad_aprobados) FROM conteo)
            ORDER BY nombre_grupo ASC
        "
    ],

    "grupo_mas_reprobados" => [
        "archivo" => "grupo_mas_reprobados.csv",
        "columnas" => ["Grupo", "Turno", "Aula", "Cantidad de reprobados"],
        "sql" => "
            WITH conteo AS (
                SELECT 
                    g.nombre_grupo,
                    g.turno,
                    a.nombre_aula,
                    COUNT(DISTINCT CASE WHEN n.resultado = 'REPROBADO' THEN p.id_postulante END) AS cantidad_reprobados
                FROM grupos g
                INNER JOIN aulas a ON g.id_aula = a.id_aula
                LEFT JOIN postulante_grupo pg ON g.id_grupo = pg.id_grupo
                LEFT JOIN postulantes p ON pg.id_postulante = p.id_postulante
                LEFT JOIN notas n ON p.id_postulante = n.id_postulante
                GROUP BY g.id_grupo, g.nombre_grupo, g.turno, a.nombre_aula
            )
            SELECT *
            FROM conteo
            WHERE cantidad_reprobados = (SELECT MAX(cantidad_reprobados) FROM conteo)
            ORDER BY nombre_grupo ASC
        "
    ],

    "general_postulantes" => [
        "archivo" => "reporte_general_postulantes.csv",
        "columnas" => ["CI", "Postulante", "Teléfono", "Correo", "Carrera principal", "Segunda opción", "Grupo", "Cantidad notas", "Promedio general", "Resultado general"],
        "sql" => "
            SELECT 
                p.ci,
                TRIM(p.nombre || ' ' || COALESCE(NULLIF(p.apellido_paterno, ''), p.apellido, '') || ' ' || COALESCE(p.apellido_materno, '')) AS postulante,
                p.telefono,
                p.correo,
                c1.nombre_carrera AS carrera_principal,
                COALESCE(c2.nombre_carrera, 'Sin segunda opción') AS carrera_segunda_opcion,
                COALESCE(g.nombre_grupo || ' - ' || g.turno, 'Sin grupo') AS grupo,
                COUNT(n.id_nota) AS cantidad_notas,
                ROUND(AVG(n.promedio_final), 2) AS promedio_general,
                CASE 
                    WHEN COUNT(n.id_nota) = 0 THEN 'SIN NOTAS'
                    WHEN AVG(n.promedio_final) >= 60 THEN 'APROBADO'
                    ELSE 'REPROBADO'
                END AS resultado_general
            FROM postulantes p
            INNER JOIN carreras c1 ON p.id_carrera = c1.id_carrera
            LEFT JOIN carreras c2 ON p.id_carrera_segunda_opcion = c2.id_carrera
            LEFT JOIN postulante_grupo pg ON p.id_postulante = pg.id_postulante
            LEFT JOIN grupos g ON pg.id_grupo = g.id_grupo
            LEFT JOIN notas n ON p.id_postulante = n.id_postulante
            GROUP BY 
                p.id_postulante, p.ci, p.nombre, p.apellido, p.apellido_paterno, p.apellido_materno,
                p.telefono, p.correo, c1.nombre_carrera, c2.nombre_carrera, g.nombre_grupo, g.turno
            ORDER BY p.id_postulante ASC
        "
    ],

    "estadisticas_materia" => [
        "archivo" => "estadisticas_por_materia.csv",
        "columnas" => ["Materia", "Total notas", "Aprobados", "Reprobados", "Promedio materia"],
        "sql" => "
            SELECT 
                m.nombre_materia,
                COUNT(n.id_nota) AS total_notas,
                COUNT(CASE WHEN n.resultado = 'APROBADO' THEN 1 END) AS aprobados,
                COUNT(CASE WHEN n.resultado = 'REPROBADO' THEN 1 END) AS reprobados,
                ROUND(AVG(n.promedio_final), 2) AS promedio_materia
            FROM materias m
            LEFT JOIN notas n ON m.id_materia = n.id_materia
            GROUP BY m.id_materia, m.nombre_materia
            ORDER BY m.id_materia ASC
        "
    ],

    "docentes_por_grupo" => [
        "archivo" => "docentes_por_grupo.csv",
        "columnas" => ["Grupo", "Turno", "Aula", "Materia", "Docente", "Profesión", "Horario"],
        "sql" => "
            SELECT
                g.nombre_grupo,
                g.turno,
                aul.nombre_aula,
                m.nombre_materia,
                d.nombre || ' ' || d.apellido AS docente,
                d.profesion,
                a.horario
            FROM asignaciones a
            INNER JOIN grupos g ON a.id_grupo = g.id_grupo
            INNER JOIN aulas aul ON g.id_aula = aul.id_aula
            INNER JOIN materias m ON a.id_materia = m.id_materia
            INNER JOIN docentes d ON a.id_docente = d.id_docente
            ORDER BY g.id_grupo ASC, m.nombre_materia ASC
        "
    ]
];

if (!isset($reportes[$tipo])) {
    header("Location: index.php?error=1");
    exit();
}

try {
    $config = $reportes[$tipo];

    $stmt = $conexion->query($config['sql']);
    $datos = $stmt->fetchAll(PDO::FETCH_NUM);

    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=" . $config['archivo']);

    $salida = fopen("php://output", "w");

    /* BOM para que Excel abra bien acentos y ñ */
    fprintf($salida, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($salida, $config['columnas'], ";");

    foreach ($datos as $fila) {
        fputcsv($salida, $fila, ";");
    }

    fclose($salida);
    exit();

} catch (PDOException $e) {
    header("Location: index.php?error=1");
    exit();
}
?>
