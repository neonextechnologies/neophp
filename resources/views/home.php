<?php $this->layout('layouts.app'); ?>

<h1>Welcome to NeoPhp</h1>

<div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h2>Features</h2>
    <ul style="list-style-position: inside;">
        <li>✅ Module System (like Neonex Core / NestJS)</li>
        <li>✅ Dependency Injection Container</li>
        <li>✅ Repository Pattern</li>
        <li>✅ MVC Architecture</li>
        <li>✅ View Template System</li>
        <li>✅ Eloquent-like Models</li>
        <li>✅ CLI Generator</li>
    </ul>
</div>

<div style="background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h2>Quick Start</h2>
    <pre style="background: #263238; color: #aed581; padding: 15px; border-radius: 5px; overflow-x: auto;">
# Generate a new module
php neophp generate module Product

# Use models
$user = User::find(1);
$users = User::where('status', 'active')->get();

# Use views
return view('home', ['title' => 'Home Page']);
    </pre>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3>📦 Modules</h3>
        <p>Organize code by domain with #[Module] attributes</p>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3>🎯 MVC</h3>
        <p>Traditional Model-View-Controller pattern</p>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3>💾 Models</h3>
        <p>Eloquent-like ORM with relationships</p>
    </div>
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3>👁️ Views</h3>
        <p>Template system with layouts & sections</p>
    </div>
</div>
