<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AuditLogService
{
    public static function log($action, $model, $modelId, $changes = [], $status = 'success')
    {
        $user = auth()->user();
        
        $logData = [
            'timestamp' => now(),
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'changes' => $changes,
            'status' => $status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];

        Log::channel('audit')->info('Audit Log', $logData);
    }

    public static function logOrderCreated($orderId, $userId, $totalAmount)
    {
        self::log('order_created', 'Order', $orderId, [
            'user_id' => $userId,
            'total_amount' => $totalAmount
        ]);
    }

    public static function logOrderStatusChanged($orderId, $oldStatus, $newStatus)
    {
        self::log('order_status_changed', 'Order', $orderId, [
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
    }

    public static function logProductCreated($productId, $productName)
    {
        self::log('product_created', 'Product', $productId, [
            'name' => $productName
        ]);
    }

    public static function logProductUpdated($productId, $changes)
    {
        self::log('product_updated', 'Product', $productId, $changes);
    }

    public static function logProductDeleted($productId, $productName)
    {
        self::log('product_deleted', 'Product', $productId, [
            'name' => $productName
        ]);
    }

    public static function logUserLogin($userId)
    {
        self::log('user_login', 'User', $userId, []);
    }

    public static function logFailedLogin($email)
    {
        self::log('failed_login', 'User', null, [
            'email' => $email
        ], 'failed');
    }
}
