<?php
/**
 * Valida que el grafo de plantillas de tareas (y de checklist) no tenga ciclos y que
 * todas las dependencias referencian plantillas existentes. Ejecútalo después de aplicar
 * migrations/06-event-planner-seed.sql, y de nuevo cada vez que se edite el grafo desde
 * el admin de plantillas.
 *
 * Uso (PowerShell, desde la raíz del proyecto):
 *   php scripts/event_planner_validate_templates.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

require_once __DIR__ . '/../event_planner/lib/graph.php';
require_once __DIR__ . '/../assets/db.php';

$stmt = $conn->prepare("SELECT `key` FROM task_templates WHERE is_active = 1");
$stmt->execute();
$keys = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'key');
$stmt->close();

$stmt = $conn->prepare(
    "SELECT t.key AS tkey, d.key AS dkey FROM task_template_dependencies ttd
     JOIN task_templates t ON t.id = ttd.task_template_id
     JOIN task_templates d ON d.id = ttd.depends_on_template_id
     WHERE t.is_active = 1 AND d.is_active = 1"
);
$stmt->execute();
$depRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$deps = [];
foreach ($depRows as $r) {
    $deps[$r['tkey']][] = $r['dkey'];
}

$missing = [];
foreach ($deps as $tkey => $dkeys) {
    foreach ($dkeys as $dkey) {
        if (!in_array($dkey, $keys, true)) {
            $missing[] = "$tkey -> $dkey";
        }
    }
}

if (!empty($missing)) {
    fwrite(STDERR, "Dependencias que referencian plantillas inexistentes o inactivas:\n");
    foreach ($missing as $m) {
        fwrite(STDERR, "  - $m\n");
    }
    exit(1);
}

try {
    $order = event_planner_topo_sort($keys, $deps);
    echo "OK: " . count($keys) . " plantillas activas, sin ciclos.\n";
    echo "Orden topológico: " . implode(' -> ', $order) . "\n";
} catch (EventPlannerCycleException $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . "\n");
    exit(1);
}

$conn->close();
