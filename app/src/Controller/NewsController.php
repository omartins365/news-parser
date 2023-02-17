<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NewsController extends AbstractController
{
    private $csrfTokenManager;

    public function __construct(CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * @Route("/news", name="app_news")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('/dashboard');
    }

    /**
     * @Route("/news/{id}", name="delete_news_item", methods={"DELETE","POST"})
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, NewsRepository $newsRepository, int $id): Response
    {
        $newsItem = $newsRepository->find($id);

        if (!$newsItem) {
            // throw $this->createNotFoundException('The news item does not exist');
            $this->addFlash('warning', 'The news item does not exist');

            return $this->redirectToRoute('dashboard');
        }

        // Check if the user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            // Remove the news item
            $entityManager->remove($newsItem);
            $entityManager->flush();
        }
        // Set a success message
        $this->addFlash('success', 'The news item has been deleted');

        return $this->redirectToRoute('dashboard');
    }
}
