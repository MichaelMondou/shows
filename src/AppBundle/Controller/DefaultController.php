<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/shows", name="shows")
     * @Template()
     */
    public function showsAction(Request $request, $page = 1)
    {
        $nbPerPage = 6;

        $get_page = $request->query->get('page');

        $page = !empty($get_page) ? $get_page : $page;

        $shows = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:TVShow')
            ->getTvShows($page, $nbPerPage)
        ;

        $nbPages = ceil(count($shows)/$nbPerPage);

        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        return [
            'shows' => $shows,
            'nbPages'     => $nbPages,
            'page'        => $page
        ];
    }

    /**
     * @Route("/show/{id}", name="show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:TVShow');

        return [
            'show' => $repo->find($id)
        ];
    }

    /**
     * @Route("/calendar", name="calendar")
     * @Template()
     */
    public function calendarAction()
    {
        return [];
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction()
    {
        return [];
    }

    /**
     * @Route("/search", name="search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $term = $request->request->get('term');

        if (!empty($term)) {
            $em = $this->get('doctrine')->getManager();
            $query = $em->createQuery('SELECT tv_show FROM AppBundle:TVShow tv_show
             WHERE tv_show.name LIKE :tv_show_name OR tv_show.synopsis LIKE :tv_show_synopsis
             ORDER BY tv_show.id ASC'
            )->setParameter('tv_show_name', '%' . $term . '%')
                ->setParameter('tv_show_synopsis', '%' . $term . '%');

            $results = $query->getResult();

            if (!empty($results)) {
                return [
                    'shows' => $results,
                    'term' => $term
                ];
            } else {
                return [
                    'shows' => false,
                    'term' => $term
                ];
            }
        }
        return [
            'empty' => true
        ];
    }
}
