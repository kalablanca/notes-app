<?php
/**
 * TodoItem controller.
 */

namespace App\Controller;

use App\Entity\TodoItem;
use App\Form\Type\TodoItemType;
use App\Service\TodoItemServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TodoItemController.
 */
#[Route('/todo-item')]
class TodoItemController extends AbstractController
{
    /**
     * TodoItem service.
     */
    private TodoItemServiceInterface $todoItemService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * TodoItemController constructor.
     *
     * @param TodoItemServiceInterface $todoItemService TodoItem service
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(TodoItemServiceInterface $todoItemService, TranslatorInterface $translator)
    {
        $this->todoItemService = $todoItemService;
        $this->translator = $translator;
    }

    /**
     * Show action.
     *
     * @param TodoItem $todoItem TodoItem entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'todo_item_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET'],
    )]
    #[IsGranted('VIEW', subject: 'todoItem')]
    public function show(TodoItem $todoItem): Response
    {
        return $this->render(
            'todo_item/show.html.twig',
            [
                'todo_item' => $todoItem,
            ]
        );
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'todo_item_create',
        methods: 'GET|POST',
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): Response
    {
        $todoItem = new TodoItem();
        $form = $this->createForm(
            TodoItemType::class,
            $todoItem,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('todo_item_create'),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->todoItemService->save($todoItem);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute(
                'todo_show',
                ['id' => $todoItem->getTodo()->getId()]
            );
        }

        return $this->render(
            'todo_item/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param TodoItem $todoItem TodoItem entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'todo_item_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('EDIT', subject: 'todoItem')]
    public function edit(Request $request, TodoItem $todoItem): Response
    {
        $form = $this->createForm(
            TodoItemType::class,
            $todoItem,
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->todoItemService->save($todoItem);

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute(
                'todo_show',
                ['id' => $todoItem->getTodo()->getId()]
            );
        }

        return $this->render(
            'todo_item/edit.html.twig',
            [
                'form' => $form->createView(),
                'todo_item' => $todoItem,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param TodoItem $todoItem TodoItem entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'todo_item_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'DELETE'],
    )]
    public function delete(Request $request, TodoItem $todoItem): Response
    {
        $form = $this->createForm(
            FormType::class,
            $todoItem,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'todo_item_delete',
                    ['id' => $todoItem->getId()]
                ),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->todoItemService->delete($todoItem);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('todo_item_index');
        }

        return $this->render(
            'todo_item/delete.html.twig',
            [
                'form' => $form->createView(),
                'todo_item' => $todoItem,
            ]
        );
    }
}
