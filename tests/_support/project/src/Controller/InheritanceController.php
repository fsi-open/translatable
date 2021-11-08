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
use Tests\FSi\App\Entity\HomePage;
use Tests\FSi\App\Form\HomePageType;
use Twig\Environment;

final class InheritanceController
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
        $homePage = $this->getHomePage($id);
        $form = $this->formFactory->create(HomePageType::class, $homePage);
        $form->handleRequest($request);
        if (true === $form->isSubmitted() && true === $form->isValid()) {
            if (false === $this->manager->contains($homePage)) {
                $this->manager->persist($homePage);
            }

            $this->manager->flush();
            return new RedirectResponse(
                $this->urlGenerator->generate('edit_inheritance', ['id' => $homePage->getId()])
            );
        }

        return new Response(
            $this->twig->render('inheritance.html.twig', [
                'homepage' => $homePage,
                'form' => $form->createView(),
                'message' => $this->formErrorsToMessage($form->getErrors(true))
            ])
        );
    }

    private function getHomePage(?int $id): HomePage
    {
        if (null !== $id) {
            $homepage = $this->manager->getRepository(HomePage::class)->find($id);
            Assertion::isInstanceOf($homepage, HomePage::class, "No home page for id \"{$id}\"");
        } else {
            $homepage = new HomePage();
        }

        return $homepage;
    }
}
