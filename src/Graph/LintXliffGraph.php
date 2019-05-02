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
        $output = $task->isSuccessful() ? $task->getOutput() : $task->getErrorOutput();
        $data = [];
        $data['errors']['violations'] = [];

        if (is_string($output) && strlen($output) > 0) {
            $files = json_decode($output, true);
            if (JSON_ERROR_NONE !== \json_last_error()) {
                $data['errors']['violations'][] = [
                    'file' => 'output',
                    'line' => 0,
                    'message' => 'Invalid output. Failed to parse json.',
                ];
            } else {
                foreach ($files as $file) {
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