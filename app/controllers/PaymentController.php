<?php
require_once ROOT_PATH . '/app/services/VnpayService.php';
require_once ROOT_PATH . '/app/models/Order.php';

class PaymentController extends Controller
{
    public function vnpayReturn(): void
    {
        $result = $this->process($_GET);
        if ($result['valid'] && $result['paid']) {
            unset($_SESSION['last_order']['payment_error']);
            flash('success', 'Thanh toán VNPay thành công.');
        } else {
            flash('error', $result['message']);
            if (isset($_SESSION['last_order'])) {
                $_SESSION['last_order']['payment_error'] = $result['message'];
            }
        }
        $this->redirect('checkout/success');
    }

    public function vnpayIpn(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $result = $this->process($_GET);
        echo json_encode([
            'RspCode' => $result['valid'] ? '00' : '97',
            'Message' => $result['valid'] ? 'Confirm Success' : $result['message'],
        ]);
    }

    private function process(array $data): array
    {
        $service = new VnpayService();
        if (!$service->verifyResponse($data)) return ['valid'=>false, 'paid'=>false, 'message'=>'Chữ ký VNPay không hợp lệ.'];
        $code = (string)($data['vnp_TxnRef'] ?? '');
        $orderModel = new Order();
        $order = $orderModel->getByCode($code);
        if (!$order) return ['valid'=>false, 'paid'=>false, 'message'=>'Không tìm thấy đơn hàng.'];
        if ((int)($data['vnp_Amount'] ?? 0) !== (int)$order['total_amount'] * 100) return ['valid'=>false, 'paid'=>false, 'message'=>'Số tiền thanh toán không khớp.'];
        $gatewayPaid = ($data['vnp_ResponseCode'] ?? '') === '00'
            && ($data['vnp_TransactionStatus'] ?? '') === '00';
        $orderModel->updatePayment($code, $gatewayPaid ? 'paid' : 'failed');
        $updatedOrder = $orderModel->getByCode($code);
        $paid = ($updatedOrder['payment_status'] ?? '') === 'paid';
        if (isset($_SESSION['last_order']) && ($_SESSION['last_order']['order_code'] ?? '') === $code) {
            $_SESSION['last_order']['payment_status'] = $paid ? 'paid' : 'failed';
        }
        return ['valid'=>true, 'paid'=>$paid, 'message'=>$paid ? '' : 'Bạn đã hủy thanh toán hoặc giao dịch VNPay không thành công.'];
    }
}
