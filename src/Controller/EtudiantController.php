<?php

namespace App\Controller;

use App\Entity\AbstractEntity;
use App\Entity\Etudiant;
use App\Entity\Examen;
use App\Entity\Utilisateur;
use App\Repository\Database;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EtudiantController extends UtilisateurController
{
    public function __construct() {
        $this->addPath = '/admin/etudiants';
    }
    #[Route('/admin/etudiants', name: 'etudiants')]
    public function etudiants(Request $request) {
        $etudiant = new Etudiant($request);

        $etudiant->setCreated(new \DateTimeImmutable('now'));
        $etudiant->setPassword($this->randomPassword());
        $form = $this->getForm($etudiant);

        if ($response = $this->submit($request, $form, $etudiant, $this->addPath)) {
            return $response;
        }

        return $this->render('back-end/etudiants.html.twig', [
            'form' => $form,
            'add_path' => $this->addPath,
            'items' => $this->list($etudiant)
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/admin/etudiants/{user_id}', name: 'etudiant')]
    public function detail(Request $request): Response
    {
        $etudiant = new Etudiant($request);
        $etudiant = $this->select($etudiant);
        $form = $this->getForm($etudiant, 'Modifier');

        if ($response = $this->submit($request, $form, $etudiant, $this->addPath)) {
            return $response;
        }

        return $this->render('back-end/etudiants.html.twig', [
            'form' => $form,
            'add_path' => $this->addPath,
            'items' => $this->list($etudiant)
        ]);
    }

    #[Route('/admin/etudiants/delete/{user_id}', name: 'etudiant_delete')]
    public function delete(Request $request): Response
    {
        $etudiant = new Etudiant($request);
        $etudiant->delete();

        return $this->redirect($this->addPath);
    }

    /**
     * @inheritDoc
     */
    public static function list(AbstractEntity $entity): array
    {
        $st = Database::create()->prepare("SELECT * FROM utilisateur WHERE(role = :role)");
        $st->execute([
            'role' => 'etudiant'
        ]);

        $rslts = $st->fetchAll(PDO::FETCH_ASSOC);
        $etudiants = [];

        if (!empty($rslts)) {
            foreach ($rslts as $rslt) {
                $etudiant = new Etudiant(new Request());
                foreach ($rslt as $field => $value) {
                    $etudiant = static::setData($etudiant, $rslt, $field, $value);
                }

                $etudiants[] = $etudiant;
            }
        }

        return $etudiants;
    }
}