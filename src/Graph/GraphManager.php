<?php

declare(strict_types=1);

namespace App\Graph;

use App\Entity\Task;

class GraphManager
{
    public function __construct(private iterable $graphs)
    {
    }

    public function getGraph(Task $task): ?GraphInterface
    {
        foreach ($this->graphs as $graph) {
            if ($graph->supports($task)) {
                return $graph;
            }
        }

        return null;
    }
}
