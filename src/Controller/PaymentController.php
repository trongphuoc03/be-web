<?php

namespace App\Controller;

use App\DTO\Request\Payment\CreatePaymentDTO;
use App\DTO\Request\Payment\UpdatePaymentDTO;
use App\DTO\Response\Payment\PaymentResponseDTO;
use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\JWTService;

class PaymentController extends AbstractController
{
    private const PAYMENT_ROUTE = '/payments/{id}';
    public function __construct(private PaymentService $paymentService, private JWTService $jWTService) {}

    #[Route('/payments', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new CreatePaymentDTO(userId: $data['userId'], bookingId: $data['bookingId'], paymentMethod: $data['paymentMethod']);
        $payment = $this->paymentService->createPayment($dto);

        return $this->json(new PaymentResponseDTO($payment), Response::HTTP_CREATED);
    }
    #[Route('/payments/bulk', methods: ['GET'])]
    public function bulkRead(): JsonResponse
    {
        $payments = $this->paymentService->getAllPayments();
        $response = [];

        foreach ($payments as $payment) {
            $response[] = (new PaymentResponseDTO($payment))->toArray();
        }

        return $this->json($response);
    }
    #[Route(self::PAYMENT_ROUTE, methods: ['GET'])]
    public function read(int $id): JsonResponse
    {
        $payment = $this->paymentService->getPaymentById($id);

        if (!$payment) {
            return $this->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(new PaymentResponseDTO($payment));
    }
    #[Route(self::PAYMENT_ROUTE, methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $check = $this->checkAdminRole($request);
        if (!$check) {
            return $this->json(['message' => 'Không đủ quyền'], Response::HTTP_UNAUTHORIZED);
        }
        $this->paymentService->deletePayment($id);

        return $this->json(['message' => 'Payment deleted successfully'], Response::HTTP_OK);
    }

    private function checkAdminRole(Request $request)
    {
       // Lấy header Authorization
       $authorizationHeader = $request->headers->get('Authorization');
       $check = true;
       // Kiểm tra nếu không có header hoặc header không đúng định dạng
       if (!$authorizationHeader || !preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
           $check = false;
       }
       // Tách token từ header
       $token = $matches[1];

       // Kiểm tra role
       if (!$this->jWTService->isAdmin($token)) {
           $check = false;
       }
       return $check;
    }
}
