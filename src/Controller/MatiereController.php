<?php

namespace App\Controller;

use App\Entity\AbstractEntity;
use App\Entity\Matiere;
use App\Entity\Utilisateur;
use App\Repository\Database;
use PDO;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MatiereController extends ControllerBase
{
    public function __construct() {
        $this->addPath = '/admin/matieres';
    }
    #[Route('/admin/matieres', name: 'matieres')]
    public function matiere(Request $request) {
        $matiere = new Matiere($request);

        $matiere->setCreated(new \DateTimeImmutable('now'));
        $form = $this->getForm($matiere, 'Créer une matière');

        if ($response = $this->submit($request, $form, $matiere, $this->addPath)) {
            return $response;
        }

        return $this->render('back-end/matieres.html.twig', [
            'form' => $form,
            'add_path' => $this->addPath,
            'items' => $this->list($matiere)
        ]);
    }

    #[Route('/admin/matieres/{id}', name: 'matiere')]
    public function detail(Request $request): Response
    {
        $matiere = new Matiere($request);
        $matiere = $this->select($matiere);

        $form = $this->getForm($matiere, 'Modifier');

        if ($response = $this->submit($request, $form, $matiere, $this->addPath)) {
            return $response;
        }

        return $this->render('back-end/matieres.html.twig', [
            'form' => $form,
            'add_path' => $this->addPath,
            'items' => $this->list($matiere)
        ]);
    }

    #[Route('/admin/matieres/delete/{id}', name: 'matiere_delete')]
    public function delete(Request $request): Response
    {
        $matiere = new Matiere($request);
        $matiere->delete();

        return $this->redirect($this->addPath);
    }

    protected function getForm(AbstractEntity $entity, string $submitTitle = 'Créer'): FormInterface
    {
        return $this->createFormBuilder($entity)
            ->add('title', TextType::class)
            ->add('coef', NumberType::class)
            ->add('save', SubmitType::class, ['label' => $submitTitle])
            ->getForm();
    }

    public static function list(AbstractEntity $entity): array
    {
        $st = Database::create()->prepare("SELECT * FROM matiere");
        $st->execute();

        $rslts = $st->fetchAll(PDO::FETCH_ASSOC);
        $matieres = [];

        if (!empty($rslts)) {
            foreach ($rslts as $rslt) {
                $matiere = new Matiere(new Request());
                foreach ($rslt as $field => $value) {
                    $matiere = static::setData($matiere, $rslt, $field, $value);
                }

                $matieres[] = $matiere;
            }
        }

        return $matieres;
    }

    /**
     * @throws \Exception
     */
    public static function select(AbstractEntity $entity): AbstractEntity
    {
        $st = Database::create()->prepare("SELECT * FROM matiere WHERE(id = :id)");
        $st->execute(['id' => $entity->getId()]);

        $entities = $st->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($entities)) {
            $line = reset($entities);

            foreach ($line as $field => $value) {
                if (property_exists($entity, $field)) {
                    switch ($field) {
                        case 'created':
                            $entity->set($field, new \DateTimeImmutable($value));
                            break;

                        default:
                            $entity->set($field, $value);
                            break;
                    }
                }
            }
        }

        return $entity;
    }
}