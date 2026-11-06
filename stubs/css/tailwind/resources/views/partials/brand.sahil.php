{{--
    SahilPHP brand lockup for the Tailwind profile.

    `public/logo.png` is the official lockup (mark + wordmark) and
    `public/logo-mark.png` the mark alone, for tight spaces such as footers.
--}}
<a class="{{ class ?? 'inline-flex shrink-0 items-center' }}" href="{{ home_url ?? '/' }}" aria-label="SahilPHP home">
    @if ((variant ?? 'wordmark') == 'mark')
        <img class="h-9 w-auto" src="{{ asset('logo-mark.png') }}" alt="SahilPHP"
            width="109" height="160" decoding="async">
    @else
        <img class="h-8 w-auto" src="{{ asset('logo.png') }}" alt="SahilPHP"
            width="380" height="160" decoding="async">
    @endif
</a>
