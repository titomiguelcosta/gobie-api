<?php

namespace App\Graph;

use App\Entity\Task;

interface GraphInterface
{
    public function getData(Task $task): array;

    public function supports(Task $task): bool;
}
