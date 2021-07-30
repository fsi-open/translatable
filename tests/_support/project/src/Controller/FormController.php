<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App\Controller;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FSi\App\Entity\Article;
use Tests\FSi\App\Form\ArticleType;
use Twig\Environment;

final class FormController
{
    private Environment $twig;
    private FormFactoryInterface $formFactory;
    private EntityManagerInterface $manager;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        Environment $twig,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(Request $request, ?int $id): Response
    {
        $article = $this->getArticle($id);
        $form = $this->formFactory->create(ArticleType::class, $article);
        $form->handleRequest($request);
        if (true === $form->isSubmitted() && true === $form->isValid()) {
            if (false === $this->manager->contains($article)) {
                $this->manager->persist($article);
            }

            $this->manager->flush();
            return new RedirectResponse(
                $this->urlGenerator->generate('edit_article', ['id' => $article->getId()])
            );
        }

        return new Response(
            $this->twig->render('index.html.twig', [
                'article' => $article,
                'form' => $form->createView(),
                'message' => $this->formErrorsToMessage($form->getErrors(true))
            ])
        );
    }

    private function getArticle(?int $id): Article
    {
        if (null !== $id) {
            $article = $this->manager->getRepository(Article::class)->find($id);
            Assertion::isInstanceOf($article, Article::class, "No article for id \"{$id}\"");
        } else {
            $article = new Article(null, null);
        }

        return $article;
    }

    private function formErrorsToMessage(FormErrorIterator $errors): ?string
    {
        $message = '';

        /** @var FormError $error */
        foreach ($errors as $error) {
            /** @var FormInterface<FormInterface> $origin */
            $origin = $error->getOrigin();
            $message .= "[{$origin->getName()}]: {$error->getMessage()}\r\n";
        }

        return '' !== $message ? $message : null;
    }
}
