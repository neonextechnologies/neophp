@extends('layouts.app')

@section('content')
<h1>Welcome to NeoPhp</h1>

<div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h2>Features</h2>
    <ul style="list-style-position: inside;">
        <li>✅ Module System (Neonex/NestJS style)</li>
        <li>✅ MVC Architecture</li>
        <li>✅ Eloquent-like ORM</li>
        <li>✅ Blade Template Engine</li>
        <li>✅ Validation</li>
        <li>✅ Authentication</li>
        <li>✅ Middleware</li>
        <li>✅ Cache</li>
        <li>✅ Migration</li>
        <li>✅ CLI Generator</li>
    </ul>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
    @foreach(['Modules', 'MVC', 'ORM', 'Blade', 'Validation', 'Auth', 'Middleware', 'Cache'] as $feature)
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>{{ $feature }}</h3>
            <p>Full support ✅</p>
        </div>
    @endforeach
</div>
@endsection
