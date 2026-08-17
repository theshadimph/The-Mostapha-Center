<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_renders(): void
    {
        $this->get(route('contact'))->assertOk();
    }

    public function test_contact_form_requires_valid_fields(): void
    {
        $response = $this->post(route('contact.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
    }

    public function test_contact_form_sends_mail_when_recipient_is_configured(): void
    {
        Mail::fake();
        config(['mail.contact_recipient' => 'librarian@mostapha-center.test']);

        $response = $this->post(route('contact.store'), [
            'name' => 'زائر',
            'email' => 'visitor@example.com',
            'message' => 'استفسار عن المكتبة',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        Mail::assertSent(ContactMessage::class, fn (ContactMessage $mail) => $mail->senderEmail === 'visitor@example.com');
    }

    public function test_contact_form_does_not_error_when_no_recipient_is_configured(): void
    {
        Mail::fake();
        config(['mail.contact_recipient' => null]);

        $response = $this->post(route('contact.store'), [
            'name' => 'زائر',
            'email' => 'visitor@example.com',
            'message' => 'استفسار',
        ]);

        $response->assertRedirect();
        Mail::assertNothingSent();
    }

    public function test_about_activities_and_donations_pages_render(): void
    {
        $this->get(route('about'))->assertOk();
        $this->get(route('activities'))->assertOk();
        $this->get(route('donations'))->assertOk();
    }
}
