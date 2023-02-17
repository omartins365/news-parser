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
    

    public function __construct()
    {
        
    }
    public function checkAction()
    {
        // this accounts for when the user tries to go directly to a protected route 
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MODERATOR')) {
            return $this->redirectToRoute('app_login');
        }
        
        // more code here
    } 

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard(Request $request, PaginatorInterface $paginator): Response
    {
        // check if the user has the required roles
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MODERATOR')) {
            return $this->redirectToRoute('app_login');
        }

        // get the page number from the request parameters
        $page = $request->query->getInt('page', 1);

        // get the news entities from the database, sorted by publication date
        $news = $this->getDoctrine()->getRepository(News::class)->findBy(
            [],
            ['date' => 'DESC']
        );

        // use the KnpPaginatorBundle to paginate the news entities
        $news = $paginator->paginate(
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
