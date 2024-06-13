<?php

namespace App\Controller;

use App\Entity\AbstractEntity;
use App\Entity\Examen;
use App\Entity\Matiere;
use App\Entity\Releve;
use App\Entity\Utilisateur;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class ControllerBase extends AbstractController
{
    protected string $addPath;

    /**
     * Detail d'une entité.
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public abstract function detail(Request $request): Response;

    /**
     * Suppression d'une entité.
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public abstract function delete(Request $request): Response;

    /**
     * Soumission du formulaire et redirection.
     *
     * @param Request $request
     * @param FormInterface $form
     * @param AbstractEntity $entity
     * @param string $redirect_url
     * @return RedirectResponse|null
     * @throws Exception
     */
    protected function submit(Request $request, FormInterface $form, AbstractEntity $entity, string $redirect_url = '/'): ?RedirectResponse
    {
        if ($request->isMethod('POST')) {
            $allValues = $request->request->all();
            $form->submit($allValues[$form->getName()]);

            if ($form->isSubmitted() && $form->isValid()) {
                $entity->save();

                // Redirection.
                return $this->redirect($redirect_url);
            }
        }

        return NULL;
    }

    /**
     * Création du formulaire.
     *
     * @param AbstractEntity $entity
     * @param string $submitTitle
     * @return FormInterface
     * @throws Exception
     */
    protected abstract function getForm(AbstractEntity $entity, string $submitTitle = 'Créer'): FormInterface;

    /**
     * Sélectionne la liste des entités dans la BDD.
     *
     * @param AbstractEntity $entity
     * @return array
     * @throws Exception
     */
    public static abstract function list(AbstractEntity $entity): array;

    /**
     * Retourne un objet de type AbstractEntity ventillé des données de la BDD.
     *
     * @param AbstractEntity $entity
     * @return AbstractEntity
     * @throws Exception
     */
    public static abstract function select(AbstractEntity $entity): AbstractEntity;

    /**
     * @param AbstractEntity $entity
     * @param array $dataArray
     * @param string $field
     * @param $value
     * @return AbstractEntity
     * @throws Exception
     */
    public static function setData(AbstractEntity $entity, array $dataArray, string $field, $value): AbstractEntity
    {
        $request = new Request();

        switch ($field) {
            case 'created':
                $entity->set($field, new \DateTimeImmutable($value));
                break;
            case 'id_utilisateur':
                $u = new Utilisateur($request);
                $u->set('id', $dataArray['id_utilisateur']);
                $u = UtilisateurController::select($u);

                $entity->set('utilisateur', $u);
                break;
            case 'id_examen':
                $ex = new Examen($request);
                $ex->set('id', $dataArray['id_examen']);
                $ex = ExamenController::select($ex);

                $entity->set('examen', $ex);
                break;
            case 'id_matiere':
                $mat = new Matiere($request);
                $mat->set('id', $dataArray['id_matiere']);
                $mat = MatiereController::select($mat);

                $entity->set('matiere', $mat);
                break;
            case 'id_releve':
                $rel = new Releve($request);
                $rel->set('id', $dataArray['id_releve']);
                $rel = ReleveController::select($rel);

                $entity->set('releve', $rel);
                break;
            case 'birthDayDate':
                $entity->set($field, new \DateTime($value));
                break;
            default:
                $entity->set($field, $value);
                break;
        }

        return $entity;
    }

    protected function randomPassword(): string
    {
        $random_characters = 2;

        $lower_case = "abcdefghijklmnopqrstuvwxyz";
        $upper_case = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $numbers = "1234567890";
        $symbols = "!@#$%^&*";

        $lower_case = str_shuffle($lower_case);
        $upper_case = str_shuffle($upper_case);
        $numbers = str_shuffle($numbers);
        $symbols = str_shuffle($symbols);

        $random_password = substr($lower_case, 0, $random_characters);
        $random_password .= substr($upper_case, 0, $random_characters);
        $random_password .= substr($numbers, 0, $random_characters);
        $random_password .= substr($symbols, 0, $random_characters);
        return  str_shuffle($random_password);
    }
}