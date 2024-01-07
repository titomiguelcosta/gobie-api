<?php

declare(strict_types=1);

namespace App\Graph;

use App\Entity\Task;

class LintTwigGraph implements GraphInterface
{
    public const TOOL = 'lint:twig';

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
                        $data['errors']['violations'][] = [
                            'file' => $file['file'],
                            'line' => $file['line'],
                            'message' => $file['message'],
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
