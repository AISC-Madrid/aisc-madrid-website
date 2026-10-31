<?php
// Utilidades de grafo compartidas por: validación del seed, el asistente (herencia de
// dependencias al desmarcar tareas) y el admin de plantillas (Fase 3).
//
// $deps es un array [nodo => [dependencia, ...]].

class EventPlannerCycleException extends RuntimeException
{
}

// Lanza EventPlannerCycleException si el grafo tiene un ciclo. Devuelve un orden topológico
// (dependencias antes que dependientes) si es válido.
function event_planner_topo_sort(array $nodes, array $deps): array
{
    $state = []; // 0 = sin visitar, 1 = en curso, 2 = cerrado
    $order = [];

    $visit = function (string $node, array &$state, array &$order, array $deps, callable $visit) {
        $state[$node] = 1;
        foreach ($deps[$node] ?? [] as $dep) {
            if (($state[$dep] ?? 0) === 1) {
                throw new EventPlannerCycleException("Ciclo de dependencias detectado en: $node -> $dep");
            }
            if (($state[$dep] ?? 0) === 0) {
                $visit($dep, $state, $order, $deps, $visit);
            }
        }
        $state[$node] = 2;
        $order[] = $node;
    };

    foreach ($nodes as $node) {
        if (($state[$node] ?? 0) === 0) {
            $visit($node, $state, $order, $deps, $visit);
        }
    }

    return $order;
}

// Dado el grafo completo de plantillas y el conjunto de keys incluidas en el asistente,
// devuelve, para cada key incluida, el conjunto de dependencias "efectivas": las directas
// que están incluidas, y las de cualquier antecesor excluido (herencia transitiva), para
// no romper la cadena cuando se desmarca una tarea.
function event_planner_resolve_effective_dependencies(array $deps, array $includedKeys): array
{
    $includedSet = array_fill_keys($includedKeys, true);
    $cache = [];

    $resolve = function (string $key, array &$cache, array $deps, array $includedSet, callable $resolve, array $visiting = []): array {
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        if (isset($visiting[$key])) {
            // Ciclo ya validado en otro sitio; evita recursión infinita aquí.
            return [];
        }
        $visiting[$key] = true;

        $effective = [];
        foreach ($deps[$key] ?? [] as $dep) {
            if (!empty($includedSet[$dep])) {
                $effective[$dep] = true;
            } else {
                foreach ($resolve($dep, $cache, $deps, $includedSet, $resolve, $visiting) as $inherited) {
                    $effective[$inherited] = true;
                }
            }
        }

        $result = array_keys($effective);
        $cache[$key] = $result;
        return $result;
    };

    $out = [];
    foreach ($includedKeys as $key) {
        $out[$key] = $resolve($key, $cache, $deps, $includedSet, $resolve);
    }
    return $out;
}
