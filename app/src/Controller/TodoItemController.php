<?php
/**
 * TodoItem controller.
 */

namespace App\Controller;

use App\Entity\TodoItem;
use App\Form\Type\TodoItemType;
use App\Service\TodoItemServiceInterface;
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
    private TodoItemServiceInterface $TodoItemService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * TodoItemController constructor.
     *
     * @param TodoItemServiceInterface $TodoItemService TodoItem service
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(TodoItemServiceInterface $TodoItemService, TranslatorInterface $translator)
    {
        $this->TodoItemService = $TodoItemService;
        $this->translator = $translator;
    }

    /**
     * Index action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'todo_item_index',
        methods: ['GET']
    )]
    public function index(Request $request): Response
    {
        $pagination = $this->TodoItemService->getPaginatedList(
            $request->query->getInt('page', 1)
        );

        return $this->render(
            'todo_item/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * Show action.
     *
     * @param TodoItem $TodoItem TodoItem entity
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'todo_item_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET'],
    )]
    public function show(TodoItem $TodoItem): Response
    {
        return $this->render(
            'todo_item/show.html.twig',
            [
                'todo_item' => $TodoItem,
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
    public function create(Request $request): Response
    {
        $TodoItem = new TodoItem();
        $form = $this->createForm(
            TodoItemType::class,
            $TodoItem,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('TodoItem_create'),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->TodoItemService->save($TodoItem);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('TodoItem_index');
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
     * @param TodoItem $TodoItem TodoItem entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'todo_item_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'POST'],
    )]
    public function edit(Request $request, TodoItem $TodoItem): Response
    {
        $form = $this->createForm(
            TodoItemType::class,
            $TodoItem,
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->TodoItemService->save($TodoItem);

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('TodoItem_index');
        }

        return $this->render(
            'todo_item/edit.html.twig',
            [
                'form' => $form->createView(),
                'todo_item' => $TodoItem,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param TodoItem $TodoItem TodoItem entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'todo_item_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'DELETE'],
    )]
    public function delete(Request $request, TodoItem $TodoItem): Response
    {
        $form = $this->createForm(
            FormType::class,
            $TodoItem,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'todo_item_delete',
                    ['id' => $TodoItem->getId()]
                ),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->TodoItemService->delete($TodoItem);

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
                'todo_item' => $TodoItem,
            ]
        );
    }
}
