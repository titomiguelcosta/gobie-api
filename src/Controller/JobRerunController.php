<?php

namespace App\Controller;

use App\Entity\Job;
use App\Entity\Task;
use App\Security\Permissions;
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
        Job $job
    ) {
        if (
            $this->isGranted(Permissions::JOB_RERUN, $job)
            || $job->getToken() === $request->query->get('token')
        ) {
            if (in_array($job->getStatus(), [Job::STATUS_FINISHED, Job::STATUS_ABORTED])) {
                // @see JobStatusSubscriber and JobStartSubscriber
                $job->setStatus(Job::STATUS_PENDING);
                foreach ($job->getTasks() as $task) {
                    $task->setStatus(Task::STATUS_PENDING);
                }

                return $job;
            }

            throw new BadRequestHttpException('Only aborted or finished jobs can be rerun');
        }

        throw $this->createAccessDeniedException();
    }
}
