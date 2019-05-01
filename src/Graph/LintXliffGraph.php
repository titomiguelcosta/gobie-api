<?php

namespace App\Graph;

use App\Entity\Task;

class LintXliffGraph implements GraphInterface
{
    const TOOL = 'lint:xliff';

    /**
     * [
     *      {
     *          "file": "/tmp/example.xliff",
     *          "valid": false,
     *          "messages": [
     *              {
     *                  "line": 1,
     *                  "column": 0,
     *                  "message": "Element 'a': No matching global declaration available for the validation root."
     *              }
     *          ]
     *      }
     *  ]
     */
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
                    foreach ($file['messages'] as $message) {
                        $data['errors']['violations'][] = [
                            'file' => $file['file'],
                            'line' => $message['line'],
                            'message' => $message['message'],
                        ];
                    }
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