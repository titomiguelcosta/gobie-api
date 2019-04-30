<?php

namespace App\Graph;

use App\Entity\Task;

class SecurityCheckGraph implements GraphInterface
{
    const TOOL = 'security:check';

    public function getData(Task $task): array
    {
        $data = json_decode($task->getOutput(), true);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            $data = [];
        }
        
        $data['tool'] = self::TOOL;

        return $data;
    }

    public function supports(Task $task): bool
    {
        return self::TOOL === $task->getTool();
    }
}