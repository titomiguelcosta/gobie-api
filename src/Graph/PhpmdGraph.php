<?php

namespace App\Graph;

use App\Entity\Task;
use Symfony\Component\DomCrawler\Crawler;

class PhpmdGraph implements GraphInterface
{
    const TOOL = 'phpmd';

    private $crawler;

    public function __construct(Crawler $crawler = null)
    {
        $this->crawler = $crawler ?? new Crawler();
    }

    public function getData(Task $task): array
    {
        $this->crawler->addXmlContent($task->getOutput());

        $data = [];

        $data['errors']['violations'] = $this->crawler->filter('file')->each(function (Crawler $file, $i) {
            $violations = $file->children()->each(function (Crawler $violation, $i) use ($file) {
                return [
                    'file' => $file->attr('name'),
                    'beginline' => (int)$violation->attr('beginline'),
                    'endline' => (int)$violation->attr('endline'),
                    'rule' => $violation->attr('rule'),
                    'ruleset' => $violation->attr('ruleset'),
                    'priority' => (int)$violation->attr('priority'),
                    'message' => trim($violation->text()),
                ];
            });

            return array_shift($violations);
        });

        $data['errors']['total'] = count($data['errors']['violations']);
        $data['tool'] = self::TOOL;

        return $data;
    }

    public function supports(Task $task): bool
    {
        return self::TOOL === $task->getTool();
    }
}