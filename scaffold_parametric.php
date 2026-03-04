<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$models = ['Department', 'City', 'TrainingCenter', 'EntityPosition', 'LinkageType', 'TrainingRecord', 'TrainingProgramType', 'TrainingProgram', 'ResearchLine', 'TechnologicalLine', 'ThematicArea', 'ProjectModality', 'InvestigationType', 'MincienciasTypology', 'KnowledgeGrandArea', 'KnowledgeArea'];

$data = [];
foreach($models as $m) {
    if(class_exists("App\\Models\\$m")) {
        $model = "App\\Models\\$m";
        $instance = new $model;
        $data[$m] = $instance->getFillable();
    } else {
        $data[$m] = "NOT_FOUND";
    }
}
echo json_encode($data, JSON_PRETTY_PRINT);
