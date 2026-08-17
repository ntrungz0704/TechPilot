<?php
require_once ROOT_PATH . '/app/services/VnpayService.php';
require_once ROOT_PATH . '/app/models/Order.php';

class PaymentController extends Controller
{
    public function vnpayReturn(): void
    {
        $result = $this->process($_GET);
        $orderCode = trim((string)($_GET['vnp_TxnRef'] ?? ''));
        if ($result['valid'] && $result['paid']) {
            unset($_SESSION['last_order']['payment_error']);
            flash('success', 'Thanh toán VNPay thành công.');
        } else {
            flash('error', $result['message']);
            if (isset($_SESSION['last_order'])) {
                $_SESSION['last_order']['payment_error'] = $result['message'];
            }
        }
        $this->redirect('checkout/success' . ($orderCode !== '' ? '?order_code=' . urlencode($orderCode) : ''));
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

    public function vnpaySandboxSim(): void
    {
        $config = require ROOT_PATH . '/config/vnpay.php';
        if (APP_ENV !== 'development' || empty($config['simulator_enabled'])) {
            ErrorHandler::renderErrorView(404);
            return;
        }

        $secret = trim((string)($config['simulator_hash_secret'] ?? ''));
        if ($secret === '') {
            ErrorHandler::renderErrorView(404);
            return;
        }

        $txnRef = trim($_GET['vnp_TxnRef'] ?? '');
        $amount = (int)($_GET['vnp_Amount'] ?? 0);
        $tmnCode = trim((string)($config['tmn_code'] ?? 'DEMO0001'));

        // Prepare return parameters for success simulation
        $successParams = [
            'vnp_Amount' => $amount,
            'vnp_BankCode' => 'NCB',
            'vnp_BankTranNo' => 'VNP' . time(),
            'vnp_CardType' => 'ATM',
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $txnRef,
            'vnp_PayDate' => date('YmdHis'),
            'vnp_ResponseCode' => '00',
            'vnp_TmnCode' => $tmnCode,
            'vnp_TransactionNo' => '1410' . random_int(100000, 999999),
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $txnRef,
        ];
        ksort($successParams);
        $hashParts = [];
        foreach ($successParams as $k => $v) {
            $hashParts[] = urlencode($k) . '=' . urlencode((string)$v);
        }
        $successHash = hash_hmac('sha512', implode('&', $hashParts), $secret);
        $successUrl = url('payment/vnpay-return?' . implode('&', $hashParts) . '&vnp_SecureHash=' . $successHash);

        // Prepare return parameters for fail simulation
        $failParams = $successParams;
        $failParams['vnp_ResponseCode'] = '24'; // User cancelled
        $failParams['vnp_TransactionStatus'] = '02';
        ksort($failParams);
        $failHashParts = [];
        foreach ($failParams as $k => $v) {
            $failHashParts[] = urlencode($k) . '=' . urlencode((string)$v);
        }
        $failHash = hash_hmac('sha512', implode('&', $failHashParts), $secret);
        $failUrl = url('payment/vnpay-return?' . implode('&', $failHashParts) . '&vnp_SecureHash=' . $failHash);

        $displayAmount = number_format($amount / 100, 0, ',', '.') . 'đ';
        $qrData = $successUrl;
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($qrData);

        $this->render('payment/vnpay_sandbox', [
            'pageTitle' => 'Cổng thanh toán VNPAY-QR (Mô phỏng Test)',
            'orderCode' => $txnRef,
            'amountRaw' => $amount / 100,
            'displayAmount' => $displayAmount,
            'qrImageUrl' => $qrImageUrl,
            'successUrl' => $successUrl,
            'failUrl' => $failUrl
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
