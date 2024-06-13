<?php

namespace App\Controller;

use App\Entity\AbstractEntity;
use App\Entity\Examen;
use App\Repository\Database;
use PDO;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExamenController extends ControllerBase
{
    public function __construct() {
        $this->addPath = '/admin/examens';
    }
    /**
     * @throws \Exception
     */
    #[Route('/admin/examens', name: 'examens')]
    public function examen(Request $request): Response
    {
        $examen = new Examen($request);

        $examen->setCreated(new \DateTimeImmutable('now'));
        $form = $this->getForm($examen);

        if ($response = $this->submit($request, $form, $examen, $this->addPath)) {
            return $response;
        }

        return $this->render('back-end/examens.html.twig', [
            'form' => $form,
            'add_path' => $this->addPath,
            'items' => static::list($examen)
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/admin/examens/{id}', name: 'examen')]
    public function detail(Request $request): Response
    {
        $examen = new Examen($request);
        $examen = $this->select($examen);

        $form = $this->getForm($examen, 'Modifier');

        if ($response = $this->submit($request, $form, $examen, $this->addPath)) {
            return $response;
        }

        return $this->render('back-end/examens.html.twig', [
            'form' => $form,
            'add_path' => $this->addPath,
            'items' => static::list($examen)
        ]);
    }

    #[Route('/admin/examens/delete/{id}', name: 'examen_delete')]
    public function delete(Request $request): Response
    {
        $examen = new Examen($request);
        $examen->delete();

        return $this->redirect($this->addPath);
    }

    /**
     * @inheritDoc
     */
    protected function getForm(AbstractEntity $entity, string $submitTitle = 'Créer un examen'): FormInterface
    {
        return $this->createFormBuilder($entity)
            ->add('title', TextType::class)
            ->add('save', SubmitType::class, ['label' => $submitTitle])
            ->getForm();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public static function list(AbstractEntity $entity): array
    {
        $st = Database::create()->prepare("SELECT * FROM examen");
        $st->execute();

        $rslts = $st->fetchAll(PDO::FETCH_ASSOC);
        $examens = [];

        if (!empty($rslts)) {
            foreach ($rslts as $rslt) {
                $examen = new Examen(new Request());
                foreach ($rslt as $field => $value) {
                    $examen = static::setData($examen, $rslt, $field, $value);
                }

                $examens[] = $examen;
            }
        }

        return $examens;

    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public static function select(AbstractEntity $entity): AbstractEntity
    {
        $st = Database::create()->prepare("SELECT * FROM examen WHERE(id = :id)");
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