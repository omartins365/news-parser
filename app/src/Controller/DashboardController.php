<?php

namespace App\Controller;

use App\Entity\News;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    private $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard(Request $request): Response
    {
        // check if the user has the required roles
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MODERATOR')) {
            throw $this->createAccessDeniedException();
        }

        // get the page number from the request parameters
        $page = $request->query->getInt('page', 1);

        // get the news entities from the database, sorted by publication date
        $news = $this->getDoctrine()->getRepository(News::class)->findBy(
            [],
            ['date' => 'DESC']
        );

        // use the KnpPaginatorBundle to paginate the news entities
        $news = $this->paginator->paginate(
            $news,
            $page,
            10 // limit 10 news per page
        );
// dd($news);
        // render the template with the paginated news entities
        return $this->render('dashboard.html.twig', [
            'news' => $news,
        ]);
    }
}
