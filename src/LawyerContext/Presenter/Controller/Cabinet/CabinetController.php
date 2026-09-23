<?php
declare(strict_types=1);

namespace Websymphonie\LawyerContext\Presenter\Controller\Cabinet;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Repository\Cabinet\CabinetRepository;
use Websymphonie\LawyerContext\Presenter\Form\CabinetFormType;
use Websymphonie\SharedContext\Presenter\AbstractController;

#[Route('/cabinets', name: 'lawyer_admin_cabinet_')]
#[IsGranted('CABINET_VIEW')]
final class CabinetController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly CabinetRepository $repository, private readonly RichTextSanitizerInterface $richTextSanitizer) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('lawyer/admin/cabinet/index.html.twig', ['cabinets' => $this->repository->listOrdered()]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('CABINET_CREATE')]
    public function new(Request $request): Response
    {
        $cabinet = new CabinetEntity();
        $form = $this->createForm(CabinetFormType::class, $cabinet);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cabinet->setDescription($this->richTextSanitizer->sanitize($cabinet->getDescription() ?? ''));
            $this->entityManager->persist($cabinet);
            $this->entityManager->flush();
            $this->flash()->success('Cabinet créé avec succès.');
            return $this->redirectToRoute('lawyer_admin_cabinet_list');
        }
        return $this->render('lawyer/admin/cabinet/form.html.twig', ['form' => $form, 'title' => 'Nouveau cabinet']);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('CABINET_EDIT')]
    public function edit(Request $request, CabinetEntity $cabinet): Response
    {
        $form = $this->createForm(CabinetFormType::class, $cabinet);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cabinet->setDescription($this->richTextSanitizer->sanitize($cabinet->getDescription() ?? ''));
            $this->entityManager->flush();
            $this->flash()->success('Cabinet modifié avec succès.');
            return $this->redirectToRoute('lawyer_admin_cabinet_list');
        }
        return $this->render('lawyer/admin/cabinet/form.html.twig', ['form' => $form, 'title' => 'Modifier le cabinet']);
    }
}
