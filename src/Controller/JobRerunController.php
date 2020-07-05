<?php

namespace App\Controller;

use App\Entity\Job;
use App\Entity\Task;
use App\Security\Permissions;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JobRerunController extends AbstractController
{
    /**
     * @ParamConverter("job", class=Job::class)
     */
    public function __invoke(
        Request $request,
        Job $job,
        EntityManagerInterface $entityManager
    ) {
        if (
            $this->isGranted(Permissions::JOB_RERUN, $job)
            || $job->getToken() === $request->query->get('token')
        ) {
            if (in_array($job->getStatus(), [Job::STATUS_FINISHED, Job::STATUS_ABORTED])) {
                $copyJob = new Job();
                $copyJob->setProject($job->getProject());
                $copyJob->setBranch($job->getBranch());
                $copyJob->setEnvironment($job->getEnvironment());
                $entityManager->persist($copyJob);

                foreach ($job->getTasks() as $task) {
                    $copyTask = new Task();
                    $copyTask->setTool($task->getTool());
                    $copyTask->setOptions($task->getOptions());
                    $copyTask->setCommand($task->getCommand());

                    $copyJob->addTask($copyTask);

                    $entityManager->persist($copyTask);
                }

                return $copyJob;
            }

            throw new BadRequestHttpException('Only aborted or finished jobs can be rerun');
        }

        throw $this->createAccessDeniedException();
    }
}
