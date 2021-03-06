<?php

namespace App\Type\CustomMessage;

use App\Entity\Agent;
use App\Service\MedalChecker;
use App\Service\TelegramBotHelper;
use App\Type\AbstractCustomMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class LevelUpMessage extends AbstractCustomMessage
{
    private TranslatorInterface $translator;

    private int $level;

    private Agent $agent;

    private MedalChecker $medalChecker;

    private string $pageBaseUrl;

    private int $recursions;

    public function __construct(
        TelegramBotHelper $telegramBotHelper,
        TranslatorInterface $translator,
        Agent $agent,
        MedalChecker $medalChecker,
        int $level,
        int $recursions,
        string $pageBaseUrl
    ) {
        $this->agent = $agent;
        $this->level = $level;
        $this->recursions = $recursions;
        $this->translator = $translator;
        $this->medalChecker = $medalChecker;
        $this->pageBaseUrl = $pageBaseUrl;

        parent::__construct($telegramBotHelper);
    }

    public function getMessage(): array
    {
        $tada = $this->telegramBotHelper->getEmoji('tadaa');

        $response = [];

        $response[] = $this->translator->trans('new.medal.header');

        $response[] = '[ ]('.$this->pageBaseUrl.'/build/images/badges/'
            .$this->medalChecker->getBadgePath('LevelUp_'.$this->level, 0).')';

        $response[] = $this->translator->trans(
            'new.level.text.1',
            [
                'agent' => $this->getAgentTelegramName($this->agent),
            ]
        );

        $response[] = '';

        if ($this->recursions) {
            $response[] = str_repeat('16+', $this->recursions);
        }

        $response[] = $this->translator->trans(
            'new.level.text.2',
            ['level' => $this->level]
        );

        $response[] = '';

        $response[] = $this->translator->trans(
            'new.medal.text.3',
            [
                'link' => sprintf(
                    '%s/stats/agent/%s',
                    $this->pageBaseUrl,
                    $this->agent->getId()
                ),
            ]
        );
        $response[] = '';
        $response[] = $this->translator->trans(
            'new.medal.text.4',
            [
                'tadaa' => $tada.$tada.$tada,
            ]
        );

        return $response;
    }
}
