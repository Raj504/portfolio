<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Handle a message sent from the contact form.
     */
    public function store(Request $request): RedirectResponse
    {
        // Bots tend to fill every field they find, including the hidden one.
        // Pretend it worked rather than telling them why it did not.
        if (filled($request->input('website'))) {
            return $this->done();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:20', 'max:4000'],
        ]);

        // Store first: the inbox in the admin panel is the durable record,
        // and a mail transport failure should not lose the message.
        ContactMessage::create($data + ['ip' => $request->ip()]);

        $to = Profile::current()->email ?: config('portfolio.email');

        // Plain text keeps this dependency-free. With MAIL_MAILER=log (the
        // Laravel default) the message lands in storage/logs instead of an inbox.
        Mail::raw(
            "From: {$data['name']} <{$data['email']}>\n\n{$data['message']}",
            fn ($mail) => $mail->to($to)
                ->replyTo($data['email'], $data['name'])
                ->subject("[Portfolio] {$data['subject']}")
        );

        return $this->done();
    }

    /**
     * Redirect back to the contact section with a confirmation.
     */
    protected function done(): RedirectResponse
    {
        return redirect()
            ->to(route('home').'#contact')
            ->with('contact.success', 'Message received. I will get back to you shortly.');
    }
}
