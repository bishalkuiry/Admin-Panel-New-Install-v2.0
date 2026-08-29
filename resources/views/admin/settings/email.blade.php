@extends('admin.layouts.app')
@section('title', 'Email Settings (SMTP)')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Email Settings (SMTP)</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.email-templates.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Manage Email Templates
            </a>
            <a href="{{ route('admin.settings.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Settings
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- SMTP Configuration Form -->
            <div class="card p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    SMTP Configuration
                </h3>
                
                <form action="{{ route('admin.settings.email.update') }}" method="POST" id="smtp-form">
                    @csrf
                    @method('PUT')
                    
                    <input type="hidden" name="mail_mailer" value="smtp">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mail Host</label>
                            <input type="text" name="mail_host" id="mail_host" value="{{ old('mail_host', $emailSettings['mail_host']) }}" class="input w-full" placeholder="smtp.example.com" required>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mail Port</label>
                            <input type="number" name="mail_port" id="mail_port" value="{{ old('mail_port', $emailSettings['mail_port']) }}" class="input w-full" placeholder="587" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username (Email)</label>
                            <input type="text" name="mail_username" id="mail_username" value="{{ old('mail_username', $emailSettings['mail_username']) }}" class="input w-full" placeholder="you@example.com">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="mail_password" id="mail_password" value="{{ old('mail_password', $emailSettings['mail_password']) }}" class="input w-full" placeholder="********">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
                            <select name="mail_encryption" id="mail_encryption" class="input w-full bg-white">
                                <option value="tls" {{ old('mail_encryption', $emailSettings['mail_encryption']) == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('mail_encryption', $emailSettings['mail_encryption']) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ old('mail_encryption', $emailSettings['mail_encryption']) == '' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>

                    <div class="border-t pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Sender Identity</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">From Address</label>
                                <input type="email" name="mail_from_address" id="mail_from_address" value="{{ old('mail_from_address', $emailSettings['mail_from_address']) }}" class="input w-full" placeholder="noreply@example.com" required>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">From Name</label>
                                <input type="text" name="mail_from_name" id="mail_from_name" value="{{ old('mail_from_name', $emailSettings['mail_from_name']) }}" class="input w-full" placeholder="My App Name" required>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn-primary">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Auto Configuration Widget -->
            <div class="card p-6 bg-indigo-50 border border-indigo-100">
                <h3 class="text-lg font-bold text-indigo-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Smart Auto-Fill
                </h3>
                <p class="text-sm text-indigo-700 mb-4">Enter your domain (e.g., example.com) to automatically guess SMTP settings.</p>
                
                <div class="flex gap-2">
                    <input type="text" id="auto-domain" placeholder="example.com" class="input w-full border-indigo-200 focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="button" id="btn-auto-config" class="btn-secondary bg-white text-indigo-600 border-indigo-200 hover:bg-indigo-50">
                        Auto-Detect
                    </button>
                </div>
                <p id="auto-config-status" class="text-xs mt-2 min-h-[1.5em]"></p>
            </div>

            <!-- Test Email Widget -->
            <div class="card p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Test Configuration
                </h3>
                <p class="text-sm text-gray-600 mb-4">Send a test email to verify your settings are working correctly.</p>
                
                <div class="space-y-3">
                    <input type="email" id="test-email" placeholder="recipient@example.com" class="input w-full">
                    <button type="button" id="btn-send-test" class="btn-secondary w-full">Send Test Email</button>
                </div>
                <p id="test-email-status" class="text-xs mt-2 min-h-[1.5em] font-medium"></p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto Configuration
        const btnAutoConfig = document.getElementById('btn-auto-config');
        const autoDomainInput = document.getElementById('auto-domain');
        const statusText = document.getElementById('auto-config-status');

        btnAutoConfig.addEventListener('click', function() {
            const domain = autoDomainInput.value.trim();
            if (!domain) {
                statusText.textContent = 'Please enter a domain.';
                statusText.className = 'text-xs mt-2 text-red-600';
                return;
            }

            statusText.textContent = 'Detecting settings...';
            statusText.className = 'text-xs mt-2 text-gray-500';

            fetch("{{ route('admin.settings.email.auto-configure') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ domain: domain })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fill the form
                    if (data.config.mail_host) document.getElementById('mail_host').value = data.config.mail_host;
                    if (data.config.mail_port) document.getElementById('mail_port').value = data.config.mail_port;
                    if (data.config.mail_encryption) document.getElementById('mail_encryption').value = data.config.mail_encryption;
                    
                    statusText.textContent = 'Settings applied! Please check and save.';
                    statusText.className = 'text-xs mt-2 text-green-600 font-bold';
                } else {
                    statusText.textContent = data.message || 'Could not detect settings.';
                    statusText.className = 'text-xs mt-2 text-red-600';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusText.textContent = 'An error occurred.';
                statusText.className = 'text-xs mt-2 text-red-600';
            });
        });

        // Test Email
        const btnSendTest = document.getElementById('btn-send-test');
        const testEmailInput = document.getElementById('test-email');
        const testStatus = document.getElementById('test-email-status');

        btnSendTest.addEventListener('click', function() {
            const email = testEmailInput.value.trim();
            if (!email) {
                testStatus.textContent = 'Please enter an email address.';
                testStatus.className = 'text-xs mt-2 text-red-600 font-medium';
                return;
            }

            btnSendTest.disabled = true;
            btnSendTest.textContent = 'Sending...';
            testStatus.textContent = '';

            fetch("{{ route('admin.settings.email.test') }}?email=" + encodeURIComponent(email), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                btnSendTest.disabled = false;
                btnSendTest.textContent = 'Send Test Email';
                
                if (data.success) {
                    testStatus.textContent = 'Success! Check your inbox.';
                    testStatus.className = 'text-xs mt-2 text-green-600 font-bold';
                } else {
                    testStatus.textContent = 'Failed: ' + (data.message || 'Unknown error');
                    testStatus.className = 'text-xs mt-2 text-red-600 font-medium';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btnSendTest.disabled = false;
                btnSendTest.textContent = 'Send Test Email';
                testStatus.textContent = 'Network error occurred.';
                testStatus.className = 'text-xs mt-2 text-red-600 font-medium';
            });
        });
    });
</script>
@endsection
