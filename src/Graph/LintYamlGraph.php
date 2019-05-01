<?php

namespace App\Graph;

use App\Entity\Task;

class LintYamlGraph implements GraphInterface
{
    const TOOL = 'lint:yaml';

    public function getData(Task $task): array
    {
        $data = json_decode($task->getOutput(), true);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            $data = [];
        }
        
        $data['errors']['total'] = count($data);
        $data['tool'] = self::TOOL;

        return $data;
    }

    public function supports(Task $task): bool
    {
        return self::TOOL === $task->getTool();
    }
}