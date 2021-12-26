<?php

declare(strict_types=1);

namespace App\Graph;

use App\Entity\Task;

class PhpstanGraph implements GraphInterface
{
    const TOOL = 'phpstan';

    public function getData(Task $task): array
    {
        // even if we get errors, they will be stored in the output
        $output = $task->getOutput();
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
                $data['output'] = $files;
                if (array_key_exists('files', $files)) {
                    foreach ($files['files'] as $file => $messages) {
                        foreach ($messages['messages'] as $message) {
                            $data['errors']['violations'][] = [
                                'file' => $file,
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
