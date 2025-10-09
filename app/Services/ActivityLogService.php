<?php
// app/Services/ActivityLogService.php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $model = null,
        ?array $oldData = null,
        ?array $newData = null
    ): ActivityLog {
        $user = Auth::user();
        
        return ActivityLog::create([
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_data' => $oldData,
            'new_data' => $newData,
            'user_id' => $user ? $user->id : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    // Common log methods
    public static function login($user)
    {
        return self::log('user_login', "User {$user->email} logged in", $user);
    }

    public static function logout($user)
    {
        return self::log('user_logout', "User {$user->email} logged out", $user);
    }

    // USER ACCOUNT ACTIVITY
    public static function userCreated($user, $createdBy)
    {
        return self::log(
            'user_created',
            "User {$user->first_name} {$user->last_name} ({$user->email}) was created by {$createdBy->name} with role: {$user->role}",
            $user,
            null,
            [
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ]
        );
    }

    public static function userApproved($unverifiedUser, $newUser, $adminUser)
    {
        return self::log(
            'user_verification_approved',
            "User verification for {$unverifiedUser->email} was approved by {$adminUser->first_name} {$adminUser->last_name}. New user account created.",
            $newUser,
            [
                'verification_id' => $unverifiedUser->id,
                'verification_email' => $unverifiedUser->email,
                'verification_name' => $unverifiedUser->first_name . ' ' . $unverifiedUser->last_name,
            ],
            [
                'user_id' => $newUser->id,
                'user_email' => $newUser->email,
                'user_name' => $newUser->first_name . ' ' . $newUser->last_name,
                'role' => $newUser->role,
                'status' => $newUser->status,
            ]
        );
    }

    // public static function userApprovalFailed($unverifiedUser, $adminUser, $reason)
    // {
    //     return self::log(
    //         'user_verification_failed',
    //         "User verification for {$unverifiedUser->email} failed. Reason: {$reason}",
    //         $unverifiedUser,
    //         null,
    //         ['failure_reason' => $reason]
    //     );
    // }

    public static function userVerificationRejected($unverifiedUser, $adminUser, $reason = null)
    {
        $description = "User verification for {$unverifiedUser->email} was rejected by {$adminUser->first_name} {$adminUser->last_name}";
        if ($reason) {
            $description .= ". Reason: {$reason}";
        }

        return self::log(
            'user_verification_rejected',
            $description,
            $unverifiedUser,
            $unverifiedUser->toArray(),
            null
        );
    }

    public static function userVerificationDeclined($unverifiedUser, $adminUser)
    {
        return self::log(
            'user_verification_declined',
            "User verification for {$unverifiedUser->email} was declined by {$adminUser->first_name} {$adminUser->last_name}",
            $unverifiedUser,
            $unverifiedUser->toArray(),
            null
        );
    }

    public static function userUpdated($user, $adminUser, $oldData, $newData)
    {
        $changes = [];
        
        // Track what actually changed
        if ($oldData['first_name'] !== $newData['first_name']) {
            $changes[] = "first name: {$oldData['first_name']} → {$newData['first_name']}";
        }
        if ($oldData['last_name'] !== $newData['last_name']) {
            $changes[] = "last name: {$oldData['last_name']} → {$newData['last_name']}";
        }
        if ($oldData['email'] !== $newData['email']) {
            $changes[] = "email: {$oldData['email']} → {$newData['email']}";
        }
        if ($oldData['role'] !== $newData['role']) {
            $changes[] = "role: {$oldData['role']} → {$newData['role']}";
        }

        $changeDescription = empty($changes) ? 'No changes detected' : implode(', ', $changes);

        return self::log(
            'user_updated',
            "User {$user->email} was updated by {$adminUser->first_name} {$adminUser->last_name}. Changes: {$changeDescription}",
            $user,
            $oldData,
            $newData
        );
    }

    public static function userDeactivated($user, $adminUser)
    {
        return self::log(
            'user_deactivated',
            "User {$user->email} was deactivated by {$adminUser->first_name} {$adminUser->last_name}",
            $user,
            ['status' => 'Active'],
            ['status' => 'Inactive']
        );
    }

    public static function userReactivated($user, $adminUser)
    {
        return self::log(
            'user_reactivated',
            "User {$user->email} was reactivated by {$adminUser->first_name} {$adminUser->last_name}",
            $user,
            ['status' => 'Inactive'],
            ['status' => 'Active']
        );
    }

    // ORDER ACTIVITY
    public static function orderApproved($order, $staffUser, $oldStatus)
    {
        return self::log(
            'order_approved',
            "Order #{$order->id} was approved by {$staffUser->first_name} {$staffUser->last_name}. Status changed from {$oldStatus} to Approved",
            $order,
            ['status' => $oldStatus],
            ['status' => 'Approved', 'approved_by' => $staffUser->id]
        );
    }

    public static function orderDeclined($order, $staffUser, $oldStatus)
    {
        return self::log(
            'order_declined',
            "Order #{$order->id} was declined by {$staffUser->first_name} {$staffUser->last_name}. Status changed from {$oldStatus} to Declined",
            $order,
            ['status' => $oldStatus],
            ['status' => 'Declined', 'declined_by' => $staffUser->id]
        );
    }

    public static function orderReadyForPickup($order, $staffUser)
    {
        return self::log(
            'order_ready_for_pickup',
            "Order #{$order->id} was marked as ready for pickup by {$staffUser->first_name} {$staffUser->last_name}",
            $order,
            ['pickup_status' => null],
            ['pickup_status' => 'Pending']
        );
    }

    public static function componentReadyForPickup($checkout, $staffUser, $oldStatus)
    {
        return self::log(
            'component_ready_for_pickup',
            "Component order #{$checkout->id} was marked as ready for pickup by {$staffUser->first_name} {$staffUser->last_name}",
            $checkout,
            ['pickup_status' => $oldStatus],
            ['pickup_status' => 'Pending']
        );
    }

    public static function orderPickedUp($order, $staffUser, $oldData)
    {
        return self::log(
            'order_picked_up',
            "Order #{$order->id} was marked as picked up by {$staffUser->first_name} {$staffUser->last_name}",
            $order,
            $oldData,
            [
                'pickup_status' => 'Picked up',
                'pickup_date' => now()->toDateTimeString(),
                'payment_status' => 'Paid'
            ]
        );
    }

    public static function componentPickedUp($checkout, $staffUser, $oldData)
    {
        return self::log(
            'component_picked_up',
            "Component order #{$checkout->id} was marked as picked up by {$staffUser->first_name} {$staffUser->last_name}",
            $checkout,
            $oldData,
            [
                'pickup_status' => 'Picked up',
                'pickup_date' => now()->toDateTimeString(),
                'payment_status' => 'Paid'
            ]
        );
    }

    public static function invoiceCreated($invoice, $staffUser)
    {
        return self::log(
            'invoice_created',
            "Invoice #{$invoice->id} was created for order by {$staffUser->first_name} {$staffUser->last_name}",
            $invoice,
            null,
            [
                'invoice_id' => $invoice->id,
                'order_reference' => $invoice->order_id ?? $invoice->build_id,
                'staff_id' => $invoice->staff_id,
                'invoice_date' => $invoice->invoice_date
            ]
        );
    }

    // COMPONENT ACTIVITY
    public static function componentCreated($componentType, $component, $staffUser)
    {
        return self::log(
            'component_created',
            "{$componentType} component {$component->brand} {$component->model} was created by {$staffUser->first_name} {$staffUser->last_name}",
            $component,
            null,
            [
                'component_type' => $componentType,
                'brand' => $component->brand,
                'model' => $component->model,
                'price' => $component->price,
                'stock' => $component->stock,
                'build_category_id' => $component->build_category_id,
                'supplier_id' => $component->supplier_id,
            ]
        );
    }

    // Generic component update method (for future use)
    public static function componentUpdated($componentType, $component, $staffUser, $oldData, $newData)
    {
        $changes = [];
        foreach ($oldData as $key => $oldValue) {
            $newValue = $newData[$key] ?? null;
            
            // Handle arrays by converting to JSON string
            if (is_array($oldValue) || is_array($newValue)) {
                $oldValueString = is_array($oldValue) ? json_encode($oldValue) : $oldValue;
                $newValueString = is_array($newValue) ? json_encode($newValue) : $newValue;
                
                if ($oldValueString != $newValueString) {
                    $changes[] = "{$key}: [array data changed]";
                }
                continue;
            }
            
            // Handle regular values
            if ($oldValue != $newValue) {
                $changes[] = "{$key}: {$oldValue} → {$newValue}";
            }
        }

        $changeDescription = empty($changes) ? 'No changes detected' : implode(', ', $changes);

        return self::log(
            'component_updated',
            "{$componentType} component {$component->brand} {$component->model} was updated by {$staffUser->first_name} {$staffUser->last_name}. Changes: {$changeDescription}",
            $component,
            $oldData,
            $newData
        );
    }

    public static function componentImageUpdated($componentType, $component, $staffUser)
    {
        return self::log(
            'component_image_updated',
            "{$componentType} component {$component->brand} {$component->model} image was updated by {$staffUser->first_name} {$staffUser->last_name}",
            $component,
            null,
            ['image_updated' => true]
        );
    }

    public static function component3dModelUpdated($componentType, $component, $staffUser)
    {
        return self::log(
            'component_3d_model_updated',
            "{$componentType} component {$component->brand} {$component->model} 3D model was updated by {$staffUser->first_name} {$staffUser->last_name}",
            $component,
            null,
            ['3d_model_updated' => true]
        );
    }

    // // Price update specific method (for future use)
    // public static function componentPriceUpdated($componentType, $component, $staffUser, $oldPrice, $newPrice)
    // {
    //     return self::log(
    //         'component_price_updated',
    //         "{$componentType} component {$component->brand} {$component->model} price updated from ₱{$oldPrice} to ₱{$newPrice} by {$staffUser->first_name} {$staffUser->last_name}",
    //         $component,
    //         ['price' => $oldPrice],
    //         ['price' => $newPrice]
    //     );
    // }

    // // Stock update specific method (for future use)
    // public static function componentStockUpdated($componentType, $component, $staffUser, $oldStock, $newStock)
    // {
    //     return self::log(
    //         'component_stock_updated',
    //         "{$componentType} component {$component->brand} {$component->model} stock updated from {$oldStock} to {$newStock} by {$staffUser->first_name} {$staffUser->last_name}",
    //         $component,
    //         ['stock' => $oldStock],
    //         ['stock' => $newStock]
    //     );
    // }

    public static function componentDeleted($componentType, $component, $staffUser, $componentData)
    {
        return self::log(
            'component_deleted',
            "{$componentType} component {$component->brand} {$component->model} was deleted by {$staffUser->first_name} {$staffUser->last_name}",
            $component,
            $componentData,
            null
        );
    }

    public static function componentRestored($componentType, $component, $staffUser, $componentData)
    {
        return self::log(
            'component_restored',
            "{$componentType} component {$component->brand} {$component->model} was restored by {$staffUser->first_name} {$staffUser->last_name}",
            $component,
            $componentData,
            [
                'id' => $component->id,
                'brand' => $component->brand,
                'model' => $component->model,
                'price' => $component->price,
                'stock' => $component->stock,
                'restored_at' => now()->toDateTimeString(),
            ]
        );
    }

    public static function componentImageDeleted($componentType, $component, $staffUser)
    {
        return self::log(
            'component_image_deleted',
            "{$componentType} component {$component->brand} {$component->model} image was deleted by {$staffUser->first_name} {$staffUser->last_name}",
            $component,
            ['image_path' => $component->image],
            null
        );
    }

    public static function component3dModelDeleted($componentType, $component, $staffUser)
    {
        return self::log(
            'component_3d_model_deleted',
            "{$componentType} component {$component->brand} {$component->model} 3D model was deleted by {$staffUser->first_name} {$staffUser->last_name}",
            $component,
            ['model_3d_path' => $component->model_3d],
            null
        );
    }

    public static function caseRadiatorSupportAdded($case, $radiatorSupport, $staffUser)
    {
        return self::log(
            'case_radiator_support_added',
            "Radiator support added to case {$case->brand} {$case->model} by {$staffUser->first_name} {$staffUser->last_name}",
            $case,
            null,
            [
                'location' => $radiatorSupport->location,
                'size_mm' => $radiatorSupport->size_mm,
            ]
        );
    }

    public static function caseDriveBaysAdded($case, $driveBays, $staffUser)
    {
        return self::log(
            'case_drive_bays_added',
            "Drive bays configuration added to case {$case->brand} {$case->model} by {$staffUser->first_name} {$staffUser->last_name}",
            $case,
            null,
            [
                '3_5_bays' => $driveBays->{'3_5_bays'},
                '2_5_bays' => $driveBays->{'2_5_bays'},
            ]
        );
    }

    public static function caseFrontUsbPortsAdded($case, $frontUsbPorts, $staffUser)
    {
        return self::log(
            'case_front_usb_ports_added',
            "Front USB ports configuration added to case {$case->brand} {$case->model} by {$staffUser->first_name} {$staffUser->last_name}",
            $case,
            null,
            [
                'usb_3_0_type_A' => $frontUsbPorts->usb_3_0_type_A,
                'usb_2_0' => $frontUsbPorts->usb_2_0,
                'usb_c' => $frontUsbPorts->usb_c,
                'audio_jacks' => $frontUsbPorts->audio_jacks,
            ]
        );
    }

    public static function caseRadiatorSupportsUpdated($case, $staffUser, $oldSupports, $newSupports, $oldCount, $newCount)
    {
        $oldLocations = collect($oldSupports)->pluck('location')->implode(', ');
        $newLocations = collect($newSupports)->pluck('location')->implode(', ');
        
        $description = "Case {$case->brand} {$case->model} radiator supports updated by {$staffUser->first_name} {$staffUser->last_name}. ";
        $description .= "Count changed from {$oldCount} to {$newCount}. ";
        
        if ($oldLocations !== $newLocations) {
            $description .= "Locations changed from [{$oldLocations}] to [{$newLocations}]";
        }

        return self::log(
            'case_radiator_supports_updated',
            $description,
            $case,
            ['radiator_supports' => $oldSupports, 'count' => $oldCount],
            ['radiator_supports' => $newSupports, 'count' => $newCount]
        );
    }

    public static function caseDriveBaysUpdated($case, $staffUser, $oldData, $newData)
    {
        $changes = [];
        
        if ($oldData) {
            if ($oldData['3_5_bays'] != $newData['3_5_bays']) {
                $changes[] = "3.5\" bays: {$oldData['3_5_bays']} → {$newData['3_5_bays']}";
            }
            if ($oldData['2_5_bays'] != $newData['2_5_bays']) {
                $changes[] = "2.5\" bays: {$oldData['2_5_bays']} → {$newData['2_5_bays']}";
            }
        } else {
            $changes[] = "Drive bays added: 3.5\"={$newData['3_5_bays']}, 2.5\"={$newData['2_5_bays']}";
        }

        $changeDescription = empty($changes) ? 'No changes' : implode(', ', $changes);

        return self::log(
            'case_drive_bays_updated',
            "Case {$case->brand} {$case->model} drive bays updated by {$staffUser->first_name} {$staffUser->last_name}. {$changeDescription}",
            $case,
            $oldData ? ['drive_bays' => $oldData] : null,
            ['drive_bays' => $newData]
        );
    }

    public static function caseFrontUsbPortsUpdated($case, $staffUser, $oldData, $newData)
    {
        $changes = [];
        
        if ($oldData) {
            if ($oldData['usb_3_0_type_A'] != $newData['usb_3_0_type_A']) {
                $changes[] = "USB 3.0: {$oldData['usb_3_0_type_A']} → {$newData['usb_3_0_type_A']}";
            }
            if ($oldData['usb_2_0'] != $newData['usb_2_0']) {
                $changes[] = "USB 2.0: {$oldData['usb_2_0']} → {$newData['usb_2_0']}";
            }
            if ($oldData['usb_c'] != $newData['usb_c']) {
                $changes[] = "USB-C: {$oldData['usb_c']} → {$newData['usb_c']}";
            }
            if ($oldData['audio_jacks'] != $newData['audio_jacks']) {
                $changes[] = "Audio Jacks: {$oldData['audio_jacks']} → {$newData['audio_jacks']}";
            }
        } else {
            $changes[] = "Front USB ports added: USB 3.0={$newData['usb_3_0_type_A']}, USB 2.0={$newData['usb_2_0']}, USB-C={$newData['usb_c']}, Audio={$newData['audio_jacks']}";
        }

        $changeDescription = empty($changes) ? 'No changes' : implode(', ', $changes);

        return self::log(
            'case_front_usb_ports_updated',
            "Case {$case->brand} {$case->model} front USB ports updated by {$staffUser->first_name} {$staffUser->last_name}. {$changeDescription}",
            $case,
            $oldData ? ['front_usb_ports' => $oldData] : null,
            ['front_usb_ports' => $newData]
        );
    }

    public static function supplierDeactivated($supplier, $staffUser, $oldStatus)
    {
        return self::log(
            'supplier_deactivated',
            "Supplier {$supplier->name} was deactivated by {$staffUser->first_name} {$staffUser->last_name}",
            $supplier,
            ['is_active' => $oldStatus],
            ['is_active' => false]
        );
    }

    public static function supplierActivated($supplier, $staffUser, $oldStatus)
    {
        return self::log(
            'supplier_activated',
            "Supplier {$supplier->name} was activated by {$staffUser->first_name} {$staffUser->last_name}",
            $supplier,
            ['is_active' => $oldStatus],
            ['is_active' => true]
        );
    }

    public static function supplierCreated($supplier, $staffUser)
    {
        return self::log(
            'supplier_created',
            "Supplier {$supplier->name} was created by {$staffUser->first_name} {$staffUser->last_name}",
            $supplier,
            null,
            [
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'is_active' => $supplier->is_active,
            ]
        );
    }

    public static function supplierUpdated($supplier, $staffUser, $oldData, $newData)
    {
        $changes = [];
        foreach ($oldData as $key => $oldValue) {
            if ($oldValue != $newData[$key]) {
                $changes[] = "{$key}: {$oldValue} → {$newData[$key]}";
            }
        }

        $changeDescription = empty($changes) ? 'No changes detected' : implode(', ', $changes);

        return self::log(
            'supplier_updated',
            "Supplier {$supplier->name} was updated by {$staffUser->first_name} {$staffUser->last_name}. Changes: {$changeDescription}",
            $supplier,
            $oldData,
            $newData
        );
    }

    public static function supplierDeleted($supplier, $staffUser)
    {
        return self::log(
            'supplier_deleted',
            "Supplier {$supplier->name} was permanently deleted by {$staffUser->first_name} {$staffUser->last_name}",
            $supplier,
            $supplier->toArray(),
            null
        );
    }

    public static function stockIn($component, $staffUser, $oldStock, $newStock, $quantityAdded)
    {
        return self::log(
            'stock_in',
            "Stock in for {$component->brand} {$component->model}: {$quantityAdded} units added. Stock changed from {$oldStock} to {$newStock} by {$staffUser->first_name} {$staffUser->last_name}",
            $component,
            ['stock' => $oldStock],
            [
                'stock' => $newStock,
                'quantity_added' => $quantityAdded,
                'change_type' => 'stock_in'
            ]
        );
    }

    public static function stockOut($component, $staffUser, $oldStock, $newStock, $quantityRemoved)
    {
        return self::log(
            'stock_out',
            "Stock out for {$component->brand} {$component->model}: {$quantityRemoved} units removed. Stock changed from {$oldStock} to {$newStock} by {$staffUser->first_name} {$staffUser->last_name}",
            $component,
            ['stock' => $oldStock],
            [
                'stock' => $newStock,
                'quantity_removed' => $quantityRemoved,
                'change_type' => 'stock_out'
            ]
        );
    }

    public static function stockOutFailed($component, $staffUser, $currentStock, $requestedQuantity)
    {
        return self::log(
            'stock_out_failed',
            "Stock out failed for {$component->brand} {$component->model}. Requested: {$requestedQuantity}, Available: {$currentStock} by {$staffUser->first_name} {$staffUser->last_name}",
            $component,
            null,
            [
                'current_stock' => $currentStock,
                'requested_quantity' => $requestedQuantity,
                'reason' => 'insufficient_stock'
            ]
        );
    }

    public static function stockAdjusted($component, $staffUser, $oldStock, $newStock, $reason = null)
    {
        $change = $newStock - $oldStock;
        $changeType = $change > 0 ? 'increased' : 'decreased';
        $changeAmount = abs($change);

        $description = "Stock adjusted for {$component->brand} {$component->model}: {$changeAmount} units {$changeType}. Stock changed from {$oldStock} to {$newStock} by {$staffUser->first_name} {$staffUser->last_name}";
        
        if ($reason) {
            $description .= ". Reason: {$reason}";
        }

        return self::log(
            'stock_adjusted',
            $description,
            $component,
            ['stock' => $oldStock],
            [
                'stock' => $newStock,
                'adjustment_amount' => $change,
                'reason' => $reason
            ]
        );
    }

    public static function lowStockWarning($component, $staffUser, $currentStock, $threshold = 5)
    {
        return self::log(
            'low_stock_warning',
            "Low stock warning for {$component->brand} {$component->model}. Current stock: {$currentStock}, Threshold: {$threshold}",
            $component,
            null,
            [
                'current_stock' => $currentStock,
                'threshold' => $threshold,
                'alert_type' => 'low_stock'
            ]
        );
    }

    public static function outOfStock($component, $staffUser)
    {
        return self::log(
            'out_of_stock',
            "{$component->brand} {$component->model} is now out of stock",
            $component,
            null,
            [
                'current_stock' => 0,
                'alert_type' => 'out_of_stock'
            ]
        );
    }

    public static function softwareCreated($software, $staffUser)
    {
        return self::log(
            'software_created',
            "Software '{$software->name}' was created by {$staffUser->first_name} {$staffUser->last_name}",
            $software,
            null,
            [
                'name' => $software->name,
                'build_category_id' => $software->build_category_id,
                'os_min' => $software->os_min,
                'cpu_min' => $software->cpu_min,
                'gpu_min' => $software->gpu_min,
                'ram_min' => $software->ram_min,
                'storage_min' => $software->storage_min,
                'cpu_reco' => $software->cpu_reco,
                'gpu_reco' => $software->gpu_reco,
                'ram_reco' => $software->ram_reco,
                'storage_reco' => $software->storage_reco,
            ]
        );
    }

    public static function softwareUpdated($software, $staffUser, $oldData, $newData)
    {
        $changes = [];
        
        // Track specific field changes
        if ($oldData['name'] !== $newData['name']) {
            $changes[] = "name: {$oldData['name']} → {$newData['name']}";
        }
        if ($oldData['build_category_id'] !== $newData['build_category_id']) {
            $changes[] = "build category changed";
        }
        if ($oldData['os_min'] !== $newData['os_min']) {
            $changes[] = "min OS: {$oldData['os_min']} → {$newData['os_min']}";
        }
        if ($oldData['cpu_min'] !== $newData['cpu_min']) {
            $changes[] = "min CPU: {$oldData['cpu_min']} → {$newData['cpu_min']}";
        }
        if ($oldData['gpu_min'] !== $newData['gpu_min']) {
            $changes[] = "min GPU: {$oldData['gpu_min']} → {$newData['gpu_min']}";
        }
        if ($oldData['ram_min'] !== $newData['ram_min']) {
            $changes[] = "min RAM: {$oldData['ram_min']} → {$newData['ram_min']}";
        }
        if ($oldData['storage_min'] !== $newData['storage_min']) {
            $changes[] = "min storage: {$oldData['storage_min']} → {$newData['storage_min']}";
        }

        $changeDescription = empty($changes) ? 'No changes detected' : implode(', ', $changes);

        return self::log(
            'software_updated',
            "Software '{$software->name}' was updated by {$staffUser->first_name} {$staffUser->last_name}. Changes: {$changeDescription}",
            $software,
            $oldData,
            $newData
        );
    }

    public static function softwareDeleted($software, $staffUser, $softwareData)
    {
        return self::log(
            'software_deleted',
            "Software '{$software->name}' was deleted by {$staffUser->first_name} {$staffUser->last_name}",
            $software,
            $softwareData,
            null
        );
    }

    public static function softwareRestored($software, $staffUser, $softwareData)
    {
        return self::log(
            'software_restored',
            "Software '{$software->name}' was restored by {$staffUser->first_name} {$staffUser->last_name}",
            $software,
            $softwareData,
            [
                'name' => $software->name,
                'restored_at' => now()->toDateTimeString(),
            ]
        );
    }

    public static function softwareIconUploaded($software, $staffUser, $filename)
    {
        $softwareName = $software ? $software->name : 'new software';
        return self::log(
            'software_icon_uploaded',
            "Software icon uploaded for '{$softwareName}' by {$staffUser->first_name} {$staffUser->last_name}. File: {$filename}",
            $software,
            null,
            ['icon_filename' => $filename]
        );
    }

    public static function softwareIconUpdated($software, $staffUser, $imagePath)
    {
        return self::log(
            'software_icon_updated',
            "Software icon updated for '{$software->name}' by {$staffUser->first_name} {$staffUser->last_name}. New file: {$imagePath}",
            $software,
            ['old_icon' => $software->icon],
            ['new_icon' => $imagePath]
        );
    }

    public static function softwareIconDeleted($software, $staffUser)
    {
        return self::log(
            'software_icon_deleted',
            "Software icon deleted for '{$software->name}' by {$staffUser->first_name} {$staffUser->last_name}",
            $software,
            ['icon_path' => $software->icon],
            null
        );
    }
}