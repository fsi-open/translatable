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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\FSi\App\Entity\Article;

final class RemoveController
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(int $id): Response
    {
        $article = $this->manager->getRepository(Article::class)->find($id);
        if (null === $article) {
            throw new NotFoundHttpException("No article for id {$id}");
        }

        $this->manager->remove($article);
        $this->manager->flush();

        return new Response('OK');
    }
}
