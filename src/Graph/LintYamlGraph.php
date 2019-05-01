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
        $data['errors']['violations'] = [];

        if (JSON_ERROR_NONE !== \json_last_error()) {
            $data['errors']['violations'][] = [
                'file' => 'output',
                'line' => 0,
                'message' => 'Invalid output. Failed to parse json.',
            ];
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