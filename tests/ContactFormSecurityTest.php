<?php

use Illuminate\Support\Facades\Mail;
use Plugins\ContactForm\ContactFormServiceProvider;
use Plugins\ContactForm\src\Mail\ContactFormSubmitted;
use Plugins\ContactForm\src\Models\ContactSubmission;

beforeEach(function () {
    $this->app->register(ContactFormServiceProvider::class);
    $this->artisan('migrate', ['--path' => 'plugins/ContactForm/database/migrations']);
    config(['mail.admin_address' => 'admin@example.test']);
    Mail::fake();
});

it('rejects malformed or oversized contact fields without storing or queuing mail', function (array $invalid, string $field) {
    $this->postJson('/contact-form/submit', array_merge([
        'name' => 'Visitor', 'email' => 'visitor@example.test', 'message' => 'Hello',
    ], $invalid))->assertUnprocessable()->assertJsonValidationErrors($field);
    expect(ContactSubmission::count())->toBe(0);
    Mail::assertNothingQueued();
})->with([
    [['name' => ['array']], 'name'],
    [['email' => ['array']], 'email'],
    [['email' => "visitor@example.test\r\nBcc: other@example.test"], 'email'],
    [['subject' => ['array']], 'subject'],
    [['subject' => "Hello\r\nBcc: other@example.test"], 'subject'],
    [['name' => "Visitor\r\nBcc: other@example.test"], 'name'],
    [['message' => str_repeat('x', 5001)], 'message'],
]);

it('escapes untrusted contact text when rendering notification emails', function () {
    $this->postJson('/contact-form/submit', [
        'name' => '<img src=x onerror=alert(1)>', 'email' => 'visitor@example.test',
        'subject' => '<script>alert(2)</script>', 'message' => '<iframe src="javascript:alert(3)"></iframe>',
    ])->assertOk();
    $html = (new ContactFormSubmitted(ContactSubmission::firstOrFail()))->render();
    expect($html)->not->toContain('<script>', '<iframe', '<img src=x');
    Mail::assertQueued(ContactFormSubmitted::class, 1);
});

it('limits contact submission abuse before creating more records', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->postJson('/contact-form/submit', [
            'name' => 'Visitor', 'email' => "visitor{$i}@example.test", 'message' => 'Hello',
        ])->assertOk();
    }
    $this->postJson('/contact-form/submit', [
        'name' => 'Visitor', 'email' => 'visitor@example.test', 'message' => 'Hello',
    ])->assertTooManyRequests();
    expect(ContactSubmission::count())->toBe(10);
    $this->postJson('/forgot-password', ['email' => 'visitor@example.test'])->assertRedirect();
});
