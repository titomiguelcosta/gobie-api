<?php

namespace App\Controller;

use App\Entity\Job;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
        $user = $job->getProject()->getCreatedBy();
        if (
            ($this->getUser() instanceof User && $this->getUser()->getId() === $user->getId())
            || $job->getToken() === $request->query->get('token')
        ) {
            if (in_array($job->getStatus(), [Job::STATUS_FINISHED, Job::STATUS_ABORTED])) {
                // @see JobStatusSubscriber and JobStartSubscriber
                $job->setStatus(Job::STATUS_PENDING);
                $entityManager->flush();

                return new Response('', Response::HTTP_CREATED);
            }

            throw new BadRequestHttpException('Only aborted or finished jobs can be rerun');
        }

        throw $this->createAccessDeniedException();
    }
}
