@extends('layouts.app')

@section('content')
    @php
        // The layout defines its own $profile for the nav and footer, but
        // sections are captured before the layout renders, so it is resolved
        // again here for the includes below.
        $profile = $site->profile();
    @endphp

    @include('sections.hero')
    @include('sections.about')
    @include('sections.work')
    @include('sections.stack')
    @include('sections.path')
    @include('sections.contact')
@endsection
