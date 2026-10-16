<?php

return [
    // URL prefix for every module route: /blood-bank/...
    'route_prefix' => 'blood-bank',

    // Units per blood group + component. At or below "low" the matrix cell turns amber,
    // at or below "critical" it turns red and offers the one-click donor request.
    'stock_thresholds' => [
        'whole_blood'      => ['low' => 10, 'critical' => 4],
        'packed_red_cells' => ['low' => 10, 'critical' => 4],
        'platelets'        => ['low' => 6,  'critical' => 2],
        'plasma'           => ['low' => 10, 'critical' => 4],
    ],

    // A unit enters the "expiring soon" list when it is this many days from expiry (per component).
    'expiry_alert_days' => [
        'whole_blood'      => 7,
        'packed_red_cells' => 7,
        'platelets'        => 2,
        'plasma'           => 30,
    ],

    // Inside this many hours a unit is flagged "critical" regardless of component.
    'expiry_critical_hours' => 48,

    // The "Expiring" filter on the blood units list.
    'expiring_filter_days' => 7,

    'emergency' => [
        'default_radius_km'        => 15,
        'max_radius_km'            => 100,
        'max_donors_per_broadcast' => 200,
        'request_ttl_hours'        => 24,   // how long a request stays open for donor replies
        'response_link_ttl_hours'  => 24,   // lifetime of the signed accept/decline links
        // Optional extra channel (SMS gateway). The class must call $notification->toSms($notifiable).
        'extra_channel' => null,
    ],

    // Minimum gap between whole-blood donations before a donor is contacted again.
    'donor_min_interval_days' => 56,

    // Fire the broadcast event so open dashboards refresh instantly (needs Reverb/Pusher + Echo).
    'broadcast_stock_updates' => true,
];
