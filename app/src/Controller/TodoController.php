<?php
/**
 * Todo controller.
 */

namespace App\Controller;

use App\Entity\Todo;
use App\Form\Type\TodoType;
use App\Service\TodoItemServiceInterface;
use App\Service\TodoServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TodoController.
 */
#[Route('/todo')]
class TodoController extends AbstractController
{
    /**
     * TodoItem service.
     */
    private TodoItemServiceInterface $todoItemService;

    /**
     * Todo service.
     */
    private TodoServiceInterface $todoService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * TodoController constructor.
     *
     * @param TodoItemServiceInterface $todoItemService Todo Item service
     * @param  TodoServiceInterface $todoService Todo service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(TodoItemServiceInterface $todoItemService, TranslatorInterface $translator, TodoServiceInterface $todoService)
    {
        $this->todoItemService = $todoItemService;
        $this->todoService = $todoService;
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
        name: 'todo_index',
        methods: ['GET']
    )]
    public function index(Request $request): Response
    {
        $pagination = $this->todoService->getPaginatedList(
            $request->query->getInt('page', 1),
            $this->getUser()
        );

        return $this->render(
            'todo/index.html.twig',
            ['pagination' => $pagination]
        );
    }

    /**
     * Show action.
     *
     * @param Todo    $todo    Todo entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'todo_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET'],
    )]
    public function show(Todo $todo, Request $request): Response
    {
        $todoItemByTodoPagedList = $this->todoService->getTodoItemsByTodoPaginatedList(
            $request->query->getInt('page', 1),
            $todo
        );

        return $this->render(
            'todo/show.html.twig',
            [
                'todo' => $todo,
                'pagination' => $todoItemByTodoPagedList,
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
        name: 'todo_create',
        methods: 'GET|POST',
    )]
    public function create(Request $request): Response
    {
        $todo = new Todo();
        $form = $this->createForm(
            TodoType::class,
            $todo,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('todo_create'),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->todoService->save($todo);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('todo_index');
        }

        return $this->render(
            'todo/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Todo    $todo    Todo entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'todo_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'POST'],
    )]
    public function edit(Request $request, Todo $todo): Response
    {
        $form = $this->createForm(
            TodoType::class,
            $todo,
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->todoService->save($todo);

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('todo_index');
        }

        return $this->render(
            'todo/edit.html.twig',
            [
                'form' => $form->createView(),
                'todo' => $todo,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Todo    $todo    Todo entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'todo_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'DELETE'],
    )]
    public function delete(Request $request, Todo $todo): Response
    {
        if (!$this->todoService->canBeDeleted($todo)) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.todo_contains_items')
            );

            return $this->redirectToRoute('category_index');
        }
        $form = $this->createForm(
            FormType::class,
            $todo,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'todo_delete',
                    ['id' => $todo->getId()]
                ),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->todoService->delete($todo);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('todo_index');
        }

        return $this->render(
            'todo/delete.html.twig',
            [
                'form' => $form->createView(),
                'todo' => $todo,
            ]
        );
    }
}
