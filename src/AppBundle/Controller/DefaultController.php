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
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:Episode');

        $results = $repo->getEpisodesForCalendar();

        if (!empty($results)) {
            $items = array();

            foreach ($results as $result) {
                $tv_show_id = $result->getSeason()->getShow()->getId();
                $tv_show_name = $result->getSeason()->getShow()->getName();
                $tv_show_image = $result->getSeason()->getShow()->getImage();
                $season_id = $result->getSeason()->getId();
                $season_number = $result->getSeason()->getNumber();
                $episode_id = $result->getId();
                $episode_name = $result->getName();
                $episode_date = $result->getDate();

                if (!empty($items[$tv_show_id])) {
                    if (!empty($items[$tv_show_id]['seasons'][$season_id])) {
                        $items[$tv_show_id]['seasons'][$season_id]['episodes'][$episode_id] = array(
                            'name' => $episode_name,
                            'date' => $episode_date
                        );
                    } else {
                        $items[$tv_show_id]['seasons'][$season_id] = array(
                            'number' => $season_number,
                            'episodes' => array(
                                $episode_id => array(
                                    'name' => $episode_name,
                                    'date' => $episode_date
                                )
                            )
                        );
                    }
                } else {
                    $items[$tv_show_id] = array(
                        'name' => $tv_show_name,
                        'image' => $tv_show_image,
                        'seasons' => array(
                            $season_id => array(
                                'number' => $season_number,
                                'episodes' => array(
                                    $episode_id => array(
                                        'name' => $episode_name,
                                        'date' => $episode_date
                                    )
                                )
                            )
                        )
                    );
                }
            }

            return [
                'items' => $items
            ];
        }
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
            $repo = $em->getRepository('AppBundle:TVShow');

            $results = $repo->getTvShowsBySearch($term);

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
