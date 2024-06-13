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

class UtilisateurController extends ControllerBase
{
    public function __construct() {
        $this->addPath = '/admin/utilisateurs';
    }
    #[Route('/admin/utilisateurs', name: 'utilisateurs')]
    public function utilisateur(Request $request) {
        $utilisateur = new Utilisateur($request);

        $utilisateur->setCreated(new \DateTimeImmutable('now'));
        $utilisateur->setPassword($this->randomPassword());
        $form = $this->getForm($utilisateur);

        if ($response = $this->submit($request, $form, $utilisateur, '/admin/utilisateurs')) {
            return $response;
        }

        return $this->render('back-end/utilisateurs.html.twig', [
            'form' => $form,
            'add_path' => $this->addPath,
            'items' => $this->list($utilisateur)
        ]);
    }
    #[Route('/admin/utilisateurs/{user_id}', name: 'utilisateur')]
    public function detail(Request $request): Response
    {
        $utilisateur = new Utilisateur($request);
        $utilisateur = $this->select($utilisateur);
        $form = $this->getForm($utilisateur, 'Modifier');

        if ($response = $this->submit($request, $form, $utilisateur, '/admin/utilisateurs')) {
            return $response;
        }

        return $this->render('back-end/utilisateurs.html.twig', [
            'form' => $form,
            'add_path' => $this->addPath,
            'items' => $this->list($utilisateur)
        ]);
    }

    #[Route('/admin/utilisateurs/delete/{user_id}', name: 'utilisateur_delete')]
    public function delete(Request $request): Response
    {
        $utilisateur = new Utilisateur($request);
        $utilisateur->delete();

        return $this->redirect('/admin/utilisateurs');
    }

    /**
     * @inheritDoc
     */
    protected function getForm(AbstractEntity $entity, string $submitTitle = 'Créer un utilisateur'): FormInterface
    {
        $options = [
            'Edudiant' => 'etudiant'
        ];
        if ($entity->getTarget() == 'utilisateur') {
            $options['Administrateur'] = 'admin';
        }
        return $this->createFormBuilder($entity)
            ->add('nin', IntegerType::class, [
                'label' => "Numéro d'identité national :",
                'attr' => ['pattern' => '/^[0-9]{6}$/', 'maxlength' => 6]
            ])
            ->add('name', TextType::class, [
                'label' => "Nom :"
            ])
            ->add('firstName', TextType::class, [
                'label' => "Prénom :"
            ])
            ->add('birthDayDate', DateType::class, [
                'label' => "Date de naissance :"
            ])
            ->add('birthPlace', TextType::class, [
                'label' => "lieu de naissance :"
            ])
            ->add('email', EmailType::class, [
                'label' => "Email :",
            ])
            ->add('role', ChoiceType::class, [
                'label' => "Role :",
                'choices' => $options
            ])
            ->add('save', SubmitType::class, ['label' => $submitTitle])
            ->getForm();
    }

    /**
     * @inheritDoc
     */
    public static function list(AbstractEntity $entity): array
    {
        $st = Database::create()->prepare("SELECT * FROM utilisateur");
        $st->execute();

        $rslts = $st->fetchAll(PDO::FETCH_ASSOC);
        $utilisateurs = [];

        if (!empty($rslts)) {
            foreach ($rslts as $rslt) {
                $utilisateur = new Utilisateur(new Request());
                foreach ($rslt as $field => $value) {
                    $utilisateur = static::setData($utilisateur, $rslt, $field, $value);
                }

                $utilisateurs[] = $utilisateur;
            }
        }

        return $utilisateurs;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public static function select(AbstractEntity $entity): AbstractEntity
    {
        $st = Database::create()->prepare("SELECT * FROM utilisateur WHERE(id = :id)");
        $st->execute([
            'id' => $entity->getId()
        ]);

        $entities = $st->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($entities)) {
            $line = reset($entities);
            foreach ($line as $field => $value) {
                if (property_exists($entity, $field)) {
                    switch ($field) {
                        case 'created':
                            $entity->set($field, new \DateTimeImmutable($value));
                            break;
                        case 'birthDayDate':
                            $entity->set($field, new \DateTime($value));
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