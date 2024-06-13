<?php

namespace App\Controller;

use App\Entity\AbstractEntity;
use App\Entity\Etudiant;
use App\Entity\Search;
use App\Entity\Utilisateur;
use App\Repository\Database;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class DashboardController extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route('/', name: 'home')]
    public function home(Request $request): Response
    {
        $searchEntity = new Search();
        $form = $this->createFormBuilder($searchEntity)->add('search', SearchType::class, [
            'label' => 'Rechercher un étudiant par son numéro d\'identification national (NIN)'
        ])->add('save', SubmitType::class, ['label' => 'Recherche'])
            ->getForm();

        $isEmpty = FALSE;
        if ($request->isMethod('POST')) {
            $allValues = $request->request->all();
            $form->submit($allValues[$form->getName()]);

            if ($form->isSubmitted() && $form->isValid()) {
                $st = Database::create()->prepare("SELECT * FROM utilisateur WHERE(role = :role AND nin = :nin)");
                $st->execute([
                    'role' => 'etudiant',
                    'nin' => $searchEntity->getSearch()
                ]);

                $results = $st->fetchAll(PDO::FETCH_ASSOC);

                $utilisateurs = [];

                if (!empty($results)) {
                    foreach ($results as $rslt) {
                        $utilisateur = new Etudiant(new Request());
                        foreach ($rslt as $field => $value) {
                            $utilisateur = ControllerBase::setData($utilisateur, $rslt, $field, $value);
                        }

                        $utilisateurs[] = $utilisateur;
                    }

                    return $this->render('front-end/search-results.html.twig', ['results' => $utilisateurs]);
                }

                $isEmpty = TRUE;
            }
        }

        return $this->render('front-end/dashboard.html.twig', [
            'form' => $form,
            'is_empty' => $isEmpty
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(Request $request): Response
    {
        $searchEntity = new Search();
        $form = $this->createFormBuilder($searchEntity)->add('search', SearchType::class, [
            'label' => 'Rechercher un étudiant par son numéro d\'identification national (NIN)'
        ])->add('save', SubmitType::class, ['label' => 'Recherche'])
            ->getForm();

        $isEmpty = FALSE;
        if ($request->isMethod('POST')) {
            $allValues = $request->request->all();
            $form->submit($allValues[$form->getName()]);

            if ($form->isSubmitted() && $form->isValid()) {
                $st = Database::create()->prepare("SELECT * FROM utilisateur WHERE(role = :role AND nin = :nin)");
                $st->execute([
                    'role' => 'etudiant',
                    'nin' => $searchEntity->getSearch()
                ]);

                $results = $st->fetchAll(PDO::FETCH_ASSOC);

                $utilisateurs = [];

                if (!empty($results)) {
                    foreach ($results as $rslt) {
                        $utilisateur = new Etudiant(new Request());
                        foreach ($rslt as $field => $value) {
                            $utilisateur = ControllerBase::setData($utilisateur, $rslt, $field, $value);
                        }

                        $utilisateurs[] = $utilisateur;
                    }

                    return $this->render('back-end/search-results.html.twig', ['results' => $utilisateurs]);
                }

                $isEmpty = TRUE;
            }
        }

        return $this->render('back-end/dashboard.html.twig', [
            'form' => $form,
            'is_empty' => $isEmpty
        ]);
    }
    #[Route('/admin', name: 'admin')]
    public function admin(Request $request): Response
    {
       return $this->redirect('/dashboard');
    }
}