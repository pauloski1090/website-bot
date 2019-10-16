<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Repository\AgentStatRepository;
use App\Repository\UserRepository;
use App\Service\MedalChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/stats")
 */
class StatsController extends AbstractController
{
    /**
     * @Route("/", name="stats")
     * @IsGranted("ROLE_EDITOR")
     */
    public function index()
    {
        return $this->render(
            'stats/index.html.twig',
            [
                'controller_name' => 'StatsController',
            ]
        );
    }

    /**
     * @Route("/my-stats", name="my_stats")
     * @IsGranted("ROLE_AGENT")
     */
    public function myStats(
        Security $security,
        AgentStatRepository $statRepository,
        MedalChecker $medalChecker
    ): Response {
        $user = $security->getUser();

        $agent = $user->getAgent();

        if (!$agent) {
            throw $this->createAccessDeniedException(
                'No tiene un agente asignado a su usuario - contacte un admin!'
            );
        }

        $entries = $statRepository->getAgentStats($agent);
        $medalGroups = [];
        $latest = null;

        if ($entries) {
            $latest = $entries[0];
            $medalGroups = $this->getMedalGroups($medalChecker->checkLevels($latest));
        }

        return $this->render(
            'stats/mystats.html.twig',
            [
                'agent'       => $agent,
                'medalGroups' => $medalGroups,
                'stats'       => $entries,
                'entries'     => $entries,
                'latest'      => $latest,
            ]
        );
    }

    /**
     * @Route("/agent/{id}", name="agent_stats")
     * @IsGranted("ROLE_AGENT")
     */
    public function AgentStats(Agent $agent, AgentStatRepository $statRepository, MedalChecker $medalChecker)
    {
        $entries = $statRepository->getAgentStats($agent);

        $latest = null;
        $medalGroups = [];

        if ($entries) {
            $latest = $entries[0];
            $medalGroups = $this->getMedalGroups($medalChecker->checkLevels($entries[0]));
        }

        return $this->render(
            'stats/mystats.html.twig',
            [
                'agent'       => $agent,
                'medalGroups' => $medalGroups,
                'stats'       => $entries,
                'entries'     => $entries,
                'latest'      => $latest,
            ]
        );
    }

    /**
     * @Route("/agent/data/{id}", name="agent_stats_data")
     * @IsGranted("ROLE_AGENT")
     */
    public function agentStatsJson(Agent $agent, AgentStatRepository $statRepository)
    {
        $data = new \stdClass();

        $data->ap = [];
        $data->hacker = [];

        $entries = $statRepository->getAgentStats($agent, 'ASC');

        $latest = null;

        if ($entries) {
            foreach ($entries as $entry) {
                $date = $entry->getDatetime()->format('Y-m-d');
                $data->ap[] = [$date, $entry->getAp()];
                $data->hacker[] = [$date, $entry->getHacker()];
            }
        }

        return new JsonResponse($data);//, $status, $headers);

        $data = json_encode($data);

        return $this->json($data);
    }

    /**
     * @Route("/leaderboard", name="stats_leaderboard")
     * @IsGranted("ROLE_AGENT")
     */
    public function leaderBoard(
        AgentStatRepository $statRepository,
        UserRepository $userRepository
    ): Response {
        $users = $userRepository->findAll();

        $boardEntries = [];

        foreach ($users as $user) {
            $agent = $user->getAgent();

            if (!$agent) {
                continue;
            }

            $agentEntry = $statRepository->getAgentLatest($agent);

            if ($agentEntry) {
                foreach ($agentEntry->getProperties() as $property) {
                    $methodName = 'get'.$property;
                    if ($agentEntry->$methodName()) {
                        $boardEntries[$property][$agent->getNickname()] = $agentEntry->$methodName();
                    }
                }
            }
        }

        foreach ($boardEntries as $type => $entries) {
            arsort($boardEntries[$type]);
        }

        return $this->render(
            'stats/leaderboard.html.twig',
            [
                'board' => $boardEntries,
            ]
        );
    }

    /**
     * @Route("/by-date", name="stats_by_date")
     * @IsGranted("ROLE_AGENT")
     */
    public function byDate(
        Request $request,
        AgentStatRepository $statRepository,
        MedalChecker $medalChecker
    ): Response {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $stats = [];
        $medalsGained = [];

        if ($startDate && $endDate) {
            $entries = $statRepository->findByDate($startDate, $endDate);
            $previous = [];

            foreach ($entries as $entry) {
                $agentName = $entry->getAgent()->getNickname();

                if (false === isset($previous[$agentName])) {
                    $previousEntry = $statRepository->getPrevious($entry);

                    $previous[$agentName] = $previousEntry
                        ? $medalChecker->checkLevels(
                            $previousEntry
                        ) : $medalChecker->checkLevels($entry);
                }

                $levels = $medalChecker->checkLevels($entry);
                $dateString = $entry->getDatetime()->format('Y-m-d');

                foreach ($levels as $name => $level) {
                    if (!$level) {
                        continue;
                    }
                    if (false === isset($previous[$agentName][$name])) {
                        $medalsGained[$dateString][$agentName][$name] = $level;
                        $previous[$name] = $level;
                    } elseif ($previous[$agentName][$name] < $level) {
                        $medalsGained[$dateString][$agentName][$name] = $level;
                        $previous[$agentName][$name] = $level;
                    }
                }
            }
        }

        return $this->render(
            'stats/by_date.html.twig',
            [
                'startDate'    => new \DateTime($startDate),
                'endDate'      => new \DateTime($endDate),
                'stats'        => $stats,
                'medalsGained' => $medalsGained,
            ]
        );
    }

    private function getMedalGroups($medals, int $medalsPerRow = 6): array
    {
        $medalGroups = [];
        $rowCount = 1;
        $groupCount = 0;

        foreach ($medals as $medalName => $level) {
            $medalGroups[$groupCount][$medalName] = $level;
            $rowCount++;

            if ($rowCount > $medalsPerRow) {
                $groupCount++;
                $rowCount = 1;
            }
        }

        return $medalGroups;
    }
}

