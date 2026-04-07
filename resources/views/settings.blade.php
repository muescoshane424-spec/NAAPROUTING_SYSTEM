@extends('layouts.app')

@section('title', 'Settings')

@section('head')
<style>
    .settings-card {
        background: var(--panel);
        border: 1px solid var(--panel-border);
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 25px;
    }

    .icon-box {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 1.4rem;
    }

    /* Section Specific Colors */
    .bg-security { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .bg-qr { background: rgba(34, 211, 238, 0.15); color: var(--accent-cyan); }
    .bg-notif { background: rgba(168, 85, 247, 0.15); color: var(--accent-purple); }
    .bg-logs { background: rgba(34, 197, 94, 0.15); color: #22c55e; }

    .form-group-custom {
        background: rgba(15, 23, 42, 0.3);
        border: 1px solid var(--panel-border);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .form-label-custom {
        margin-bottom: 0;
        font-weight: 500;
    }

    .form-control-dark {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid var(--panel-border);
        color: white;
        border-radius: 8px;
        padding: 8px 12px;
        width: 120px;
        text-align: center;
    }

    .form-control-dark:focus {
        background: rgba(15, 23, 42, 0.8);
        border-color: var(--accent-cyan);
        color: white;
        box-shadow: none;
    }

    /* Toggle Switch Styling */
    .form-check-input {
        width: 3em;
        height: 1.5em;
        cursor: pointer;
    }
    
    .form-check-input:checked {
        background-color: var(--accent-cyan);
        border-color: var(--accent-cyan);
    }

    .save-bar {
        position: sticky;
        bottom: 20px;
        background: rgba(22, 30, 49, 0.9);
        backdrop-filter: blur(10px);
        padding: 15px 25px;
        border-radius: 15px;
        border: 1px solid var(--panel-border);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        z-index: 100;
    }
</style>
@endsection

@section('content')
<div class="container-fluid pb-5">
    <div class="mb-4">
        <h2 class="fw-bold text-white">Settings</h2>
        <p class="text-dim">System configuration and preferences</p>
    </div>

    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="settings-card">
            <div class="section-header">
                <div class="icon-box bg-security">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div>
                    <h5 class="m-0 fw-bold text-white">Security Settings</h5>
                    <small class="text-dim">Password and authentication</small>
                </div>
            </div>

            <div class="form-group-custom">
                <div>
                    <div class="form-label-custom text-white">Two-Factor Authentication</div>
                    <small class="text-dim">Add an extra layer of security</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="2fa_enabled" checked>
                </div>
            </div>

            <div class="form-group-custom flex-column align-items-start">
                <div class="w-100 d-flex justify-content-between align-items-center mb-2">
                    <div class="form-label-custom text-white">Minimum Password Length</div>
                    <input type="number" class="form-control-dark" name="min_password" value="12">
                </div>
                <small class="text-dim">Recommended: 12 characters minimum</small>
            </div>

            <div class="form-group-custom">
                <div>
                    <div class="form-label-custom text-white">Session Timeout (minutes)</div>
                </div>
                <input type="number" class="form-control-dark" name="session_timeout" value="30">
            </div>
        </div>

        <div class="settings-card">
            <div class="section-header">
                <div class="icon-box bg-qr">
                    <i class="bi bi-qr-code"></i>
                </div>
                <div>
                    <h5 class="m-0 fw-bold text-white">QR Code Settings</h5>
                    <small class="text-dim">Generation and sizing preferences</small>
                </div>
            </div>

            <div class="form-group-custom">
                <div>
                    <div class="form-label-custom text-white">Auto-Generate QR Codes</div>
                    <small class="text-dim">Automatically create QR codes for new documents</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="auto_qr">
                </div>
            </div>

            <div class="form-group-custom">
                <div>
                    <div class="form-label-custom text-white">QR Code Size (pixels)</div>
                </div>
                <input type="number" class="form-control-dark" name="qr_size" value="256">
            </div>
        </div>

        <div class="settings-card">
            <div class="section-header">
                <div class="icon-box bg-notif">
                    <i class="bi bi-bell"></i>
                </div>
                <div>
                    <h5 class="m-0 fw-bold text-white">Notifications</h5>
                    <small class="text-dim">Email and system alerts</small>
                </div>
            </div>

            <div class="form-group-custom">
                <div>
                    <div class="form-label-custom text-white">Email Notifications</div>
                    <small class="text-dim">Receive email alerts for document updates</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="email_notif" checked>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <div class="section-header">
                <div class="icon-box bg-logs">
                    <i class="bi bi-database"></i>
                </div>
                <div>
                    <h5 class="m-0 fw-bold text-white">System Logs</h5>
                    <small class="text-dim">Activity logging settings</small>
                </div>
            </div>

            <div class="form-group-custom flex-column align-items-start">
                <div class="w-100 d-flex justify-content-between align-items-center mb-2">
                    <div class="form-label-custom text-white">Log Retention Period (days)</div>
                    <input type="number" class="form-control-dark" name="log_retention" value="90">
                </div>
                <small class="text-dim">Logs older than this will be automatically deleted</small>
            </div>
        </div>

        <div class="save-bar">
            <button type="button" class="btn btn-outline-secondary border-0 text-white" onclick="window.location.reload()">Cancel</button>
            <button type="submit" class="btn btn-info px-4 fw-bold" style="background: var(--accent-cyan); border: none;">Save Changes</button>
        </div>
    </form>
</div>
@endsection