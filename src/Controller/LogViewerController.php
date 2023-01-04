<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusSchedulerCommandPlugin\DataRetriever\LogDataRetriever;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

final class LogViewerController extends AbstractController
{
    public function __construct(
        private ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        private LogDataRetriever $logDataRetriever,
        private TranslatorInterface $translator,
        private string $logsDir,
        /** @var int The time in milliseconds between two AJAX requests to the server. */
        private int $updateTime = 2000,
    ) {
    }

    public function getLogs(Request $request, string $command): JsonResponse
    {
        $scheduleCommand = $this->scheduledCommandRepository->find($command);

        if (null === $scheduleCommand ||
            null === $scheduleCommand->getLogFile()
        ) {
            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }

        if (true === (bool) $request->get('refresh')) {
            $result = $this->logDataRetriever->getLog(
                $this->logsDir . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile(),
                (int) $request->get('lastsize'),
                (string) $request->get('grep-keywords'),
                (bool) $request->get('invert'),
            );

            return new JsonResponse([
                'size' => $result['size'],
                'data' => $result['data'],
            ]);
        }

        $result = $this->logDataRetriever->getLog($this->logsDir . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile());

        return new JsonResponse([
            'size' => $result['size'],
            'data' => $result['data'],
        ]);
    }

    public function show(string $command): Response
    {
        $scheduledCommand = $this->scheduledCommandRepository->find($command);

        if (null === $scheduledCommand ||
            null === $scheduledCommand->getLogFile()
        ) {
            $this->addFlash('error', $this->translator->trans('sylius.ui.does_not_exists_or_missing_log_file'));

            return $this->redirectToRoute('synolia_admin_command_index');
        }

        return $this->render('@SynoliaSyliusSchedulerCommandPlugin/Controller/show.html.twig', [
            'route' => $this->generateUrl('sylius_admin_scheduler_get_log_file', ['command' => $command]),
            'updateTime' => $this->updateTime,
            'scheduledCommand' => $scheduledCommand,
        ]);
    }
}
