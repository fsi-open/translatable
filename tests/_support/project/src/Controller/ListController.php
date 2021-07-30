<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FSi\Component\Translatable\TranslationProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\FSi\App\Entity\Article;
use Twig\Environment;

final class ListController
{
    private EntityManagerInterface $entityManager;
    private TranslationProvider $translationsProvider;
    private Environment $twig;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslationProvider $translationsProvider,
        Environment $twig
    ) {
        $this->entityManager = $entityManager;
        $this->translationsProvider = $translationsProvider;
        $this->twig = $twig;
    }

    public function __invoke(int $id): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        if (null === $article) {
            throw new NotFoundHttpException("No article for id {$id}");
        }

        return new Response(
            $this->twig->render(
                'list.html.twig',
                [
                    'article' => $article,
                    'translations' => $this->translationsProvider->findAllForEntity($article)
                ]
            )
        );
    }
}
