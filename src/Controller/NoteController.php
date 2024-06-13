<?php

namespace App\Controller;

use App\Entity\AbstractEntity;
use App\Entity\Examen;
use App\Entity\Matiere;
use App\Entity\Note;
use App\Entity\Releve;
use App\Entity\Utilisateur;
use App\Repository\Database;
use Exception;
use PDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NoteController extends ControllerBase
{
    protected AbstractEntity $utilisateurCourant;
    protected AbstractEntity $releveCourant;

    /**
     * @throws Exception
     */
    #[Route('/admin/notes/{user_id}/{releve_id}', name: 'notes')]
    public function note(Request $request): RedirectResponse|Response
    {
        $note = new Note($request);

        $note->setCreated(new \DateTimeImmutable('now'));

        $this->initialize($request);
        $this->addPath = '/admin/notes/' . $this->utilisateurCourant->getId() . '/' . $this->releveCourant->getId();

        $note->set('releve', $this->releveCourant);

        $form = $this->getForm($note, 'Créer une note');

        if ($response = $this->submit($request, $form, $note, $this->addPath)) {
            return $response;
        }

        return $this->render('back-end/notes.html.twig', [
            'form' => $form,
            'user' => $this->utilisateurCourant,
            'releve' => $this->releveCourant,
            'add_path' => $this->addPath,
            'items' => $this->list($note)
        ]);
    }

    /**
     * @inheritDoc
     */
    #[Route('/admin/note/{user_id}/{releve_id}/{note_id}', name: 'note')]
    public function detail(Request $request): Response
    {
        $note = new Note($request);
        $note = $this->select($note);

        $form = $this->getForm($note, 'Modifier');
        $this->initialize($request);
        $this->addPath = '/admin/notes/' . $this->utilisateurCourant->getId() . '/' . $this->releveCourant->getId();

        if ($response = $this->submit($request, $form, $note, $this->addPath)) {
            return $response;
        }

        return $this->render('back-end/notes.html.twig', [
            'form' => $form,
            'user' => $this->utilisateurCourant,
            'releve' => $this->releveCourant,
            'add_path' => $this->addPath,
            'items' => $this->list($note)
        ]);
    }

    /**
     * @inheritDoc
     */
    #[Route('/admin/notes/delete/{user_id}/{releve_id}{note_id}', name: 'delete_note')]
    public function delete(Request $request): Response
    {
        $releve = new Releve($request);
        $releve->delete();

        $this->initialize($request);

        return $this->redirect('/admin/notes/' . $this->utilisateurCourant->getId() . '/' . $this->releveCourant->getId());
    }

    /**
     * @inheritDoc
     */
    protected function getForm(AbstractEntity $entity, string $submitTitle = 'Créer'): FormInterface
    {
        // Matières
        $matiere = new Matiere(new Request());
        $matieres = MatiereController::list($matiere);
        $mats = [];
        if (!empty($matieres)) {
            foreach ($matieres as $matiere) {
                $mats[$matiere->getTitle()] = $matiere;
            }
        }

        return $this->createFormBuilder($entity)
            ->add('matiere', ChoiceType::class, [
                'label' => 'Matière : ',
                'choices' => $mats
            ])
            ->add('note', NumberType::class, ['label' => 'Note : '])
            ->add('save', SubmitType::class, ['label' => $submitTitle])
            ->getForm();
    }

    /**
     * @inheritDoc
     */
    public static function list(AbstractEntity $entity): array
    {
        $st = Database::create()->prepare("SELECT * FROM note");
        $st->execute();

        $rslts = $st->fetchAll(PDO::FETCH_ASSOC);
        $notes = [];

        if (!empty($rslts)) {
            foreach ($rslts as $rslt) {
                $note = new Note(new Request());
                foreach ($rslt as $field => $value) {
                    $note = static::setData($note, $rslt, $field, $value);
                }

                $notes[] = $note;
            }
        }

        return $notes;
    }

    /**
     * @inheritDoc
     */
    public static function select(AbstractEntity $entity): AbstractEntity
    {
        $st = Database::create()->prepare("SELECT * FROM note WHERE(id = :id)");
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

    /**
     * @throws Exception
     */
    private function initialize(Request $request) {
        // Utilisateur.
        $this->utilisateurCourant = new Utilisateur($request);
        $this->utilisateurCourant = UtilisateurController::select($this->utilisateurCourant);

        // Relevé.
        $this->releveCourant = new Releve($request);
        $this->releveCourant = ReleveController::select($this->releveCourant);
    }
}