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
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FSi\App\Controller\Traits\FormatFormErrors;
use Tests\FSi\App\Entity\Post;
use Tests\FSi\App\Form\PostType;
use Twig\Environment;

final class PostController
{
    use FormatFormErrors;

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
        $post = $this->getPost($id);
        $form = $this->formFactory->create(PostType::class, $post);
        $form->handleRequest($request);
        if (true === $form->isSubmitted() && true === $form->isValid()) {
            if (false === $this->manager->contains($post)) {
                $this->manager->persist($post);
            }

            $this->manager->flush();
            return new RedirectResponse(
                $this->urlGenerator->generate('edit_post', ['id' => $post->getId()])
            );
        }

        return new Response(
            $this->twig->render('post.html.twig', [
                'form' => $form->createView(),
                'message' => $this->formErrorsToMessage($form->getErrors(true))
            ])
        );
    }

    private function getPost(?int $id): Post
    {
        if (null !== $id) {
            $post = $this->manager->getRepository(Post::class)->find($id);
            Assertion::isInstanceOf($post, Post::class, "No post for id \"{$id}\"");
        } else {
            $post = new Post();
        }

        return $post;
    }
}
