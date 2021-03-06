<?php

namespace App\Controller;

use App\Service\MarkdownHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/markdown")
 */
class Markdown extends AbstractController
{
    /**
     * Converts a markdown string to HTML.
     *
     * @Route("/preview", name="markdown_preview")
     * @IsGranted("ROLE_EDITOR")
     */
    public function preview(Request $request, MarkdownHelper $markdownHelper)
    {
        $text = $request->request->get('text');

        $data = $text ? $markdownHelper->parse($text) : ':(';

        return $this->json(
            [
                'data' => $data,
            ]
        );
    }
}
