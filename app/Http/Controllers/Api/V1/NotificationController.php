<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    private const PRESENTATION_BY_TYPE = [
        'appointment_confirmed'    => ['icon' => 'calendarCheck', 'colorHex' => '#4CAF50'],
        'appointment_confirmation' => ['icon' => 'calendarCheck', 'colorHex' => '#4CAF50'],
        'appointment_booked'       => ['icon' => 'calendarCheck', 'colorHex' => '#4CAF50'],
        'medication_reminder'      => ['icon' => 'pill', 'colorHex' => '#009688'],
        'lab_results_ready'        => ['icon' => 'fileText', 'colorHex' => '#2196F3'],
        'lab_report'               => ['icon' => 'fileText', 'colorHex' => '#2196F3'],
        'prescription_issued'      => ['icon' => 'checkCircle2', 'colorHex' => '#FF9800'],
        'prescription_renewed'     => ['icon' => 'checkCircle2', 'colorHex' => '#FF9800'],
        'doctor_alert'             => ['icon' => 'stethoscope', 'colorHex' => '#9C27B0'],
        'api_post_action'          => ['icon' => 'checkCircle2', 'colorHex' => '#4CAF50'],
        'system_update'            => ['icon' => 'info', 'colorHex' => '#9E9E9E'],
    ];

    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->get(['id', 'data', 'created_at', 'read_at'])
            ->map(function (DatabaseNotification $notification): array {
                $data = $notification->data;
                $presentation = self::PRESENTATION_BY_TYPE[$data['type'] ?? ''] ?? [
                    'icon'     => 'info',
                    'colorHex' => '#9E9E9E',
                ];

                return [
                    'id'          => $notification->id,
                    'title'       => $data['title'] ?? 'Notification',
                    'message'     => $data['message'] ?? '',
                    'time'        => $notification->created_at->diffForHumans(),
                    'icon'        => $data['icon'] ?? $presentation['icon'],
                    'colorHex'    => $data['colorHex'] ?? $presentation['colorHex'],
                    'isRead'      => $notification->read_at !== null,
                    'actionRoute' => $data['actionRoute'] ?? $data['url'] ?? null,
                ];
            })
            ->values();

        return response()->json([
            'success'    => true,
            'statusCode' => JsonResponse::HTTP_OK,
            'message'    => 'Notifications loaded',
            'data'       => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->whereKey($id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success'    => true,
            'statusCode' => JsonResponse::HTTP_OK,
            'message'    => 'Notification marked as read',
        ]);
    }
}
