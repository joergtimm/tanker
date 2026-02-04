<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'leaflet' => [
        'version' => '1.9.4',
    ],
    'leaflet/dist/leaflet.min.css' => [
        'version' => '1.9.4',
        'type' => 'css',
    ],
    '@symfony/ux-leaflet-map' => [
        'path' => './vendor/symfony/ux-leaflet-map/assets/dist/map_controller.js',
    ],
    'mermaid' => [
        'version' => '11.12.2',
    ],
    'dayjs' => [
        'version' => '1.11.19',
    ],
    'khroma' => [
        'version' => '2.1.0',
    ],
    'dompurify' => [
        'version' => '3.3.0',
    ],
    'd3' => [
        'version' => '7.9.0',
    ],
    '@braintree/sanitize-url' => [
        'version' => '7.1.1',
    ],
    'lodash-es/memoize.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/merge.js' => [
        'version' => '4.17.21',
    ],
    '@iconify/utils' => [
        'version' => '3.1.0',
    ],
    'marked' => [
        'version' => '16.4.2',
    ],
    'ts-dedent' => [
        'version' => '2.2.0',
    ],
    'roughjs' => [
        'version' => '4.6.6',
    ],
    'stylis' => [
        'version' => '4.3.6',
    ],
    'lodash-es/isEmpty.js' => [
        'version' => '4.17.21',
    ],
    'katex' => [
        'version' => '0.16.25',
    ],
    'mermaid/dist/chunks/mermaid.core/dagre-6UL2VRFP.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/cose-bilkent-S5V4N54A.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/c4Diagram-YG6GDRKO.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/flowDiagram-NV44I4VS.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/erDiagram-Q2GNP2WA.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/gitGraphDiagram-NY62KEGX.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/ganttDiagram-JELNMOA3.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/infoDiagram-WHAUD3N6.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/pieDiagram-ADFJNKIX.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/quadrantDiagram-AYHSOK5B.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/xychartDiagram-PRI3JC2R.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/requirementDiagram-UZGBJVZJ.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/sequenceDiagram-WL72ISMW.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/classDiagram-2ON5EDUG.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/classDiagram-v2-WZHVMYZB.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/stateDiagram-FKZM4ZOC.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/stateDiagram-v2-4FDKWEC3.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/journeyDiagram-XKPGCS4Q.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/timeline-definition-IT6M3QCI.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/mindmap-definition-VGOIOE7T.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/kanban-definition-3W4ZIXB7.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/sankeyDiagram-TZEHDZUN.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/diagram-S2PKOQOG.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/diagram-QEK2KX5R.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/blockDiagram-VD42YOAC.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/architectureDiagram-VXUJARFQ.mjs' => [
        'version' => '11.12.2',
    ],
    'mermaid/dist/chunks/mermaid.core/diagram-PSM6KHXK.mjs' => [
        'version' => '11.12.2',
    ],
    'd3-array' => [
        'version' => '3.2.4',
    ],
    'd3-axis' => [
        'version' => '3.0.0',
    ],
    'd3-brush' => [
        'version' => '3.0.0',
    ],
    'd3-chord' => [
        'version' => '3.0.1',
    ],
    'd3-color' => [
        'version' => '3.1.0',
    ],
    'd3-contour' => [
        'version' => '4.0.2',
    ],
    'd3-delaunay' => [
        'version' => '6.0.4',
    ],
    'd3-dispatch' => [
        'version' => '3.0.1',
    ],
    'd3-drag' => [
        'version' => '3.0.0',
    ],
    'd3-dsv' => [
        'version' => '3.0.1',
    ],
    'd3-ease' => [
        'version' => '3.0.1',
    ],
    'd3-fetch' => [
        'version' => '3.0.1',
    ],
    'd3-force' => [
        'version' => '3.0.0',
    ],
    'd3-format' => [
        'version' => '3.1.0',
    ],
    'd3-geo' => [
        'version' => '3.1.1',
    ],
    'd3-hierarchy' => [
        'version' => '3.1.2',
    ],
    'd3-interpolate' => [
        'version' => '3.0.1',
    ],
    'd3-path' => [
        'version' => '3.1.0',
    ],
    'd3-polygon' => [
        'version' => '3.0.1',
    ],
    'd3-quadtree' => [
        'version' => '3.0.1',
    ],
    'd3-random' => [
        'version' => '3.0.1',
    ],
    'd3-scale' => [
        'version' => '4.0.2',
    ],
    'd3-scale-chromatic' => [
        'version' => '3.1.0',
    ],
    'd3-selection' => [
        'version' => '3.0.0',
    ],
    'd3-shape' => [
        'version' => '3.2.0',
    ],
    'd3-time' => [
        'version' => '3.1.0',
    ],
    'd3-time-format' => [
        'version' => '4.1.0',
    ],
    'd3-timer' => [
        'version' => '3.0.1',
    ],
    'd3-transition' => [
        'version' => '3.0.1',
    ],
    'd3-zoom' => [
        'version' => '3.0.0',
    ],
    'dagre-d3-es/src/dagre/index.js' => [
        'version' => '7.0.13',
    ],
    'dagre-d3-es/src/graphlib/json.js' => [
        'version' => '7.0.13',
    ],
    'dagre-d3-es/src/graphlib/index.js' => [
        'version' => '7.0.13',
    ],
    'cytoscape' => [
        'version' => '3.33.1',
    ],
    'cytoscape-cose-bilkent' => [
        'version' => '4.1.0',
    ],
    '@mermaid-js/parser' => [
        'version' => '0.6.3',
    ],
    'dayjs/plugin/isoWeek.js' => [
        'version' => '1.11.19',
    ],
    'dayjs/plugin/customParseFormat.js' => [
        'version' => '1.11.19',
    ],
    'dayjs/plugin/advancedFormat.js' => [
        'version' => '1.11.19',
    ],
    'dayjs/plugin/duration.js' => [
        'version' => '1.11.19',
    ],
    'uuid' => [
        'version' => '11.1.0',
    ],
    'd3-sankey' => [
        'version' => '0.12.3',
    ],
    'lodash-es/clone.js' => [
        'version' => '4.17.21',
    ],
    'cytoscape-fcose' => [
        'version' => '2.2.0',
    ],
    'internmap' => [
        'version' => '2.0.3',
    ],
    'delaunator' => [
        'version' => '5.0.0',
    ],
    'lodash-es' => [
        'version' => '4.17.21',
    ],
    'cose-base' => [
        'version' => '2.2.0',
    ],
    'langium' => [
        'version' => '3.3.1',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/info-NVLQJR56.mjs' => [
        'version' => '0.6.3',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/packet-BFZMPI3H.mjs' => [
        'version' => '0.6.3',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/pie-7BOR55EZ.mjs' => [
        'version' => '0.6.3',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/architecture-U656AL7Q.mjs' => [
        'version' => '0.6.3',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/gitGraph-F6HP7TQM.mjs' => [
        'version' => '0.6.3',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/radar-NHE76QYJ.mjs' => [
        'version' => '0.6.3',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/treemap-KMMF4GRG.mjs' => [
        'version' => '0.6.3',
    ],
    'robust-predicates' => [
        'version' => '3.0.0',
    ],
    'layout-base' => [
        'version' => '2.0.1',
    ],
    '@chevrotain/regexp-to-ast' => [
        'version' => '11.0.3',
    ],
    'chevrotain' => [
        'version' => '11.0.3',
    ],
    'chevrotain-allstar' => [
        'version' => '0.3.1',
    ],
    'vscode-languageserver-types' => [
        'version' => '3.17.5',
    ],
    'vscode-jsonrpc/lib/common/cancellation.js' => [
        'version' => '8.2.1',
    ],
    'vscode-languageserver-textdocument' => [
        'version' => '1.0.12',
    ],
    'vscode-uri' => [
        'version' => '3.0.8',
    ],
    'vscode-jsonrpc/lib/common/events.js' => [
        'version' => '8.2.1',
    ],
    '@chevrotain/utils' => [
        'version' => '11.0.3',
    ],
    '@chevrotain/gast' => [
        'version' => '11.0.3',
    ],
    '@chevrotain/cst-dts-gen' => [
        'version' => '11.0.3',
    ],
    'lodash-es/map.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/filter.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/min.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/flatMap.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/uniqBy.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/flatten.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/forEach.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/reduce.js' => [
        'version' => '4.17.21',
    ],
    'three.js' => [
        'version' => '0.77.1',
    ],
    'three' => [
        'version' => '0.77.0',
    ],
];
