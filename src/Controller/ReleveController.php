<?php

namespace App\Controller;

use App\Entity\AbstractEntity;
use App\Entity\Examen;
use App\Entity\Releve;
use App\Entity\Utilisateur;
use App\Repository\Database;
use Exception;
use PDO;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReleveController extends ControllerBase
{
    protected AbstractEntity $utilisateurCourant;

    /**
     * @throws Exception
     */
    #[Route('/admin/releves/{user_id}', name: 'releves')]
    public function releve(Request $request): RedirectResponse|Response
    {
        $releve = new Releve($request);

        $releve->setCreated(new \DateTimeImmutable('now'));

        // Utilisateur.
        $this->utilisateurCourant = new Utilisateur($request);
        $this->utilisateurCourant = UtilisateurController::select($this->utilisateurCourant);
        $this->addPath = '/admin/releves/' . $this->utilisateurCourant->getId();

        $releve->set('utilisateur', $this->utilisateurCourant);

        $form = $this->getForm($releve, 'Créer une matière');

        if ($response = $this->submit($request, $form, $releve, $this->addPath)) {
            return $response;
        }

        return $this->render('back-end/releves.html.twig', [
            'form' => $form,
            'user' => $this->utilisateurCourant,
            'add_path' => $this->addPath,
            'items' => $this->list($releve)
        ]);
    }

    /**
     * @inheritDoc
     */
    #[Route('/admin/releve/{user_id}/{releve_id}', name: 'releve')]
    public function detail(Request $request): Response
    {
        // Utilisateur.
        $this->utilisateurCourant = new Utilisateur($request);
        $this->utilisateurCourant = UtilisateurController::select($this->utilisateurCourant);
        $this->addPath = '/admin/releves/' . $this->utilisateurCourant->getId();

        $releve = new Releve($request);
        $releve = $this->select($releve);

        $form = $this->getForm($releve, 'Modifier');

        if ($response = $this->submit($request, $form, $releve, $this->addPath)) {
            return $response;
        }

        return $this->render('back-end/releves.html.twig', [
            'form' => $form,
            'user' => $this->utilisateurCourant,
            'add_path' => $this->addPath,
            'items' => $this->list($releve)
        ]);
    }

    /**
     * @inheritDoc
     */
    #[Route('/admin/releves/delete/{user_id}/{releve_id}', name: 'releve_delete')]
    public function delete(Request $request): Response
    {
        $releve = new Releve($request);
        $releve->delete();

        $this->utilisateurCourant = new Utilisateur($request);
        $this->utilisateurCourant = UtilisateurController::select($this->utilisateurCourant);
        $this->addPath = '/admin/releves/' . $this->utilisateurCourant->getId();

        return $this->redirect($this->addPath);
    }

    /**
     * @inheritDoc
     */
    protected function getForm(AbstractEntity $entity, string $submitTitle = 'Créer'): FormInterface
    {
        // Examens
        $examen = new Examen(new Request());
        $examens = ExamenController::list($examen);
        $exs = [];
        if (!empty($examens)) {
            foreach ($examens as $examen) {
                $exs[$examen->getTitle()] = $examen;
            }
        }

        return $this->createFormBuilder($entity)
            ->add('examen', ChoiceType::class, [
                'label' => 'Examen : ',
                'choices' => $exs
            ])
            ->add('year', IntegerType::class, ['label' => 'Session : '])
            ->add('serial', TextType::class, ['label' => 'Série : '])
            ->add('save', SubmitType::class, ['label' => $submitTitle])
            ->getForm();
    }

    /**
     * @inheritDoc
     */
    public static function list(AbstractEntity $entity): array
    {
        $st = Database::create()->prepare("SELECT * FROM releve");
        $st->execute();

        $rslts = $st->fetchAll(PDO::FETCH_ASSOC);
        $releves = [];

        if (!empty($rslts)) {
            foreach ($rslts as $rslt) {
                $releve = new Releve(new Request());
                foreach ($rslt as $field => $value) {
                    $releve = static::setData($releve, $rslt, $field, $value);
                }

                $releves[] = $releve;
            }
        }

        return $releves;
    }

    /**
     * @inheritDoc
     */
    public static function select(AbstractEntity $entity): AbstractEntity
    {
        $st = Database::create()->prepare("SELECT * FROM releve WHERE(id = :id)");
        $st->execute(['id' => $entity->getId()]);

        $entities = $st->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($entities)) {
            $line = reset($entities);
            foreach ($line as $field => $value) {
                $entity = static::setData($entity, $line, $field, $value);
            }
        }

        return $entity;
    }
}