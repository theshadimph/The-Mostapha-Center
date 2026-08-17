<x-mail::message>
# {{ __('رسالة جديدة من نموذج التواصل') }}

**{{ __('الاسم') }}:** {{ $senderName }}
**{{ __('البريد الإلكتروني') }}:** {{ $senderEmail }}

{{ $messageBody }}

{{ __('تم إرسال هذه الرسالة من نموذج التواصل في موقع :name.', ['name' => config('app.name')]) }}
</x-mail::message>
