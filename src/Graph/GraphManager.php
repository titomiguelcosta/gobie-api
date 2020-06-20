<?php

namespace App\Graph;

use App\Entity\Task;

class GraphManager
{
    private $graphs = [];

    public function __construct(iterable $graphs)
    {
        $this->graphs = $graphs;
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
