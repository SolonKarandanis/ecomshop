<?php

return [
    'add_to_cart'=>[
        'title'=>'Add To Cart',
        'success'=>'Product added to cart successfully!',
        'error'=>'Failed to add product to cart!',
        'unauthorized'=>'Only buyers can add products to cart!',
    ],
    'update_quantity'=>[
        'title'=>'Update Quantity',
        'success'=>'Product quantity updated successfully!',
        'error'=>'Failed to update product quantity!',
    ],
    'remove_from_cart'=>[
        'title'=>'Remove From Cart',
        'success'=>'Product removed from cart successfully!',
        'error'=>'Failed to remove product from cart!',
    ],
    'clear_cart'=>[
        'title'=>'Clear Cart',
        'success'=>'Cart cleared successfully!',
        'error'=>'Failed to clear cart!',
    ],
    'order_paid'=>[
        'title'=>'Order Paid',
        'success'=>'Order paid successfully!',
        'error'=>'Failed to pay order!',
    ],
    'export_orders'=>[
        'title'=>'Export Failed',
        'limit_error'=>'You cannot export more than 10,000 orders at once. Current results: :count',
    ],
    'update_profile'=>[
        'title'=>'Update Profile',
        'success'=>'Profile updated successfully!',
        'email_taken'=>'This email address is already in use by another account.',
    ],
    'change_password'=>[
        'title'=>'Change Password',
        'success'=>'Password changed successfully!',
        'wrong_current'=>'The current password you entered is incorrect.',
    ],
    'submit_review'=>[
        'title'=>'Submit Review',
        'success'=>'Review submitted successfully!',
        'error'=>'Failed to submit review!',
        'not_eligible'=>'You are not eligible to review this product.',
    ],
    'cart_exceptions'=>[
        'save_cart'=>'Failed to save cart',
        'update_items'=>'Failed to update cart items',
        'delete_items'=>'Failed to delete cart items',
        'clear_cart'=>'Failed to clear cart',
        'empty_cart'=>'Cart is empty',
    ],
    'order_exceptions'=>[
        'create_order'=>'Failed to create new order',
        'checkout'=>'Something went wrong during checkout!',
        'stripe_processing'=>'Something went wrong during stripe order processing!',
    ],
    'payment_exceptions'=>[
        'create_stripe_session'=>'Failed to create Stripe session',
        'retrieve_stripe_session'=>'Failed to retrieve Stripe session',
        'unsupported_payment_method'=>'Unsupported payment method: :method',
    ],
    'product_exceptions'=>[
        'not_found'=>'Product with ID :id not found.',
    ],
];
