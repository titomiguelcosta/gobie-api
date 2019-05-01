<?php

namespace App\Graph;

use App\Entity\Task;

class LintYamlGraph implements GraphInterface
{
    const TOOL = 'lint:yaml';

    public function getData(Task $task): array
    {
        $output = json_decode($task->getOutput(), true);
        $data = [];

        if (JSON_ERROR_NONE !== \json_last_error()) {
            $data = [];
        } else {
            foreach ($output as $file) {
                if (false === $file['valid']) {
                    $data['errors']['violations'][] = [
                        'file' => $file['file'],
                        'line' => $file['line'],
                        'message' => $file['message'],
                    ];
                }
            }
        }
        
        $data['errors']['total'] = count($data['errors']['violations']);
        $data['tool'] = self::TOOL;

        return $data;
    }

    public function supports(Task $task): bool
    {
        return self::TOOL === $task->getTool();
    }
}