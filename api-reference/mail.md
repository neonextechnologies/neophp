# Mail Classes

Complete reference for email functionality.

## Mail

Mail manager and facade.

### Sending Mail

#### `to($users)`

Set recipients.

```php
Mail::to('user@example.com')->send(new WelcomeEmail($user));
Mail::to(['user1@example.com', 'user2@example.com'])->send($mailable);
Mail::to($user)->send($mailable);
```

#### `cc($users)`

Add CC recipients.

```php
Mail::to($user)
    ->cc('manager@example.com')
    ->send($mailable);
```

#### `bcc($users)`

Add BCC recipients.

```php
Mail::to($user)
    ->bcc(['admin@example.com', 'archive@example.com'])
    ->send($mailable);
```

#### `send($mailable)`

Send mail.

```php
Mail::to($user)->send(new OrderShipped($order));
```

#### `queue($mailable)`

Queue mail.

```php
Mail::to($user)->queue(new WelcomeEmail($user));
```

#### `later($delay, $mailable)`

Queue with delay.

```php
Mail::to($user)->later(60, new ReminderEmail($reminder));
Mail::to($user)->later(now()->addHour(), $mailable);
```

### Mailer Selection

#### `mailer($name = null)`

Use specific mailer.

```php
Mail::mailer('smtp')->to($user)->send($mailable);
Mail::mailer('ses')->to($user)->queue($mailable);
```

---

## Mailable

Base mailable class.

### Creating Mailables

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function build()
    {
        return $this
            ->subject('Welcome to ' . config('app.name'))
            ->view('emails.welcome')
            ->with([
                'userName' => $this->user->name
            ]);
    }
}
```

### Mailable Methods

#### `from($address, $name = null)`

Set sender.

```php
public function build()
{
    return $this
        ->from('noreply@example.com', 'App Name')
        ->view('emails.welcome');
}
```

#### `subject($subject)`

Set subject.

```php
public function build()
{
    return $this
        ->subject('Order Confirmation')
        ->view('emails.order');
}
```

#### `view($view, $data = [])`

Set view.

```php
public function build()
{
    return $this->view('emails.welcome', [
        'name' => $this->user->name
    ]);
}
```

#### `text($view, $data = [])`

Set plain text view.

```php
public function build()
{
    return $this
        ->view('emails.welcome')
        ->text('emails.welcome-text');
}
```

#### `with($key, $value = null)`

Add view data.

```php
public function build()
{
    return $this
        ->view('emails.order')
        ->with('orderNumber', $this->order->number)
        ->with('total', $this->order->total);
}
```

#### `attach($file, $options = [])`

Attach file.

```php
public function build()
{
    return $this
        ->view('emails.invoice')
        ->attach(storage_path('invoices/' . $this->invoice->pdf));
}

// With options
return $this->attach($path, [
    'as' => 'invoice.pdf',
    'mime' => 'application/pdf'
]);
```

#### `attachFromStorage($path, $name = null, $options = [])`

Attach from storage.

```php
public function build()
{
    return $this
        ->view('emails.report')
        ->attachFromStorage('reports/monthly.pdf', 'Report.pdf');
}
```

#### `attachData($data, $name, $options = [])`

Attach raw data.

```php
public function build()
{
    $pdf = $this->generatePDF();
    
    return $this
        ->view('emails.document')
        ->attachData($pdf, 'document.pdf', [
            'mime' => 'application/pdf'
        ]);
}
```

#### `priority($level)`

Set priority (1-5, 1 = highest).

```php
public function build()
{
    return $this
        ->priority(1)
        ->view('emails.urgent');
}
```

#### `replyTo($address, $name = null)`

Set reply-to.

```php
public function build()
{
    return $this
        ->replyTo('support@example.com', 'Support Team')
        ->view('emails.contact');
}
```

---

## Markdown Mailables

### Creating Markdown Mailable

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;

class OrderShipped extends Mailable
{
    public $order;
    
    public function __construct($order)
    {
        $this->order = $order;
    }
    
    public function build()
    {
        return $this->markdown('emails.orders.shipped');
    }
}
```

### Markdown View

```blade
@component('mail::message')
# Order Shipped

Your order has been shipped!

**Order Number:** {{ $order->number }}

@component('mail::button', ['url' => $url])
Track Shipment
@endcomponent

@component('mail::table')
| Item | Quantity | Price |
|------|----------|-------|
@foreach($order->items as $item)
| {{ $item->name }} | {{ $item->quantity }} | ${{ $item->price }} |
@endforeach
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
```

### Markdown Components

```blade
{{-- Button --}}
@component('mail::button', ['url' => $url, 'color' => 'success'])
Action Button
@endcomponent

{{-- Panel --}}
@component('mail::panel')
This is a panel with important content.
@endcomponent

{{-- Table --}}
@component('mail::table')
| Column 1 | Column 2 |
|----------|----------|
| Value 1  | Value 2  |
@endcomponent

{{-- Promotion --}}
@component('mail::promotion')
Use code SAVE20 for 20% off your next purchase!
@endcomponent

{{-- Subcopy --}}
@component('mail::subcopy')
This is smaller text at the bottom of the email.
@endcomponent
```

---

## Practical Examples

### Welcome Email

```php
<?php

namespace App\Mail;

use Neo\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function build()
    {
        return $this
            ->subject('Welcome to ' . config('app.name') . '!')
            ->markdown('emails.welcome')
            ->with([
                'userName' => $this->user->name,
                'verifyUrl' => route('verify.email', ['token' => $this->user->verification_token])
            ]);
    }
}
```

```blade
@component('mail::message')
# Welcome, {{ $userName }}!

Thank you for joining {{ config('app.name') }}. We're excited to have you on board!

@component('mail::button', ['url' => $verifyUrl])
Verify Email Address
@endcomponent

If you have any questions, feel free to reply to this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
```

### Order Confirmation

```php
class OrderConfirmation extends Mailable
{
    public $order;
    
    public function __construct($order)
    {
        $this->order = $order;
    }
    
    public function build()
    {
        return $this
            ->subject('Order Confirmation #' . $this->order->number)
            ->markdown('emails.orders.confirmation')
            ->attach(storage_path('invoices/' . $this->order->invoice_pdf));
    }
}
```

```blade
@component('mail::message')
# Order Confirmation

Thank you for your order!

**Order Number:** {{ $order->number }}<br>
**Order Date:** {{ $order->created_at->format('M d, Y') }}<br>
**Total:** ${{ number_format($order->total, 2) }}

## Order Items

@component('mail::table')
| Product | Quantity | Price |
|---------|----------|-------|
@foreach($order->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} |
@endforeach
@endcomponent

## Shipping Address

{{ $order->shipping_address->street }}<br>
{{ $order->shipping_address->city }}, {{ $order->shipping_address->state }} {{ $order->shipping_address->postal_code }}

@component('mail::button', ['url' => route('orders.show', $order)])
View Order Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
```

### Password Reset

```php
class PasswordReset extends Mailable
{
    public $token;
    public $email;
    
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }
    
    public function build()
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $this->email
        ]);
        
        return $this
            ->subject('Reset Your Password')
            ->markdown('emails.auth.password-reset')
            ->with('resetUrl', $url);
    }
}
```

```blade
@component('mail::message')
# Reset Password

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

This password reset link will expire in 60 minutes.

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}

@component('mail::subcopy')
If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: [{{ $resetUrl }}]({{ $resetUrl }})
@endcomponent
@endcomponent
```

### Invoice Email

```php
class InvoiceEmail extends Mailable
{
    public $invoice;
    
    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }
    
    public function build()
    {
        $pdf = $this->generateInvoicePDF($this->invoice);
        
        return $this
            ->subject('Invoice #' . $this->invoice->number)
            ->markdown('emails.invoice')
            ->attachData($pdf, 'invoice-' . $this->invoice->number . '.pdf', [
                'mime' => 'application/pdf'
            ]);
    }
    
    protected function generateInvoicePDF($invoice)
    {
        // Generate PDF logic
        return $pdfContent;
    }
}
```

### Newsletter

```php
class Newsletter extends Mailable
{
    public $posts;
    public $user;
    
    public function __construct($posts, $user)
    {
        $this->posts = $posts;
        $this->user = $user;
    }
    
    public function build()
    {
        return $this
            ->subject('Weekly Newsletter - ' . now()->format('M d, Y'))
            ->markdown('emails.newsletter')
            ->with([
                'unsubscribeUrl' => route('newsletter.unsubscribe', [
                    'token' => $this->user->newsletter_token
                ])
            ]);
    }
}
```

```blade
@component('mail::message')
# This Week's Highlights

Hi {{ $user->name }},

Here are the top posts from this week:

@foreach($posts as $post)
## {{ $post->title }}

{{ $post->excerpt }}

@component('mail::button', ['url' => route('posts.show', $post)])
Read More
@endcomponent

---
@endforeach

@component('mail::subcopy')
You're receiving this email because you subscribed to our newsletter.
[Unsubscribe]({{ $unsubscribeUrl }})
@endcomponent
@endcomponent
```

---

## Queued Mailables

### Queue Mailable

```php
class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Neo\Queue\InteractsWithQueue;
    
    public $tries = 3;
    public $timeout = 30;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function build()
    {
        return $this->view('emails.welcome');
    }
}
```

### Queue with Delay

```php
Mail::to($user)
    ->later(now()->addMinutes(10), new WelcomeEmail($user));
```

### Queue to Specific Queue

```php
class WelcomeEmail extends Mailable implements ShouldQueue
{
    public $queue = 'emails';
    
    // ...
}
```

---

## Mail Configuration

### SMTP Configuration

```php
// config/mail.php
'mailers' => [
    'smtp' => [
        'transport' => 'smtp',
        'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
        'port' => env('MAIL_PORT', 587),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
    ],
]
```

### Multiple Mailers

```php
'mailers' => [
    'smtp' => [
        'transport' => 'smtp',
        // SMTP config
    ],
    
    'ses' => [
        'transport' => 'ses',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    
    'mailgun' => [
        'transport' => 'mailgun',
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],
]
```

### Global From Address

```php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
    'name' => env('MAIL_FROM_NAME', 'App Name'),
],
```

---

## Testing Mailables

### Preview Mailables

```php
Route::get('/mailable', function() {
    $order = Order::find(1);
    
    return new OrderShipped($order);
});
```

### Test Mailables

```php
use Neo\Support\Facades\Mail;

public function test_order_confirmation_sent()
{
    Mail::fake();
    
    $order = Order::factory()->create();
    
    // Place order
    $this->post('/orders', $orderData);
    
    Mail::assertSent(OrderConfirmation::class, function($mail) use ($order) {
        return $mail->order->id === $order->id;
    });
}

public function test_welcome_email_queued()
{
    Mail::fake();
    
    $user = User::factory()->create();
    
    Mail::assertQueued(WelcomeEmail::class);
    Mail::assertNotSent(WelcomeEmail::class);
}
```

---

## Best Practices

### Mailable Organization

```php
App/
  Mail/
    Auth/
      PasswordReset.php
      EmailVerification.php
    Orders/
      OrderConfirmation.php
      OrderShipped.php
      OrderCancelled.php
    User/
      WelcomeEmail.php
      AccountDeleted.php
```

### Use Markdown for Consistency

```php
// Consistent branding and styling
public function build()
{
    return $this->markdown('emails.template');
}
```

### Queue Heavy Emails

```php
// Queue emails with attachments or complex rendering
class ReportEmail extends Mailable implements ShouldQueue
{
    // ...
}
```

### Localization

```php
public function build()
{
    return $this
        ->subject(__('mail.welcome.subject'))
        ->markdown('emails.welcome')
        ->with([
            'greeting' => __('mail.welcome.greeting', ['name' => $this->user->name])
        ]);
}
```

---

## Next Steps

- [Notification Classes](notification.md)
- [Queue Classes](queue.md)
- [Events & Listeners](events.md)
- [Testing Guide](../tutorials/testing-guide.md)
