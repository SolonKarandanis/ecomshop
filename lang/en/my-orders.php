<?php

return [
    'title'=>'My Orders',
    'columns'=>[
        'order'=>'Order',
        'date'=>'Date',
        'order_status'=>'Order Status',
        'payment_status'=>'Payment Status',
        'amount'=>'Amount',
        'action'=>'Action',
    ],
    'search'=>[
        'title'=>'Search Orders',
        'fields'=>[
            'order_status'=>'Order Status',
            'payment_status'=>'Payment Status',
            'from_date'=>'From Date',
            'to_date'=>'To Date',
            'min_amount'=>'Min Amount',
            'max_amount'=>'Max Amount',
        ],
    ],
    'buttons'=>[
        'view'=>'View Details',
        'search'=>'Search',
        'reset'=>'Reset',
        'export'=>'Export',
    ],
];
